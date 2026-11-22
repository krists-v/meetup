<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Notifications\PlayerNotice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function confirm(Event $event, User $user): RedirectResponse
    {
        $this->authorize('roster', $event);
        abort_unless($event->getUserStatus($user) === 'requested', 404);

        if ($event->hasReachedCapacity()) {
            return back()->withErrors([
                'roster' => 'It\'s full.',
            ]);
        }

        $event->participants()->updateExistingPivot($user->id, [
            'status' => 'going',
            'attended' => null,
        ]);

        $user->notify(new PlayerNotice(
            $event,
            'You\'re in for '.$event->title,
            'You\'re in',
            'The host said you\'re in for '.$event->title.'.',
        ));

        return back()->with('status', 'They\'re in.');
    }

    public function decline(Event $event, User $user): RedirectResponse
    {
        $this->authorize('roster', $event);
        abort_unless($event->getUserStatus($user) === 'requested', 404);

        $event->participants()->detach($user->id);

        $user->notify(new PlayerNotice(
            $event,
            'Not this time for '.$event->title,
            'Not this time',
            'The host passed on '.$event->title.'.',
        ));

        return back()->with('status', 'Request declined.');
    }

    public function remove(Event $event, User $user): RedirectResponse
    {
        $this->authorize('roster', $event);
        abort_unless($event->getUserStatus($user) === 'going', 404);
        abort_if($event->isCreator($user), 403);

        if (! $event->isPoll() && $event->event_date->lte(now())) {
            return back()->withErrors([
                'roster' => 'The game has started.',
            ]);
        }

        $event->participants()->updateExistingPivot($user->id, [
            'status' => 'not_going',
            'attended' => null,
        ]);
        $event->promoteWaitlist();

        $user->notify(new PlayerNotice(
            $event,
            'You\'re off '.$event->title,
            'You\'re off the list',
            'The host took you off '.$event->title.'.',
        ));

        return back()->with('status', 'They\'re off the list.');
    }

    public function attendance(Request $request, Event $event, User $user): RedirectResponse
    {
        $this->authorize('roster', $event);
        abort_unless($event->getUserStatus($user) === 'going', 404);

        if ($event->isPoll() || $event->event_date->gt(now())) {
            return back()->withErrors([
                'roster' => 'The game hasn\'t started.',
            ]);
        }

        $validated = $request->validate([
            'attended' => ['required', 'boolean'],
        ]);

        $event->participants()->updateExistingPivot($user->id, [
            'attended' => $request->boolean('attended'),
        ]);

        return back()->with('status', $validated['attended'] ? 'Marked as showed up.' : 'Marked as a no-show.');
    }
}
