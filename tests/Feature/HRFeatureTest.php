<?php

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
use App\Models\JobApplicant;
use App\Models\Salary;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

function createSuperAdmin(): User
{
    test()->seed(RolePermissionSeeder::class);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('super_admin');

    return $user;
}

function createCompanyAdmin(): User
{
    test()->seed(RolePermissionSeeder::class);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('company_admin');

    return $user;
}

function createEmployee(): User
{
    test()->seed(RolePermissionSeeder::class);
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('employee');

    return $user;
}

// ============================================================
// Auth Tests
// ============================================================
test('login page returns 200', function () {
    $this->get(route('login'))->assertOk();
});

test('register page returns 200', function () {
    $this->get(route('register'))->assertOk();
});

test('guests are redirected from dashboard to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

// ============================================================
// Dashboard Tests
// ============================================================
test('super admin can access dashboard', function () {
    $user = createSuperAdmin();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

test('employee can access dashboard', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

// ============================================================
// Company Tests
// ============================================================
test('super admin can list companies', function () {
    $user = createSuperAdmin();
    Company::factory()->count(3)->create();
    $this->actingAs($user)->get(route('companies.index'))->assertOk();
});

test('super admin can view a company', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $this->actingAs($user)->get(route('companies.show', $company))->assertOk();
});

test('super admin can create a company', function () {
    $user = createSuperAdmin();
    $this->actingAs($user)->get(route('companies.create'))->assertOk();
});

test('super admin can store a company', function () {
    $user = createSuperAdmin();
    $this->actingAs($user)->post(route('companies.store'), [
        'name' => 'New Company',
        'slug' => 'new-company',
        'domain' => 'new.com',
        'country' => 'BD',
        'timezone' => 'Asia/Dhaka',
    ])->assertRedirect();

    $this->assertDatabaseHas('companies', ['name' => 'New Company']);
});

test('super admin can edit a company', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $this->actingAs($user)->get(route('companies.edit', $company))->assertOk();
});

test('super admin can update a company', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $this->actingAs($user)->put(route('companies.update', $company), [
        'name' => 'Updated Company',
        'slug' => 'updated-company',
        'domain' => $company->domain,
    ])->assertRedirect();

    $this->assertDatabaseHas('companies', ['name' => 'Updated Company']);
});

test('super admin cannot delete a company with users', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->delete(route('companies.destroy', $company))
        ->assertRedirect();
    $this->assertDatabaseHas('companies', ['id' => $company->id]);
});

test('super admin can delete an empty company', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();

    $this->actingAs($user)->delete(route('companies.destroy', $company))
        ->assertRedirect();
    $this->assertDatabaseMissing('companies', ['id' => $company->id]);
});

test('non-super-admin cannot access companies', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('companies.index'))->assertForbidden();
});

// ============================================================
// Department Tests
// ============================================================
test('authenticated user can list departments', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('departments.index'))->assertOk();
});

test('authenticated user can view a department', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id, 'name' => 'Test Dept']);
    $this->actingAs($user)->get(route('departments.show', $dept))->assertOk();
});

test('authenticated user can create a department', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('departments.create'))->assertOk();
});

test('authenticated user can store a department', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('departments.store'), [
        'name' => 'New Department',
        'code' => 'ND',
        'company_id' => $company->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('departments', ['name' => 'New Department']);
});

test('authenticated user can edit a department', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user)->get(route('departments.edit', $dept))->assertOk();
});

test('authenticated user can update a department', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user)->put(route('departments.update', $dept), [
        'name' => 'Updated Dept',
        'code' => 'UD',
        'company_id' => $company->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('departments', ['name' => 'Updated Dept']);
});

test('authenticated user cannot delete a department with users', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    User::factory()->create(['company_id' => $company->id, 'department_id' => $dept->id]);

    $this->actingAs($user)->delete(route('departments.destroy', $dept))
        ->assertRedirect();
    $this->assertDatabaseHas('departments', ['id' => $dept->id]);
});

test('authenticated user cannot view department from another company', function () {
    $user = createCompanyAdmin();
    $otherCompany = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $otherCompany->id]);
    $this->actingAs($user)->get(route('departments.show', $dept))->assertForbidden();
});

// ============================================================
// Designation Tests
// ============================================================
test('authenticated user can list designations', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('designations.index'))->assertOk();
});

test('authenticated user can view a designation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $desig = Designation::factory()->create(['company_id' => $company->id, 'title' => 'Test Desig']);
    $this->actingAs($user)->get(route('designations.show', $desig))->assertOk();
});

test('authenticated user can create a designation', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('designations.create'))->assertOk();
});

test('authenticated user can store a designation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('designations.store'), [
        'title' => 'Software Engineer',
        'level' => 2,
        'company_id' => $company->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('designations', ['title' => 'Software Engineer']);
});

test('authenticated user can delete an unused designation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $desig = Designation::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->delete(route('designations.destroy', $desig))
        ->assertRedirect();
    $this->assertDatabaseMissing('designations', ['id' => $desig->id]);
});

test('authenticated user cannot delete a designation with users', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    User::factory()->create(['company_id' => $company->id, 'designation_id' => $desig->id]);

    $this->actingAs($user)->delete(route('designations.destroy', $desig))
        ->assertRedirect();
    $this->assertDatabaseHas('designations', ['id' => $desig->id]);
});

// ============================================================
// Employee (User) Tests
// ============================================================
test('authenticated user can list employees', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('users.index'))->assertOk();
});

test('authenticated user can view an employee', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user)->get(route('users.show', $emp))->assertOk();
});

test('authenticated user can create an employee', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('users.create'))->assertOk();
});

test('authenticated user can store an employee', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('users.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'company_id' => $company->id,
        'role' => 'employee',
    ])->assertRedirect();

    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});

