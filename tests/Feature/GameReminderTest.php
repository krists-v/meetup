<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventReminder;
use App\Models\User;
use App\Notifications\GameReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class GameReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_going_players_and_the_host_get_one_reminder_about_three_hours_before(): void
    {
        Notification::fake();
        $host = User::factory()->create(['name' => 'Alex Host']);
        $player = User::factory()->create(['name' => 'Sam Player']);
        $event = $this->game($host, now()->addHours(3));
        $event->participants()->attach($host->id, ['status' => 'going']);
        $event->participants()->attach($player->id, ['status' => 'going']);

        $this->artisan('games:remind')->assertSuccessful();

        Notification::assertSentTo($player, GameReminder::class, function (GameReminder $notice) use ($event, $player): bool {
            $mail = $notice->toMail($player);

            return $mail->subject === $event->title.' is on soon'
                && $mail->greeting === 'Three hours. Then you play.'
                && $this->linesContain($mail, $event->location)
                && $this->linesContain($mail, 'Alex Host is hosting.')
                && $this->linesContain($mail, 'Sam Player')
                && $mail->actionText === 'See the game';
        });
        Notification::assertSentTo($host, GameReminder::class, function (GameReminder $notice) use ($host): bool {
            return $this->linesContain($notice->toMail($host), 'You\'re hosting.');
        });
        $this->assertSame(2, EventReminder::query()->count());

        $this->artisan('games:remind')->assertSuccessful();
        Notification::assertSentToTimes($player, GameReminder::class, 1);
        Notification::assertSentToTimes($host, GameReminder::class, 1);
    }

    public function test_it_skips_games_that_are_not_due_cancelled_or_left(): void
    {
        Notification::fake();
        $host = User::factory()->create();
        $later = User::factory()->create();
        $past = User::factory()->create();
        $left = User::factory()->create();
        $waiting = User::factory()->create();
        $quiet = User::factory()->create(['game_reminders' => false]);

        $tooSoon = $this->game($host, now()->addHours(3)->addMinute());
        $tooSoon->participants()->attach($later->id, ['status' => 'going']);

        $started = $this->game($host, now()->subMinute());
        $started->participants()->attach($past->id, ['status' => 'going']);

        $due = $this->game($host, now()->addHours(2));
        $due->participants()->attach($left->id, ['status' => 'not_going']);
        $due->participants()->attach($waiting->id, ['status' => 'waitlist']);
        $due->participants()->attach($quiet->id, ['status' => 'going']);

        $poll = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Pick a time',
            'event_date' => now()->addHour(),
            'visibility' => 'public',
            'schedule_type' => 'poll',
            'location' => 'Park pitch',
        ]);
        $poll->participants()->attach($later->id, ['status' => 'going']);

        $cancelled = $this->game($host, now()->addHours(2));
        $cancelled->participants()->attach($later->id, ['status' => 'going']);
        $cancelled->delete();

        $this->artisan('games:remind')->assertSuccessful();

        Notification::assertSentTo($host, GameReminder::class, fn (GameReminder $notice) => $notice->event->is($due));
        Notification::assertSentToTimes($host, GameReminder::class, 1);
        Notification::assertNotSentTo($later, GameReminder::class);
        Notification::assertNotSentTo($past, GameReminder::class);
        Notification::assertNotSentTo($left, GameReminder::class);
        Notification::assertNotSentTo($waiting, GameReminder::class);
        Notification::assertNotSentTo($quiet, GameReminder::class);
        $this->assertSame(1, EventReminder::query()->where('user_id', $host->id)->count());
        $this->assertSame(0, EventReminder::query()->where('user_id', $quiet->id)->count());
    }

    public function test_a_failed_send_is_tried_again_and_does_not_stop_the_command(): void
    {
        $host = User::factory()->create();
        $event = $this->game($host, now()->addHours(2));

        EventFacade::listen(MessageSending::class, function (): void {
            throw new RuntimeException('Mail is down.');
        });

        try {
            $this->artisan('games:remind')->assertSuccessful();
        } finally {
            EventFacade::forget(MessageSending::class);
        }

        $this->assertSame(0, EventReminder::query()->count());

        Notification::fake();
        $this->artisan('games:remind')->assertSuccessful();
        Notification::assertSentToTimes($host, GameReminder::class, 1);
        $this->assertSame(1, EventReminder::query()->count());
    }

    public function test_reminders_are_scheduled_every_minute(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($scheduled) => str_contains((string) $scheduled->command, 'games:remind'));

        $this->assertNotNull($event);
        $this->assertSame('* * * * *', $event->expression);
    }

    public function test_the_profile_reminder_toggle_defaults_on_and_can_be_saved(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->fresh()->game_reminders);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Remind me before a game')
            ->assertSee('About three hours before. You\'ll get the time, the place, and who\'s in.', false);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'game_reminders' => '0',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertFalse($user->fresh()->game_reminders);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'game_reminders' => '1',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertTrue($user->fresh()->game_reminders);
    }

    private function game(User $host, \DateTimeInterface $when): Event
    {
        return Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Thursday padel',
            'event_date' => $when,
            'location' => 'Park pitch',
            'visibility' => 'public',
            'max_participants' => 4,
        ]);
    }

    private function linesContain(MailMessage $mail, string $needle): bool
    {
        return collect($mail->introLines)->contains(fn (string $line) => str_contains($line, $needle));
    }
}
