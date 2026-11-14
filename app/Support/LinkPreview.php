<?php

namespace App\Support;

use App\Models\Event;

class LinkPreview
{
    public function __construct(
        public string $title,
        public string $description,
        public string $image,
        public string $url,
    ) {}

    public static function page(?string $title = null, ?string $description = null, ?string $url = null): self
    {
        return new self(
            title: $title ?: 'Varza',
            description: $description ?: 'Get the crew together. Pick a time, lock the pitch, play.',
            image: self::image('default'),
            url: $url ?: url()->current(),
        );
    }

    public static function forEvent(Event $event, bool $revealed, ?string $url = null): self
    {
        $url = $url ?: url()->full();

        if (! $revealed) {
            return new self(
                title: "You're invited to a game on Varza",
                description: 'Log in to see the sport, the time, and the place.',
                image: self::image('default'),
                url: $url,
            );
        }

        return new self(
            title: $event->title,
            description: self::description($event),
            image: self::image($event->sportSelection()),
            url: $url,
        );
    }

    public static function image(string $name): string
    {
        return asset('images/og/'.$name.'.png');
    }

    private static function description(Event $event): string
    {
        $when = $event->isPoll()
            ? 'Vote on a time'
            : $event->event_date->format('l j F Y, H:i');

        return $event->sportLabel().', '.$when.', '.$event->location.'. '.self::spots($event).'.';
    }

    private static function spots(Event $event): string
    {
        if ($event->max_participants === null) {
            return 'No limit on spots';
        }

        if ($event->going_participants_count === null) {
            $event->loadCount('goingParticipants');
        }

        $left = max(0, $event->max_participants - (int) $event->going_participants_count);

        return $left === 1 ? '1 spot left' : $left.' spots left';
    }
}
