<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DesignationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->jobTitle(),
            'description' => fake()->optional()->sentence(),
            'level' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
