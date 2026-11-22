<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class GameReminder extends Notification
{
    use Queueable;

    public function __construct(public Event $event) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->event->title.' is on soon')
            ->greeting('Three hours. Then you play.')
            ->line($this->whenLine())
            ->line($this->whoLine($notifiable))
            ->action('See the game', route('events.show', $this->event))
            ->salutation('See you on the pitch.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'message' => $this->event->title.' is on soon. '.$this->whenLine(),
        ];
    }

    public function whenLine(): string
    {
        return $this->event->whenLabel().' at '.$this->event->location.'.';
    }

    public function whoLine(object $notifiable): string
    {
        $host = $notifiable instanceof User && $this->event->isCreator($notifiable)
            ? 'You\'re hosting.'
            : ($this->event->creator->name ?? 'The host').' is hosting.';

        $names = $this->goingNames();

        $list = $names->isEmpty()
            ? 'No one is in yet.'
            : $names->join(', ', ' and ');

        return $host.' '.$this->event->playersLabel().': '.$list;
    }

    /**
     * @return Collection<int, string>
     */
    private function goingNames(): Collection
    {
        $people = $this->event->relationLoaded('goingParticipants')
            ? $this->event->goingParticipants
            : $this->event->goingParticipants()->get();

        return $people->pluck('name')->filter(fn ($name) => is_string($name) && $name !== '')->values();
    }
}
