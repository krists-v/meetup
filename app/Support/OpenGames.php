<?php

namespace App\Support;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OpenGames
{
    /**
     * Soonest public game today or this weekend that still has a free spot.
     * Skips past games, full games, polls, games this person is hosting, and games they have already joined.
     */
    public static function soonest(?User $user): ?Event
    {
        $now = now();
        [$weekendStart, $weekendEnd] = GameFilters::weekendRange($now);

        return self::open($user)
            ->where(function (Builder $query) use ($now, $weekendStart, $weekendEnd) {
                $query->whereBetween('event_date', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
                    ->orWhereBetween('event_date', [$weekendStart, $weekendEnd]);
            })
            ->orderBy('event_date')
            ->first();
    }

    /**
     * Today's public games that have not started and still have a free spot.
     *
     * @return Collection<int, Event>
     */
    public static function startingSoon(): Collection
    {
        $now = now();

        return self::open(null)
            ->whereBetween('event_date', [$now, $now->copy()->endOfDay()])
            ->orderBy('event_date')
            ->get();
    }

    public static function withCards(Builder $query): Builder
    {
        return $query
            ->with([
                'creator',
                'goingParticipants' => fn ($players) => $players->orderByPivot('id'),
            ])
            ->withCount(['goingParticipants', 'timeOptions']);
    }

    /**
     * @param  iterable<int, Event>  $events
     */
    public static function hydrate(iterable $events): void
    {
        User::loadAttendance(collect($events)->map(fn (Event $event) => $event->creator));
    }

    /**
     * @return Builder<Event>
     */
    private static function open(?User $except): Builder
    {
        return Event::query()
            ->public()
            ->where(function (Builder $query) {
                $query->whereNull('schedule_type')
                    ->orWhere('schedule_type', '!=', 'poll');
            })
            ->where('event_date', '>=', now())
            ->when($except, function (Builder $query) use ($except) {
                $query->where('user_id', '!=', $except->id)
                    ->whereDoesntHave('participants', fn ($q) => $q->whereKey($except->id));
            })
            ->where(function (Builder $query) {
                $query->whereNull('max_participants')
                    ->orWhereRaw(
                        'max_participants > (select count(*) from event_user where event_user.event_id = events.id and event_user.status = ?)',
                        ['going'],
                    );
            });
    }
}
