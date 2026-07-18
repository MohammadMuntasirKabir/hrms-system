<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobApplicantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'city' => fake()->optional()->city(),
            'country' => fake()->countryCode(),
            'cover_letter' => fake()->optional()->paragraph(),
            'source' => fake()->randomElement(['Website', 'LinkedIn', 'Indeed', 'Referral']),
            'expected_salary' => fake()->optional()->numberBetween(30000, 150000),
            'currency' => 'BDT',
            'available_from' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'status' => fake()->randomElement(['pending', 'reviewing', 'shortlisted', 'hired', 'rejected']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
