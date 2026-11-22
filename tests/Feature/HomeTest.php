<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_homepage_shows_the_new_pitch(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bored at home? Go play.')
            ->assertSee('Less scrolling, more playing.')
            ->assertSee('Happening this weekend')
            ->assertSee('Nothing on this weekend.')
            ->assertSee('Rainy day? Go indoors')
            ->assertSee('How it works')
            ->assertSee('Find a game')
            ->assertSee('Tap I\'m in', false)
            ->assertSee('Show up and play')
            ->assertSee('New here? Totally fine')
            ->assertSee('Beginners are welcome.')
            ->assertSee('Pick an activity')
            ->assertSee('Your weekend just got a plan.')
            ->assertSee('I\'m in', false)
            ->assertSee('href="'.route('games.index').'"', false)
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('setting=indoor', false)
            ->assertSee('sport=hiking', false)
            ->assertSee('text-5xl font-bold', false)
            ->assertSee('bg-success-soft', false)
            ->assertSee('data-app-header', false)
            ->assertDontSee('text-pitch-700', false)
            ->assertSee('Log in')
            ->assertSee('Sign up')
            ->assertDontSee('bg-pitch-mesh', false)
            ->assertDontSee('bg-pitch-band', false)
            ->assertDontSee('WhatsApp')
            ->assertDontSee('ten friends')
            ->assertDontSee('small crew')
            ->assertDontSee('the crew');
    }

    public function test_homepage_lists_up_to_six_public_games_this_weekend(): void
    {
        Carbon::setTestNow('2026-11-18 12:00:00');
        $host = User::factory()->create();

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Saturday padel',
            'sport' => 'padel',
            'visibility' => 'public',
            'event_date' => '2026-11-21 11:00:00',
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Sunday climb',
            'sport' => 'climbing',
            'visibility' => 'public',
            'event_date' => '2026-11-22 10:00:00',
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Weekday football',
            'sport' => 'football',
            'visibility' => 'public',
            'event_date' => '2026-11-18 18:00:00',
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Next week run',
            'sport' => 'running',
            'visibility' => 'public',
            'event_date' => '2026-11-25 09:00:00',
        ]);

        Event::factory()->private()->create([
            'user_id' => $host->id,
            'title' => 'Secret hike',
            'sport' => 'hiking',
            'event_date' => '2026-11-21 15:00:00',
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Last weekend bowls',
            'sport' => 'bowling',
            'visibility' => 'public',
            'event_date' => '2026-11-14 15:00:00',
        ]);

        foreach (range(1, 5) as $slot) {
            Event::factory()->create([
                'user_id' => $host->id,
                'title' => 'Weekend slot '.$slot,
                'sport' => 'tennis',
                'visibility' => 'public',
                'event_date' => Carbon::parse('2026-11-22 12:00:00')->addHours($slot),
            ]);
        }

        $response = $this->get('/')
            ->assertOk()
            ->assertSee('Saturday padel')
            ->assertSee('Sunday climb')
            ->assertDontSee('Nothing on this weekend.')
            ->assertDontSee('Weekday football')
            ->assertDontSee('Next week run')
            ->assertDontSee('Secret hike')
            ->assertDontSee('Last weekend bowls');

        foreach (range(1, 4) as $slot) {
            $response->assertSee('Weekend slot '.$slot);
        }

        $response->assertDontSee('Weekend slot 5');
    }

    public function test_signed_in_visitor_skips_the_homepage(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }
}
