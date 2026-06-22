<?php

use App\Models\JobApplicant;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// === Full Name Accessor ===

test('applicant full name combines first and last name', function () {
    $applicant = JobApplicant::factory()->make([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($applicant->full_name)->toBe('John Doe');
});

// === Status Check Methods ===

test('applicant isHired returns true when status is hired', function () {
    $applicant = JobApplicant::factory()->make(['status' => 'hired']);
    expect($applicant->isHired())->toBeTrue();
    expect($applicant->isPending())->toBeFalse();
    expect($applicant->isReviewing())->toBeFalse();
    expect($applicant->isShortlisted())->toBeFalse();
    expect($applicant->isRejected())->toBeFalse();
});

test('applicant isPending returns true when status is pending', function () {
    $applicant = JobApplicant::factory()->make(['status' => 'pending']);
    expect($applicant->isPending())->toBeTrue();
    expect($applicant->isHired())->toBeFalse();
});

test('applicant isReviewing returns true when status is reviewing', function () {
    $applicant = JobApplicant::factory()->make(['status' => 'reviewing']);
    expect($applicant->isReviewing())->toBeTrue();
    expect($applicant->isHired())->toBeFalse();
});

test('applicant isShortlisted returns true when status is shortlisted', function () {
    $applicant = JobApplicant::factory()->make(['status' => 'shortlisted']);
    expect($applicant->isShortlisted())->toBeTrue();
    expect($applicant->isHired())->toBeFalse();
});

test('applicant isRejected returns true when status is rejected', function () {
    $applicant = JobApplicant::factory()->make(['status' => 'rejected']);
    expect($applicant->isRejected())->toBeTrue();
    expect($applicant->isHired())->toBeFalse();
});

// === Relations ===

test('applicant belongs to company', function () {
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create(['company_id' => $company->id]);

    expect($applicant->company->id)->toBe($company->id);
});

test('applicant belongs to department', function () {
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    expect($applicant->department->id)->toBe($dept->id);
});

test('applicant belongs to designation', function () {
    $company = Company::factory()->create();
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'designation_id' => $desig->id,
    ]);

    expect($applicant->designation->id)->toBe($desig->id);
});

test('applicant belongs to reviewer', function () {
    $company = Company::factory()->create();
    $reviewer = User::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'reviewed_by' => $reviewer->id,
    ]);

    expect($applicant->reviewer->id)->toBe($reviewer->id);
});

test('applicant belongs to hired as user', function () {
    $company = Company::factory()->create();
    $employee = User::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'hired_as_user_id' => $employee->id,
    ]);

    expect($applicant->hiredAsUser->id)->toBe($employee->id);
});

// === Casts ===

test('expected salary is cast to decimal', function () {
    $applicant = JobApplicant::factory()->create(['expected_salary' => 50000.50]);
    expect($applicant->expected_salary)->toBe(50000.50);
});

test('available from is cast to date', function () {
    $applicant = JobApplicant::factory()->create(['available_from' => '2025-06-01']);
    expect($applicant->available_from)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('reviewed at is cast to datetime', function () {
    $applicant = JobApplicant::factory()->create(['reviewed_at' => '2025-01-15 10:30:00']);
    expect($applicant->reviewed_at)->toBeInstanceOf(\Carbon\Carbon::class);
});


uses(RefreshDatabase::class);