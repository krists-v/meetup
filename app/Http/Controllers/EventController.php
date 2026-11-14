<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventTimeOption;
use App\Models\EventTimeVote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EventController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $myEvents = Event::query()
            ->upcoming()
            ->with('creator')
            ->withCount(['goingParticipants', 'timeOptions'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('participants', function ($query) use ($user) {
                        $query->where('users.id', $user->id)
                            ->whereIn('event_user.status', ['going', 'maybe', 'not_going', 'invited']);
                    });
            })
            ->orderBy('event_date')
            ->get();

        $publicEvents = Event::query()
            ->public()
            ->upcoming()
            ->with('creator')
            ->withCount(['goingParticipants', 'timeOptions'])
            ->whereNotIn('id', $myEvents->pluck('id'))
            ->orderBy('event_date')
            ->get();

        return view('dashboard', [
            'myEvents' => $myEvents,
            'publicEvents' => $publicEvents,
        ]);
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
                ? 'Event created. Share the invite link so friends can vote on a time.'
                : 'Event created. Share the invite link on WhatsApp.');
    }

    public function show(Request $request, Event $event): View
    {
        $this->authorize('view', $event);

        $event->load(['creator', 'participants', 'timeOptions.votes.user']);

        return view('events.show', [
            'event' => $event,
            'userStatus' => $event->getUserStatus($request->user()),
        ]);
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('events.edit', ['event' => $event]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Event cancelled.');
    }

    public function invite(Request $request, string $token): RedirectResponse
    {
        $event = Event::where('invite_token', $token)->firstOrFail();
        $user = $request->user();

        if (! $event->isCreator($user) && $event->getUserStatus($user) === null) {
            $event->participants()->attach($user->id, ['status' => 'invited']);
        }

        return redirect()->route('events.show', $event);
    }

    public function rsvp(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('view', $event);

        if ($event->isPoll()) {
            return back()->withErrors([
                'status' => 'Choose a time before marking Going, Maybe, or Not going.',
            ]);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:going,maybe,not_going'],
        ]);

        $status = $validated['status'];
        $current = $event->getUserStatus($request->user());

        if ($status === 'going' && $current !== 'going' && $event->hasReachedCapacity()) {
            return back()
                ->withErrors([
                    'status' => 'This event is full. You can still mark Maybe or Not going.',
                ])
                ->withInput();
        }

        $event->participants()->syncWithoutDetaching([
            $request->user()->id => ['status' => $status],
        ]);

        return back()->with('status', 'RSVP saved.');
    }

    public function vote(Request $request, Event $event, EventTimeOption $option): RedirectResponse
    {
        $this->authorize('view', $event);
        abort_unless($option->event_id === $event->id, 404);
        abort_unless($event->isPoll(), 403);

        if ($option->hasPassed()) {
            return back()->withErrors([
                'vote' => 'That time has already passed.',
            ]);
        }

        $validated = $request->validate([
            'vote' => ['required', 'in:yes,maybe,no'],
        ]);

        EventTimeVote::query()->updateOrCreate(
            [
                'event_time_option_id' => $option->id,
                'user_id' => $request->user()->id,
            ],
            ['vote' => $validated['vote']],
        );

        return back()->with('status', 'Vote saved.');
    }

    public function lock(Event $event, EventTimeOption $option): RedirectResponse
    {
        $this->authorize('update', $event);
        abort_unless($option->event_id === $event->id, 404);
        abort_unless($event->isPoll(), 403);

        if ($option->hasPassed()) {
            return back()->withErrors([
                'option' => 'That time has already passed.',
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
            ->with('status', 'Time locked in. People who voted Yes are marked Going while there is room.');
    }
}