test('authenticated user can edit an employee', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user)->get(route('users.edit', $emp))->assertOk();
});

test('authenticated user cannot delete themselves', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->delete(route('users.destroy', $user))
        ->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});

// ============================================================
// Contract Tests
// ============================================================
test('authenticated user can list contracts', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('contracts.index'))->assertOk();
});

test('authenticated user can view a contract', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'contract_type' => 'full_time',
        'position' => 'Developer',
    ]);
    $this->actingAs($user)->get(route('contracts.show', $contract))->assertOk();
});

test('authenticated user can create a contract', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('contracts.create'))->assertOk();
});

test('authenticated user can store a contract', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->post(route('contracts.store'), [
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'contract_type' => 'full_time',
        'position' => 'Developer',
        'start_date' => '2025-01-01',
        'salary' => 50000,
        'currency' => 'BDT',
        'status' => 'active',
    ])->assertRedirect();

    $this->assertDatabaseHas('contracts', ['position' => 'Developer']);
});

// ============================================================
// Job Applicant Tests
// ============================================================
test('authenticated user can list applicants', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('applicants.index'))->assertOk();
});

test('authenticated user can view an applicant', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Test',
        'last_name' => 'Applicant',
        'email' => 'test@example.com',
    ]);
    $this->actingAs($user)->get(route('applicants.show', $applicant))->assertOk();
});

test('authenticated user can create an applicant', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->get(route('applicants.create'))->assertOk();
});

test('authenticated user can store an applicant', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('applicants.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'company_id' => $company->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['email' => 'jane@example.com', 'status' => 'pending']);
});

test('authenticated user can edit an applicant', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
    ]);
    $this->actingAs($user)->get(route('applicants.edit', $applicant))->assertOk();
});

test('super admin can shortlist an applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'shortlist@example.com',
        'status' => 'pending',
    ]);

    $this->actingAs($user)->post(route('applicants.shortlist', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'shortlisted']);
});

test('super admin can reject an applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'reject@example.com',
        'status' => 'pending',
    ]);

    $this->actingAs($user)->post(route('applicants.reject', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'rejected']);
});

test('super admin can undo rejection', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'undoreject@example.com',
        'status' => 'rejected',
    ]);

    $this->actingAs($user)->post(route('applicants.undo-reject', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'pending']);
});

test('super admin can permanently delete rejected applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'forcedelete@example.com',
        'status' => 'rejected',
    ]);

    $this->actingAs($user)->delete(route('applicants.force-delete', $applicant))
        ->assertRedirect();

    $this->assertDatabaseMissing('job_applicants', ['id' => $applicant->id]);
});

test('super admin can hire an applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'pending',
        'first_name' => 'Hire',
        'last_name' => 'Me',
        'email' => 'hireme@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.hire', $applicant), [
        'position' => 'Developer',
        'contract_type' => 'full_time',
        'start_date' => '2025-06-01',
        'salary' => 50000,
        'role' => 'employee',
    ])->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'hired']);
    $this->assertDatabaseHas('users', ['email' => 'hireme@example.com']);
});

test('super admin can view all companies data', function () {
    $user = createSuperAdmin();
    $companies = Company::factory()->count(5)->create();
    $response = $this->actingAs($user)->get(route('companies.index'));
    $response->assertOk();

    foreach ($companies as $company) {
        $response->assertSee($company->name);
    }
});

test('validation errors are shown on invalid company creation', function () {
    $user = createSuperAdmin();
    $this->actingAs($user)->post(route('companies.store'), [])
        ->assertSessionHasErrors(['name', 'slug']);
});

test('validation errors are shown on invalid department creation', function () {
    $user = createCompanyAdmin();
    $this->actingAs($user)->post(route('departments.store'), [])
        ->assertSessionHasErrors(['name', 'company_id']);
});

test('validation errors are shown on invalid applicant creation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('applicants.store'), ['company_id' => $company->id])
        ->assertSessionHasErrors(['first_name', 'last_name', 'email']);
});

// ============================================================
// Salary Tests
// ============================================================
test('super admin can list salaries', function () {
    $user = createSuperAdmin();
    $this->actingAs($user)->get(route('salaries.index'))->assertOk();
});

test('super admin can view a salary', function () {
    $user = createSuperAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $emp->id, 'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);
    $this->actingAs($user)->get(route('salaries.show', $salary))->assertOk();
});

test('super admin can create a salary', function () {
    $user = createSuperAdmin();
    $this->actingAs($user)->get(route('salaries.create'))->assertOk();
});

test('super admin can store a salary', function () {
    $user = createSuperAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->post(route('salaries.store'), [
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'contract_id' => null,
        'base_salary' => 60000,
        'allowances' => 5000,
        'deductions' => 2000,
        'currency' => 'BDT',
        'pay_frequency' => 'monthly',
        'effective_from' => '2025-01-01',
        'status' => 'active',
    ])->assertRedirect();

    $this->assertDatabaseHas('salaries', ['user_id' => $emp->id, 'base_salary' => 60000]);
});

test('super admin can edit a salary', function () {
    $user = createSuperAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $emp->id, 'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);
    $this->actingAs($user)->get(route('salaries.edit', $salary))->assertOk();
});

test('super admin can update a salary', function () {
    $user = createSuperAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $emp->id, 'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    $this->actingAs($user)->put(route('salaries.update', $salary), [
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'contract_id' => null,
        'base_salary' => 70000,
        'allowances' => 5000,
        'deductions' => 2000,
        'currency' => 'BDT',
        'pay_frequency' => 'monthly',
        'effective_from' => '2025-01-01',
        'status' => 'active',
    ])->assertRedirect();

    $this->assertDatabaseHas('salaries', ['id' => $salary->id, 'base_salary' => 70000]);
});

