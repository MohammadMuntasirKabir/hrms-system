<?php

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Salary;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// === Role Checks ===

test('user can be identified as super admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($user->isSuperAdmin())->toBeTrue();
    expect($user->isSeniorMember())->toBeTrue();
    expect($user->canViewSalaries())->toBeTrue();
});

test('user can be identified as company admin', function () {
    $user = User::factory()->create();
    $user->assignRole('company_admin');

    expect($user->isCompanyAdmin())->toBeTrue();
    expect($user->isSeniorMember())->toBeTrue();
    expect($user->canViewSalaries())->toBeTrue();
    expect($user->isSuperAdmin())->toBeFalse();
});

test('user can be identified as hr manager', function () {
    $user = User::factory()->create();
    $user->assignRole('hr_manager');

    expect($user->isHrManager())->toBeTrue();
    expect($user->isSeniorMember())->toBeTrue();
    expect($user->canViewSalaries())->toBeTrue();
});

test('user can be identified as department head', function () {
    $user = User::factory()->create();
    $user->assignRole('department_head');

    expect($user->isDepartmentHead())->toBeTrue();
    expect($user->isSeniorMember())->toBeFalse();
    expect($user->canViewSalaries())->toBeFalse();
});

test('user can be identified as employee', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    expect($user->isEmployee())->toBeTrue();
    expect($user->isSeniorMember())->toBeFalse();
    expect($user->canViewSalaries())->toBeFalse();
    expect($user->isSuperAdmin())->toBeFalse();
    expect($user->isCompanyAdmin())->toBeFalse();
    expect($user->isHrManager())->toBeFalse();
    expect($user->isDepartmentHead())->toBeFalse();
});

// === Initials ===

test('user initials are generated correctly', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    expect($user->initials())->toBe('JD');
});

test('user initials handle single name', function () {
    $user = User::factory()->create(['name' => 'Madonna']);
    expect($user->initials())->toBe('M');
});

test('user initials handle multi-word name', function () {
    $user = User::factory()->create(['name' => 'John Jacob Jingleheimer Schmidt']);
    expect($user->initials())->toBe('JJ');
});

// === Company View Authorization ===

test('super admin can view any company', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $company = Company::factory()->create();

    expect($user->canViewCompany($company))->toBeTrue();
});

test('user can view their own company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('employee');

    expect($user->canViewCompany($company))->toBeTrue();
});

test('user cannot view a different company', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company1->id]);
    $user->assignRole('employee');

    expect($user->canViewCompany($company2))->toBeFalse();
});

test('user can view sub-company of their company', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->create(['parent_company_id' => $parent->id]);
    $user = User::factory()->create(['company_id' => $parent->id]);
    $user->assignRole('employee');

    expect($user->canViewCompany($child))->toBeTrue();
});

test('user in sub-company can view parent company', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->create(['parent_company_id' => $parent->id]);
    $user = User::factory()->create(['company_id' => $child->id]);
    $user->assignRole('employee');

    expect($user->canViewCompany($parent))->toBeTrue();
});

// === Allowed Company IDs ===

test('super admin gets all company ids', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $companies = Company::factory()->count(3)->create();

    $ids = $user->getAllowedCompanyIds();
    foreach ($companies as $company) {
        expect($ids)->toContain($company->id);
    }
});

test('employee gets their own company id', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('employee');

    $ids = $user->getAllowedCompanyIds();
    expect($ids)->toContain($company->id);
});

test('employee without company gets empty ids', function () {
    $user = User::factory()->create(['company_id' => null]);
    $user->assignRole('employee');

    $ids = $user->getAllowedCompanyIds();
    expect($ids)->toBeEmpty();
});

// === Active Contract Relation ===

test('user active contract returns the latest active contract', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);

    $oldContract = Contract::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'status' => 'active',
        'start_date' => '2024-01-01',
    ]);

    $newContract = Contract::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'status' => 'active',
        'start_date' => '2025-01-01',
    ]);

    $activeContract = $user->activeContract;
    expect($activeContract)->not->toBeNull();
    expect($activeContract->id)->toBe($newContract->id);
});

// === Active Salary Relation ===

test('user active salary returns the latest active salary', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);

    Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'status' => 'active',
        'effective_from' => '2024-01-01',
    ]);

    $newSalary = Salary::factory()->create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'status' => 'active',
        'effective_from' => '2025-01-01',
    ]);

    $activeSalary = $user->activeSalary;
    expect($activeSalary)->not->toBeNull();
    expect($activeSalary->id)->toBe($newSalary->id);
});


uses(RefreshDatabase::class);