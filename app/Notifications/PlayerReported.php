<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlayerReported extends Notification
{
    use Queueable;

    public function __construct(public Report $report) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->report->loadMissing(['reporter', 'reported']);

        $mail = (new MailMessage)
            ->subject('A player was reported')
            ->greeting('A player was reported')
            ->line($this->report->reporter->name.' reported '.$this->report->reported->name.'.')
            ->line('Reason: '.$this->report->reasonLabel().'.');

        if (filled($this->report->note)) {
            $mail->line($this->report->note);
        }

        return $mail
            ->action('See the player', route('players.show', $this->report->reported))
            ->salutation('See you on the pitch.');
    }
}
