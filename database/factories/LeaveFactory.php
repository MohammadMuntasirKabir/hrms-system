<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+2 months');
        $days = fake()->numberBetween(1, 10);
        $end = (clone $start)->modify("+{$days} days");

        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'type' => fake()->randomElement(Leave::TYPES),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'total_days' => $days,
            'reason' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(Leave::STATUSES),
            'approved_by' => null,
            'admin_note' => null,
            'decided_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ]);
    }
}
