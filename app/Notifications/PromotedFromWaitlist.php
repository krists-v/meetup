<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromotedFromWaitlist extends Notification
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
            ->subject('You\'re in for '.$this->event->title)
            ->greeting('A spot opened')
            ->line('You\'re in. A spot opened for '.$this->event->title.'.')
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
            'message' => 'You\'re in. A spot opened for '.$this->event->title.'.',
        ];
    }
}
