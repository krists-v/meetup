<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_counts_past_games_as_showed_up_unless_marked_a_no_show(): void
    {
        $player = User::factory()->create();

        $unmarked = $this->going($player, now()->subDays(2));
        foreach (range(3, 10) as $days) {
            $this->going($player, now()->subDays($days));
        }

        $missed = $this->going($player, now()->subDay());
        $missed->participants()->updateExistingPivot($player->id, ['attended' => false]);

        $future = $this->going($player, now()->addDays(3));
        $future->participants()->updateExistingPivot($player->id, ['attended' => false]);

        $maybe = Event::factory()->create(['event_date' => now()->subDays(4)]);
        $maybe->participants()->attach($player->id, ['status' => 'maybe', 'attended' => false]);

        $this->assertNull(DB::table('event_user')->where('event_id', $unmarked->id)->value('attended'));
        $this->assertTrue(Schema::hasColumn('event_user', 'attended'));

        $this->actingAs($player)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-attendance="Shows up 9/10"', false)
            ->assertSee('Shows up 9/10');
    }

    public function test_an_explicit_yes_counts_as_showed_up(): void
    {
        $player = User::factory()->create();
        $this->going($player, now()->subDays(3));
        $this->going($player, now()->subDays(2));
        $shown = $this->going($player, now()->subDay());
        $shown->participants()->updateExistingPivot($player->id, ['attended' => true]);

        $this->assertSame('Shows up 3/3', $player->fresh()->attendanceLabel());
    }

    public function test_fewer_than_three_past_games_says_new_player(): void
    {
        $player = User::factory()->create();
        $this->going($player, now()->subDays(2));
        $this->going($player, now()->subDay());

        $this->actingAs($player)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('data-attendance="New player"', false)
            ->assertSee('New player')
            ->assertDontSee('Shows up');
    }

    public function test_the_going_list_marks_who_shows_up_and_who_is_new(): void
    {
        $host = User::factory()->create();
        $regular = User::factory()->create(['name' => 'Regular Pat']);
        $newcomer = User::factory()->create(['name' => 'New Nia']);

        foreach (range(1, 3) as $days) {
            $this->going($regular, now()->subDays($days));
        }

        $game = Event::factory()->create([
            'user_id' => $host->id,
            'title' => 'Tonight padel',
            'event_date' => now()->addDay(),
            'visibility' => 'public',
        ]);
        $game->participants()->attach([
            $regular->id => ['status' => 'going'],
            $newcomer->id => ['status' => 'going'],
        ]);

        $this->actingAs($host)
            ->get(route('events.show', $game))
            ->assertOk()
            ->assertSee('data-attendance="Shows up 3/3"', false)
            ->assertSee('data-attendance="New player"', false)
            ->assertSee('Regular Pat')
            ->assertSee('New Nia');
    }

    private function going(User $player, \DateTimeInterface $when): Event
    {
        $event = Event::factory()->create([
            'event_date' => $when,
            'visibility' => 'public',
        ]);
        $event->participants()->attach($player->id, [
            'status' => 'going',
            'attended' => null,
        ]);

        return $event;
    }
}
