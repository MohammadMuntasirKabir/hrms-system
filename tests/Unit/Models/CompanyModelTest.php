<?php

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
use App\Models\JobApplicant;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// === Company Hierarchy ===

test('company is HQ when no parent', function () {
    $company = Company::factory()->create(['parent_company_id' => null]);

    expect($company->isHq())->toBeTrue();
    expect($company->isSubCompany())->toBeFalse();
});

test('company is sub-company when has parent', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->create(['parent_company_id' => $parent->id]);

    expect($child->isSubCompany())->toBeTrue();
    expect($child->isHq())->toBeFalse();
});

test('getMainCompany returns self for HQ', function () {
    $company = Company::factory()->create();

    expect($company->getMainCompany()->id)->toBe($company->id);
});

test('getMainCompany returns parent for sub-company', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->create(['parent_company_id' => $parent->id]);

    expect($child->getMainCompany()->id)->toBe($parent->id);
});

test('getAllSubCompanyIds includes self and children', function () {
    $parent = Company::factory()->create();
    $child1 = Company::factory()->create(['parent_company_id' => $parent->id]);
    $child2 = Company::factory()->create(['parent_company_id' => $parent->id]);
    $grandchild = Company::factory()->create(['parent_company_id' => $child1->id]);

    $ids = $parent->getAllSubCompanyIds();

    expect($ids)->toContain($parent->id);
    expect($ids)->toContain($child1->id);
    expect($ids)->toContain($child2->id);
    expect($ids)->toContain($grandchild->id);
    expect($ids)->toHaveCount(4);
});

// === Company Relations ===

test('company has many departments', function () {
    $company = Company::factory()->create();
    Department::factory()->count(3)->create(['company_id' => $company->id]);

    expect($company->departments()->count())->toBe(3);
});

test('company has many users', function () {
    $company = Company::factory()->create();
    User::factory()->count(3)->create(['company_id' => $company->id]);

    expect($company->users()->count())->toBe(3);
});

test('company has many designations', function () {
    $company = Company::factory()->create();
    Designation::factory()->count(3)->create(['company_id' => $company->id]);

    expect($company->designations()->count())->toBe(3);
});

test('company has many contracts', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $emp = User::factory()->create(['company_id' => $company->id]);
    Contract::factory()->count(3)->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($company->contracts()->count())->toBe(3);
});

test('company has many salaries', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $emp = User::factory()->create(['company_id' => $company->id]);
    Salary::factory()->count(3)->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($company->salaries()->count())->toBe(3);
});

test('company has many applicants', function () {
    $company = Company::factory()->create();
    JobApplicant::factory()->count(3)->create(['company_id' => $company->id]);

    expect($company->applicants()->count())->toBe(3);
});

test('company has many child companies', function () {
    $parent = Company::factory()->create();
    Company::factory()->count(3)->create(['parent_company_id' => $parent->id]);

    expect($parent->childCompanies()->count())->toBe(3);
});

test('company belongs to parent company', function () {
    $parent = Company::factory()->create();
    $child = Company::factory()->create(['parent_company_id' => $parent->id]);

    expect($child->parentCompany->id)->toBe($parent->id);
});

// === Active Contracts Scope ===

test('activeContracts returns only active contracts', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $emp = User::factory()->create(['company_id' => $company->id]);

    Contract::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'status' => 'active',
    ]);
    Contract::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'status' => 'terminated',
    ]);

    expect($company->activeContracts()->count())->toBe(1);
});

// === Casts ===

test('is_active is cast to boolean', function () {
    $company = Company::factory()->create(['is_active' => 1]);
    expect($company->is_active)->toBeTrue();

    $company->update(['is_active' => 0]);
    expect($company->fresh()->is_active)->toBeFalse();
});

uses(RefreshDatabase::class);
