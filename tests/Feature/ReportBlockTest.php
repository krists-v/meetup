<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Event;
use App\Models\Report;
use App\Models\User;
use App\Notifications\PlayerReported;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;
use Tests\TestCase;

class ReportBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_report_is_stored_and_emailed_when_contact_email_is_set(): void
    {
        Notification::fake();
        config(['app.contact_email' => 'safety@example.com']);

        $reporter = User::factory()->create(['name' => 'Sam Reporter']);
        $player = User::factory()->create(['name' => 'Casey Court']);

        $this->actingAs($reporter)
            ->post(route('players.report', $player), [
                'reason' => 'spam',
                'note' => 'They kept posting the same pitch.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Report sent.');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reported_id' => $player->id,
            'reason' => 'spam',
            'note' => 'They kept posting the same pitch.',
            'status' => Report::OPEN,
        ]);

        Notification::assertSentOnDemand(PlayerReported::class, function (PlayerReported $notice, array $channels, object $notifiable) use ($player): bool {
            $mail = $notice->toMail($notifiable);

            return $notifiable->routes['mail'] === 'safety@example.com'
                && $mail instanceof MailMessage
                && $mail->subject === 'A player was reported'
                && $mail->actionText === 'See the player'
                && $mail->actionUrl === route('players.show', $player)
                && in_array('Reason: Spam.', $mail->introLines, true)
                && in_array('They kept posting the same pitch.', $mail->introLines, true)
                && ! str_contains(implode(' ', $mail->introLines), '!');
        });
    }

    public function test_a_blank_contact_email_still_stores_the_report(): void
    {
        Notification::fake();
        config(['app.contact_email' => '']);

        $reporter = User::factory()->create();
        $player = User::factory()->create();

        $this->actingAs($reporter)
            ->post(route('players.report', $player), ['reason' => 'other'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Report sent.');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reported_id' => $player->id,
            'reason' => 'other',
            'note' => null,
            'status' => Report::OPEN,
        ]);
        Notification::assertNothingSent();
    }

    public function test_a_mail_failure_still_stores_the_report(): void
    {
        config(['app.contact_email' => 'safety@example.com']);
        $reporter = User::factory()->create();
        $player = User::factory()->create();

        EventFacade::listen(MessageSending::class, function (): void {
            throw new RuntimeException('mail down');
        });

        try {
            $this->actingAs($reporter)
                ->post(route('players.report', $player), [
                    'reason' => 'rude',
                    'note' => 'Kept at it.',
                ])
                ->assertRedirect()
                ->assertSessionHas('status', 'Report sent.');
        } finally {
            EventFacade::forget(MessageSending::class);
        }

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reported_id' => $player->id,
            'reason' => 'rude',
        ]);
    }

    public function test_a_second_open_report_for_the_same_player_is_rejected(): void
    {
        Notification::fake();
        $reporter = User::factory()->create();
        $player = User::factory()->create();

        $this->actingAs($reporter)
            ->post(route('players.report', $player), ['reason' => 'no_show'])
            ->assertRedirect();

        $this->actingAs($reporter)
            ->post(route('players.report', $player), ['reason' => 'spam'])
            ->assertRedirect()
            ->assertSessionHasErrors('report');

        $this->assertSame(1, Report::query()->count());
        $this->assertSame('no_show', Report::query()->first()->reason);
    }

    public function test_an_invalid_reason_is_rejected(): void
    {
        $reporter = User::factory()->create();
        $player = User::factory()->create();

        $this->actingAs($reporter)
            ->post(route('players.report', $player), ['reason' => 'bored'])
            ->assertSessionHasErrors('reason');

        $this->assertSame(0, Report::query()->count());
    }

    public function test_you_cannot_report_or_block_yourself(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('players.report', $user), ['reason' => 'spam'])
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('players.block', $user))
            ->assertForbidden();

        $this->assertSame(0, Report::query()->count());
        $this->assertSame(0, Block::query()->count());
    }

    public function test_guests_cannot_report(): void
    {
        $player = User::factory()->create();

        $this->post(route('players.report', $player), ['reason' => 'spam'])
            ->assertRedirect(route('login'));

        $this->assertSame(0, Report::query()->count());
    }

    public function test_reports_are_rate_limited(): void
    {
        Notification::fake();
        RateLimiter::for('reports', fn () => Limit::perMinute(2)->by('report-test'));

        $reporter = User::factory()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $this->actingAs($reporter)->post(route('players.report', $first), ['reason' => 'spam'])->assertRedirect();
        $this->actingAs($reporter)->post(route('players.report', $second), ['reason' => 'spam'])->assertRedirect();
        $this->actingAs($reporter)
            ->post(route('players.report', $third), ['reason' => 'spam'])
            ->assertStatus(429)
            ->assertSee('Too many tries');

        $this->assertNull(Report::query()->where('reported_id', $third->id)->first());
    }

    public function test_blocked_comments_are_hidden_only_for_the_blocker(): void
    {
        $host = User::factory()->create();
        $blocker = User::factory()->create();
        $author = User::factory()->create(['name' => 'Loud Lou']);
        $event = $this->game($host);
        $event->comments()->create([
            'user_id' => $author->id,
            'body' => 'Secret note from the blocked player.',
        ]);

        $this->actingAs($blocker)
            ->post(route('players.block', $author))
            ->assertRedirect()
            ->assertSessionHas('status', 'Blocked.');

        $this->actingAs($blocker)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertDontSee('Secret note from the blocked player.')
            ->assertSee('No notes yet. Say if you\'re bringing a ball.', false);

        $this->actingAs($host)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Secret note from the blocked player.');
    }

    public function test_a_blocked_player_cannot_join_or_be_added_to_the_blockers_game(): void
    {
        $host = User::factory()->create();
        $player = User::factory()->create();
        $event = $this->game($host);

        Block::query()->create([
            'blocker_id' => $host->id,
            'blocked_id' => $player->id,
        ]);

        $this->actingAs($player)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertRedirect()
            ->assertSessionHasErrors('status');
        $this->assertNull($event->fresh()->getUserStatus($player));

        $this->actingAs($player)
            ->post(route('events.join', $event))
            ->assertRedirect()
            ->assertSessionHasErrors('status');
        $this->assertNull($event->fresh()->getUserStatus($player));

        $this->actingAs($player)
            ->post(route('events.rsvp', $event), ['status' => 'maybe'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Saved.');
        $this->assertSame('maybe', $event->fresh()->getUserStatus($player));

        $event->participants()->updateExistingPivot($player->id, ['status' => 'requested']);

        $this->actingAs($host)
            ->post(route('events.roster.confirm', [$event, $player]))
            ->assertRedirect()
            ->assertSessionHasErrors('roster');
        $this->assertSame('requested', $event->fresh()->getUserStatus($player));
    }

    public function test_the_blocker_cannot_join_a_game_the_blocked_player_hosts(): void
    {
        $blocker = User::factory()->create();
        $player = User::factory()->create();
        $event = $this->game($player);

        Block::query()->create([
            'blocker_id' => $blocker->id,
            'blocked_id' => $player->id,
        ]);

        $this->actingAs($blocker)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertSessionHasErrors('status');
        $this->assertNull($event->fresh()->getUserStatus($blocker));

        $event->participants()->attach($blocker->id, ['status' => 'requested']);

        $this->actingAs($player)
            ->post(route('events.roster.confirm', [$event, $blocker]))
            ->assertSessionHasErrors('roster');
        $this->assertSame('requested', $event->fresh()->getUserStatus($blocker));
    }

    public function test_unblocking_from_profile_settings_lets_them_join_again(): void
    {
        $host = User::factory()->create();
        $player = User::factory()->create(['name' => 'Casey Court']);
        $event = $this->game($host);

        $this->actingAs($host)->post(route('players.block', $player))->assertRedirect();

        $this->actingAs($host)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Casey Court')
            ->assertSee('Unblock')
            ->assertDontSee('No one is blocked.');

        $this->actingAs($player)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertSessionHasErrors('status');

        $this->actingAs($host)
            ->delete(route('players.unblock', $player))
            ->assertRedirect()
            ->assertSessionHas('status', 'Unblocked.');

        $this->actingAs($host)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('No one is blocked.')
            ->assertDontSee('Casey Court');

        $this->actingAs($player)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertRedirect()
            ->assertSessionHas('status', 'You\'re in.');
        $this->assertSame('going', $event->fresh()->getUserStatus($player));
    }

    public function test_report_and_block_are_offered_on_the_profile_going_list_and_comments(): void
    {
        $viewer = User::factory()->create(['name' => 'Viewer Vic']);
        $player = User::factory()->create(['name' => 'Casey Court']);
        $event = $this->game($viewer);
        $event->participants()->attach($viewer->id, ['status' => 'going']);
        $event->participants()->attach($player->id, ['status' => 'going']);
        $event->comments()->create([
            'user_id' => $player->id,
            'body' => 'I can bring a ball.',
        ]);

        $this->actingAs($viewer)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee(route('players.show', $player), false)
            ->assertSee(route('players.block', $player), false)
            ->assertSee(route('players.report', $player), false)
            ->assertSee('Rude or abusive')
            ->assertSee('Fake profile')
            ->assertDontSee(route('players.block', $viewer), false);

        $this->actingAs($viewer)
            ->get(route('players.show', $player))
            ->assertOk()
            ->assertSee('Casey Court')
            ->assertSee('name="reason"', false)
            ->assertSee('name="note"', false)
            ->assertSee('No-show');

        $this->actingAs($viewer)
            ->get(route('players.show', $viewer))
            ->assertOk()
            ->assertSee('This is you.')
            ->assertDontSee(route('players.report', $viewer), false);
    }

    private function game(User $host): Event
    {
        return Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Thursday padel',
            'event_date' => now()->addDay(),
            'visibility' => 'public',
            'max_participants' => 8,
        ]);
    }
}
