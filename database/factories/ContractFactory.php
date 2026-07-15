<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'contract_type' => 'full_time',
            'position' => fake()->jobTitle(),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->optional(0.3)->dateTimeBetween('now', '+2 years'),
            'salary' => fake()->numberBetween(30000, 150000),
            'currency' => 'BDT',
            'status' => 'active',
        ];
    }
}
