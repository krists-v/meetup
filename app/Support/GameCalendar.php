<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Carbon;

class GameCalendar
{
    public function __construct(public Event $event) {}

    public static function for(Event $event): self
    {
        return new self($event);
    }

    public function timezone(): string
    {
        return (string) config('app.timezone', 'Europe/Riga');
    }

    public function startsAt(): Carbon
    {
        return $this->event->event_date->copy()->timezone($this->timezone());
    }

    public function endsAt(): Carbon
    {
        return $this->startsAt()->copy()->addHours(2);
    }

    public function googleUrl(): string
    {
        $query = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $this->event->title,
            'dates' => $this->startsAt()->format('Ymd\THis').'/'.$this->endsAt()->format('Ymd\THis'),
            'ctz' => $this->timezone(),
            'location' => $this->event->location,
            'details' => $this->event->sportLabel().'. Come play.',
        ]);

        return 'https://calendar.google.com/calendar/render?'.$query;
    }

    public function ics(): string
    {
        $timezone = $this->timezone();
        $start = $this->startsAt()->format('Ymd\THis');
        $end = $this->endsAt()->format('Ymd\THis');
        $stamp = now()->utc()->format('Ymd\THis\Z');
        $uid = 'event-'.$this->event->id.'-'.$this->event->invite_token.'@varza';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Varza//Games//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
        ];

        if ($timezone === 'Europe/Riga') {
            array_push($lines, ...$this->rigaTimezone());
            $startLine = 'DTSTART;TZID=Europe/Riga:'.$start;
            $endLine = 'DTEND;TZID=Europe/Riga:'.$end;
        } else {
            $startLine = 'DTSTART:'.$this->startsAt()->utc()->format('Ymd\THis\Z');
            $endLine = 'DTEND:'.$this->endsAt()->utc()->format('Ymd\THis\Z');
        }

        array_push($lines,
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$stamp,
            $startLine,
            $endLine,
            'SUMMARY:'.$this->escape($this->event->title),
            'LOCATION:'.$this->escape((string) $this->event->location),
            'DESCRIPTION:'.$this->escape($this->event->sportLabel().'. Come play.'),
            'END:VEVENT',
            'END:VCALENDAR',
        );

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * @return list<string>
     */
    private function rigaTimezone(): array
    {
        return [
            'BEGIN:VTIMEZONE',
            'TZID:Europe/Riga',
            'X-LIC-LOCATION:Europe/Riga',
            'BEGIN:DAYLIGHT',
            'TZOFFSETFROM:+0200',
            'TZOFFSETTO:+0300',
            'TZNAME:EEST',
            'DTSTART:19700329T030000',
            'RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU',
            'END:DAYLIGHT',
            'BEGIN:STANDARD',
            'TZOFFSETFROM:+0300',
            'TZOFFSETTO:+0200',
            'TZNAME:EET',
            'DTSTART:19701025T040000',
            'RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU',
            'END:STANDARD',
            'END:VTIMEZONE',
        ];
    }

    private function escape(string $value): string
    {
        return str_replace(
            ['\\', "\r\n", "\n", ',', ';'],
            ['\\\\', '\\n', '\\n', '\\,', '\\;'],
            $value,
        );
    }
}
