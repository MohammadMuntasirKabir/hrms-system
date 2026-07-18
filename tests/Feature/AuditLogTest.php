<?php

use App\Models\AuditLog;
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
    $this->employee = User::factory()->create(['company_id' => $this->company->id]);
    $this->employee->assignRole('employee');
});

it('super admin can view audit log', function () {
    AuditLog::query()->delete();
    AuditLog::record('created', 'Test entry', $this->company);

    $this->actingAs($this->superAdmin)
        ->get(route('audit-logs.index'))
        ->assertOk()
        ->assertSee('Audit Log')
        ->assertSee('Test entry');
});

it('employee cannot view audit log', function () {
    $this->actingAs($this->employee)
        ->get(route('audit-logs.index'))
        ->assertForbidden();
});

it('records an entry when a company is created', function () {
    AuditLog::query()->delete();
    $this->actingAs($this->superAdmin);
    $newCompany = Company::factory()->create();

    expect(AuditLog::where('model_type', Company::class)->where('model_id', $newCompany->id)->exists())->toBeTrue();
});

it('filters audit log by company', function () {
    AuditLog::query()->delete();
    $otherCompany = Company::factory()->create();
    AuditLog::record('created', 'Entry A', $this->company);
    AuditLog::record('created', 'Entry B', $otherCompany);

    $this->actingAs($this->superAdmin)
        ->get(route('audit-logs.index', ['company_id' => $this->company->id]))
        ->assertOk()
        ->assertSee('Entry A')
        ->assertDontSee('Entry B');
});
