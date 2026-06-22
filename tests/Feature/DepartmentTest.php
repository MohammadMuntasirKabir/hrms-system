<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('super admin can create a department', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $response = $this->actingAs($user)->post(route('departments.store'), [
        'name' => 'Engineering',
        'code' => 'ENG',
        'description' => 'Software development',
        'company_id' => $company->id,
        'parent_department_id' => null,
        'head_user_id' => null,
    ]);

    $response->assertRedirectContains('/departments');
    $this->assertDatabaseHas('departments', ['name' => 'Engineering', 'company_id' => $company->id]);
});

test('super admin can view department list', function () {
    $company = Company::factory()->create();
    Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $response = $this->actingAs($user)->get(route('departments.index'));

    $response->assertOk();
    $response->assertViewHas('departments');
});

test('super admin can update a department', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id, 'name' => 'Old Dept']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $response = $this->actingAs($user)->put(route('departments.update', $dept), [
        'name' => 'Updated Dept',
        'code' => $dept->code,
        'description' => 'Updated description',
        'company_id' => $company->id,
        'parent_department_id' => null,
        'head_user_id' => null,
        'is_active' => true,
    ]);

    $response->assertRedirectContains('/departments');
    $this->assertDatabaseHas('departments', ['id' => $dept->id, 'name' => 'Updated Dept']);
});

test('non-admin cannot access department management', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('employee');

    $response = $this->actingAs($user)->get(route('departments.index'));

    $response->assertForbidden();
});

test('hr manager can view departments', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('hr_manager');

    $response = $this->actingAs($user)->get(route('departments.show', $dept));

    $response->assertOk();
});
