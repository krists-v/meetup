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
            ->assertSee('M22 4v16', false)
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
            ->assertSee('bg-pitch-700', false);

        $this->blade('<x-ui.button variant="secondary">See what\'s on</x-ui.button>')
            ->assertSee('motion-press', false)
            ->assertSee('bg-white', false);

        $this->blade('<x-ui.button variant="ghost" href="/games">Start a game</x-ui.button>')
            ->assertSee('href="/games"', false)
            ->assertSee('motion-press', false)
            ->assertSee('bg-transparent', false);
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
            ->assertSee('bg-pitch-100', false)
            ->assertSee('Indoor')
            ->assertSee('Outdoor')
            ->assertSee('bg-canvas', false);
    }

    public function test_chip_uses_the_activity_accent_and_falls_back(): void
    {
        foreach (Event::SPORTS as $sport => $label) {
            $this->blade('<x-ui.chip :sport="$sport" />', ['sport' => $sport])
                ->assertSee($label)
                ->assertSee('bg-activity-'.$sport.'-soft', false);
        }

        $this->blade('<x-ui.chip sport="curling" />')
            ->assertSee('Other')
            ->assertSee('bg-activity-other-soft', false);
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
        $this->assertStringContainsString('Padel', $html);
        $this->assertStringContainsString('bg-activity-padel-soft', $html);
        $this->assertStringContainsString('Thu 10 Dec, 18:00', $html);
        $this->assertStringContainsString('0 of 4 going · 4 spots left', $html);
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
            ->assertSee('1 of 1 going · 0 spots left')
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
            ->assertSee('Vote on a time')
            ->assertSee('2 time options')
            ->assertDontSee('Thu 10 Dec, 18:00');
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
            ->assertSee('Date')
            ->assertSee('Thu 10 Dec')
            ->assertSee('Time')
            ->assertSee('18:00')
            ->assertSee('Where')
            ->assertSee('Spots')
            ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=Park%20pitch', false)
            ->assertSee('bg-activity-padel-soft', false);
    }
}
