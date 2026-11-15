<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventTimeOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_joining_sets_a_flash_and_a_second_tap_does_not(): void
    {
        $user = User::factory()->create(['name' => 'Ada Player']);
        $event = Event::factory()->create([
            'title' => 'Thursday padel',
            'sport' => 'padel',
            'visibility' => 'public',
            'max_participants' => 8,
            'event_date' => now()->addDay(),
        ]);

        $this->actingAs($user)
            ->from(route('events.show', $event))
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHas('joined', true)
            ->assertSessionHas('status', "You're in.");

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('motion-joined', false)
            ->assertSee('motion-tick', false)
            ->assertSee('motion-burst', false)
            ->assertSee('motion-avatar', false)
            ->assertSee('You\'re in');

        $this->actingAs($user)
            ->from(route('events.show', $event))
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionMissing('joined');

        $this->assertSame(1, $event->fresh()->goingCount());

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertDontSee('motion-joined', false);
    }

    public function test_a_new_vote_bounces_and_the_same_vote_does_not(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'schedule_type' => 'poll',
            'visibility' => 'public',
            'event_date' => now()->addDays(3),
        ]);
        $option = EventTimeOption::factory()->create([
            'event_id' => $event->id,
            'starts_at' => now()->addDays(2),
        ]);

        $this->actingAs($user)
            ->from(route('events.show', $event))
            ->post(route('events.options.vote', [$event, $option]), ['vote' => 'yes'])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHas('voted', $option->id);

        $this->actingAs($user)
            ->from(route('events.show', $event))
            ->followingRedirects()
            ->post(route('events.options.vote', [$event, $option]), ['vote' => 'maybe'])
            ->assertOk()
            ->assertSee('motion-pop', false)
            ->assertSee('motion-tick', false);

        $this->actingAs($user)
            ->from(route('events.show', $event))
            ->post(route('events.options.vote', [$event, $option]), ['vote' => 'maybe'])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionMissing('voted');

        $this->assertSame(1, $option->fresh()->votes()->count());
    }
}
