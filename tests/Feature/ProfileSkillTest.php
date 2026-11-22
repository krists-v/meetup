<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileSkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_player_can_save_a_level_for_each_sport_they_play(): void
    {
        $user = User::factory()->create([
            'name' => 'Alex Rivera',
            'favorite_sports' => ['football'],
            'sport_levels' => ['football' => 'beginner'],
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'favorite_sports' => ['padel', 'football'],
                'sport_levels' => [
                    'padel' => 'advanced',
                    'football' => '',
                    'tennis' => 'pro',
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame(['padel', 'football'], $user->favorite_sports);
        $this->assertSame(['padel' => 'advanced'], $user->sport_levels);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('id="sport-level-padel"', false)
            ->assertSee('data-sport-level="padel:advanced"', false)
            ->assertSee('Padel · Advanced')
            ->assertSee('Add your level if you want. People see it when you show up.');

        $padel = Event::factory()->create([
            'title' => 'Evening padel',
            'sport' => 'padel',
            'level' => null,
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);
        $padel->participants()->attach($user->id, ['status' => 'going']);

        $football = Event::factory()->create([
            'title' => 'Evening football',
            'sport' => 'football',
            'level' => null,
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);
        $football->participants()->attach($user->id, ['status' => 'going']);

        $this->actingAs($user)
            ->get(route('events.show', $padel))
            ->assertOk()
            ->assertSee('data-player-level="advanced"', false)
            ->assertSee('Alex Rivera')
            ->assertSee('Advanced');

        $this->actingAs($user)
            ->get(route('events.show', $football))
            ->assertOk()
            ->assertDontSee('data-player-level=', false);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'favorite_sports' => ['football'],
                'sport_levels' => ['padel' => 'advanced'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->sport_levels);
    }

    public function test_a_level_has_to_be_one_of_the_four_for_a_real_sport(): void
    {
        $user = User::factory()->create([
            'sport_levels' => ['padel' => 'beginner'],
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'favorite_sports' => ['padel'],
                'sport_levels' => ['padel' => 'legend'],
            ])
            ->assertSessionHasErrors('sport_levels.padel');

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'favorite_sports' => ['padel'],
                'sport_levels' => ['curling' => 'pro'],
            ])
            ->assertSessionHasErrors('sport_levels');

        $this->assertSame(['padel' => 'beginner'], $user->fresh()->sport_levels);
    }

    public function test_games_for_a_sport_start_at_your_level_until_you_pick_another(): void
    {
        $user = User::factory()->create([
            'sport_levels' => ['padel' => 'intermediate'],
        ]);
        Event::factory()->create([
            'title' => 'Easy padel',
            'sport' => 'padel',
            'level' => 'beginner',
            'visibility' => 'public',
            'event_date' => now()->addDay(),
        ]);
        Event::factory()->create([
            'title' => 'Club padel',
            'sport' => 'padel',
            'level' => 'intermediate',
            'visibility' => 'public',
            'event_date' => now()->addDays(2),
        ]);

        $this->actingAs($user)
            ->get(route('games.index', ['sport' => 'padel']))
            ->assertRedirect(route('games.index', ['sport' => 'padel', 'level' => 'intermediate']));

        $this->actingAs($user)
            ->get(route('games.index', ['sport' => 'padel', 'level' => 'intermediate']))
            ->assertOk()
            ->assertSee('Club padel')
            ->assertDontSee('Easy padel');

        $this->actingAs($user)
            ->get(route('games.index', ['sport' => 'padel', 'level' => 'any']))
            ->assertOk()
            ->assertSee('Easy padel')
            ->assertSee('Club padel');

        $this->actingAs($user)
            ->get(route('games.index'))
            ->assertOk()
            ->assertSee('Easy padel')
            ->assertSee('Club padel');
    }
}
