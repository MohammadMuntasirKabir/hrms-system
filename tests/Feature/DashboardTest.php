<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->seed(RolePermissionSeeder::class);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('employee');
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
