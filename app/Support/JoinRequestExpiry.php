<?php

namespace App\Support;

use App\Models\EventUser;
use App\Notifications\PlayerNotice;

class JoinRequestExpiry
{
    /**
     * Expire requests the host has not answered. The cutoff is 24 hours after
     * the request, or 2 hours before the game, whichever comes first.
     */
    public static function expire(): int
    {
        $due = EventUser::query()
            ->with(['user', 'event'])
            ->where('status', 'requested')
            ->where(function ($query): void {
                $query->where('created_at', '<=', now()->subHours(24))
                    ->orWhereHas('event', function ($event): void {
                        $event->where('schedule_type', '!=', 'poll')
                            ->where('event_date', '<=', now()->addHours(2));
                    });
            })
            ->get();

        $expired = 0;

        foreach ($due as $row) {
            $updated = EventUser::query()
                ->whereKey($row->id)
                ->where('status', 'requested')
                ->update(['status' => 'expired']);

            if ($updated !== 1) {
                continue;
            }

            $expired++;

            if ($row->user === null || $row->event === null) {
                continue;
            }

            $row->user->notify(new PlayerNotice(
                $row->event,
                'Request expired for '.$row->event->title,
                'Request expired',
                'The host didn\'t answer in time for '.$row->event->title.'.',
            ));
        }

        return $expired;
    }
}
