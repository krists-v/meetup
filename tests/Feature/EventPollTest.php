<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventTimeOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPollTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_poll_event_with_several_times(): void
    {
        $user = User::factory()->create();
        $first = now()->addDays(2)->startOfMinute();
        $second = now()->addDays(3)->startOfMinute();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Pick a kickoff',
            'sport_type' => 'football',
            'schedule_type' => 'poll',
            'options' => [
                $first->format('Y-m-d\TH:i'),
                $second->format('Y-m-d\TH:i'),
            ],
            'location' => 'Park pitch',
            'visibility' => 'public',
        ]);

        $event = Event::query()->first();

        $this->assertNotNull($event);
        $response->assertRedirect(route('events.show', $event));
        $this->assertTrue($event->isPoll());
        $this->assertCount(2, $event->timeOptions);
        $this->assertEquals($second->format('Y-m-d H:i'), $event->event_date->format('Y-m-d H:i'));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Vote on a time')
            ->assertSee('Pick a kickoff');
    }

    public function test_a_poll_needs_at_least_two_times(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Too few times',
            'sport_type' => 'tennis',
            'schedule_type' => 'poll',
            'options' => [now()->addDay()->format('Y-m-d\TH:i')],
            'location' => 'Court',
            'visibility' => 'public',
        ])->assertSessionHasErrors('options');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_friends_can_vote_and_change_votes(): void
    {
        $owner = User::factory()->create();
        $friend = User::factory()->create();
        [$event, $early, $late] = $this->pollEvent($owner);

        $this->actingAs($friend)
            ->post(route('events.options.vote', [$event, $early]), ['vote' => 'yes'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_time_votes', [
            'event_time_option_id' => $early->id,
            'user_id' => $friend->id,
            'vote' => 'yes',
        ]);

        $this->actingAs($friend)
            ->post(route('events.options.vote', [$event, $early]), ['vote' => 'no'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_time_votes', [
            'event_time_option_id' => $early->id,
            'user_id' => $friend->id,
            'vote' => 'no',
        ]);
        $this->assertSame(1, $early->votes()->count());

        $this->actingAs($owner)->post(route('events.options.vote', [$event, $late]), ['vote' => 'yes']);
        $this->actingAs($friend)->post(route('events.options.vote', [$event, $late]), ['vote' => 'yes']);
        $this->actingAs($friend)->post(route('events.options.vote', [$event, $early]), ['vote' => 'maybe']);

        $event->load('timeOptions.votes');

        $this->assertTrue($event->bestTimeOption()->is($late));

        $this->actingAs($friend)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Best time')
            ->assertSee('Availability');
    }

    public function test_private_polls_are_hidden_until_the_invite_link_is_opened(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        [$event, $option] = $this->pollEvent($owner, ['visibility' => 'private', 'title' => 'Secret poll']);

        $this->actingAs($stranger)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Secret poll');

        $this->actingAs($stranger)
            ->get(route('events.show', $event))
            ->assertForbidden();

        $this->actingAs($stranger)
            ->post(route('events.options.vote', [$event, $option]), ['vote' => 'yes'])
            ->assertForbidden();

        $this->actingAs($stranger)
            ->get(route('events.invite', $event->invite_token))
            ->assertRedirect(route('events.show', $event));

        $this->actingAs($stranger)
            ->post(route('events.options.vote', [$event, $option]), ['vote' => 'yes'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_time_votes', [
            'event_time_option_id' => $option->id,
            'user_id' => $stranger->id,
            'vote' => 'yes',
        ]);
    }

    public function test_only_the_creator_can_lock_a_time_and_locking_respects_capacity(): void
    {
        $owner = User::factory()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();
        $maybe = User::factory()->create();

        [$event, $chosen, $other] = $this->pollEvent($owner, ['max_participants' => 2]);

        $this->actingAs($first)->post(route('events.options.vote', [$event, $chosen]), ['vote' => 'yes']);
        $this->actingAs($second)->post(route('events.options.vote', [$event, $chosen]), ['vote' => 'yes']);
        $this->actingAs($third)->post(route('events.options.vote', [$event, $chosen]), ['vote' => 'yes']);
        $this->actingAs($maybe)->post(route('events.options.vote', [$event, $chosen]), ['vote' => 'maybe']);
        $this->actingAs($first)->post(route('events.options.vote', [$event, $other]), ['vote' => 'no']);

        $this->actingAs($first)
            ->post(route('events.options.lock', [$event, $chosen]))
            ->assertForbidden();

        $this->assertTrue($event->fresh()->isPoll());

        $this->actingAs($owner)
            ->post(route('events.options.lock', [$event, $chosen]))
            ->assertRedirect(route('events.show', $event));

        $event->refresh();

        $this->assertFalse($event->isPoll());
        $this->assertSame('fixed', $event->schedule_type);
        $this->assertEquals($chosen->starts_at->format('Y-m-d H:i'), $event->event_date->format('Y-m-d H:i'));
        $this->assertSame('going', $event->getUserStatus($first));
        $this->assertSame('going', $event->getUserStatus($second));
        $this->assertNull($event->getUserStatus($third));
        $this->assertNull($event->getUserStatus($maybe));
        $this->assertSame(2, $event->goingParticipants()->count());

        $this->actingAs($third)
            ->post(route('events.options.vote', [$event, $chosen]), ['vote' => 'no'])
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Your RSVP')
            ->assertDontSee('Lock in this time');

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Vote on a time');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{0: Event, 1: EventTimeOption, 2: EventTimeOption}
     */
    private function pollEvent(User $owner, array $overrides = []): array
    {
        $early = now()->addDays(2)->startOfMinute();
        $late = now()->addDays(4)->startOfMinute();

        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Poll game',
            'schedule_type' => 'poll',
            'event_date' => $late,
            'visibility' => 'public',
            ...$overrides,
        ]);

        $first = $event->timeOptions()->create([
            'starts_at' => $early,
            'position' => 0,
        ]);
        $second = $event->timeOptions()->create([
            'starts_at' => $late,
            'position' => 1,
        ]);

        return [$event, $first, $second];
    }
}