test('super admin can delete a salary', function () {
    $user = createSuperAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $emp->id, 'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    $this->actingAs($user)->delete(route('salaries.destroy', $salary))
        ->assertRedirect();
    $this->assertDatabaseMissing('salaries', ['id' => $salary->id]);
});

test('employee cannot access salary information', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('salaries.index'))->assertForbidden();
});

test('validation errors are shown on invalid salary creation', function () {
    $user = createSuperAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('salaries.store'), [
        'company_id' => $company->id,
    ])->assertSessionHasErrors(['user_id', 'base_salary', 'pay_frequency', 'effective_from', 'status']);
});

// ============================================================
// Contract Extended Tests
// ============================================================
test('authenticated user can edit a contract', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $emp->id, 'company_id' => $company->id,
        'department_id' => $dept->id, 'position' => 'Developer',
    ]);
    $this->actingAs($user)->get(route('contracts.edit', $contract))->assertOk();
});

test('authenticated user can update a contract', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $emp->id, 'company_id' => $company->id,
        'department_id' => $dept->id, 'position' => 'Developer',
    ]);

    $this->actingAs($user)->put(route('contracts.update', $contract), [
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'contract_type' => 'full_time',
        'position' => 'Senior Developer',
        'start_date' => '2025-01-01',
        'salary' => 75000,
        'currency' => 'BDT',
        'status' => 'active',
    ])->assertRedirect();

    $this->assertDatabaseHas('contracts', ['id' => $contract->id, 'position' => 'Senior Developer']);
});

test('authenticated user can delete a contract', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $emp->id, 'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    $this->actingAs($user)->delete(route('contracts.destroy', $contract))
        ->assertRedirect();
    $this->assertDatabaseMissing('contracts', ['id' => $contract->id]);
});

test('validation errors are shown on invalid contract creation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('contracts.store'), [
        'company_id' => $company->id,
    ])->assertSessionHasErrors(['user_id', 'contract_type', 'position', 'start_date', 'status']);
});

// ============================================================
// Applicant Extended Tests
// ============================================================
test('super admin can review an applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id, 'status' => 'pending',
        'first_name' => 'Review', 'last_name' => 'Me',
        'email' => 'review@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.review', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'reviewing']);
});

test('super admin can undo hire within grace period', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id, 'department_id' => $dept->id,
        'designation_id' => $desig->id, 'status' => 'hired',
        'first_name' => 'Undo', 'last_name' => 'Hire',
        'email' => 'undohire@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.undo-hire', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'pending']);
});

test('super admin can update an applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id, 'status' => 'pending',
        'first_name' => 'Update', 'last_name' => 'Me',
        'email' => 'update@example.com',
    ]);

    $this->actingAs($user)->put(route('applicants.update', $applicant), [
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'updated@example.com',
        'company_id' => $company->id,
        'status' => 'reviewing',
    ])->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'first_name' => 'Updated']);
});

test('super admin can delete an applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Delete', 'last_name' => 'Me',
        'email' => 'delete@example.com',
    ]);

    $this->actingAs($user)->delete(route('applicants.destroy', $applicant))
        ->assertRedirect();
    $this->assertDatabaseMissing('job_applicants', ['id' => $applicant->id]);
});

test('super admin can view applicant edit form', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Edit', 'last_name' => 'Form',
        'email' => 'editform@example.com',
    ]);
    $this->actingAs($user)->get(route('applicants.edit', $applicant))->assertOk();
});

test('super admin cannot reject an already hired applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id, 'status' => 'hired',
        'first_name' => 'Hired', 'last_name' => 'Reject',
        'email' => 'hiredreject@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.reject', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'hired']);
});

test('super admin cannot shortlist a rejected applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id, 'status' => 'rejected',
        'first_name' => 'Rejected', 'last_name' => 'Shortlist',
        'email' => 'rejectedshort@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.shortlist', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'rejected']);
});

// ============================================================
// User/Employee Extended Tests
// ============================================================
test('authenticated user can update an employee', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->put(route('users.update', $emp), [
        'name' => 'Updated Employee',
        'email' => 'updated_emp@example.com',
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'role' => 'employee',
    ])->assertRedirect();

    $this->assertDatabaseHas('users', ['id' => $emp->id, 'name' => 'Updated Employee']);
});

test('authenticated user can delete an employee', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->delete(route('users.destroy', $emp))
        ->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $emp->id]);
});

test('authenticated user can view employee edit form', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user)->get(route('users.edit', $emp))->assertOk();
});

test('validation errors are shown on invalid employee creation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('users.store'), [
        'company_id' => $company->id,
    ])->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

// ============================================================
// Company Extended Tests
// ============================================================
test('super admin can view company edit form', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $this->actingAs($user)->get(route('companies.edit', $company))->assertOk();
});

test('super admin cannot delete a company with departments', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    Department::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->delete(route('companies.destroy', $company))
        ->assertRedirect();
    $this->assertDatabaseHas('companies', ['id' => $company->id]);
});

test('validation errors are shown on invalid company update', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $this->actingAs($user)->put(route('companies.update', $company), [])
        ->assertSessionHasErrors(['name', 'slug']);
});

// ============================================================
// Designation Extended Tests
// ============================================================
test('authenticated user can view designation details page', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $desig = Designation::factory()->create(['company_id' => $company->id, 'title' => 'Test Desig']);
    $this->actingAs($user)->get(route('designations.show', $desig))->assertOk();
});

test('authenticated user can edit a designation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user)->get(route('designations.edit', $desig))->assertOk();
});

test('authenticated user can update a designation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->put(route('designations.update', $desig), [
        'title' => 'Updated Title',
        'level' => 3,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('designations', ['id' => $desig->id, 'title' => 'Updated Title']);
});

