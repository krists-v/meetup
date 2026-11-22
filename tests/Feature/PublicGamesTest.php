<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicGamesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_public_games_page_lists_only_upcoming_public_games(): void
    {
        $host = User::factory()->create();
        $public = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Thursday five-a-side',
            'sport' => 'football',
            'location' => 'Mezaparks pitch',
            'event_date' => now()->addDays(2)->setTime(18, 0),
            'max_participants' => 8,
            'visibility' => 'public',
        ]);
        $public->participants()->attach(User::factory()->create()->id, ['status' => 'going']);
        Event::factory()->private()->create([
            'user_id' => $host->id,
            'title' => 'Secret padel',
            'sport' => 'padel',
            'event_date' => now()->addDay(),
        ]);
        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Last week',
            'sport' => 'tennis',
            'event_date' => now()->subDay(),
            'visibility' => 'public',
        ]);

        $this->get(route('games.index'))
            ->assertOk()
            ->assertSee('What\'s on')
            ->assertSee('Thursday five-a-side')
            ->assertSee('Football')
            ->assertSee($public->whenLabel())
            ->assertSee('Mezaparks pitch')
            ->assertSee('1 / 8 players')
            ->assertSee('7 spots left')
            ->assertSee('https://www.google.com/maps/search/?api=1&query=Mezaparks%20pitch')
            ->assertDontSee('Secret padel')
            ->assertDontSee('Last week');

        $this->get('/')
            ->assertOk()
            ->assertSee('See what\'s on', false)
            ->assertSee(route('games.index'), false);

        $this->actingAs($host)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('What\'s on', false)
            ->assertSee(route('games.index'), false);
    }

    public function test_spots_show_a_full_badge_or_just_the_going_count(): void
    {
        $host = User::factory()->create();
        $full = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Packed football',
            'sport' => 'football',
            'location' => 'Hall',
            'max_participants' => 1,
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);
        $full->participants()->attach($host->id, ['status' => 'going']);
        $open = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Open tennis',
            'sport' => 'tennis',
            'location' => 'Court',
            'max_participants' => null,
            'visibility' => 'public',
            'event_date' => now()->addDays(2),
        ]);
        $open->participants()->attach([
            User::factory()->create()->id => ['status' => 'going'],
            User::factory()->create()->id => ['status' => 'going'],
        ]);

        $this->get(route('games.index'))
            ->assertOk()
            ->assertSee('1 / 1 players')
            ->assertSee('Full')
            ->assertSee('2 players')
            ->assertDontSee('2 going ·');

        $this->actingAs(User::factory()->create())
            ->get(route('events.show', $full))
            ->assertOk()
            ->assertSee('1 of 1 going · 0 spots left')
            ->assertSee('Full')
            ->assertSee('https://www.google.com/maps/search/?api=1&query=Hall');

        $this->actingAs(User::factory()->create())
            ->get(route('events.show', $open))
            ->assertOk()
            ->assertSee('2 going')
            ->assertDontSee('spots left');
    }

    public function test_filters_are_shareable_on_games_and_the_dashboard(): void
    {
        Carbon::setTestNow('2026-11-18 12:00:00');

        $host = User::factory()->create();
        $viewer = User::factory()->create();
        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Today football',
            'sport' => 'football',
            'location' => 'Riga hall',
            'event_date' => Carbon::parse('2026-11-18 18:00:00'),
        ]);
        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Saturday padel',
            'sport' => 'padel',
            'location' => 'Riga court',
            'event_date' => Carbon::parse('2026-11-21 11:00:00'),
        ]);
        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Sunday tennis',
            'sport' => 'tennis',
            'location' => 'Liepaja court',
            'event_date' => Carbon::parse('2026-11-22 11:00:00'),
        ]);
        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Friday hockey',
            'sport' => 'hockey',
            'location' => 'Riga rink',
            'event_date' => Carbon::parse('2026-11-20 18:00:00'),
        ]);
        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Next week football',
            'sport' => 'football',
            'location' => 'Riga hall',
            'event_date' => Carbon::parse('2026-11-25 18:00:00'),
        ]);
        Event::factory()->private()->create([
            'user_id' => $viewer->id,
            'title' => 'My private padel',
            'sport' => 'padel',
            'location' => 'Riga court',
            'event_date' => Carbon::parse('2026-11-21 19:00:00'),
        ]);

        $this->get(route('games.index', ['when' => 'today']))
            ->assertOk()
            ->assertSee('Today football')
            ->assertDontSee('Saturday padel');

        $this->get(route('games.index', ['when' => 'weekend']))
            ->assertOk()
            ->assertSee('Saturday padel')
            ->assertSee('Sunday tennis')
            ->assertDontSee('Friday hockey')
            ->assertDontSee('Today football');

        $this->get(route('games.index', ['when' => 'week']))
            ->assertOk()
            ->assertSee('Friday hockey')
            ->assertSee('Saturday padel')
            ->assertDontSee('Next week football');

        $this->get(route('games.index', ['sport' => 'padel', 'where' => 'Riga']))
            ->assertOk()
            ->assertSee('Saturday padel')
            ->assertDontSee('Sunday tennis')
            ->assertDontSee('Today football')
            ->assertDontSee('My private padel')
            ->assertSee('sport=padel', false)
            ->assertSee('where=Riga', false);

        $this->get(route('games.index', ['where' => 'riga%']))
            ->assertOk()
            ->assertDontSee('Saturday padel')
            ->assertSee('Nothing for that.');

        $this->actingAs($viewer)
            ->get(route('dashboard', ['sport' => 'padel']))
            ->assertOk()
            ->assertSee('My private padel')
            ->assertSee('Saturday padel')
            ->assertDontSee('Today football')
            ->assertSee('Your next game');
    }

    public function test_the_filter_button_counts_active_filters(): void
    {
        $this->get(route('games.index'))
            ->assertOk()
            ->assertDontSee('data-filter-count=', false)
            ->assertSee('Filters')
            ->assertSee('document.documentElement.classList.add(\'js\')', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false);

        $this->get(route('games.index', [
            'sport' => 'padel',
            'setting' => 'indoor',
            'level' => 'beginner',
        ]))
            ->assertOk()
            ->assertSee('data-filter-count="3"', false)
            ->assertSee('Clear all')
            ->assertSee('Show games');
    }

    public function test_im_in_sends_a_guest_through_login_and_joins_the_game(): void
    {
        $player = User::factory()->create();
        $event = Event::factory()->create([
            'title' => 'Open football',
            'sport' => 'football',
            'location' => 'Park',
            'visibility' => 'public',
            'max_participants' => 8,
            'event_date' => now()->addDay(),
        ]);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee("I'm in", false)
            ->assertSee('Create an account');

        $this->post(route('events.join', $event))
            ->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $player->email,
            'password' => 'password',
        ])->assertRedirect(route('events.show', $event));

        $this->assertSame('going', $event->fresh()->getUserStatus($player));
        $this->assertAuthenticatedAs($player);
    }

    public function test_im_in_sends_a_new_player_through_register_and_joins_the_game(): void
    {
        $event = Event::factory()->create([
            'title' => 'Open padel',
            'sport' => 'padel',
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);

        $this->post(route('events.join', $event), ['account' => 'new'])
            ->assertRedirect(route('register'));

        $this->post('/register', [
            'name' => 'Bruno Ozols',
            'email' => 'bruno-qa@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('events.show', $event));

        $user = User::where('email', 'bruno-qa@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('going', $event->fresh()->getUserStatus($user));
    }

    public function test_im_in_does_not_join_a_full_or_private_game(): void
    {
        $player = User::factory()->create();
        $full = Event::factory()->create([
            'title' => 'Full game',
            'visibility' => 'public',
            'max_participants' => 1,
            'event_date' => now()->addDay(),
        ]);
        $full->participants()->attach(User::factory()->create()->id, ['status' => 'going']);
        $private = Event::factory()->private()->create([
            'title' => 'Private game',
            'event_date' => now()->addDay(),
        ]);

        $this->actingAs($player)
            ->post(route('events.join', $full))
            ->assertRedirect(route('events.show', $full))
            ->assertSessionHasErrors('status');

        $this->assertNull($full->fresh()->getUserStatus($player));

        $this->actingAs($player)
            ->get(route('events.join', $event = Event::factory()->create([
                'visibility' => 'public',
                'event_date' => now()->addDays(3),
            ])))
            ->assertStatus(405);

        $this->assertNull($event->fresh()->getUserStatus($player));

        $this->actingAs($player)
            ->post(route('events.join', $private))
            ->assertForbidden();

        $this->assertNull($private->fresh()->getUserStatus($player));
    }

    public function test_a_private_invite_still_joins_after_login(): void
    {
        $player = User::factory()->create();
        $event = Event::factory()->private()->create([
            'title' => 'Secret floorball',
            'sport' => 'other',
            'custom_sport' => 'Floorball',
            'location' => 'Olympic center',
            'event_date' => now()->addDays(2),
        ]);

        $this->post(route('events.join', $event), ['token' => 'not-the-token'])
            ->assertNotFound();

        $this->get(route('events.invite', $event->invite_token))
            ->assertOk()
            ->assertSee("I'm in", false)
            ->assertSee($event->invite_token, false);

        $this->post(route('events.join', $event), [
            'token' => $event->invite_token,
        ])->assertRedirect(route('login'));

        $this->post('/login', [
            'email' => $player->email,
            'password' => 'password',
        ])->assertRedirect(route('events.show', $event));

        $this->assertSame('going', $event->fresh()->getUserStatus($player));
    }
}
