<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OpenGamesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_bored_opens_the_soonest_game_you_can_join(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00:00'));

        $viewer = User::factory()->create();
        $host = User::factory()->create();
        $filler = User::factory()->create();

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Already started',
            'visibility' => 'public',
            'event_date' => now()->subHour(),
            'max_participants' => 8,
        ]);

        $full = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'No spots',
            'visibility' => 'public',
            'event_date' => now()->addHour(),
            'max_participants' => 1,
        ]);
        $full->participants()->attach($filler->id, ['status' => 'going']);

        Event::factory()->create([
            'user_id' => $viewer->id,
            'title' => 'My game',
            'visibility' => 'public',
            'event_date' => now()->addMinutes(90),
            'max_participants' => 8,
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Private game',
            'visibility' => 'private',
            'event_date' => now()->addMinutes(100),
            'max_participants' => 8,
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Pick a time',
            'visibility' => 'public',
            'schedule_type' => 'poll',
            'event_date' => now()->addMinutes(110),
            'max_participants' => 8,
        ]);

        $soon = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Lunch tennis',
            'visibility' => 'public',
            'sport' => 'tennis',
            'event_date' => now()->addHours(2),
            'max_participants' => 8,
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Saturday kickabout',
            'visibility' => 'public',
            'event_date' => Carbon::parse('2026-11-28 15:00:00'),
            'max_participants' => 8,
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Next week',
            'visibility' => 'public',
            'event_date' => Carbon::parse('2026-11-30 12:00:00'),
            'max_participants' => 8,
        ]);

        $this->actingAs($viewer)
            ->get(route('bored'))
            ->assertRedirect(route('events.show', $soon));

        $this->actingAs($host)
            ->get(route('bored'))
            ->assertRedirect(route('events.show', Event::query()->where('title', 'My game')->first()));
    }

    public function test_bored_skips_a_game_you_have_already_joined(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00:00'));

        $viewer = User::factory()->create();
        $host = User::factory()->create();

        $joined = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Already in',
            'visibility' => 'public',
            'event_date' => now()->addHours(2),
            'max_participants' => 8,
        ]);
        $joined->participants()->attach($viewer->id, ['status' => 'maybe']);

        $later = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Saturday kickabout',
            'visibility' => 'public',
            'event_date' => Carbon::parse('2026-11-28 15:00:00'),
            'max_participants' => 8,
        ]);

        $this->actingAs($viewer)
            ->get(route('bored'))
            ->assertRedirect(route('events.show', $later));
    }

    public function test_bored_shows_a_host_prompt_when_nothing_is_open(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00:00'));

        $host = User::factory()->create();

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Next week',
            'visibility' => 'public',
            'event_date' => Carbon::parse('2026-11-30 12:00:00'),
        ]);

        $this->get(route('bored'))
            ->assertRedirect(route('games.index', ['bored' => 1]));

        $this->get(route('games.index', ['bored' => 1]))
            ->assertSee('Nothing to jump into.')
            ->assertSee('No open spot for you today or this weekend.')
            ->assertSee('Host one')
            ->assertSee(route('events.create'), false)
            ->assertDontSee('Next week');
    }

    public function test_starting_soon_lists_todays_open_games(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00:00'));

        $host = User::factory()->create();

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Soon padel',
            'visibility' => 'public',
            'sport' => 'padel',
            'event_date' => now()->addMinutes(30),
            'max_participants' => 8,
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Lunch tennis',
            'visibility' => 'public',
            'sport' => 'tennis',
            'event_date' => now()->addHours(2),
            'max_participants' => 8,
        ]);

        foreach (['/', '/games'] as $path) {
            $section = $this->soonSection($path);

            $this->assertNotNull($section);
            $this->assertStringContainsString('Soon padel', $section);
            $this->assertStringContainsString('in 30 min', $section);
            $this->assertStringContainsString('Lunch tennis', $section);
            $this->assertStringContainsString('in 2 h', $section);
        }
    }

    public function test_starting_soon_stays_hidden_without_a_game_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-25 10:00:00'));

        $host = User::factory()->create();
        $filler = User::factory()->create();

        $full = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Full court',
            'visibility' => 'public',
            'event_date' => now()->addHour(),
            'max_participants' => 1,
        ]);
        $full->participants()->attach($filler->id, ['status' => 'going']);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Tomorrow bowl',
            'visibility' => 'public',
            'event_date' => now()->addDay(),
            'max_participants' => 8,
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Pick a time',
            'visibility' => 'public',
            'schedule_type' => 'poll',
            'event_date' => now()->addHours(3),
            'max_participants' => 8,
        ]);

        Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Saturday kickabout',
            'visibility' => 'public',
            'event_date' => Carbon::parse('2026-11-28 15:00:00'),
            'max_participants' => 8,
        ]);

        $this->get('/')->assertDontSee('Starting soon');
        $this->get('/games')->assertDontSee('Starting soon');
    }

    public function test_come_solo_is_on_by_default_and_can_be_turned_off(): void
    {
        $host = User::factory()->create();

        $this->actingAs($host)
            ->get(route('events.create'))
            ->assertSee('Solo players welcome');

        $this->actingAs($host)
            ->post(route('events.store'), $this->payload('Open court'))
            ->assertSessionHasNoErrors();

        $open = Event::query()->where('title', 'Open court')->first();
        $this->assertNotNull($open);
        $this->assertTrue($open->come_solo);

        $this->actingAs($host)
            ->get(route('events.show', $open))
            ->assertSee('Come solo');

        $this->get(route('games.index'))
            ->assertSee('Come solo');

        $this->actingAs($host)
            ->post(route('events.store'), $this->payload('Pairs only', ['come_solo' => '0']))
            ->assertSessionHasNoErrors();

        $pairs = Event::query()->where('title', 'Pairs only')->first();
        $this->assertNotNull($pairs);
        $this->assertFalse($pairs->come_solo);

        $this->actingAs($host)
            ->get(route('events.show', $pairs))
            ->assertDontSee('Come solo');

        $this->actingAs($host)
            ->put(route('events.update', $open), $this->payload('Open court', [
                'come_solo' => '0',
                'event_date' => $open->event_date->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('events.show', $open));

        $this->assertFalse($open->fresh()->come_solo);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(string $title, array $overrides = []): array
    {
        return array_merge([
            'title' => $title,
            'sport' => 'tennis',
            'event_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'location' => 'Court 1',
            'visibility' => 'public',
            'max_participants' => 4,
        ], $overrides);
    }

    private function soonSection(string $path): ?string
    {
        $html = $this->get($path)->assertOk()->getContent();

        if (! preg_match('/aria-label="Starting soon".*<\\/section>/s', $html, $match)) {
            return null;
        }

        return $match[0];
    }
}