test('validation errors are shown on invalid designation creation', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('designations.store'), [
        'company_id' => $company->id,
    ])->assertSessionHasErrors(['title', 'level']);
});

// ============================================================
// Department Extended Tests
// ============================================================
test('authenticated user can view department edit form', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user)->get(route('departments.edit', $dept))->assertOk();
});

test('authenticated user cannot delete a department with children', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    Department::factory()->create(['company_id' => $company->id, 'parent_department_id' => $dept->id]);

    $this->actingAs($user)->delete(route('departments.destroy', $dept))
        ->assertRedirect();
    $this->assertDatabaseHas('departments', ['id' => $dept->id]);
});

test('authenticated user cannot set department as its own parent', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)->put(route('departments.update', $dept), [
        'name' => $dept->name,
        'company_id' => $company->id,
        'parent_department_id' => $dept->id,
    ])->assertSessionHas('error');
});

test('department show page loads employees with active contracts', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $emp = User::factory()->create(['company_id' => $company->id, 'department_id' => $dept->id]);
    Contract::factory()->create([
        'user_id' => $emp->id, 'company_id' => $company->id,
        'department_id' => $dept->id, 'status' => 'active',
        'contract_type' => 'full_time', 'position' => 'Dev',
    ]);

    $response = $this->actingAs($user)->get(route('departments.show', $dept));
    $response->assertOk();
    $response->assertSee($emp->name);
});

// ============================================================
// Authorization / Permission Tests
// ============================================================
test('employee cannot access department list', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('departments.index'))->assertForbidden();
});

test('employee cannot access contract list', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('contracts.index'))->assertForbidden();
});

test('employee cannot access applicant list', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('applicants.index'))->assertForbidden();
});

test('employee cannot access employee list', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('users.index'))->assertForbidden();
});

test('employee cannot access designation list', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('designations.index'))->assertForbidden();
});

test('company admin cannot access companies index', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('companies.index'))->assertForbidden();
});

test('company admin cannot view cross-company department', function () {
    $user = createCompanyAdmin();
    $otherCompany = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $otherCompany->id]);
    $this->actingAs($user)->get(route('departments.show', $dept))->assertForbidden();
});

test('authenticated user cannot view employee from another company', function () {
    $user = createCompanyAdmin();
    $otherCompany = Company::factory()->create();
    $emp = User::factory()->create(['company_id' => $otherCompany->id]);
    $this->actingAs($user)->get(route('users.show', $emp))->assertForbidden();
});

test('authenticated user cannot view contract from another company', function () {
    $user = createCompanyAdmin();
    $otherCompany = Company::factory()->create();
    $emp = User::factory()->create(['company_id' => $otherCompany->id]);
    $dept = Department::factory()->create(['company_id' => $otherCompany->id]);
    $contract = Contract::factory()->create([
        'user_id' => $emp->id, 'company_id' => $otherCompany->id,
        'department_id' => $dept->id,
    ]);
    $this->actingAs($user)->get(route('contracts.show', $contract))->assertForbidden();
});

test('authenticated user cannot view applicant from another company', function () {
    $user = createCompanyAdmin();
    $otherCompany = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $otherCompany->id,
        'first_name' => 'Other', 'last_name' => 'Company',
        'email' => 'other_company@example.com',
    ]);
    $this->actingAs($user)->get(route('applicants.show', $applicant))->assertForbidden();
});

// ============================================================
// Dashboard Tests Per Role
// ============================================================
test('super admin dashboard shows stats', function () {
    $user = createSuperAdmin();
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Total');
});

test('employee dashboard is accessible', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

// ============================================================
// Settings / Profile Tests
// ============================================================
test('authenticated user can view profile settings', function () {
    $user = createEmployee();
    $this->actingAs($user)->get(route('profile.edit'))->assertOk();
});

// ============================================================
// Applicant Index Page Tests
// ============================================================
test('applicant index shows status filter buttons', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    JobApplicant::factory()->count(3)->create(['company_id' => $company->id, 'status' => 'pending']);
    JobApplicant::factory()->count(2)->create(['company_id' => $company->id, 'status' => 'shortlisted']);

    $response = $this->actingAs($user)->get(route('applicants.index'));
    $response->assertOk();
    $response->assertSee('Pending');
    $response->assertSee('Shortlisted');
});

test('applicant index filters by pending status', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    JobApplicant::factory()->create(['company_id' => $company->id, 'status' => 'pending', 'first_name' => 'Pending', 'last_name' => 'User', 'email' => 'pending_filter@example.com']);
    JobApplicant::factory()->create(['company_id' => $company->id, 'status' => 'rejected', 'first_name' => 'Rejected', 'last_name' => 'User', 'email' => 'rejected_filter@example.com']);

    $response = $this->actingAs($user)->get(route('applicants.index', ['status' => 'pending']));
    $response->assertOk();
    $response->assertSee('Pending');
});

test('applicant index filters by rejected status', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    JobApplicant::factory()->create(['company_id' => $company->id, 'status' => 'rejected', 'first_name' => 'Rejected', 'last_name' => 'User', 'email' => 'rejected_status@example.com']);

    $response = $this->actingAs($user)->get(route('applicants.index', ['status' => 'rejected']));
    $response->assertOk();
    $response->assertSee('Rejected');
});

test('applicant index filters by department', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    JobApplicant::factory()->create(['company_id' => $company->id, 'department_id' => $dept->id, 'status' => 'pending', 'first_name' => 'Dept', 'last_name' => 'User', 'email' => 'dept_filter@example.com']);

    $response = $this->actingAs($user)->get(route('applicants.index', ['department_id' => $dept->id, 'company_id' => $company->id]));
    $response->assertOk();
    $response->assertSee('Dept');
});

