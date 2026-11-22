<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Notifications\PlayerNotice;
use App\Notifications\PromotedFromWaitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_host_can_confirm_or_decline_a_request_and_others_cannot(): void
    {
        Notification::fake();
        $host = User::factory()->create();
        $player = User::factory()->create(['name' => 'Casey Request']);
        $other = User::factory()->create();
        $event = $this->game($host);

        $event->participants()->attach($player->id, ['status' => 'requested']);

        $this->actingAs($host)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('data-roster="1"', false)
            ->assertSee('data-roster-request="'.$player->id.'"', false)
            ->assertSee('Confirm')
            ->assertSee('Decline')
            ->assertDontSee('No-show');

        $this->actingAs($other)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertDontSee('data-roster=', false);

        $this->actingAs($other)
            ->post(route('events.roster.confirm', [$event, $player]))
            ->assertForbidden();

        $full = $this->game($host, 1);
        $full->participants()->attach(User::factory()->create()->id, ['status' => 'going']);
        $full->participants()->attach($player->id, ['status' => 'requested']);

        $this->actingAs($host)
            ->post(route('events.roster.confirm', [$full, $player]))
            ->assertRedirect()
            ->assertSessionHasErrors('roster');
        $this->assertSame('requested', $full->fresh()->getUserStatus($player));

        $this->actingAs($host)
            ->post(route('events.roster.confirm', [$event, $player]))
            ->assertRedirect(route('events.show', $event));

        $this->assertSame('going', $event->fresh()->getUserStatus($player));
        Notification::assertSentTo($player, PlayerNotice::class, function (PlayerNotice $notice) use ($event): bool {
            return $notice->event->is($event)
                && str_contains($notice->bodyLine, $event->title);
        });

        $second = User::factory()->create();
        $event->participants()->attach($second->id, ['status' => 'requested']);

        $this->actingAs($host)
            ->post(route('events.roster.decline', [$event, $second]))
            ->assertRedirect();

        $this->assertNull($event->fresh()->getUserStatus($second));
        Notification::assertSentTo($second, PlayerNotice::class);
    }

    public function test_removing_a_going_player_frees_the_spot_and_uses_the_existing_notice(): void
    {
        Notification::fake();
        $host = User::factory()->create();
        $going = User::factory()->create(['name' => 'Going Gal']);
        $waiting = User::factory()->create();
        $event = $this->game($host, 1);
        $event->participants()->attach($going->id, ['status' => 'going']);
        $event->participants()->attach($waiting->id, ['status' => 'waitlist']);

        $this->actingAs($going)
            ->delete(route('events.roster.remove', [$event, $going]))
            ->assertForbidden();

        $this->actingAs($host)
            ->delete(route('events.roster.remove', [$event, $going]))
            ->assertRedirect();

        $fresh = $event->fresh();
        $this->assertSame('not_going', $fresh->getUserStatus($going));
        $this->assertSame('going', $fresh->getUserStatus($waiting));
        Notification::assertSentTo($going, PlayerNotice::class);
        Notification::assertSentTo($waiting, PromotedFromWaitlist::class);
    }

    public function test_removing_a_player_after_the_game_starts_does_not_free_the_spot(): void
    {
        Notification::fake();
        $host = User::factory()->create();
        $going = User::factory()->create();
        $waiting = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $host->id,
            'event_date' => now()->subHour(),
            'visibility' => 'public',
            'max_participants' => 1,
        ]);
        $event->participants()->attach($going->id, [
            'status' => 'going',
            'attended' => false,
        ]);
        $event->participants()->attach($waiting->id, ['status' => 'waitlist']);

        $this->actingAs($host)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertDontSee(route('events.roster.remove', [$event, $going]).'"', false);

        $this->actingAs($host)
            ->delete(route('events.roster.remove', [$event, $going]))
            ->assertRedirect()
            ->assertSessionHasErrors('roster');

        $fresh = $event->fresh();
        $this->assertSame('going', $fresh->getUserStatus($going));
        $this->assertSame('waitlist', $fresh->getUserStatus($waiting));
        $this->assertFalse($fresh->participants()->where('users.id', $going->id)->first()->pivot->attended);
        Notification::assertNothingSent();
    }

    public function test_the_host_marks_attendance_only_after_the_game_starts(): void
    {
        $host = User::factory()->create();
        $player = User::factory()->create();
        $future = $this->game($host);
        $future->participants()->attach($player->id, ['status' => 'going']);

        $this->actingAs($host)
            ->get(route('events.show', $future))
            ->assertOk()
            ->assertSee('Remove')
            ->assertDontSee('No-show');

        $this->actingAs($host)
            ->post(route('events.roster.attendance', [$future, $player]), ['attended' => '0'])
            ->assertSessionHasErrors('roster');

        $this->assertNull($future->participants()->where('users.id', $player->id)->first()->pivot->attended);

        $past = [];
        foreach (range(1, 3) as $days) {
            $event = Event::factory()->create([
                'user_id' => $host->id,
                'event_date' => now()->subDays($days),
                'visibility' => 'public',
                'max_participants' => 4,
            ]);
            $event->participants()->attach($player->id, ['status' => 'going']);
            $past[] = $event;
        }

        $this->actingAs($player)
            ->post(route('events.roster.attendance', [$past[0], $player]), ['attended' => '0'])
            ->assertForbidden();

        $this->actingAs($host)
            ->get(route('events.show', $past[0]))
            ->assertOk()
            ->assertSee('No-show')
            ->assertSee('Showed up');

        $this->actingAs($host)
            ->post(route('events.roster.attendance', [$past[0], $player]), ['attended' => '0'])
            ->assertRedirect();

        $this->assertFalse($past[0]->participants()->where('users.id', $player->id)->first()->pivot->attended);
        $this->assertSame('Shows up 2/3', $player->fresh()->attendanceLabel());

        $this->actingAs($host)
            ->post(route('events.roster.attendance', [$past[0], $player]), ['attended' => '1'])
            ->assertRedirect();

        $this->assertTrue($past[0]->participants()->where('users.id', $player->id)->first()->pivot->attended);
        $this->assertSame('Shows up 3/3', $player->fresh()->attendanceLabel());
    }

    private function game(User $host, ?int $spots = 4): Event
    {
        return Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Thursday padel',
            'event_date' => now()->addDay(),
            'visibility' => 'public',
            'max_participants' => $spots,
        ]);
    }
}
