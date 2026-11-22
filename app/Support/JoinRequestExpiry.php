<?php

namespace App\Support;

use App\Models\EventUser;
use App\Notifications\PlayerNotice;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class JoinRequestExpiry
{
    /**
     * Clear due requests without sending mail. Web requests use this so a
     * slow or failed send cannot stall or fail the page.
     */
    public static function markDue(): int
    {
        return self::due(EventUser::query()->where('status', 'requested'))
            ->update(['status' => 'expired']);
    }

    /**
     * Mail players whose request has expired and remember the send. A failed
     * send leaves the row unmarked so the next run tries again.
     */
    public static function notify(): int
    {
        $rows = EventUser::query()
            ->with(['user', 'event'])
            ->where('status', 'expired')
            ->whereNull('expiry_notified_at')
            ->get();

        $sent = 0;

        foreach ($rows as $row) {
            if ($row->user === null || $row->event === null) {
                self::markNotified($row);

                continue;
            }

            try {
                $row->user->notify(new PlayerNotice(
                    $row->event,
                    'Request expired for '.$row->event->title,
                    'Request expired',
                    'The host didn\'t answer in time for '.$row->event->title.'.',
                ));
            } catch (Throwable $exception) {
                report($exception);

                continue;
            }

            self::markNotified($row);
            $sent++;
        }

        return $sent;
    }

    /**
     * Expire requests the host has not answered, then send the notices.
     * The cutoff is 24 hours after the request, or 2 hours before the game,
     * whichever comes first.
     */
    public static function expire(): int
    {
        self::markDue();

        return self::notify();
    }

    /**
     * @param  Builder<EventUser>  $query
     * @return Builder<EventUser>
     */
    private static function due(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('created_at', '<=', now()->subHours(24))
                ->orWhereHas('event', function (Builder $event): void {
                    $event->where('schedule_type', '!=', 'poll')
                        ->where('event_date', '<=', now()->addHours(2));
                });
        });
    }

    private static function markNotified(EventUser $row): void
    {
        EventUser::query()
            ->whereKey($row->id)
            ->whereNull('expiry_notified_at')
            ->update(['expiry_notified_at' => now()]);
    }
}
