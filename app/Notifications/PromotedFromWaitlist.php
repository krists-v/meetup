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
            ->subject('A spot opened on '.$this->event->title)
            ->greeting('You are in')
            ->line($this->event->title.' has a free spot. You were first on the waitlist, so you are going.')
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
            'message' => 'A spot opened. You are going to '.$this->event->title.'.',
        ];
    }
}
