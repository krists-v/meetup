<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $alex = User::factory()->create([
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
        ]);

        $sam = User::factory()->create([
            'name' => 'Sam Okonkwo',
            'email' => 'sam@example.com',
        ]);

        $jordan = User::factory()->create([
            'name' => 'Jordan Lee',
            'email' => 'jordan@example.com',
        ]);

        $football = Event::factory()->create([
            'user_id' => $alex->id,
            'title' => 'Sunday five-a-side',
            'sport_type' => 'football',
            'event_date' => now()->addDays(2)->setTime(18, 0),
            'location' => 'Riverside pitches, pitch 3',
            'max_participants' => 10,
            'description' => 'Bib versus bib. Bring water.',
            'visibility' => 'public',
        ]);

        $football->participants()->attach($alex->id, ['status' => 'going']);
        $football->participants()->attach($sam->id, ['status' => 'going']);
        $football->participants()->attach($jordan->id, ['status' => 'maybe']);

        Event::factory()->create([
            'user_id' => $sam->id,
            'title' => 'Morning tennis',
            'sport_type' => 'tennis',
            'event_date' => now()->addDays(4)->setTime(9, 30),
            'location' => 'City courts, court 2',
            'max_participants' => 4,
            'description' => 'Doubles if four people show up.',
            'visibility' => 'public',
        ]);

        $basketball = Event::factory()->private()->create([
            'user_id' => $jordan->id,
            'title' => 'Thursday basketball',
            'sport_type' => 'basketball',
            'event_date' => now()->addDays(5)->setTime(20, 0),
            'location' => 'School gym',
            'max_participants' => 8,
            'description' => 'Invite-only run. Share the link with the usual group.',
        ]);

        $basketball->participants()->attach($alex->id, ['status' => 'going']);

        $poll = Event::factory()->create([
            'user_id' => $sam->id,
            'title' => 'Friday game — pick a time',
            'sport_type' => 'football',
            'schedule_type' => 'poll',
            'event_date' => now()->addDays(8)->setTime(20, 0),
            'location' => 'Riverside pitches',
            'max_participants' => 10,
            'visibility' => 'public',
            'description' => 'Vote on a kickoff. The best time can be locked in.',
        ]);

        $poll->timeOptions()->createMany([
            ['starts_at' => now()->addDays(6)->setTime(18, 0), 'position' => 0],
            ['starts_at' => now()->addDays(8)->setTime(20, 0), 'position' => 1],
        ]);

        Event::factory()->create([
            'user_id' => $alex->id,
            'title' => 'Last week kickabout',
            'sport_type' => 'football',
            'event_date' => now()->subDays(3)->setTime(19, 0),
            'location' => 'Riverside pitches',
            'visibility' => 'public',
        ]);
    }
}
