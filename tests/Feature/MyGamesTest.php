<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_games_lists_what_you_host_and_what_you_joined(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Hosted tomorrow',
            'visibility' => 'private',
            'location' => 'Park pitch',
            'event_date' => now()->addDay(),
            'max_participants' => 4,
        ]);

        $joined = Event::factory()->create([
            'user_id' => $other->id,
            'title' => 'Joined tomorrow',
            'visibility' => 'public',
            'event_date' => now()->addDay(),
            'max_participants' => 4,
        ]);
        $joined->participants()->attach($user->id, ['status' => 'going']);

        $requested = Event::factory()->create([
            'user_id' => $other->id,
            'title' => 'Requested bowls',
            'visibility' => 'public',
            'event_date' => now()->addDays(2),
        ]);
        $requested->participants()->attach($user->id, ['status' => 'requested']);

        Event::factory()->create([
            'user_id' => $other->id,
            'title' => 'Not mine',
            'visibility' => 'private',
            'event_date' => now()->addDay(),
        ]);

        $this->actingAs($user)
            ->get(route('my-games.index'))
            ->assertOk()
            ->assertSee('Hosted tomorrow')
            ->assertSee('Hosting')
            ->assertSee('Open roster')
            ->assertSee('Edit')
            ->assertSee('Cancel game')
            ->assertSee('Park pitch')
            ->assertDontSee('Joined tomorrow')
            ->assertDontSee('Requested bowls')
            ->assertDontSee('Not mine');

        $this->get(route('my-games.index', ['tab' => 'playing']))
            ->assertOk()
            ->assertSee('Joined tomorrow')
            ->assertSee('Requested bowls')
            ->assertSee("You're in")
            ->assertSee('Requested')
            ->assertSee("Can't make it", false)
            ->assertSee('Open')
            ->assertDontSee('Hosted tomorrow')
            ->assertDontSee('Not mine')
            ->assertDontSee('Open roster')
            ->assertDontSee('Cancel game');

        $lonely = User::factory()->create();

        $this->actingAs($lonely)
            ->get(route('my-games.index'))
            ->assertOk()
            ->assertSee("Nothing you're hosting yet.")
            ->assertSee('Host a game');

        $this->get(route('my-games.index', ['tab' => 'playing']))
            ->assertOk()
            ->assertSee("You're not in a game yet.")
            ->assertSee('Find a game');

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(route('my-games.index'), false);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My games');
    }

    public function test_upcoming_is_the_default_and_past_is_its_own_tab(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Still ahead',
            'event_date' => now()->addDay(),
        ]);
        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Already played',
            'event_date' => now()->subDay(),
        ]);

        $pastJoined = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Old padel',
            'visibility' => 'public',
            'event_date' => now()->subDays(3),
        ]);
        $pastJoined->participants()->attach($user->id, ['status' => 'going']);

        $this->actingAs($user)
            ->get(route('my-games.index'))
            ->assertOk()
            ->assertSee('Still ahead')
            ->assertSee('Upcoming')
            ->assertDontSee('Already played')
            ->assertDontSee('Old padel');

        $this->get(route('my-games.index', ['when' => 'past']))
            ->assertOk()
            ->assertSee('Already played')
            ->assertDontSee('Still ahead')
            ->assertDontSee('Old padel');

        $this->get(route('my-games.index', ['tab' => 'playing', 'when' => 'past']))
            ->assertOk()
            ->assertSee('Old padel')
            ->assertDontSee('Already played')
            ->assertDontSee('Still ahead');

        $this->get(route('my-games.index', ['tab' => 'playing']))
            ->assertOk()
            ->assertDontSee('Old padel')
            ->assertSee('Find a game');
    }

    public function test_the_rsvp_toggle_needs_auth_and_updates_your_place(): void
    {
        $host = User::factory()->create();
        $player = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Thursday padel',
            'visibility' => 'public',
            'event_date' => now()->addDay(),
            'max_participants' => 8,
        ]);
        $event->participants()->attach($player->id, ['status' => 'going']);

        $this->get(route('my-games.index'))
            ->assertRedirect(route('login'));

        $this->post(route('events.rsvp', $event), ['status' => 'not_going'])
            ->assertRedirect(route('login'));

        $this->actingAs($player)
            ->from(route('my-games.index', ['tab' => 'playing']))
            ->get(route('my-games.index', ['tab' => 'playing']))
            ->assertOk()
            ->assertSee("Can't make it", false);

        $this->post(route('events.rsvp', $event), ['status' => 'not_going'])
            ->assertRedirect(route('my-games.index', ['tab' => 'playing']));

        $this->assertSame('not_going', $event->fresh()->getUserStatus($player));

        $this->get(route('my-games.index', ['tab' => 'playing']))
            ->assertOk()
            ->assertSee('Thursday padel')
            ->assertSee("I'm in", false)
            ->assertSee("Can't make it");

        $this->post(route('events.rsvp', $event), ['status' => 'going'])
            ->assertRedirect(route('my-games.index', ['tab' => 'playing']));

        $this->assertSame('going', $event->fresh()->getUserStatus($player));

        $private = Event::factory()->private()->create([
            'user_id' => $host->id,
            'event_date' => now()->addDay(),
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('events.rsvp', $private), ['status' => 'going'])
            ->assertForbidden();
    }
}
