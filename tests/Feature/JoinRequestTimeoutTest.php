<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use App\Notifications\PlayerNotice;
use App\Notifications\PromotedFromWaitlist;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class JoinRequestTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_request_expires_at_24_hours_or_2_hours_before_the_game(): void
    {
        Notification::fake();
        $host = User::factory()->create();
        $old = User::factory()->create();
        $soon = User::factory()->create();
        $waiting = User::factory()->create();

        $later = $this->game($host, now()->addDays(3));
        $this->request($later, $old, now()->subHours(24));
        $later->participants()->attach($waiting->id, ['status' => 'waitlist']);

        $starting = $this->game($host, now()->addHours(2));
        $this->request($starting, $soon, now()->subMinute());

        $this->artisan('join-requests:expire')->assertSuccessful();

        $this->assertSame('expired', $later->fresh()->getUserStatus($old));
        $this->assertSame('expired', $starting->fresh()->getUserStatus($soon));
        $this->assertSame('waitlist', $later->fresh()->getUserStatus($waiting));
        $this->assertSame(0, $later->fresh()->goingParticipants()->count());

        Notification::assertSentTo($old, PlayerNotice::class, function (PlayerNotice $notice) use ($later): bool {
            return $notice->event->is($later)
                && $notice->greetingLine === 'Request expired'
                && str_contains($notice->bodyLine, $later->title);
        });
        Notification::assertSentTo($soon, PlayerNotice::class);
        Notification::assertNotSentTo($waiting, PromotedFromWaitlist::class);

        $this->artisan('join-requests:expire')->assertSuccessful();
        Notification::assertSentToTimes($old, PlayerNotice::class, 1);
    }

    public function test_a_request_stays_open_until_the_earlier_cutoff(): void
    {
        Notification::fake();
        $host = User::factory()->create();
        $player = User::factory()->create();
        $going = User::factory()->create();

        $later = $this->game($host, now()->addDays(3));
        $this->request($later, $player, now()->subHours(23));

        $notYet = $this->game($host, now()->addHours(2)->addMinute());
        $this->request($notYet, $player, now()->subMinute());

        $played = $this->game($host, now()->subDay());
        $played->participants()->attach($going->id, ['status' => 'going']);
        $played->participants()->updateExistingPivot($going->id, [
            'created_at' => now()->subDays(2),
        ]);

        $poll = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Pick a time',
            'event_date' => now()->addMinutes(30),
            'visibility' => 'public',
            'schedule_type' => 'poll',
            'max_participants' => 4,
        ]);
        $this->request($poll, $player, now()->subMinute());

        $this->artisan('join-requests:expire')->assertSuccessful();

        $this->assertSame('requested', $later->fresh()->getUserStatus($player));
        $this->assertSame('requested', $notYet->fresh()->getUserStatus($player));
        $this->assertSame('requested', $poll->fresh()->getUserStatus($player));
        $this->assertSame('going', $played->fresh()->getUserStatus($going));
        Notification::assertNothingSent();
    }

    public function test_the_player_sees_request_expired_and_the_spot_is_free(): void
    {
        Notification::fake();
        $host = User::factory()->create();
        $player = User::factory()->create();
        $event = $this->game($host, now()->addDays(2), 1);
        $this->request($event, $player, now()->subHours(25));

        $this->actingAs($player)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Request expired')
            ->assertSee('data-request-expired="1"', false)
            ->assertSee('I\'m in');

        $this->assertSame('expired', $event->fresh()->getUserStatus($player));
        Notification::assertNothingSent();

        $this->artisan('join-requests:expire')->assertSuccessful();
        Notification::assertSentTo($player, PlayerNotice::class);

        $this->actingAs($host)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertDontSee('data-roster-request="'.$player->id.'"', false);

        $this->actingAs($player)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertRedirect();

        $this->assertSame('going', $event->fresh()->getUserStatus($player));
    }

    public function test_a_failed_expiry_notice_is_sent_on_the_next_run(): void
    {
        $host = User::factory()->create();
        $player = User::factory()->create();
        $event = $this->game($host, now()->addDays(2));
        $this->request($event, $player, now()->subHours(25));

        EventFacade::listen(MessageSending::class, function (): void {
            throw new RuntimeException('Mail is down.');
        });

        try {
            $this->artisan('join-requests:expire')->assertSuccessful();
        } finally {
            EventFacade::forget(MessageSending::class);
        }

        $this->assertSame('expired', $event->fresh()->getUserStatus($player));
        $this->assertNull(EventUser::query()->where('user_id', $player->id)->value('expiry_notified_at'));

        Notification::fake();
        $this->artisan('join-requests:expire')->assertSuccessful();
        Notification::assertSentToTimes($player, PlayerNotice::class, 1);
        $this->assertNotNull(EventUser::query()->where('user_id', $player->id)->value('expiry_notified_at'));

        $this->artisan('join-requests:expire')->assertSuccessful();
        Notification::assertSentToTimes($player, PlayerNotice::class, 1);
    }

    public function test_join_requests_expire_every_minute(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($scheduled) => str_contains((string) $scheduled->command, 'join-requests:expire'));

        $this->assertNotNull($event);
        $this->assertSame('* * * * *', $event->expression);
    }

    private function game(User $host, \DateTimeInterface $when, ?int $spots = 4): Event
    {
        return Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Thursday padel',
            'event_date' => $when,
            'visibility' => 'public',
            'max_participants' => $spots,
        ]);
    }

    private function request(Event $event, User $player, \DateTimeInterface $at): void
    {
        $event->participants()->syncWithoutDetaching([
            $player->id => ['status' => 'requested'],
        ]);
        $event->participants()->updateExistingPivot($player->id, [
            'status' => 'requested',
            'created_at' => $at,
        ]);
    }
}
