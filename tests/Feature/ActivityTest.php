<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_labels_match_the_activity_list(): void
    {
        $labels = [];

        foreach (Event::ACTIVITIES as $slug => $activity) {
            $labels[$slug] = $activity['label'];
        }

        $this->assertSame($labels, Event::SPORTS);
    }

    public function test_bowling_defaults_indoors_and_can_be_played_outside(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('events.create'))
            ->assertOk()
            ->assertSee('Climbing')
            ->assertSee('Indoors or outdoors')
            ->assertSee('All levels means beginners are welcome.');

        $this->actingAs($user)
            ->post(route('events.store'), $this->payload('Lane night', [
                'sport' => 'bowling',
            ]))
            ->assertSessionHasNoErrors();

        $event = Event::query()->where('title', 'Lane night')->first();

        $this->assertSame('indoor', $event->setting);
        $this->assertNull($event->level);
        $this->assertSame('other', $event->sport_type);

        $this->actingAs($user)
            ->put(route('events.update', $event), $this->payload('Lane night', [
                'sport' => 'bowling',
                'setting' => 'outdoor',
                'level' => 'beginner',
                'event_date' => $event->event_date->format('Y-m-d\TH:i'),
            ]))
            ->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('outdoor', $event->setting);
        $this->assertSame('beginner', $event->level);

        $this->actingAs($user)
            ->get(route('events.edit', $event))
            ->assertOk()
            ->assertSee('Bowling');
    }

    public function test_a_custom_activity_still_keeps_its_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('events.store'), $this->payload('Floorball night', [
                'sport' => 'other',
                'custom_sport' => 'Floorball',
                'setting' => 'indoor',
            ]))
            ->assertSessionHasNoErrors();

        $event = Event::query()->where('title', 'Floorball night')->first();

        $this->assertSame('Floorball', $event->sportLabel());
        $this->assertSame('indoor', $event->setting);
        $this->assertSame('other', $event->sport);
    }

    public function test_advanced_games_show_the_level_and_not_the_beginners_badge(): void
    {
        $event = Event::factory()->create([
            'title' => 'Hill repeats',
            'sport' => 'running',
            'sport_type' => 'other',
            'level' => 'advanced',
            'setting' => 'outdoor',
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);

        $this->get(route('games.index'))
            ->assertOk()
            ->assertSee('Hill repeats')
            ->assertSee('Outdoors')
            ->assertSee('Advanced')
            ->assertDontSee('Beginners welcome');

        $this->actingAs(User::factory()->create())
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Advanced')
            ->assertDontSee('Beginners welcome');
    }

    public function test_all_levels_and_beginner_games_welcome_beginners(): void
    {
        Event::factory()->create([
            'title' => 'Open climb',
            'sport' => 'climbing',
            'sport_type' => 'other',
            'level' => null,
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);
        Event::factory()->create([
            'title' => 'First ride',
            'sport' => 'cycling',
            'sport_type' => 'other',
            'level' => 'beginner',
            'visibility' => 'public',
            'event_date' => now()->addDays(2),
        ]);

        $this->get(route('games.index'))
            ->assertOk()
            ->assertSee('Open climb')
            ->assertSee('First ride')
            ->assertSee('Indoors')
            ->assertSee('Outdoors')
            ->assertSee('All levels')
            ->assertSee('Beginner');
    }

    public function test_games_can_be_filtered_by_setting_level_and_category(): void
    {
        Event::factory()->create([
            'title' => 'Indoor padel',
            'sport' => 'padel',
            'sport_type' => 'other',
            'setting' => 'indoor',
            'level' => null,
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);
        Event::factory()->create([
            'title' => 'Park run',
            'sport' => 'running',
            'sport_type' => 'other',
            'setting' => 'outdoor',
            'level' => 'intermediate',
            'visibility' => 'public',
            'event_date' => now()->addDays(2),
        ]);
        Event::factory()->create([
            'title' => 'Sunday football',
            'sport' => 'football',
            'sport_type' => 'football',
            'setting' => 'outdoor',
            'level' => 'pro',
            'visibility' => 'public',
            'event_date' => now()->addDays(3),
        ]);

        $this->get(route('games.index', ['setting' => 'indoor']))
            ->assertOk()
            ->assertSee('Indoor padel')
            ->assertDontSee('Park run')
            ->assertSee('setting=indoor', false);

        $this->get(route('games.index', ['level' => 'intermediate']))
            ->assertOk()
            ->assertSee('Park run')
            ->assertDontSee('Sunday football')
            ->assertSee('level=intermediate', false);

        $this->get(route('games.index', ['level' => 'all']))
            ->assertOk()
            ->assertSee('Indoor padel')
            ->assertDontSee('Park run');

        $this->get(route('games.index', ['category' => 'outdoor']))
            ->assertOk()
            ->assertSee('Park run')
            ->assertDontSee('Indoor padel')
            ->assertDontSee('Sunday football')
            ->assertSee('category=outdoor', false);

        $this->get(route('games.index', ['category' => 'racket']))
            ->assertOk()
            ->assertSee('Indoor padel')
            ->assertDontSee('Park run');

        $this->get(route('games.index', ['level' => 'legend']))
            ->assertOk()
            ->assertSee('Indoor padel')
            ->assertSee('Park run');
    }

    public function test_an_unknown_level_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('events.store'), $this->payload('Bad level', [
                'sport' => 'hiking',
                'level' => 'legend',
            ]))
            ->assertSessionHasErrors('level');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_a_new_activity_can_be_a_favorite_sport(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'favorite_sports' => ['bowling', 'hiking'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(['bowling', 'hiking'], $user->fresh()->favorite_sports);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(string $title, array $overrides = []): array
    {
        return [
            'title' => $title,
            'sport' => 'football',
            'event_date' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'location' => 'Park',
            'visibility' => 'public',
            ...$overrides,
        ];
    }
}