test('applicant index shows applicant details', function () {
    session()->forget('filter_company_id');
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john_doe@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('applicants.index', ['company_id' => $company->id]));
    $response->assertOk();
    $response->assertSee('John');
    $response->assertSee('john_doe@example.com');
});

// ============================================================
// Applicant Show Page Tests (Different Statuses)
// ============================================================
test('applicant show page displays pending applicant with action buttons', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'first_name' => 'Action',
        'last_name' => 'Test',
        'email' => 'action_test@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('applicants.show', $applicant));
    $response->assertOk();
    $response->assertSee('Action Test');
    $response->assertSee('Review');
    $response->assertSee('Shortlist');
    $response->assertSee('Reject');
    $response->assertSee('Hire');
});

test('applicant show page displays reviewing applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'reviewing',
        'first_name' => 'Reviewing',
        'last_name' => 'User',
        'email' => 'reviewing_user@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('applicants.show', $applicant));
    $response->assertOk();
    $response->assertSee('Reviewing User');
    $response->assertSee('Shortlist');
});

test('applicant show page displays shortlisted applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'shortlisted',
        'first_name' => 'Shortlisted',
        'last_name' => 'User',
        'email' => 'shortlisted_user@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('applicants.show', $applicant));
    $response->assertOk();
    $response->assertSee('Shortlisted User');
    $response->assertSee('Hire');
});

test('applicant show page displays hired applicant with undo option', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'hired',
        'first_name' => 'Hired',
        'last_name' => 'User',
        'email' => 'hired_user@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('applicants.show', $applicant));
    $response->assertOk();
    $response->assertSee('Hired User');
    $response->assertSee('Hired');
});

test('applicant show page displays rejected applicant with undo and delete options', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'rejected',
        'first_name' => 'Rejected',
        'last_name' => 'User',
        'email' => 'rejected_show@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('applicants.show', $applicant));
    $response->assertOk();
    $response->assertSee('Rejected User');
    $response->assertSee('Undo Rejection');
    $response->assertSee('Delete Permanently');
});

test('applicant show page displays applicant information fields', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'pending',
        'first_name' => 'Info',
        'last_name' => 'User',
        'email' => 'info_user@example.com',
        'phone' => '+8801712345678',
        'city' => 'Dhaka',
        'country' => 'BD',
        'expected_salary' => 50000,
        'currency' => 'BDT',
        'source' => 'LinkedIn',
        'cover_letter' => 'Test cover letter',
        'notes' => 'Test notes',
    ]);

    $response = $this->actingAs($user)->get(route('applicants.show', $applicant));
    $response->assertOk();
    $response->assertSee('Info User');
    $response->assertSee('info_user@example.com');
    $response->assertSee('Dhaka');
    $response->assertSee('LinkedIn');
    $response->assertSee('Test cover letter');
    $response->assertSee('Test notes');
});

// ============================================================
// Applicant Hire Tests
// ============================================================
test('super admin can hire applicant with salary', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'pending',
        'first_name' => 'Hire',
        'last_name' => 'WithSalary',
        'email' => 'hire_salary@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.hire', $applicant), [
        'position' => 'Developer',
        'contract_type' => 'full_time',
        'start_date' => '2025-06-01',
        'salary' => 75000,
        'role' => 'employee',
    ])->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'hired']);
    $this->assertDatabaseHas('users', ['email' => 'hire_salary@example.com']);
    $this->assertDatabaseHas('contracts', ['position' => 'Developer', 'salary' => 75000]);
    $this->assertDatabaseHas('salaries', ['base_salary' => 75000, 'net_salary' => 75000]);
});

test('super admin can hire applicant without salary', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'pending',
        'first_name' => 'Hire',
        'last_name' => 'NoSalary',
        'email' => 'hire_nosalary@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.hire', $applicant), [
        'position' => 'Intern',
        'contract_type' => 'internship',
        'start_date' => '2025-06-01',
        'role' => 'employee',
    ])->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'hired']);
    $this->assertDatabaseHas('users', ['email' => 'hire_nosalary@example.com']);
    $this->assertDatabaseHas('contracts', ['position' => 'Intern']);
    $this->assertDatabaseMissing('salaries', ['user_id' => User::where('email', 'hire_nosalary@example.com')->first()->id]);
});

test('super admin cannot hire already hired applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'hired',
        'first_name' => 'Already',
        'last_name' => 'Hired',
        'email' => 'already_hired@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.hire', $applicant), [
        'position' => 'Developer',
        'contract_type' => 'full_time',
        'start_date' => '2025-06-01',
        'role' => 'employee',
    ])->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'hired']);
});

test('super admin can hire rejected applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'rejected',
        'first_name' => 'Rejected',
        'last_name' => 'Hire',
        'email' => 'rejected_hire@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.hire', $applicant), [
        'position' => 'Developer',
        'contract_type' => 'full_time',
        'start_date' => '2025-06-01',
        'role' => 'employee',
    ])->assertRedirect();

    // The controller allows hiring rejected applicants (no guard against it)
    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'hired']);
});

// ============================================================
// Applicant Undo Hire Tests
// ============================================================
test('undo hire deletes employee contract and salary records', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'hired',
        'first_name' => 'Undo',
        'last_name' => 'Records',
        'email' => 'undo_records@example.com',
    ]);

    // Create the employee that was "hired"
    $employee = User::factory()->create(['company_id' => $company->id, 'email' => 'undo_emp@example.com']);
    $contract = Contract::factory()->create(['user_id' => $employee->id, 'company_id' => $company->id, 'department_id' => $dept->id]);
    $salary = Salary::factory()->create(['user_id' => $employee->id, 'company_id' => $company->id, 'department_id' => $dept->id]);

    $applicant->update(['hired_as_user_id' => $employee->id]);

    $this->actingAs($user)->post(route('applicants.undo-hire', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id, 'status' => 'pending']);
    $this->assertDatabaseMissing('users', ['id' => $employee->id]);
    $this->assertDatabaseMissing('contracts', ['id' => $contract->id]);
    $this->assertDatabaseMissing('salaries', ['id' => $salary->id]);
});

