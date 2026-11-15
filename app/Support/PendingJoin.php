<?php

namespace App\Support;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PendingJoin
{
    public static function store(Request $request, Event $event, ?string $token): void
    {
        $request->session()->put('pending_join', [
            'event_id' => $event->id,
            'token' => $token,
        ]);
    }

    public static function consume(Request $request): ?RedirectResponse
    {
        $pending = $request->session()->pull('pending_join');
        $user = $request->user();

        if (! is_array($pending) || ! $user instanceof User) {
            return null;
        }

        $event = Event::query()->find($pending['event_id'] ?? null);

        if (! $event instanceof Event) {
            return null;
        }

        $token = $pending['token'] ?? null;

        if (! self::canJoin($event, $user, $token)) {
            return null;
        }

        if ($event->matchesInviteToken($token)) {
            self::rememberInvite($event, $user);
        }

        return self::markGoing($event, $user);
    }

    public static function markGoing(Event $event, User $user): RedirectResponse
    {
        if ($event->isPoll()) {
            return redirect()
                ->route('events.show', $event)
                ->with('status', 'Pick a time first. Then say you\'re in.');
        }

        if ($event->getUserStatus($user) !== 'going') {
            if ($event->hasReachedCapacity()) {
                return redirect()
                    ->route('events.show', $event)
                    ->withErrors([
                        'status' => 'It\'s full. Join the waitlist, or say maybe.',
                    ]);
            }

            $event->participants()->syncWithoutDetaching([
                $user->id => ['status' => 'going'],
            ]);
        }

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'You\'re in.');
    }

    private static function canJoin(Event $event, User $user, mixed $token): bool
    {
        return $event->visibility === 'public'
            || $event->isCreator($user)
            || $event->matchesInviteToken($token)
            || $event->participants()->where('users.id', $user->id)->exists();
    }

    private static function rememberInvite(Event $event, User $user): void
    {
        if (! $event->isCreator($user) && $event->getUserStatus($user) === null) {
            $event->participants()->attach($user->id, ['status' => 'invited']);
        }
    }
}
