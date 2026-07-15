<?php

use App\Models\Salary;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;

// === Salary Calculation ===

test('calculateNetSalary returns correct value', function () {
    $salary = Salary::factory()->make([
        'base_salary' => 50000,
        'allowances' => 10000,
        'deductions' => 5000,
    ]);

    expect($salary->calculateNetSalary())->toBe(55000.0);
});

test('calculateNetSalary handles zero allowances and deductions', function () {
    $salary = Salary::factory()->make([
        'base_salary' => 60000,
        'allowances' => 0,
        'deductions' => 0,
    ]);

    expect($salary->calculateNetSalary())->toBe(60000.0);
});

test('calculateNetSalary handles decimal values', function () {
    $salary = Salary::factory()->make([
        'base_salary' => 50000.50,
        'allowances' => 10000.25,
        'deductions' => 5000.75,
    ]);

    expect($salary->calculateNetSalary())->toBe(55000.0);
});

// === Annual Salary ===

test('annual salary for monthly pay frequency', function () {
    $salary = Salary::factory()->make([
        'net_salary' => 50000,
        'pay_frequency' => 'monthly',
    ]);

    expect($salary->getAnnualSalary())->toBe(600000.0);
});

test('annual salary for bi-weekly pay frequency', function () {
    $salary = Salary::factory()->make([
        'net_salary' => 20000,
        'pay_frequency' => 'bi_weekly',
    ]);

    expect($salary->getAnnualSalary())->toBe(520000.0);
});

test('annual salary for weekly pay frequency', function () {
    $salary = Salary::factory()->make([
        'net_salary' => 10000,
        'pay_frequency' => 'weekly',
    ]);

    expect($salary->getAnnualSalary())->toBe(520000.0);
});

// === Salary Relations ===

test('salary belongs to user', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($salary->user->id)->toBe($user->id);
});

test('salary belongs to company', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($salary->company->id)->toBe($company->id);
});

test('salary belongs to department', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($salary->department->id)->toBe($dept->id);
});

test('salary belongs to designation', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = \App\Models\Designation::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
    ]);

    expect($salary->designation->id)->toBe($desig->id);
});

test('salary belongs to contract', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);
    $salary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'contract_id' => $contract->id,
    ]);

    expect($salary->contract->id)->toBe($contract->id);
});

test('salary belongs to creator', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $creator = User::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'created_by' => $creator->id,
    ]);

    expect($salary->creator->id)->toBe($creator->id);
});

// === Salary Casts ===

test('salary monetary fields are cast to decimal', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'base_salary' => 50000.50,
        'allowances' => 10000.25,
        'deductions' => 5000.75,
        'net_salary' => 55000.00,
    ]);

    expect($salary->base_salary)->toBe('50000.50');
    expect($salary->allowances)->toBe('10000.25');
    expect($salary->deductions)->toBe('5000.75');
    expect($salary->net_salary)->toBe('55000.00');
});

test('salary dates are cast to date', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'effective_from' => '2025-01-15',
        'effective_until' => '2025-12-31',
    ]);

    expect($salary->effective_from)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
    expect($salary->effective_until)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
});


uses(RefreshDatabase::class);