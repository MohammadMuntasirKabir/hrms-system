<?php

use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserIsActive;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

// === EnsureUserIsActive Middleware ===

test('active user can pass through middleware', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_active' => true]);
    $user->assignRole('employee');

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
});

test('inactive user is redirected to login', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_active' => false]);
    $user->assignRole('employee');

    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('inactive user session is invalidated', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id, 'is_active' => false]);
    $user->assignRole('employee');

    $this->actingAs($user)->get(route('dashboard'));

    $this->assertGuest();
});

// === EnsureUserHasPermission Middleware ===

test('super admin bypasses permission checks', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    // Super admin can access companies.index which requires companies.view permission
    $response = $this->actingAs($user)->get(route('companies.index'));
    $response->assertOk();
});

test('user with permission can access protected route', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('company_admin');

    // Company admin has departments.view permission
    $response = $this->actingAs($user)->get(route('departments.index'));
    $response->assertOk();
});

test('user without permission gets 403', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('employee');

    // Employee does not have departments.view permission
    $response = $this->actingAs($user)->get(route('departments.index'));
    $response->assertForbidden();
});

test('unauthenticated user gets 403 from permission middleware', function () {
    // The permission middleware is behind auth middleware, so this would
    // be caught by auth first. But testing the middleware directly:
    $middleware = new EnsureUserHasPermission;
    $request = Request::create('/test', 'GET');
    // No user set on request

    try {
        $middleware->handle($request, function () {
            return response('OK');
        });
        expect(false)->toBeTrue(); // Should not reach here
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
    }
});
