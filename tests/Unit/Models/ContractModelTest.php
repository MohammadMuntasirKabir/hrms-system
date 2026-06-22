<?php

use App\Models\Contract;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// === Contract Accessors ===

test('contract is expired when end date is past', function () {
    $contract = Contract::factory()->make([
        'end_date' => now()->subDays(5),
    ]);

    expect($contract->is_expired)->toBeTrue();
});

test('contract is not expired when end date is future', function () {
    $contract = Contract::factory()->make([
        'end_date' => now()->addDays(5),
    ]);

    expect($contract->is_expired)->toBeFalse();
});

test('contract is not expired when end date is null', function () {
    $contract = Contract::factory()->make([
        'end_date' => null,
    ]);

    expect($contract->is_expired)->toBeFalse();
});

test('contract is expiring soon when end date within 30 days', function () {
    $contract = Contract::factory()->make([
        'end_date' => now()->addDays(15),
    ]);

    expect($contract->is_expiring_soon)->toBeTrue();
});

test('contract is not expiring soon when end date more than 30 days', function () {
    $contract = Contract::factory()->make([
        'end_date' => now()->addDays(60),
    ]);

    expect($contract->is_expiring_soon)->toBeFalse();
});

test('contract is not expiring soon when end date is past', function () {
    $contract = Contract::factory()->make([
        'end_date' => now()->subDays(5),
    ]);

    expect($contract->is_expiring_soon)->toBeFalse();
});

test('contract is not expiring soon when end date is null', function () {
    $contract = Contract::factory()->make([
        'end_date' => null,
    ]);

    expect($contract->is_expiring_soon)->toBeFalse();
});

// === Contract Relations ===

test('contract belongs to user', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($contract->user->id)->toBe($user->id);
});

test('contract belongs to company', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($contract->company->id)->toBe($company->id);
});

test('contract belongs to department', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($contract->department->id)->toBe($dept->id);
});

// === Contract Casts ===

test('contract salary is cast to decimal', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'salary' => 50000.50,
    ]);

    expect($contract->salary)->toBe(50000.50);
});

test('contract dates are cast to date', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'start_date' => '2025-01-15',
        'end_date' => '2025-12-31',
    ]);

    expect($contract->start_date)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($contract->end_date)->toBeInstanceOf(\Carbon\Carbon::class);
});


uses(RefreshDatabase::class);