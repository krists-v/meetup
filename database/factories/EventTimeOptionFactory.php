<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventTimeOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventTimeOption>
 */
class EventTimeOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'starts_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'position' => 0,
        ];
    }
}
