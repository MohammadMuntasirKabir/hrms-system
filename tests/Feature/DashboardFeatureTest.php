<?php

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Leave;
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
    $this->department = Department::factory()->create(['company_id' => $this->company->id]);
    $this->superAdmin = User::factory()->create(['company_id' => $this->company->id]);
    $this->superAdmin->assignRole('super_admin');
    $this->admin = User::factory()->create([
        'company_id' => $this->company->id,
        'department_id' => $this->department->id,
    ]);
    $this->admin->assignRole('company_admin');
    $this->employee = User::factory()->create([
        'company_id' => $this->company->id,
        'department_id' => $this->department->id,
    ]);
    $this->employee->assignRole('employee');
});

it('admin dashboard shows pending leave and expiring contract counts', function () {
    Leave::factory()->forUser($this->employee)->pending()->create();
    Contract::factory()->create([
        'user_id' => $this->employee->id,
        'company_id' => $this->company->id,
        'department_id' => $this->department->id,
        'status' => 'active',
        'end_date' => now()->addDays(10),
    ]);

    $this->actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Pending Leaves')
        ->assertSee('Contracts Expiring')
        ->assertSee('Pending Leave Requests');
});

it('employee dashboard shows active contract and leave summary', function () {
    Contract::factory()->create([
        'user_id' => $this->employee->id,
        'company_id' => $this->company->id,
        'department_id' => $this->department->id,
        'status' => 'active',
        'position' => 'Software Engineer',
    ]);
    Leave::factory()->forUser($this->employee)->approved()->create();

    $this->actingAs($this->employee)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('My Active Contract')
        ->assertSee('My Leave')
        ->assertSee('Software Engineer');
});

it('department head dashboard lists team members', function () {
    $head = User::factory()->create([
        'company_id' => $this->company->id,
        'department_id' => $this->department->id,
    ]);
    $head->assignRole('department_head');

    $teammate = User::factory()->create([
        'company_id' => $this->company->id,
        'department_id' => $this->department->id,
        'name' => 'Teammate One',
    ]);
    $teammate->assignRole('employee');

    $this->actingAs($head)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Team Overview')
        ->assertSee('Teammate One');
});
