<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventTimeOption;
use App\Models\EventTimeVote;
use App\Models\User;
use App\Support\GameCalendar;
use App\Support\GameFilters;
use App\Support\LinkPreview;
use App\Support\PendingJoin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EventController extends Controller
{
    public function home(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $weekendGames = (new GameFilters(null, 'weekend', null, null, null, null))
            ->apply(Event::query())
            ->with('creator')
            ->withCount(['goingParticipants', 'timeOptions'])
            ->orderBy('event_date')
            ->limit(6)
            ->get();

        return view('welcome', [
            'weekendGames' => $weekendGames,
        ]);
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $involved = Event::query()
            ->upcoming()
            ->with([
                'creator',
                'participants' => function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                },
            ])
            ->withCount(['goingParticipants', 'timeOptions'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('participants', function ($query) use ($user) {
                        $query->where('users.id', $user->id);
                    });
            })
            ->orderBy('event_date')
            ->get();

        $visible = $involved->filter(function (Event $event) use ($user) {
            if ($event->user_id === $user->id) {
                return true;
            }

            return in_array($event->participants->first()?->pivot->status, ['going', 'maybe', 'waitlist', 'invited'], true);
        })->values();

        $nextGame = $visible->first(function (Event $event) use ($user) {
            return $event->user_id === $user->id
                || $event->participants->first()?->pivot->status === 'going';
        });

        $rest = $visible->reject(fn (Event $event) => $nextGame && $event->is($nextGame))->values();

        $filters = GameFilters::fromRequest($request);

        $openGames = $filters->apply(Event::query())
            ->with('creator')
            ->withCount(['goingParticipants', 'timeOptions'])
            ->whereNotIn('id', $involved->pluck('id'))
            ->orderBy('event_date')
            ->get();

        return view('dashboard', [
            'nextGame' => $nextGame,
            'hosting' => $rest->filter(fn (Event $event) => $event->user_id === $user->id)->values(),
            'joined' => $rest->reject(fn (Event $event) => $event->user_id === $user->id)->values(),
            'openGames' => $openGames,
            'filters' => $filters,
            'hasGames' => $visible->isNotEmpty(),
        ]);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $filters = GameFilters::fromRequest($request);
        $level = $filters->levelFromProfile($request);

        if ($level !== null) {
            return redirect()->route('games.index', $filters->parameters(['level' => $level]));
        }

        $events = $filters->apply(Event::query())
            ->with('creator')
            ->withCount(['goingParticipants', 'timeOptions'])
            ->orderBy('event_date')
            ->get();

        return view('games.index', [
            'events' => $events,
            'filters' => $filters,
        ]);
    }

    public function join(Request $request, Event $event): RedirectResponse
    {
        $token = $request->input('token');
        $user = $request->user();

        if ($user === null) {
            abort_unless($event->visibility === 'public' || $event->matchesInviteToken($token), 404);

            PendingJoin::store($request, $event, is_string($token) ? $token : null);
            redirect()->setIntendedUrl(
                $event->matchesInviteToken($token)
                    ? route('events.invite', $event->invite_token)
                    : route('events.show', $event)
            );

            return redirect()->route($request->input('account') === 'new' ? 'register' : 'login');
        }

        abort_unless(
            $event->visibility === 'public'
                || $event->isCreator($user)
                || $event->matchesInviteToken($token)
                || $event->participants()->where('users.id', $user->id)->exists(),
            403,
        );

        if ($event->matchesInviteToken($token)) {
            $this->rememberInvite($event, $user);
        }

        return PendingJoin::markGoing($event, $user);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('events.create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['options']);
        $scheduleType = $data['schedule_type'] ?? 'fixed';
        $data['schedule_type'] = $scheduleType;
        $data['user_id'] = $request->user()->id;

        $options = collect($request->input('options', []))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => Carbon::parse($value))
            ->unique(fn (Carbon $date) => $date->format('Y-m-d H:i'))
            ->values();

        if ($scheduleType === 'poll') {
            $data['event_date'] = $options->max();
        }

        $event = Event::create($data);

        if ($scheduleType === 'poll') {
            foreach ($options as $position => $startsAt) {
                $event->timeOptions()->create([
                    'starts_at' => $startsAt,
                    'position' => $position,
                ]);
            }
        }

        return redirect()
            ->route('events.show', $event)
            ->with('status', $scheduleType === 'poll'
                ? 'Game\'s on. Send the link so people can pick a time.'
                : 'Game\'s on. Send the link to someone who\'d be up for it.');
    }

    public function show(Request $request, Event $event): View
    {
        $user = $request->user();

        if ($user === null) {
            $token = $request->query('token');

            return $this->preview(
                $request,
                $event,
                $event->visibility === 'public' || $event->matchesInviteToken($token),
                $event->matchesInviteToken($token) ? $token : null,
            );
        }

        if ($event->matchesInviteToken($request->query('token'))) {
            $this->rememberInvite($event, $user);
        }

        $this->authorize('view', $event);

        $request->user()->unreadNotifications
            ->filter(fn ($notice) => (int) ($notice->data['event_id'] ?? 0) === $event->id)
            ->each->markAsRead();
        $request->user()->unsetRelation('unreadNotifications');

        $event->load(['creator', 'participants', 'comments.user', 'timeOptions.votes.user']);

        return view('events.show', [
            'event' => $event,
            'userStatus' => $event->getUserStatus($request->user()),
            'preview' => LinkPreview::forEvent($event, true),
        ]);
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('events.edit', ['event' => $event]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $previousLimit = $event->max_participants;

        $event->update($request->validated());
        $event->refresh();

        $raised = $event->max_participants === null
            ? $previousLimit !== null
            : $previousLimit !== null && $event->max_participants > $previousLimit;

        if ($raised) {
            $event->promoteWaitlist();
        }

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Saved.');
    }

    public function repeat(Event $event): RedirectResponse
    {
        $this->authorize('update', $event);
        abort_if($event->isPoll(), 403);

        $copy = $event->replicate(['invite_token']);
        $copy->event_date = $event->event_date->copy()->addDays(7);
        $copy->invite_token = null;
        $copy->save();
        $copy->participants()->attach($event->user_id, ['status' => 'going']);

        return redirect()
            ->route('events.edit', $copy)
            ->with('status', 'Next week is ready. Change anything, then save.');
    }

    public function calendar(Request $request, Event $event): Response
    {
        abort_if($event->isPoll(), 404);

        $user = $request->user();
        $allowed = $event->visibility === 'public'
            || $event->matchesInviteToken($request->query('token'))
            || ($user !== null && ($event->isCreator($user) || $event->participants()->where('users.id', $user->id)->exists()));

        abort_unless($allowed, $user === null ? 404 : 403);

        $calendar = GameCalendar::for($event);

        return response($calendar->ics(), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="varza-game.ics"',
        ]);
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'That game is cancelled.');
    }

    public function invite(Request $request, string $token): RedirectResponse|View
    {
        $event = Event::where('invite_token', $token)->firstOrFail();

        if ($request->user() === null) {
            return $this->preview($request, $event, true, $token);
        }

        $this->rememberInvite($event, $request->user());

        return redirect()->route('events.show', $event);
    }

    public function rsvp(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('view', $event);

        if ($event->isPoll()) {
            return back()->withErrors([
                'status' => 'Pick a time first. Then say you\'re in.',
            ]);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:going,maybe,not_going,waitlist'],
        ]);

        $status = $validated['status'];
        $current = $event->getUserStatus($request->user());

        if ($status === 'waitlist' && $current === 'going') {
            return back();
        }

        if ($status === 'going' && $current !== 'going' && $event->hasReachedCapacity()) {
            return back()
                ->withErrors([
                    'status' => 'It\'s full. Join the waitlist, or say maybe.',
                ])
                ->withInput();
        }

        if ($status === 'waitlist' && ! $event->hasReachedCapacity()) {
            $status = 'going';
        }

        if ($current === $status) {
            return back();
        }

        $event->participants()->syncWithoutDetaching([
            $request->user()->id => ['status' => $status],
        ]);

        if ($status === 'waitlist' && $current !== 'waitlist') {
            $event->participants()->updateExistingPivot($request->user()->id, [
                'created_at' => now(),
            ]);
        }

        if ($current === 'going' && $status !== 'going') {
            $event->promoteWaitlist();
        }

        $message = 'Saved.';
        $joined = false;

        if ($status === 'going' && $current !== 'going') {
            $message = "You're in.";
            $joined = true;
        } elseif ($status === 'waitlist') {
            $message = "You're on the waitlist. We'll move you up if a spot opens.";
        }

        return back()->with('status', $message)->with('joined', $joined);
    }

    public function vote(Request $request, Event $event, EventTimeOption $option): RedirectResponse
    {
        $this->authorize('view', $event);
        abort_unless($option->event_id === $event->id, 404);
        abort_unless($event->isPoll(), 403);

        if ($option->hasPassed()) {
            return back()->withErrors([
                'vote' => 'This time has passed.',
            ]);
        }

        $validated = $request->validate([
            'vote' => ['required', 'in:yes,maybe,no'],
        ]);

        $existing = EventTimeVote::query()
            ->where('event_time_option_id', $option->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && $existing->vote === $validated['vote']) {
            return back();
        }

        EventTimeVote::query()->updateOrCreate(
            [
                'event_time_option_id' => $option->id,
                'user_id' => $request->user()->id,
            ],
            ['vote' => $validated['vote']],
        );

        return back()->with('status', 'Vote\'s in.')->with('voted', $option->id);
    }

    public function lock(Event $event, EventTimeOption $option): RedirectResponse
    {
        $this->authorize('update', $event);
        abort_unless($option->event_id === $event->id, 404);
        abort_unless($event->isPoll(), 403);

        if ($option->hasPassed()) {
            return back()->withErrors([
                'option' => 'This time has passed.',
            ]);
        }

        $yesVoterIds = $option->votes()
            ->where('vote', 'yes')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('user_id');

        $event->update([
            'event_date' => $option->starts_at,
            'schedule_type' => 'fixed',
        ]);

        foreach ($yesVoterIds as $userId) {
            $voter = User::query()->find($userId);

            if ($voter === null || $event->getUserStatus($voter) === 'going') {
                continue;
            }

            if ($event->hasReachedCapacity()) {
                break;
            }

            $event->participants()->syncWithoutDetaching([
                $voter->id => ['status' => 'going'],
            ]);
        }

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Time\'s set. People who said yes are in, while there\'s room.');
    }

    private function rememberInvite(Event $event, User $user): void
    {
        if (! $event->isCreator($user) && $event->getUserStatus($user) === null) {
            $event->participants()->attach($user->id, ['status' => 'invited']);
        }
    }

    private function preview(Request $request, Event $event, bool $revealed, ?string $joinToken = null): View
    {
        redirect()->setIntendedUrl($request->fullUrl());

        return view('events.preview', [
            'preview' => LinkPreview::forEvent($event, $revealed),
            'event' => $revealed ? $event : null,
            'joinToken' => $joinToken,
        ]);
    }
}
