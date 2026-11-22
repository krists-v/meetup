<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventTimeOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_icons_render_lucide_paths(): void
    {
        $this->blade('<x-ui.icon name="calendar" />')
            ->assertSee('M8 2v4', false)
            ->assertSee('stroke-width="2"', false);

        $this->blade('<x-ui.icon name="home" />')
            ->assertSee('M15 21v-8', false);

        $this->blade('<x-ui.icon name="missing" />')
            ->assertDontSee('<svg', false);
    }

    public function test_info_rows_pair_each_fact_with_its_icon(): void
    {
        $view = $this->blade(<<<'BLADE'
            <x-ui.info-row variant="date" label="When">Thu 10 Dec</x-ui.info-row>
            <x-ui.info-row variant="time" label="Time">18:00</x-ui.info-row>
            <x-ui.info-row variant="place" label="Where" href="https://maps.example/park">Park pitch</x-ui.info-row>
            <x-ui.info-row variant="spots">4 spots left</x-ui.info-row>
            <x-ui.info-row variant="level">Beginners welcome</x-ui.info-row>
            <x-ui.info-row variant="outdoor">Outdoors</x-ui.info-row>
            <x-ui.info-row variant="indoor">Indoors</x-ui.info-row>
        BLADE);

        $view->assertSee('When')
            ->assertSee('Thu 10 Dec')
            ->assertSee('M8 2v4', false)
            ->assertSee('Time')
            ->assertSee('18:00')
            ->assertSee('12 6 12 12 16 14', false)
            ->assertSee('Where')
            ->assertSee('href="https://maps.example/park"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('4 spots left')
            ->assertSee('M16 21v-2', false)
            ->assertSee('Beginners welcome')
            ->assertSee('M18 17V9', false)
            ->assertSee('Outdoors')
            ->assertSee('M12 2v2', false)
            ->assertSee('Indoors')
            ->assertSee('M15 21v-8', false);
    }

    public function test_buttons_use_press_feedback(): void
    {
        $this->blade('<x-ui.button>I\'m in</x-ui.button>')
            ->assertSee('I\'m in', false)
            ->assertSee('motion-press', false)
            ->assertSee('bg-ink', false)
            ->assertSee('text-white', false);

        $this->blade('<x-ui.button variant="secondary">See what\'s on</x-ui.button>')
            ->assertSee('motion-press', false)
            ->assertSee('bg-mist', false)
            ->assertSee('ring-line', false)
            ->assertSee('text-ink', false);

        $this->blade('<x-ui.button variant="ghost" href="/games">Start a game</x-ui.button>')
            ->assertSee('href="/games"', false)
            ->assertSee('motion-press', false)
            ->assertSee('bg-transparent', false)
            ->assertSee('w-fit', false);
    }

    public function test_primary_buttons_fill_a_card_and_a_phone(): void
    {
        $this->blade('<x-ui.button>I\'m in</x-ui.button>')
            ->assertSee('w-full', false)
            ->assertSee('sm:w-fit', false)
            ->assertSee('bg-ink', false);

        $blocked = (string) $this->blade('<x-ui.button block>Save</x-ui.button>');
        $this->assertStringContainsString('w-full', $blocked);
        $this->assertStringNotContainsString('sm:w-fit', $blocked);

        $secondary = (string) $this->blade('<x-ui.button variant="secondary">Confirm</x-ui.button>');
        $this->assertStringContainsString('w-fit', $secondary);
        $this->assertStringNotContainsString('w-full', $secondary);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in')
            ->assertDontSee('sm:w-fit', false);
    }

    public function test_badges_render_the_presets(): void
    {
        $view = $this->blade(<<<'BLADE'
            <x-ui.badge preset="full" />
            <x-ui.badge preset="beginners" />
            <x-ui.badge preset="indoor" />
            <x-ui.badge preset="outdoor" />
        BLADE);

        $view->assertSee('Full')
            ->assertSee('bg-warn-soft', false)
            ->assertSee('Beginners welcome')
            ->assertSee('bg-success-soft', false)
            ->assertSee('Indoor')
            ->assertSee('Outdoor')
            ->assertSee('bg-white', false)
            ->assertSee('ring-line', false)
            ->assertSee('text-ink', false);
    }

    public function test_chip_uses_the_activity_accent_and_falls_back(): void
    {
        foreach (Event::SPORTS as $sport => $label) {
            $this->blade('<x-ui.chip :sport="$sport" />', ['sport' => $sport])
                ->assertSee($label)
                ->assertSee('bg-mist', false)
                ->assertSee('border-line', false)
                ->assertSee('text-ink', false);
        }

        $this->blade('<x-ui.chip sport="curling" />')
            ->assertSee('Other')
            ->assertSee('bg-mist', false)
            ->assertSee('border-line', false);
    }

    public function test_empty_state_offers_a_next_step(): void
    {
        $this->blade(<<<'BLADE'
            <x-ui.empty-state title="Nothing on yet" description="Start a game, or see what's on nearby.">
                <x-slot name="action">
                    <a href="/games">See what's on</a>
                </x-slot>
            </x-ui.empty-state>
        BLADE)
            ->assertSee('Nothing on yet')
            ->assertSee('Start a game, or see what\'s on nearby.')
            ->assertSee('href="/games"', false)
            ->assertSee('See what\'s on', false);
    }

    public function test_game_card_shows_the_plan_and_keeps_the_map_link_outside(): void
    {
        $event = Event::factory()->create([
            'title' => 'Thursday padel',
            'sport' => 'padel',
            'sport_type' => 'padel',
            'location' => 'Park pitch',
            'max_participants' => 4,
            'event_date' => Carbon::create(2026, 12, 10, 18, 0, 0),
            'schedule_type' => 'fixed',
            'visibility' => 'public',
        ]);

        $html = (string) $this->blade('<x-ui.game-card :event="$event" />', ['event' => $event]);

        $this->assertStringContainsString('Thursday padel', $html);
        $this->assertStringContainsString('Thursday, 10 December · 18:00', $html);
        $this->assertStringContainsString('4 spots left', $html);
        $this->assertStringContainsString('0 / 4 players', $html);
        $this->assertStringContainsString('images/sports/padel.svg', $html);
        $this->assertStringContainsString("I'm in", $html);
        $this->assertStringContainsString('https://www.google.com/maps/search/?api=1&amp;query=Park%20pitch', $html);

        $show = strpos($html, route('events.show', $event));
        $map = strpos($html, 'maps/search');
        $close = strpos($html, '</a>', $show);

        $this->assertNotFalse($show);
        $this->assertNotFalse($map);
        $this->assertNotFalse($close);
        $this->assertLessThan($map, $close);
    }

    public function test_full_game_card_shows_the_full_badge(): void
    {
        $event = Event::factory()->create([
            'title' => 'Full court',
            'sport' => 'tennis',
            'sport_type' => 'tennis',
            'location' => 'Court 1',
            'max_participants' => 1,
            'schedule_type' => 'fixed',
        ]);
        $event->participants()->attach($event->user_id, ['status' => 'going']);

        $this->blade('<x-ui.game-card :event="$event" />', ['event' => $event->fresh()])
            ->assertSee('1 / 1 players')
            ->assertSee('Full')
            ->assertSee('bg-warn-soft', false);
    }

    public function test_poll_card_asks_for_a_time_instead_of_a_date(): void
    {
        $event = Event::factory()->create([
            'title' => 'Padel vote',
            'sport' => 'padel',
            'sport_type' => 'padel',
            'location' => 'Court 2',
            'schedule_type' => 'poll',
            'max_participants' => 4,
            'event_date' => Carbon::create(2026, 12, 10, 18, 0, 0),
        ]);
        EventTimeOption::factory()->count(2)->create(['event_id' => $event->id]);
        $event->loadCount('timeOptions');

        $this->blade('<x-ui.game-card :event="$event" />', ['event' => $event])
            ->assertSee('Pick a time')
            ->assertSee('2 times to pick')
            ->assertDontSee('Thursday, 10 December');
    }

    public function test_game_page_uses_info_rows_for_the_plan(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Thursday padel',
            'sport' => 'padel',
            'sport_type' => 'padel',
            'location' => 'Park pitch',
            'max_participants' => 4,
            'visibility' => 'public',
            'event_date' => Carbon::create(2026, 12, 10, 18, 0, 0),
            'schedule_type' => 'fixed',
        ]);

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('When')
            ->assertSee('Thursday, 10 December')
            ->assertSee('18:00')
            ->assertSee('Where')
            ->assertSee('Players')
            ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=Park%20pitch', false)
            ->assertSee('images/sports/padel.svg', false);
    }

    public function test_the_page_is_grey_and_cards_stay_white(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--canvas: 245 245 247;', $css);
        $this->assertStringContainsString('--mist: 229 229 234;', $css);

        $this->get('/')
            ->assertOk()
            ->assertSee('bg-canvas', false)
            ->assertSee('data-app-header', false)
            ->assertSee('content="#F5F5F7"', false);

        $this->blade('<x-ui.card>Thursday padel</x-ui.card>')
            ->assertSee('bg-white', false)
            ->assertSee('border-line', false)
            ->assertSee('shadow-card', false);

        $this->assertStringContainsString('--highlight: 240 250 243;', $css);
        $this->assertStringContainsString('--highlight-line: 197 232 208;', $css);
    }

    public function test_the_highlight_card_has_one_green_edge(): void
    {
        $event = Event::factory()->create([
            'title' => 'Thursday padel',
            'sport' => 'padel',
            'sport_type' => 'padel',
            'visibility' => 'public',
        ]);

        $plain = (string) $this->blade('<x-ui.game-card :event="$event" />', ['event' => $event]);
        $this->assertStringContainsString('border-line bg-white shadow-card', $plain);
        $this->assertStringNotContainsString('data-highlight="1"', $plain);
        $this->assertStringContainsString('bg-ink', $plain);

        $highlight = (string) $this->blade('<x-ui.game-card :event="$event" highlight />', ['event' => $event]);
        $this->assertStringContainsString('data-highlight="1"', $highlight);
        $this->assertStringContainsString('border-highlight-line bg-highlight', $highlight);
        $this->assertStringNotContainsString('border-line bg-white', $highlight);
        $this->assertStringNotContainsString('shadow-card', $highlight);
        $this->assertStringContainsString('text-ink', $highlight);
        $this->assertStringContainsString('bg-ink', $highlight);
        $this->assertStringContainsString("I'm in", $highlight);
    }
}
