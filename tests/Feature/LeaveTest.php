<?php

use App\Models\Company;
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
    $this->superAdmin = User::factory()->create(['company_id' => $this->company->id]);
    $this->superAdmin->assignRole('super_admin');
    $this->employee = User::factory()->create(['company_id' => $this->company->id]);
    $this->employee->assignRole('employee');
    $this->hr = User::factory()->create(['company_id' => $this->company->id]);
    $this->hr->assignRole('hr_manager');
});

it('super admin can view leave index', function () {
    Leave::factory()->forUser($this->employee)->create();

    $this->actingAs($this->superAdmin)
        ->get(route('leaves.index'))
        ->assertOk()
        ->assertSee('Leave Management');
});

it('employee only sees own leave requests', function () {
    $other = User::factory()->create(['company_id' => $this->company->id]);
    $own = Leave::factory()->forUser($this->employee)->create();
    Leave::factory()->forUser($other)->create();

    $this->actingAs($this->employee)
        ->get(route('leaves.index'))
        ->assertOk()
        ->assertSee($this->employee->name)
        ->assertDontSee($other->name);
});

it('employee can create a leave request for themselves', function () {
    $this->actingAs($this->employee)
        ->post(route('leaves.store'), [
            'user_id' => $this->employee->id,
            'type' => 'sick',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'reason' => 'Flu',
        ])
        ->assertRedirect(route('leaves.index'));

    $this->assertDatabaseHas('leaves', [
        'user_id' => $this->employee->id,
        'type' => 'sick',
        'status' => 'pending',
        'total_days' => 3,
    ]);
});

it('total days is computed correctly', function () {
    $this->actingAs($this->hr)
        ->post(route('leaves.store'), [
            'user_id' => $this->employee->id,
            'type' => 'annual',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-05',
        ])
        ->assertRedirect();

    expect(Leave::first()->total_days)->toBe(5);
});

it('hr manager can approve a pending leave', function () {
    $leave = Leave::factory()->forUser($this->employee)->pending()->create();

    $this->actingAs($this->hr)
        ->post(route('leaves.approve', $leave))
        ->assertRedirect(route('leaves.show', $leave));

    $leave->refresh();
    expect($leave->status)->toBe('approved');
    expect($leave->approved_by)->toBe($this->hr->id);
});

it('hr manager can reject a pending leave with a note', function () {
    $leave = Leave::factory()->forUser($this->employee)->pending()->create();

    $this->actingAs($this->hr)
        ->post(route('leaves.reject', $leave), ['admin_note' => 'Insufficient staffing'])
        ->assertRedirect(route('leaves.show', $leave));

    $leave->refresh();
    expect($leave->status)->toBe('rejected');
    expect($leave->admin_note)->toBe('Insufficient staffing');
});

it('rejecting without a note fails validation', function () {
    $leave = Leave::factory()->forUser($this->employee)->pending()->create();

    $this->actingAs($this->hr)
        ->post(route('leaves.reject', $leave), [])
        ->assertSessionHasErrors('admin_note');
});

it('cannot approve an already decided leave', function () {
    $leave = Leave::factory()->forUser($this->employee)->approved()->create(['approved_by' => $this->hr->id]);

    $this->actingAs($this->hr)
        ->post(route('leaves.approve', $leave))
        ->assertRedirect(route('leaves.show', $leave))
        ->assertSessionHas('error');
});

it('employee cannot approve leave', function () {
    $leave = Leave::factory()->forUser($this->employee)->pending()->create();

    $this->actingAs($this->employee)
        ->post(route('leaves.approve', $leave))
        ->assertForbidden();
});

it('super admin can filter leaves by company', function () {
    $otherCompany = Company::factory()->create();
    $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);
    Leave::factory()->forUser($this->employee)->create();
    Leave::factory()->forUser($otherUser)->create();

    $this->actingAs($this->superAdmin)
        ->get(route('leaves.index', ['company_id' => $this->company->id]))
        ->assertOk()
        ->assertSee($this->employee->name)
        ->assertDontSee($otherUser->name);
});
