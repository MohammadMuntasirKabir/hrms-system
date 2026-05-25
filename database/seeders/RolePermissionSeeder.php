<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    private const PERMISSIONS = [
        'companies.view', 'companies.create', 'companies.edit', 'companies.delete',
        'users.view', 'users.create', 'users.edit', 'users.delete', 'users.impersonate',
        'roles.view', 'roles.assign',
        'designations.view', 'designations.create', 'designations.edit', 'designations.delete',
        'contracts.view', 'contracts.create', 'contracts.edit', 'contracts.delete',
        'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
        'applicants.view', 'applicants.create', 'applicants.hire', 'applicants.reject',
        'profile.view', 'profile.edit',
        'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
        'payroll.view', 'payroll.manage',
        'leave.view', 'leave.manage', 'leave.approve',
        'reports.view', 'reports.export',
    ];

    private const ROLE_PERMISSIONS = [
        'super_admin' => ['*'],
        'company_admin' => [
            'companies.edit',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.assign',
            'designations.view', 'designations.create', 'designations.edit', 'designations.delete',
            'contracts.view', 'contracts.create', 'contracts.edit', 'contracts.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'applicants.view', 'applicants.create', 'applicants.hire', 'applicants.reject',
            'profile.view', 'profile.edit',
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'payroll.view', 'payroll.manage',
            'leave.view', 'leave.manage', 'leave.approve',
            'reports.view', 'reports.export',
        ],
        'hr_manager' => [
            'users.view', 'designations.view',
            'contracts.view', 'contracts.create', 'contracts.edit',
            'departments.view',
            'applicants.view', 'applicants.create', 'applicants.hire', 'applicants.reject',
            'profile.view', 'profile.edit',
            'employees.view', 'employees.create', 'employees.edit',
            'payroll.view', 'payroll.manage',
            'leave.view', 'leave.manage', 'leave.approve',
            'reports.view', 'reports.export',
        ],
        'hr_executive' => [
            'profile.view', 'profile.edit', 'designations.view', 'contracts.view',
            'employees.view', 'leave.view', 'leave.manage', 'reports.view',
        ],
        'department_head' => [
            'profile.view', 'profile.edit', 'designations.view', 'contracts.view',
            'employees.view', 'leave.view', 'leave.approve', 'reports.view',
        ],
        'employee' => ['profile.view', 'profile.edit', 'leave.view'],
    ];

    public function run(): void
    {
        app('cache')->forget('spatie.permission.cache');

        foreach (self::PERMISSIONS as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if ($permissions === ['*']) {
                $role->syncPermissions(\Spatie\Permission\Models\Permission::all());
            } else {
                $role->syncPermissions($permissions);
            }
        }

        if (! Company::exists()) {
            $this->seedSampleData();
        }
    }

    private function seedSampleData(): void
    {
        // === Company 1: TechCorp HQ (Main Company) ===
        $techCorp = Company::create([
            'name' => 'TechCorp HQ', 'slug' => 'techcorp-hq',
            'domain' => 'techcorp.com', 'country' => 'BD', 'timezone' => 'Asia/Dhaka', 'is_active' => true,
        ]);

        $engDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Engineering', 'code' => 'ENG', 'description' => 'Software development and infrastructure', 'is_active' => true]);
        $hrDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Human Resources', 'code' => 'HR', 'description' => 'People operations', 'is_active' => true]);
        $finDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Finance', 'code' => 'FIN', 'description' => 'Financial operations', 'is_active' => true]);

        $srEng = Designation::create(['company_id' => $techCorp->id, 'department_id' => $engDept->id, 'title' => 'Senior Software Engineer', 'level' => 2, 'is_active' => true]);
        $jrEng = Designation::create(['company_id' => $techCorp->id, 'department_id' => $engDept->id, 'title' => 'Junior Software Engineer', 'level' => 0, 'is_active' => true]);
        $hrMgr = Designation::create(['company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'title' => 'HR Manager', 'level' => 4, 'is_active' => true]);
        $acct = Designation::create(['company_id' => $techCorp->id, 'department_id' => $finDept->id, 'title' => 'Accountant', 'level' => 1, 'is_active' => true]);

        $superAdmin = User::create(['name' => 'Super Admin', 'email' => 'admin@hrms.local', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'designation_id' => $hrMgr->id, 'employee_id' => 'EMP-001', 'email_verified_at' => now(), 'is_active' => true]);
        $superAdmin->assignRole('super_admin');

        $admin = User::create(['name' => 'TechCorp Admin', 'email' => 'admin@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'designation_id' => $hrMgr->id, 'employee_id' => 'EMP-002', 'email_verified_at' => now(), 'is_active' => true]);
        $admin->assignRole('company_admin');

        $eng1 = User::create(['name' => 'Rahim Uddin', 'email' => 'rahim@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $engDept->id, 'designation_id' => $srEng->id, 'employee_id' => 'EMP-003', 'job_title' => 'Senior Software Engineer', 'email_verified_at' => now(), 'is_active' => true]);
        $eng1->assignRole('employee');

        $eng2 = User::create(['name' => 'Fatima Akter', 'email' => 'fatima@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $engDept->id, 'designation_id' => $jrEng->id, 'employee_id' => 'EMP-004', 'job_title' => 'Junior Software Engineer', 'email_verified_at' => now(), 'is_active' => true]);
        $eng2->assignRole('employee');

        $hr1 = User::create(['name' => 'Karim Ahmed', 'email' => 'karim@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'designation_id' => $hrMgr->id, 'employee_id' => 'EMP-005', 'job_title' => 'HR Manager', 'email_verified_at' => now(), 'is_active' => true]);
        $hr1->assignRole('hr_manager');

        $fin1 = User::create(['name' => 'Nusrat Jahan', 'email' => 'nusrat@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $finDept->id, 'designation_id' => $acct->id, 'employee_id' => 'EMP-006', 'job_title' => 'Accountant', 'email_verified_at' => now(), 'is_active' => true]);
        $fin1->assignRole('employee');

        // Contracts for TechCorp
        $c1 = Contract::create(['user_id' => $eng1->id, 'company_id' => $techCorp->id, 'department_id' => $engDept->id, 'contract_type' => 'full_time', 'position' => 'Senior Software Engineer', 'start_date' => '2024-01-15', 'salary' => 120000, 'currency' => 'BDT', 'status' => 'active']);
        $c2 = Contract::create(['user_id' => $eng2->id, 'company_id' => $techCorp->id, 'department_id' => $engDept->id, 'contract_type' => 'full_time', 'position' => 'Junior Software Engineer', 'start_date' => '2024-06-01', 'salary' => 60000, 'currency' => 'BDT', 'status' => 'active']);
        $c3 = Contract::create(['user_id' => $hr1->id, 'company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'contract_type' => 'full_time', 'position' => 'HR Manager', 'start_date' => '2023-03-01', 'salary' => 95000, 'currency' => 'BDT', 'status' => 'active']);
        $c4 = Contract::create(['user_id' => $fin1->id, 'company_id' => $techCorp->id, 'department_id' => $finDept->id, 'contract_type' => 'full_time', 'position' => 'Accountant', 'start_date' => '2024-02-01', 'salary' => 55000, 'currency' => 'BDT', 'status' => 'active']);
        $c5 = Contract::create(['user_id' => $admin->id, 'company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'contract_type' => 'full_time', 'position' => 'Company Admin', 'start_date' => '2023-01-01', 'salary' => 150000, 'currency' => 'BDT', 'status' => 'active']);

        // Salaries for TechCorp
        $this->createSalary($eng1, $techCorp, $engDept, $srEng, $c1, 120000, 15000, 5000, $admin);
        $this->createSalary($eng2, $techCorp, $engDept, $jrEng, $c2, 60000, 5000, 2000, $admin);
        $this->createSalary($hr1, $techCorp, $hrDept, $hrMgr, $c3, 95000, 10000, 3000, $admin);
        $this->createSalary($fin1, $techCorp, $finDept, $acct, $c4, 55000, 3000, 1500, $admin);
        $this->createSalary($admin, $techCorp, $hrDept, $hrMgr, $c5, 150000, 20000, 8000, $superAdmin);

        // === Company 2: GreenLeaf Ltd (Independent) ===
        $greenLeaf = Company::create([
            'name' => 'GreenLeaf Ltd', 'slug' => 'greenleaf',
            'domain' => 'greenleaf.com', 'country' => 'BD', 'timezone' => 'Asia/Dhaka', 'is_active' => true,
        ]);

        $opsDept = Department::create(['company_id' => $greenLeaf->id, 'name' => 'Operations', 'code' => 'OPS', 'is_active' => true]);
        $mktDept = Department::create(['company_id' => $greenLeaf->id, 'name' => 'Marketing', 'code' => 'MKT', 'is_active' => true]);

        $opsMgr = Designation::create(['company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'title' => 'Operations Manager', 'level' => 4, 'is_active' => true]);
        $coord = Designation::create(['company_id' => $greenLeaf->id, 'department_id' => $mktDept->id, 'title' => 'Marketing Coordinator', 'level' => 1, 'is_active' => true]);

        $glAdmin = User::create(['name' => 'GreenLeaf Admin', 'email' => 'admin@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'designation_id' => $opsMgr->id, 'employee_id' => 'GL-001', 'email_verified_at' => now(), 'is_active' => true]);
        $glAdmin->assignRole('company_admin');

        $glOps = User::create(['name' => 'Hasan Mahmud', 'email' => 'hasan@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'designation_id' => $opsMgr->id, 'employee_id' => 'GL-002', 'job_title' => 'Operations Manager', 'email_verified_at' => now(), 'is_active' => true]);
        $glOps->assignRole('employee');

        $glMkt = User::create(['name' => 'Sadia Rahman', 'email' => 'sadia@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $mktDept->id, 'designation_id' => $coord->id, 'employee_id' => 'GL-003', 'job_title' => 'Marketing Coordinator', 'email_verified_at' => now(), 'is_active' => true]);
        $glMkt->assignRole('employee');

        $gc1 = Contract::create(['user_id' => $glOps->id, 'company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'contract_type' => 'full_time', 'position' => 'Operations Manager', 'start_date' => '2024-03-01', 'salary' => 85000, 'currency' => 'BDT', 'status' => 'active']);
        $gc2 = Contract::create(['user_id' => $glMkt->id, 'company_id' => $greenLeaf->id, 'department_id' => $mktDept->id, 'contract_type' => 'full_time', 'position' => 'Marketing Coordinator', 'start_date' => '2024-07-01', 'salary' => 45000, 'currency' => 'BDT', 'status' => 'active']);
        $gc3 = Contract::create(['user_id' => $glAdmin->id, 'company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'contract_type' => 'full_time', 'position' => 'Company Admin', 'start_date' => '2023-06-01', 'salary' => 110000, 'currency' => 'BDT', 'status' => 'active']);

        $this->createSalary($glOps, $greenLeaf, $opsDept, $opsMgr, $gc1, 85000, 8000, 3000, $glAdmin);
        $this->createSalary($glMkt, $greenLeaf, $mktDept, $coord, $gc2, 45000, 3000, 1000, $glAdmin);
        $this->createSalary($glAdmin, $greenLeaf, $opsDept, $opsMgr, $gc3, 110000, 15000, 5000, $glAdmin);

        // === Company 3: DataFlow Inc (Subsidiary of TechCorp) ===
        $dataFlow = Company::create([
            'name' => 'DataFlow Inc', 'slug' => 'dataflow',
            'domain' => 'dataflow.io', 'country' => 'BD', 'timezone' => 'Asia/Dhaka',
            'parent_company_id' => $techCorp->id, 'is_active' => true,
        ]);

        $dsDept = Department::create(['company_id' => $dataFlow->id, 'name' => 'Data Science', 'code' => 'DS', 'is_active' => true]);
        $dsLead = Designation::create(['company_id' => $dataFlow->id, 'department_id' => $dsDept->id, 'title' => 'Data Scientist', 'level' => 2, 'is_active' => true]);

        $dfLead = User::create(['name' => 'Tanvir Hassan', 'email' => 'tanvir@dataflow.io', 'password' => Hash::make('password'), 'company_id' => $dataFlow->id, 'department_id' => $dsDept->id, 'designation_id' => $dsLead->id, 'employee_id' => 'DF-001', 'job_title' => 'Lead Data Scientist', 'email_verified_at' => now(), 'is_active' => true]);
        $dfLead->assignRole('department_head');

        $dc1 = Contract::create(['user_id' => $dfLead->id, 'company_id' => $dataFlow->id, 'department_id' => $dsDept->id, 'contract_type' => 'full_time', 'position' => 'Lead Data Scientist', 'start_date' => '2024-04-01', 'salary' => 130000, 'currency' => 'BDT', 'status' => 'active']);

        $this->createSalary($dfLead, $dataFlow, $dsDept, $dsLead, $dc1, 130000, 18000, 6000, $admin);
    }

    private function createSalary(User $user, Company $company, Department $department, Designation $designation, Contract $contract, float $base, float $allowances, float $deductions, User $creator): void
    {
        Salary::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'contract_id' => $contract->id,
            'base_salary' => $base,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_salary' => $base + $allowances - $deductions,
            'currency' => 'BDT',
            'pay_frequency' => 'monthly',
            'effective_from' => $contract->start_date,
            'status' => 'active',
            'created_by' => $creator->id,
        ]);
    }
}
