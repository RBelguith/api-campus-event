<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampusEvent>
 */
class CampusEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 day', '+2 months');

        return [
            'user_id' => User::factory(),
            'venue_id' => Venue::factory(),
            'title' => fake()->unique()->sentence(3),
            'slug' => Str::slug(fake()->unique()->sentence(3)).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+2 hours'),
            'capacity' => fake()->numberBetween(20, 300),
            'is_public' => true,
        ];
    }

    /**
     * Mark the event as publicly listed.
     */
    public function isPublic(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Mark the event as private.
     */
    public function isPrivate(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
}
