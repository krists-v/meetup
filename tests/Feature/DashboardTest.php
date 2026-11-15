<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_splits_the_next_game_from_hosting_joined_and_open(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Soon hosted',
            'visibility' => 'private',
            'event_date' => now()->addDay(),
        ]);

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Later hosted',
            'visibility' => 'private',
            'event_date' => now()->addDays(4),
        ]);

        $waitlisted = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Waitlisted bowls',
            'visibility' => 'public',
            'event_date' => now()->addHours(6),
        ]);
        $waitlisted->participants()->attach($user->id, ['status' => 'waitlist']);

        $joined = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Joined padel',
            'visibility' => 'public',
            'event_date' => now()->addDays(2),
        ]);
        $joined->participants()->attach($user->id, ['status' => 'going']);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Open climb',
            'visibility' => 'public',
            'event_date' => now()->addDays(3),
        ]);

        $declined = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Declined run',
            'visibility' => 'public',
            'event_date' => now()->addDays(2),
        ]);
        $declined->participants()->attach($user->id, ['status' => 'not_going']);

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Past hosted',
            'visibility' => 'private',
            'event_date' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'Your next game',
                'Soon hosted',
                'Hosting',
                'Later hosted',
                'Joined',
                'Waitlisted bowls',
                'Joined padel',
                'Open near you',
                'Open climb',
            ])
            ->assertDontSee('Declined run')
            ->assertDontSee('Past hosted');
    }

    public function test_create_form_defaults_to_a_favorite_and_an_evening(): void
    {
        $user = User::factory()->create([
            'favorite_sports' => ['padel'],
        ]);
        $when = now()->addDay()->setTime(18, 0)->format('Y-m-d\TH:i');

        $response = $this->actingAs($user)
            ->get(route('events.create'))
            ->assertOk()
            ->assertSee('Start the game')
            ->assertSee('The game')
            ->assertSee('Who it\'s for', false)
            ->assertSee('All levels means beginners are welcome.')
            ->assertSee('value="'.$when.'"', false)
            ->assertSee('sticky-action', false);

        $this->assertMatchesRegularExpression('/id="sport-padel"[^>]*checked/', $response->getContent());
    }

    public function test_game_page_keeps_im_in_within_reach(): void
    {
        $host = User::factory()->create(['name' => 'Host Hanna']);
        $player = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Thursday padel',
            'sport' => 'padel',
            'visibility' => 'public',
            'location' => 'Court 2',
            'event_date' => now()->addDay()->setTime(18, 0),
        ]);

        $this->actingAs($player)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Thursday padel')
            ->assertSee('Host Hanna')
            ->assertSee('Who\'s going')
            ->assertSee('I\'m in')
            ->assertSee('sticky-action', false)
            ->assertSee('Share on WhatsApp');

        $event->participants()->attach($player->id, ['status' => 'going']);

        $this->actingAs($player)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('You\'re in')
            ->assertDontSee('I&#039;m in');
    }
}
