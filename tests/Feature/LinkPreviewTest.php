<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_has_share_card_meta(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:site_name" content="Varza"', false)
            ->assertSee('property="og:title" content="Varza"', false)
            ->assertSee('property="og:description" content="Bored at home? See what&#039;s on near you and go play."', false)
            ->assertSee('property="og:image" content="'.asset('images/og/default.png').'"', false)
            ->assertSee('property="og:url" content="'.url('/').'"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('favicon-32x32.png', false);
    }

    public function test_event_page_describes_the_game(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Sunday football',
            'sport' => 'football',
            'location' => 'Park pitch',
            'visibility' => 'public',
            'max_participants' => 10,
            'event_date' => now()->addDays(3)->setTime(18, 0),
        ]);

        $when = $event->event_date->format('l j F Y, H:i');

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('property="og:title" content="Sunday football"', false)
            ->assertSee('property="og:site_name" content="Varza"', false)
            ->assertSee('Football, '.$when.', Park pitch. 0 of 10 going · 10 spots left.', false)
            ->assertSee('property="og:image" content="'.asset('images/og/football.png').'"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);
    }

    public function test_poll_preview_says_to_vote_on_a_time(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Padel this week',
            'sport' => 'padel',
            'location' => 'Court 2',
            'visibility' => 'public',
            'schedule_type' => 'poll',
            'max_participants' => 4,
            'event_date' => now()->addDays(5),
        ]);

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Padel, Vote on a time, Court 2. max 4 once a time is chosen.', false)
            ->assertDontSee($event->event_date->format('l j F Y, H:i'));
    }

    public function test_private_event_without_a_token_stays_generic(): void
    {
        $event = Event::factory()->private()->create([
            'title' => 'Secret match',
            'sport' => 'tennis',
            'location' => 'Hidden pitch',
            'max_participants' => 6,
        ]);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee("You're invited to a game on Varza")
            ->assertSee('property="og:image" content="'.asset('images/og/default.png').'"', false)
            ->assertDontSee('Secret match')
            ->assertDontSee('Hidden pitch')
            ->assertDontSee('Tennis')
            ->assertDontSee(asset('images/og/tennis.png'), false);

        $this->get(route('events.show', ['event' => $event, 'token' => 'not-the-token']))
            ->assertOk()
            ->assertDontSee('Secret match');
    }

    public function test_private_event_with_a_token_shows_the_details(): void
    {
        $event = Event::factory()->private()->create([
            'title' => 'Secret match',
            'sport' => 'tennis',
            'location' => 'Hidden pitch',
            'max_participants' => 6,
            'event_date' => now()->addDays(2)->setTime(19, 30),
        ]);

        $when = $event->event_date->format('l j F Y, H:i');

        $this->get(route('events.invite', $event->invite_token))
            ->assertOk()
            ->assertSee('property="og:title" content="Secret match"', false)
            ->assertSee('Tennis, '.$when.', Hidden pitch. 0 of 6 going · 6 spots left.', false)
            ->assertSee('property="og:image" content="'.asset('images/og/tennis.png').'"', false)
            ->assertSee('property="og:url" content="'.route('events.invite', $event->invite_token).'"', false);

        $this->get(route('events.show', ['event' => $event, 'token' => $event->invite_token]))
            ->assertOk()
            ->assertSee('property="og:title" content="Secret match"', false)
            ->assertSee('Hidden pitch', false);
    }

    public function test_a_token_on_the_event_page_joins_after_login(): void
    {
        $friend = User::factory()->create();
        $event = Event::factory()->private()->create([
            'title' => 'Secret match',
        ]);
        $url = route('events.show', ['event' => $event, 'token' => $event->invite_token]);

        $this->get($url)
            ->assertOk()
            ->assertSee('Secret match');

        $this->post('/login', [
            'email' => $friend->email,
            'password' => 'password',
        ])->assertRedirect($url);

        $this->get($url)
            ->assertOk()
            ->assertSee('Secret match');

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => $friend->id,
            'status' => 'invited',
        ]);

        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->get(route('events.show', ['event' => $event, 'token' => 'not-the-token']))
            ->assertForbidden();
    }

    public function test_logging_in_from_an_invite_preview_joins_the_game(): void
    {
        $friend = User::factory()->create();
        $event = Event::factory()->private()->create();

        $this->get(route('events.invite', $event->invite_token))->assertOk();

        $this->post('/login', [
            'email' => $friend->email,
            'password' => 'password',
        ])->assertRedirect(route('events.invite', $event->invite_token));

        $this->get(route('events.invite', $event->invite_token))
            ->assertRedirect(route('events.show', $event));

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => $friend->id,
            'status' => 'invited',
        ]);
    }
}
