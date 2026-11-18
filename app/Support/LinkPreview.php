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
            description: $description ?: 'Bored at home? See what\'s on near you and go play.',
            image: self::image('default'),
            url: $url ?: url()->current(),
        );
    }

    public static function forEvent(Event $event, bool $revealed, ?string $url = null): self
    {
        $url = $url ?: url()->full();

        if (! $revealed) {
            return new self(
                title: 'Someone wants you in a game',
                description: 'Log in to see when and where.',
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
        $date = $event->event_date->timezone(config('app.timezone'));
        $when = $event->isPoll()
            ? 'time still open'
            : $date->format('l j F').' at '.$date->format('H:i');

        $places = $event->attendanceLabel();

        return $event->sportLabel().', '.$when.', '.$event->location.'. Come play.'.($places === '' ? '' : ' '.$places.'.');
    }
}
