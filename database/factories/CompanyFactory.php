<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'domain' => fake()->optional()->domainName(),
            'parent_company_id' => null,
            'country' => fake()->countryCode(),
            'timezone' => fake()->timezone(),
            'is_active' => true,
        ];
    }

    public function subsidiary(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_company_id' => Company::factory(),
        ]);
    }
}
