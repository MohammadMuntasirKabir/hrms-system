<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->unique()->words(2, true),
            'code' => fake()->unique()->bothify('??##'),
            'description' => fake()->optional()->sentence(),
            'parent_department_id' => null,
            'head_user_id' => null,
            'is_active' => true,
        ];
    }
}
