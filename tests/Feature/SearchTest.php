<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    session()->forget('filter_company_id');
});

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->company = Company::factory()->create();
    $this->superAdmin = User::factory()->create(['company_id' => $this->company->id]);
    $this->superAdmin->assignRole('super_admin');
    $this->employee = User::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Searchable Person',
        'email' => 'searchable@example.com',
    ]);
    $this->employee->assignRole('employee');
});

it('requires at least 2 characters', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('search', ['q' => 'a']))
        ->assertOk()
        ->assertSee('Type at least 2 characters');
});

it('super admin can search employees by name', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('search', ['q' => 'Searchable']))
        ->assertOk()
        ->assertSee('Searchable Person')
        ->assertSee('searchable@example.com');
});

it('employee can search within their company', function () {
    $otherCompany = Company::factory()->create();
    $other = User::factory()->create([
        'company_id' => $otherCompany->id,
        'name' => 'Hidden Person',
    ]);
    $other->assignRole('employee');

    $this->actingAs($this->employee)
        ->get(route('search', ['q' => 'Person']))
        ->assertOk()
        ->assertSee('Searchable Person')
        ->assertDontSee('Hidden Person');
});

it('returns no results for unknown term', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('search', ['q' => 'zzzznomatch']))
        ->assertOk()
        ->assertSee('No results found');
});
