<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $myEvents = Event::query()
            ->upcoming()
            ->with('creator')
            ->withCount('goingParticipants')
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
            ->withCount('goingParticipants')
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
        $event = Event::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'Event created. Share the invite link on WhatsApp.');
    }

    public function show(Request $request, Event $event): View
    {
        $this->authorize('view', $event);

        $event->load(['creator', 'participants']);

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
}