// ============================================================
// Applicant Force Delete Tests
// ============================================================
test('super admin cannot force delete non-rejected applicant', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'first_name' => 'Force',
        'last_name' => 'Delete',
        'email' => 'force_delete@example.com',
    ]);

    $this->actingAs($user)->delete(route('applicants.force-delete', $applicant))
        ->assertRedirect();

    $this->assertDatabaseHas('job_applicants', ['id' => $applicant->id]);
});

// ============================================================
// Applicant Validation Tests
// ============================================================
test('applicant hire requires contract_type position start_date and role', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'pending',
        'first_name' => 'Validation',
        'last_name' => 'Test',
        'email' => 'validation_hire@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.hire', $applicant), [])
        ->assertSessionHasErrors(['contract_type', 'position', 'start_date', 'role']);
});

test('applicant update requires first_name last_name email and company', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Update',
        'last_name' => 'Validation',
        'email' => 'update_validation@example.com',
    ]);

    $this->actingAs($user)->put(route('applicants.update', $applicant), [])
        ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'company_id', 'status']);
});

// ============================================================
// Contract Show Page Tests
// ============================================================
test('contract show page displays full details', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'contract_type' => 'full_time',
        'position' => 'Senior Developer',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('contracts.show', $contract));
    $response->assertOk();
    $response->assertSee('Senior Developer');
    $response->assertSee($emp->name);
});

test('contract show page shows expiring soon warning', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'status' => 'active',
        'end_date' => now()->addDays(15),
    ]);

    $response = $this->actingAs($user)->get(route('contracts.show', $contract));
    $response->assertOk();
    $response->assertSee('expires');
});

test('contract show page shows expired warning', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $contract = Contract::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'status' => 'active',
        'end_date' => now()->subDays(5),
    ]);

    $response = $this->actingAs($user)->get(route('contracts.show', $contract));
    $response->assertOk();
    $response->assertSee('expired');
});

// ============================================================
// Salary Show Page Tests
// ============================================================
test('salary show page displays full details', function () {
    $user = createSuperAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $salary = Salary::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'base_salary' => 80000,
        'allowances' => 10000,
        'deductions' => 5000,
        'net_salary' => 85000,
    ]);

    $response = $this->actingAs($user)->get(route('salaries.show', $salary));
    $response->assertOk();
    $response->assertSee($emp->name);
});

// ============================================================
// User Show Page Tests
// ============================================================
test('user show page displays contracts and salaries', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $emp = User::factory()->create(['company_id' => $company->id]);
    $dept = Department::factory()->create(['company_id' => $company->id]);
    Contract::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'position' => 'Developer',
    ]);
    Salary::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'base_salary' => 60000,
        'net_salary' => 60000,
    ]);

    $response = $this->actingAs($user)->get(route('users.show', $emp));
    $response->assertOk();
    $response->assertSee($emp->name);
    $response->assertSee('Developer');
});

// ============================================================
// Company Show Page Tests
// ============================================================
test('company show page displays departments designations and contracts', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $emp = User::factory()->create(['company_id' => $company->id]);
    Contract::factory()->create([
        'user_id' => $emp->id,
        'company_id' => $company->id,
        'department_id' => $dept->id,
    ]);

    $response = $this->actingAs($user)->get(route('companies.show', $company));
    $response->assertOk();
    $response->assertSee($company->name);
    $response->assertSee($dept->name);
    $response->assertSee($desig->title);
});

// ============================================================
// Super Admin Cross-Company Tests
// ============================================================
test('super admin can filter applicants by company', function () {
    session()->forget('filter_company_id');
    $user = createSuperAdmin();
    $companies = Company::factory()->count(2)->create();
    JobApplicant::factory()->create(['company_id' => $companies[0]->id, 'status' => 'pending', 'first_name' => 'Company', 'last_name' => 'One', 'email' => 'company_one@example.com']);
    JobApplicant::factory()->create(['company_id' => $companies[1]->id, 'status' => 'pending', 'first_name' => 'Company', 'last_name' => 'Two', 'email' => 'company_two@example.com']);

    $response = $this->actingAs($user)->get(route('applicants.index', ['company_id' => $companies[0]->id]));
    $response->assertOk();
    $response->assertSee('Company One');
});

test('super admin can filter departments by company', function () {
    $user = createSuperAdmin();
    $companies = Company::factory()->count(2)->create();
    Department::factory()->create(['company_id' => $companies[0]->id, 'name' => 'Dept One']);
    Department::factory()->create(['company_id' => $companies[1]->id, 'name' => 'Dept Two']);

    $response = $this->actingAs($user)->get(route('departments.index', ['company_id' => $companies[0]->id]));
    $response->assertOk();
    $response->assertSee('Dept One');
});

test('super admin can filter contracts by company', function () {
    $user = createSuperAdmin();
    $companies = Company::factory()->count(2)->create();
    $emp = User::factory()->create(['company_id' => $companies[0]->id]);
    $dept = Department::factory()->create(['company_id' => $companies[0]->id]);
    Contract::factory()->create(['user_id' => $emp->id, 'company_id' => $companies[0]->id, 'department_id' => $dept->id]);

    $response = $this->actingAs($user)->get(route('contracts.index', ['company_id' => $companies[0]->id]));
    $response->assertOk();
});

