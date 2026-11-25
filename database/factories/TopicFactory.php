<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Topic>
 */
class TopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Mark the topic as a technical one.
     */
    public function technical(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Robotique '.fake()->unique()->numberBetween(1, 9999),
            'description' => 'Atelier technique du campus.',
        ]);
    }
}
