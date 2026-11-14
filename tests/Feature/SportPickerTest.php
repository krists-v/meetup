<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_quick_pick_is_stored_and_shown(): void
    {
        $user = User::factory()->create();

        foreach (Event::SPORTS as $value => $label) {
            if ($value === 'other') {
                continue;
            }

            $title = $label.' pickup';

            $this->actingAs($user)
                ->post(route('events.store'), $this->payload($title, ['sport' => $value]))
                ->assertSessionHasNoErrors();

            $event = Event::query()->where('title', $title)->first();

            $this->assertNotNull($event);
            $this->assertSame($value, $event->sport);
            $this->assertNull($event->custom_sport);
            $this->assertSame(
                in_array($value, ['football', 'tennis', 'basketball'], true) ? $value : 'other',
                $event->sport_type,
            );

            $this->actingAs($user)
                ->get(route('events.show', $event))
                ->assertOk()
                ->assertSee($label);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee($label);
        }
    }

    public function test_other_requires_custom_text_and_shows_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('events.store'), $this->payload('Empty sport', [
                'sport' => 'other',
                'custom_sport' => '   ',
            ]))
            ->assertSessionHasErrors('custom_sport');

        $this->assertDatabaseCount('events', 0);

        $this->actingAs($user)
            ->post(route('events.store'), $this->payload('Floorball night', [
                'sport' => 'other',
                'custom_sport' => 'Floorball',
            ]))
            ->assertSessionHasNoErrors();

        $event = Event::query()->where('title', 'Floorball night')->first();

        $this->assertSame('other', $event->sport);
        $this->assertSame('other', $event->sport_type);
        $this->assertSame('Floorball', $event->custom_sport);

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Floorball');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Floorball');
    }

    public function test_editing_a_legacy_event_prefills_known_and_unknown_sports(): void
    {
        $user = User::factory()->create();

        $legacy = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old football',
            'sport_type' => 'football',
            'sport' => null,
            'visibility' => 'public',
        ]);

        $edit = $this->actingAs($user)->get(route('events.edit', $legacy));
        $edit->assertOk()->assertSee('Padel')->assertSee('Hockey')->assertSee('Volleyball');
        $this->assertMatchesRegularExpression('/id="sport-football"[^>]*checked/', $edit->getContent());

        $this->actingAs($user)
            ->put(route('events.update', $legacy), $this->payload('Old football', [
                'sport' => 'hockey',
                'event_date' => $legacy->event_date->format('Y-m-d\TH:i'),
                'location' => $legacy->location,
                'visibility' => 'public',
            ]))
            ->assertRedirect(route('events.show', $legacy));

        $legacy->refresh();
        $this->assertSame('hockey', $legacy->sport);
        $this->assertSame('other', $legacy->sport_type);
        $this->assertSame('Hockey', $legacy->sportLabel());

        $unknown = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Ice game',
            'sport_type' => 'other',
            'sport' => 'Curling',
            'visibility' => 'public',
        ]);

        $unknownEdit = $this->actingAs($user)->get(route('events.edit', $unknown));
        $unknownEdit->assertOk();
        $this->assertMatchesRegularExpression('/id="sport-other"[^>]*checked/', $unknownEdit->getContent());
        $unknownEdit->assertSee('value="Curling"', false);

        $this->actingAs($user)
            ->put(route('events.update', $unknown), $this->payload('Ice game', [
                'sport' => 'other',
                'custom_sport' => 'Curling',
                'event_date' => $unknown->event_date->format('Y-m-d\TH:i'),
                'location' => $unknown->location,
                'visibility' => 'public',
            ]))
            ->assertSessionHasNoErrors();

        $unknown->refresh();
        $this->assertSame('Curling', $unknown->custom_sport);
        $this->assertSame('Curling', $unknown->sportLabel());

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Hockey')
            ->assertSee('Curling');
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
