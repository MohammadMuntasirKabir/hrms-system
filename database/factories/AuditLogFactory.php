<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => null,
            'action' => fake()->randomElement(['created', 'updated', 'deleted']),
            'model_type' => User::class,
            'model_id' => fake()->randomNumber(2),
            'description' => fake()->sentence(),
            'old_values' => null,
            'new_values' => null,
        ];
    }
}