test('super admin can filter salaries by company', function () {
    $user = createSuperAdmin();
    $companies = Company::factory()->count(2)->create();
    $emp = User::factory()->create(['company_id' => $companies[0]->id]);
    $dept = Department::factory()->create(['company_id' => $companies[0]->id]);
    Salary::factory()->create(['user_id' => $emp->id, 'company_id' => $companies[0]->id, 'department_id' => $dept->id]);

    $response = $this->actingAs($user)->get(route('salaries.index', ['company_id' => $companies[0]->id]));
    $response->assertOk();
});

test('super admin can filter users by company', function () {
    $user = createSuperAdmin();
    $companies = Company::factory()->count(2)->create();
    User::factory()->create(['company_id' => $companies[0]->id, 'name' => 'User One', 'email' => 'user_one@example.com']);
    User::factory()->create(['company_id' => $companies[1]->id, 'name' => 'User Two', 'email' => 'user_two@example.com']);

    $response = $this->actingAs($user)->get(route('users.index', ['company_id' => $companies[0]->id]));
    $response->assertOk();
    $response->assertSee('User One');
});

// ============================================================
// Employee Self-Service Tests
// ============================================================
test('employee cannot view own profile via users show', function () {
    $user = createEmployee();
    // Employee role doesn't have users.view permission
    $this->actingAs($user)->get(route('users.show', $user))->assertForbidden();
});

test('employee cannot edit own profile via users edit', function () {
    $user = createEmployee();
    // Employee role doesn't have users.edit permission
    $this->actingAs($user)->get(route('users.edit', $user))->assertForbidden();
});

test('employee cannot edit other employees', function () {
    $user = createEmployee();
    $otherCompany = Company::where('id', '!=', $user->company_id)->first() ?? Company::factory()->create();
    $other = User::factory()->create(['company_id' => $otherCompany->id]);
    $this->actingAs($user)->get(route('users.edit', $other))->assertForbidden();
});

test('employee cannot delete any user', function () {
    $user = createEmployee();
    $otherCompany = Company::where('id', '!=', $user->company_id)->first() ?? Company::factory()->create();
    $other = User::factory()->create(['company_id' => $otherCompany->id]);
    $this->actingAs($user)->delete(route('users.destroy', $other))->assertForbidden();
});

// ============================================================
// Session Flash Message Tests
// ============================================================
test('applicant store shows success message', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $this->actingAs($user)->post(route('applicants.store'), [
        'first_name' => 'Flash',
        'last_name' => 'Test',
        'email' => 'flash_test@example.com',
        'company_id' => $company->id,
    ])->assertSessionHas('status');
});

test('applicant reject shows success message', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'first_name' => 'Reject',
        'last_name' => 'Flash',
        'email' => 'reject_flash@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.reject', $applicant))
        ->assertSessionHas('status');
});

test('applicant shortlist shows success message', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'status' => 'pending',
        'first_name' => 'Shortlist',
        'last_name' => 'Flash',
        'email' => 'shortlist_flash@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.shortlist', $applicant))
        ->assertSessionHas('status');
});

test('applicant hire shows success message', function () {
    $user = createSuperAdmin();
    $company = Company::factory()->create();
    $dept = Department::factory()->create(['company_id' => $company->id]);
    $desig = Designation::factory()->create(['company_id' => $company->id]);
    $applicant = JobApplicant::factory()->create([
        'company_id' => $company->id,
        'department_id' => $dept->id,
        'designation_id' => $desig->id,
        'status' => 'pending',
        'first_name' => 'Hire',
        'last_name' => 'Flash',
        'email' => 'hire_flash@example.com',
    ]);

    $this->actingAs($user)->post(route('applicants.hire', $applicant), [
        'position' => 'Developer',
        'contract_type' => 'full_time',
        'start_date' => '2025-06-01',
        'role' => 'employee',
    ])->assertSessionHas('status');
});

// ============================================================
// Admin Management Tests — Super Admin Transfer
// ============================================================
test('super admin can transfer super admin role to another user', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::where('id', $superAdmin->company_id)->first();
    $newAdmin = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($superAdmin)->post(route('admin.transfer-superadmin'), [
        'new_superadmin_id' => $newAdmin->id,
    ])->assertRedirect(route('users.index'));

    // New user should now be super admin
    expect($newAdmin->fresh()->isSuperAdmin())->toBeTrue();
    // Old super admin should no longer be super admin
    expect($superAdmin->fresh()->isSuperAdmin())->toBeFalse();
});

test('non-super admin cannot transfer super admin role', function () {
    $user = createCompanyAdmin();
    $target = User::factory()->create(['company_id' => $user->company_id]);

    $this->actingAs($user)->post(route('admin.transfer-superadmin'), [
        'new_superadmin_id' => $target->id,
    ])->assertForbidden();
});

test('super admin cannot transfer to inactive user', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::where('id', $superAdmin->company_id)->first();
    $inactiveUser = User::factory()->create(['company_id' => $company->id, 'is_active' => false]);

    $this->actingAs($superAdmin)->post(route('admin.transfer-superadmin'), [
        'new_superadmin_id' => $inactiveUser->id,
    ])->assertSessionHas('error');

    expect($superAdmin->fresh()->isSuperAdmin())->toBeTrue();
    expect($inactiveUser->fresh()->isSuperAdmin())->toBeFalse();
});

test('super admin transfer requires valid user id', function () {
    $superAdmin = createSuperAdmin();

    $this->actingAs($superAdmin)->post(route('admin.transfer-superadmin'), [
        'new_superadmin_id' => 99999,
    ])->assertSessionHasErrors('new_superadmin_id');
});

// ============================================================
// Admin Management Tests — Company Admin Assignment
// ============================================================
test('super admin can assign company admin to user', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($superAdmin)->post(route('admin.assign-company-admin'), [
        'user_id' => $user->id,
    ])->assertSessionHas('status');

    expect($user->fresh()->isCompanyAdmin())->toBeTrue();
});

