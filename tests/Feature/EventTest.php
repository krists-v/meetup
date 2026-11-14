<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_an_event(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Sunday football',
            'sport_type' => 'football',
            'event_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'location' => 'Park pitch',
            'max_participants' => 10,
            'description' => 'Bring a light shirt',
            'visibility' => 'private',
        ]);

        $event = Event::first();

        $this->assertNotNull($event);
        $response->assertRedirect(route('events.show', $event));
        $this->assertSame($user->id, $event->user_id);
        $this->assertSame('private', $event->visibility);
        $this->assertNotEmpty($event->invite_token);
        $this->assertDatabaseCount('events', 1);
    }

    public function test_guests_are_sent_to_login_before_creating_an_event(): void
    {
        $this->post(route('events.store'), [
            'title' => 'Sunday football',
            'sport_type' => 'football',
            'event_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'location' => 'Park pitch',
            'visibility' => 'public',
        ])->assertRedirect(route('login'));
    }

    public function test_private_events_are_hidden_from_other_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $private = Event::factory()->private()->create([
            'user_id' => $owner->id,
            'title' => 'Secret match',
        ]);

        Event::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Open match',
            'visibility' => 'public',
        ]);

        $this->actingAs($other)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Open match')
            ->assertDontSee('Secret match');

        $this->actingAs($other)
            ->get(route('events.show', $private))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('events.show', $private))
            ->assertOk()
            ->assertSee('Secret match');

        $private->participants()->attach($other->id, ['status' => 'going']);

        $this->actingAs($other)
            ->get(route('events.show', $private))
            ->assertOk()
            ->assertSee('Secret match');

        $this->actingAs($other)
            ->get(route('dashboard'))
            ->assertSee('Secret match');
    }

    public function test_invite_link_lets_a_user_see_and_join_a_private_event(): void
    {
        $owner = User::factory()->create();
        $friend = User::factory()->create();

        $event = Event::factory()->private()->create([
            'user_id' => $owner->id,
            'title' => 'Invite-only tennis',
        ]);

        $this->get(route('events.invite', $event->invite_token))
            ->assertOk()
            ->assertSee('Invite-only tennis');

        $this->actingAs($friend)
            ->get(route('events.invite', $event->invite_token))
            ->assertRedirect(route('events.show', $event));

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => $friend->id,
            'status' => 'invited',
        ]);

        $this->actingAs($friend)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Invite-only tennis');

        $this->actingAs($friend)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => $friend->id,
            'status' => 'going',
        ]);
    }

    public function test_going_rsvp_stops_at_max_participants_but_maybe_still_works(): void
    {
        $event = Event::factory()->create([
            'visibility' => 'public',
            'max_participants' => 2,
            'title' => 'Two-spot game',
        ]);

        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $this->actingAs($first)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertSessionHasNoErrors();

        $this->actingAs($second)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertSessionHasNoErrors();

        $this->actingAs($third)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertSessionHasErrors('status');

        $this->assertNull($event->fresh()->getUserStatus($third));

        $this->actingAs($third)
            ->post(route('events.rsvp', $event), ['status' => 'maybe'])
            ->assertSessionHasNoErrors();

        $event->refresh();

        $this->assertSame(2, $event->goingParticipants()->count());
        $this->assertSame('maybe', $event->getUserStatus($third));
        $this->assertSame(1, $event->maybeParticipants()->count());

        $this->actingAs($first)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $event->fresh()->goingParticipants()->count());
    }

    public function test_only_the_creator_can_edit_or_delete_an_event(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Editable game',
            'visibility' => 'public',
        ]);

        $payload = [
            'title' => 'Updated game',
            'sport_type' => 'basketball',
            'event_date' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'location' => 'Gym',
            'max_participants' => '',
            'description' => '',
            'visibility' => 'public',
        ];

        $this->actingAs($other)
            ->put(route('events.update', $event), $payload)
            ->assertForbidden();

        $this->actingAs($owner)
            ->put(route('events.update', $event), $payload)
            ->assertRedirect(route('events.show', $event));

        $this->assertSame('Updated game', $event->fresh()->title);
        $this->assertNull($event->fresh()->max_participants);

        $this->actingAs($other)
            ->delete(route('events.destroy', $event))
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('events.destroy', $event))
            ->assertRedirect(route('dashboard'));

        $this->assertModelMissing($event);
    }

    public function test_dashboard_lists_upcoming_events_only(): void
    {
        $user = User::factory()->create();

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Future game',
            'event_date' => now()->addDay(),
            'visibility' => 'public',
        ]);

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old game',
            'event_date' => now()->subDay(),
            'visibility' => 'public',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Future game')
            ->assertDontSee('Old game')
            ->assertSee('My upcoming events')
            ->assertSee('Public upcoming events');
    }

    public function test_strangers_cannot_rsvp_to_a_private_event(): void
    {
        $event = Event::factory()->private()->create();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertForbidden();
    }
}
