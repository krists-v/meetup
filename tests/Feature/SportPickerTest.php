<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
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

            RateLimiter::clear(md5('events'.$user->id));

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

            $this->assertSame(Event::defaultSetting($value), $event->setting);
            $this->assertNull($event->level);
            $this->assertFileExists(public_path('images/og/'.$value.'.png'));

            $show = $this->actingAs($user)
                ->get(route('events.show', $event))
                ->assertOk()
                ->assertSee($label)
                ->assertSee($event->isIndoor() ? 'Indoors' : 'Outdoors')
                ->assertSee('Beginners welcome')
                ->assertSee('images/og/'.$value.'.png', false);

            $show->assertSee('images/sports/'.$value.'.svg', false);

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

    public function test_legacy_other_event_can_be_edited_without_a_custom_name(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Mystery game',
            'sport_type' => 'other',
            'sport' => 'other',
            'custom_sport' => null,
            'visibility' => 'public',
        ]);

        $this->actingAs($user)
            ->put(route('events.update', $event), $this->payload('Mystery game renamed', [
                'sport' => 'other',
                'custom_sport' => '',
                'event_date' => $event->event_date->format('Y-m-d\TH:i'),
                'location' => $event->location,
                'visibility' => 'public',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('events.show', $event));

        $event->refresh();
        $this->assertSame('Mystery game renamed', $event->title);
        $this->assertSame('other', $event->sport);
        $this->assertNull($event->custom_sport);

        $named = Event::factory()->create([
            'user_id' => $user->id,
            'sport_type' => 'football',
            'sport' => 'football',
            'visibility' => 'public',
        ]);

        $this->actingAs($user)
            ->put(route('events.update', $named), $this->payload($named->title, [
                'sport' => 'other',
                'custom_sport' => '',
                'event_date' => $named->event_date->format('Y-m-d\TH:i'),
                'location' => $named->location,
                'visibility' => 'public',
            ]))
            ->assertSessionHasErrors('custom_sport');
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