test('non-super admin cannot assign company admin', function () {
    $user = createCompanyAdmin();
    $target = User::factory()->create(['company_id' => $user->company_id]);

    $this->actingAs($user)->post(route('admin.assign-company-admin'), [
        'user_id' => $target->id,
    ])->assertForbidden();
});

test('cannot assign company admin if company already has one', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $existingAdmin = User::factory()->create(['company_id' => $company->id]);
    $existingAdmin->assignRole('company_admin');

    $newUser = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($superAdmin)->post(route('admin.assign-company-admin'), [
        'user_id' => $newUser->id,
    ])->assertSessionHas('error');

    expect($newUser->fresh()->isCompanyAdmin())->toBeFalse();
    expect($existingAdmin->fresh()->isCompanyAdmin())->toBeTrue();
});

test('can reassign company admin by first removing existing one', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $oldAdmin = User::factory()->create(['company_id' => $company->id]);
    $oldAdmin->assignRole('company_admin');
    $newUser = User::factory()->create(['company_id' => $company->id]);

    // First remove old admin
    $this->actingAs($superAdmin)->post(route('admin.remove-company-admin'), [
        'user_id' => $oldAdmin->id,
    ])->assertSessionHas('status');

    expect($oldAdmin->fresh()->isCompanyAdmin())->toBeFalse();

    // Now assign new admin
    $this->actingAs($superAdmin)->post(route('admin.assign-company-admin'), [
        'user_id' => $newUser->id,
    ])->assertSessionHas('status');

    expect($newUser->fresh()->isCompanyAdmin())->toBeTrue();
});

test('cannot assign company admin to user without company', function () {
    $superAdmin = createSuperAdmin();
    $user = User::factory()->create(['company_id' => null]);

    $this->actingAs($superAdmin)->post(route('admin.assign-company-admin'), [
        'user_id' => $user->id,
    ])->assertSessionHas('error');
});

test('cannot assign company admin to inactive user', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $inactiveUser = User::factory()->create(['company_id' => $company->id, 'is_active' => false]);

    $this->actingAs($superAdmin)->post(route('admin.assign-company-admin'), [
        'user_id' => $inactiveUser->id,
    ])->assertSessionHas('error');

    expect($inactiveUser->fresh()->isCompanyAdmin())->toBeFalse();
});

test('assigning company admin replaces existing role', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('hr_manager');

    $this->actingAs($superAdmin)->post(route('admin.assign-company-admin'), [
        'user_id' => $user->id,
    ])->assertSessionHas('status');

    $user->refresh();
    expect($user->isCompanyAdmin())->toBeTrue();
    expect($user->isHrManager())->toBeFalse();
});

test('assigning company admin to already admin user shows info message', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('company_admin');

    $this->actingAs($superAdmin)->post(route('admin.assign-company-admin'), [
        'user_id' => $user->id,
    ])->assertSessionHas('status');

    expect($user->fresh()->isCompanyAdmin())->toBeTrue();
});

// ============================================================
// Admin Management Tests — Company Admin Removal
// ============================================================
test('super admin can remove company admin', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id]);
    $admin->assignRole('company_admin');

    $this->actingAs($superAdmin)->post(route('admin.remove-company-admin'), [
        'user_id' => $admin->id,
    ])->assertSessionHas('status');

    expect($admin->fresh()->isCompanyAdmin())->toBeFalse();
    expect($admin->fresh()->isEmployee())->toBeTrue();
});

test('non-super admin cannot remove company admin', function () {
    $user = createCompanyAdmin();
    $company = Company::where('id', $user->company_id)->first();
    $admin = User::factory()->create(['company_id' => $company->id]);
    $admin->assignRole('company_admin');

    $this->actingAs($user)->post(route('admin.remove-company-admin'), [
        'user_id' => $admin->id,
    ])->assertForbidden();
});

test('cannot remove company admin from non-admin user', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($superAdmin)->post(route('admin.remove-company-admin'), [
        'user_id' => $user->id,
    ])->assertSessionHas('error');
});

test('removing company admin downgrades to employee', function () {
    $superAdmin = createSuperAdmin();
    $company = Company::factory()->create();
    $admin = User::factory()->create(['company_id' => $company->id]);
    $admin->assignRole('company_admin');

    $this->actingAs($superAdmin)->post(route('admin.remove-company-admin'), [
        'user_id' => $admin->id,
    ]);

    $admin->refresh();
    expect($admin->isCompanyAdmin())->toBeFalse();
    expect($admin->isEmployee())->toBeTrue();
});

// ============================================================
// Admin Management Tests — Validation
// ============================================================
test('assign company admin requires user_id', function () {
    $superAdmin = createSuperAdmin();

    $this->actingAs($superAdmin)->post(route('admin.assign-company-admin'), [])
        ->assertSessionHasErrors('user_id');
});

test('remove company admin requires user_id', function () {
    $superAdmin = createSuperAdmin();

    $this->actingAs($superAdmin)->post(route('admin.remove-company-admin'), [])
        ->assertSessionHasErrors('user_id');
});

test('transfer super admin requires new_superadmin_id', function () {
    $superAdmin = createSuperAdmin();

    $this->actingAs($superAdmin)->post(route('admin.transfer-superadmin'), [])
        ->assertSessionHasErrors('new_superadmin_id');
});

// ============================================================
// Admin Management Tests — Unauthenticated Access
// ============================================================
test('unauthenticated users cannot access admin management routes', function () {
    $this->post(route('admin.transfer-superadmin'), ['new_superadmin_id' => 1])
        ->assertRedirect(route('login'));
    $this->post(route('admin.assign-company-admin'), ['user_id' => 1])
        ->assertRedirect(route('login'));
    $this->post(route('admin.remove-company-admin'), ['user_id' => 1])
        ->assertRedirect(route('login'));
});
