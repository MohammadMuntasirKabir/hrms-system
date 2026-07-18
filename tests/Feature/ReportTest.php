<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\Salary;
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
    $this->dept = Department::factory()->create(['company_id' => $this->company->id]);
    $this->dept->update(['company_id' => $this->company->id]);
    $this->superAdmin = User::factory()->create(['company_id' => $this->company->id]);
    $this->superAdmin->assignRole('super_admin');
    $this->employee = User::factory()->create([
        'company_id' => $this->company->id,
        'department_id' => $this->dept->id,
    ]);
    $this->employee->assignRole('employee');
    // Give the department several employees so it ranks in the report's top-10 by headcount
    User::factory()->count(15)->create([
        'company_id' => $this->company->id,
        'department_id' => $this->dept->id,
    ]);
    Salary::factory()->create([
        'user_id' => $this->employee->id,
        'company_id' => $this->company->id,
        'status' => 'active',
        'net_salary' => 5000,
    ]);
});

it('super admin can view reports', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Reports & Analytics')
        ->assertSee((string) $this->company->name);
});

it('shows headcount by department', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee($this->dept->name);
});

it('employee cannot view reports', function () {
    $this->actingAs($this->employee)
        ->get(route('reports.index'))
        ->assertForbidden();
});

it('super admin can export csv', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('reports.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=utf-8');

    $response->assertSee('Name,Email,Company');
    $response->assertSee($this->employee->name);
});

it('employee cannot export csv', function () {
    $this->actingAs($this->employee)
        ->get(route('reports.export'))
        ->assertForbidden();
});
