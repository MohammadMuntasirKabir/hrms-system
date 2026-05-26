<?php

namespace Database\Factories;

use App\Models\Salary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Salary>
 */
class SalaryFactory extends Factory
{
    public function definition(): array
    {
        $base = fake()->numberBetween(30000, 150000);
        $allowances = fake()->numberBetween(0, 20000);
        $deductions = fake()->numberBetween(0, 10000);

        return [
            'contract_id' => null,
            'base_salary' => $base,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_salary' => $base + $allowances - $deductions,
            'currency' => 'BDT',
            'pay_frequency' => 'monthly',
            'effective_from' => fake()->date(),
            'effective_until' => null,
            'status' => 'active',
            'notes' => null,
        ];
    }
}
