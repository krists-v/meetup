<?php

namespace App\Support;

use App\Models\Event;
use App\Models\EventReminder;
use App\Models\User;
use App\Notifications\GameReminder;
use Throwable;

class GameReminders
{
    /**
     * One reminder, about three hours before a game starts, for each person
     * who is going and for the host. A stored row keeps it to one send.
     */
    public static function send(): int
    {
        $events = Event::query()
            ->with(['creator', 'goingParticipants'])
            ->where('schedule_type', '!=', 'poll')
            ->where('event_date', '>', now())
            ->where('event_date', '<=', now()->addHours(3))
            ->get();

        $sent = 0;

        foreach ($events as $event) {
            $sent += self::remind($event);
        }

        return $sent;
    }

    private static function remind(Event $event): int
    {
        $people = $event->goingParticipants
            ->push($event->creator)
            ->filter(fn ($person) => $person instanceof User)
            ->unique('id')
            ->values();

        $sent = 0;

        foreach ($people as $person) {
            if (! $person->game_reminders) {
                continue;
            }

            $already = EventReminder::query()
                ->where('event_id', $event->id)
                ->where('user_id', $person->id)
                ->exists();

            if ($already) {
                continue;
            }

            try {
                $person->notify(new GameReminder($event));
            } catch (Throwable $exception) {
                report($exception);

                continue;
            }

            EventReminder::query()->insertOrIgnore([
                'event_id' => $event->id,
                'user_id' => $person->id,
                'sent_at' => now(),
            ]);

            $sent++;
        }

        return $sent;
    }
}
