<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'sport_type' => fake()->randomElement(['football', 'tennis', 'basketball', 'other']),
            'event_date' => fake()->dateTimeBetween('+1 day', '+3 weeks'),
            'location' => fake()->streetAddress(),
            'max_participants' => fake()->optional()->numberBetween(4, 14),
            'description' => fake()->optional()->sentence(),
            'visibility' => 'public',
            'invite_token' => Str::random(32),
        ];
    }

    public function private(): static
    {
        return $this->state(fn () => ['visibility' => 'private']);
    }
}
