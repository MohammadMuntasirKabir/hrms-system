<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('super admin can create a company', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $response = $this->actingAs($user)->post(route('companies.store'), [
        'name' => 'Test Company',
        'slug' => 'test-company',
        'domain' => 'test.com',
        'country' => 'BD',
        'timezone' => 'Asia/Dhaka',
    ]);

    $response->assertRedirect(route('companies.index'));
    $this->assertDatabaseHas('companies', ['name' => 'Test Company', 'slug' => 'test-company']);
});

test('super admin can view company list', function () {
    Company::factory()->create();
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $response = $this->actingAs($user)->get(route('companies.index'));

    $response->assertOk();
    $response->assertViewHas('companies');
});

test('super admin can update a company', function () {
    $company = Company::factory()->create(['name' => 'Old Name']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $response = $this->actingAs($user)->put(route('companies.update', $company), [
        'name' => 'Updated Name',
        'slug' => $company->slug,
        'domain' => $company->domain,
        'parent_company_id' => null,
        'country' => 'BD',
        'timezone' => 'Asia/Dhaka',
        'is_active' => true,
    ]);

    $response->assertRedirect(route('companies.show', $company));
    $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Updated Name']);
});

test('super admin cannot create company with duplicate name', function () {
    Company::factory()->create(['name' => 'Existing Corp']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $response = $this->actingAs($user)->post(route('companies.store'), [
        'name' => 'Existing Corp',
        'slug' => 'different-slug',
    ]);

    $response->assertSessionHasErrors('name');
});

test('non-admin cannot access company management', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('employee');

    $response = $this->actingAs($user)->get(route('companies.index'));

    $response->assertForbidden();
});

test('company admin can view their own company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('company_admin');

    $response = $this->actingAs($user)->get(route('companies.show', $company));

    $response->assertOk();
});

test('company admin cannot view other companies', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company1->id]);
    $user->assignRole('company_admin');

    $response = $this->actingAs($user)->get(route('companies.show', $company2));

    $response->assertForbidden();
});
