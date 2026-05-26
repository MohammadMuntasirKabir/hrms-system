<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\Designation;
use App\Models\JobApplicant;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if ($permissions === ['*']) {
                $role->syncPermissions(Permission::all());
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
        $mktDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Marketing', 'code' => 'MKT', 'description' => 'Brand and growth marketing', 'is_active' => true]);
        $opsDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Operations', 'code' => 'OPS', 'description' => 'Business operations and logistics', 'is_active' => true]);
        $salesDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Sales', 'code' => 'SLS', 'description' => 'Revenue and client acquisition', 'is_active' => true]);
        $legalDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Legal & Compliance', 'code' => 'LEG', 'description' => 'Legal affairs and regulatory compliance', 'is_active' => true]);
        $qaDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Quality Assurance', 'code' => 'QA', 'description' => 'Product quality and testing', 'is_active' => true]);
        $designDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Design', 'code' => 'DSN', 'description' => 'UI/UX and product design', 'is_active' => true]);
        $itDept = Department::create(['company_id' => $techCorp->id, 'name' => 'IT Support', 'code' => 'ITS', 'description' => 'Internal IT infrastructure and support', 'is_active' => true]);
        $bdDept = Department::create(['company_id' => $techCorp->id, 'name' => 'Business Development', 'code' => 'BD', 'description' => 'Partnerships and strategic growth', 'is_active' => true]);

        $srEng = Designation::create(['company_id' => $techCorp->id, 'department_id' => $engDept->id, 'title' => 'Senior Software Engineer', 'level' => 2, 'is_active' => true]);
        $jrEng = Designation::create(['company_id' => $techCorp->id, 'department_id' => $engDept->id, 'title' => 'Junior Software Engineer', 'level' => 0, 'is_active' => true]);
        $hrMgr = Designation::create(['company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'title' => 'HR Manager', 'level' => 4, 'is_active' => true]);
        $acct = Designation::create(['company_id' => $techCorp->id, 'department_id' => $finDept->id, 'title' => 'Accountant', 'level' => 1, 'is_active' => true]);
        $mktMgr = Designation::create(['company_id' => $techCorp->id, 'department_id' => $mktDept->id, 'title' => 'Marketing Manager', 'level' => 4, 'is_active' => true]);
        $opsMgr = Designation::create(['company_id' => $techCorp->id, 'department_id' => $opsDept->id, 'title' => 'Operations Manager', 'level' => 4, 'is_active' => true]);
        $salesExec = Designation::create(['company_id' => $techCorp->id, 'department_id' => $salesDept->id, 'title' => 'Sales Executive', 'level' => 1, 'is_active' => true]);
        $legalCounsel = Designation::create(['company_id' => $techCorp->id, 'department_id' => $legalDept->id, 'title' => 'Legal Counsel', 'level' => 5, 'is_active' => true]);
        $qaEng = Designation::create(['company_id' => $techCorp->id, 'department_id' => $qaDept->id, 'title' => 'QA Engineer', 'level' => 2, 'is_active' => true]);
        $uxDes = Designation::create(['company_id' => $techCorp->id, 'department_id' => $designDept->id, 'title' => 'UX Designer', 'level' => 2, 'is_active' => true]);
        $itAdmin = Designation::create(['company_id' => $techCorp->id, 'department_id' => $itDept->id, 'title' => 'IT Administrator', 'level' => 2, 'is_active' => true]);
        $bdMgr = Designation::create(['company_id' => $techCorp->id, 'department_id' => $bdDept->id, 'title' => 'BD Manager', 'level' => 4, 'is_active' => true]);

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

        $mkt1 = User::create(['name' => 'Rafiq Islam', 'email' => 'rafiq@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $mktDept->id, 'designation_id' => $mktMgr->id, 'employee_id' => 'EMP-007', 'job_title' => 'Marketing Manager', 'email_verified_at' => now(), 'is_active' => true]);
        $mkt1->assignRole('employee');

        $ops1 = User::create(['name' => 'Shakira Khatun', 'email' => 'shakira@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $opsDept->id, 'designation_id' => $opsMgr->id, 'employee_id' => 'EMP-008', 'job_title' => 'Operations Manager', 'email_verified_at' => now(), 'is_active' => true]);
        $ops1->assignRole('employee');

        $sales1 = User::create(['name' => 'Arman Hossain', 'email' => 'arman@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $salesDept->id, 'designation_id' => $salesExec->id, 'employee_id' => 'EMP-009', 'job_title' => 'Sales Executive', 'email_verified_at' => now(), 'is_active' => true]);
        $sales1->assignRole('employee');

        $qa1 = User::create(['name' => 'Mahmuda Akter', 'email' => 'mahmuda@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $qaDept->id, 'designation_id' => $qaEng->id, 'employee_id' => 'EMP-010', 'job_title' => 'QA Engineer', 'email_verified_at' => now(), 'is_active' => true]);
        $qa1->assignRole('employee');

        $des1 = User::create(['name' => 'Sabbir Ahmed', 'email' => 'sabbir@techcorp.com', 'password' => Hash::make('password'), 'company_id' => $techCorp->id, 'department_id' => $designDept->id, 'designation_id' => $uxDes->id, 'employee_id' => 'EMP-011', 'job_title' => 'UX Designer', 'email_verified_at' => now(), 'is_active' => true]);
        $des1->assignRole('employee');

        // Contracts for TechCorp
        $c1 = Contract::create(['user_id' => $eng1->id, 'company_id' => $techCorp->id, 'department_id' => $engDept->id, 'contract_type' => 'full_time', 'position' => 'Senior Software Engineer', 'start_date' => '2024-01-15', 'salary' => 120000, 'currency' => 'BDT', 'status' => 'active']);
        $c2 = Contract::create(['user_id' => $eng2->id, 'company_id' => $techCorp->id, 'department_id' => $engDept->id, 'contract_type' => 'full_time', 'position' => 'Junior Software Engineer', 'start_date' => '2024-06-01', 'salary' => 60000, 'currency' => 'BDT', 'status' => 'active']);
        $c3 = Contract::create(['user_id' => $hr1->id, 'company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'contract_type' => 'full_time', 'position' => 'HR Manager', 'start_date' => '2023-03-01', 'salary' => 95000, 'currency' => 'BDT', 'status' => 'active']);
        $c4 = Contract::create(['user_id' => $fin1->id, 'company_id' => $techCorp->id, 'department_id' => $finDept->id, 'contract_type' => 'full_time', 'position' => 'Accountant', 'start_date' => '2024-02-01', 'salary' => 55000, 'currency' => 'BDT', 'status' => 'active']);
        $c5 = Contract::create(['user_id' => $admin->id, 'company_id' => $techCorp->id, 'department_id' => $hrDept->id, 'contract_type' => 'full_time', 'position' => 'Company Admin', 'start_date' => '2023-01-01', 'salary' => 150000, 'currency' => 'BDT', 'status' => 'active']);
        $c6 = Contract::create(['user_id' => $mkt1->id, 'company_id' => $techCorp->id, 'department_id' => $mktDept->id, 'contract_type' => 'full_time', 'position' => 'Marketing Manager', 'start_date' => '2024-03-15', 'salary' => 90000, 'currency' => 'BDT', 'status' => 'active']);
        $c7 = Contract::create(['user_id' => $ops1->id, 'company_id' => $techCorp->id, 'department_id' => $opsDept->id, 'contract_type' => 'full_time', 'position' => 'Operations Manager', 'start_date' => '2024-04-01', 'salary' => 85000, 'currency' => 'BDT', 'status' => 'active']);
        $c8 = Contract::create(['user_id' => $sales1->id, 'company_id' => $techCorp->id, 'department_id' => $salesDept->id, 'contract_type' => 'full_time', 'position' => 'Sales Executive', 'start_date' => '2024-05-01', 'salary' => 45000, 'currency' => 'BDT', 'status' => 'active']);
        $c9 = Contract::create(['user_id' => $qa1->id, 'company_id' => $techCorp->id, 'department_id' => $qaDept->id, 'contract_type' => 'full_time', 'position' => 'QA Engineer', 'start_date' => '2024-02-15', 'salary' => 70000, 'currency' => 'BDT', 'status' => 'active']);
        $c10 = Contract::create(['user_id' => $des1->id, 'company_id' => $techCorp->id, 'department_id' => $designDept->id, 'contract_type' => 'full_time', 'position' => 'UX Designer', 'start_date' => '2024-06-01', 'salary' => 65000, 'currency' => 'BDT', 'status' => 'active']);

        // Salaries for TechCorp
        $this->createSalary($eng1, $techCorp, $engDept, $srEng, $c1, 120000, 15000, 5000, $admin);
        $this->createSalary($eng2, $techCorp, $engDept, $jrEng, $c2, 60000, 5000, 2000, $admin);
        $this->createSalary($hr1, $techCorp, $hrDept, $hrMgr, $c3, 95000, 10000, 3000, $admin);
        $this->createSalary($fin1, $techCorp, $finDept, $acct, $c4, 55000, 3000, 1500, $admin);
        $this->createSalary($admin, $techCorp, $hrDept, $hrMgr, $c5, 150000, 20000, 8000, $superAdmin);
        $this->createSalary($mkt1, $techCorp, $mktDept, $mktMgr, $c6, 90000, 10000, 3000, $admin);
        $this->createSalary($ops1, $techCorp, $opsDept, $opsMgr, $c7, 85000, 8000, 3000, $admin);
        $this->createSalary($sales1, $techCorp, $salesDept, $salesExec, $c8, 45000, 5000, 1500, $admin);
        $this->createSalary($qa1, $techCorp, $qaDept, $qaEng, $c9, 70000, 5000, 2000, $admin);
        $this->createSalary($des1, $techCorp, $designDept, $uxDes, $c10, 65000, 5000, 2000, $admin);

        // === Company 2: GreenLeaf Ltd (Independent) ===
        $greenLeaf = Company::create([
            'name' => 'GreenLeaf Ltd', 'slug' => 'greenleaf',
            'domain' => 'greenleaf.com', 'country' => 'BD', 'timezone' => 'Asia/Dhaka', 'is_active' => true,
        ]);

        $opsDept = Department::create(['company_id' => $greenLeaf->id, 'name' => 'Operations', 'code' => 'OPS', 'is_active' => true]);
        $mktDept = Department::create(['company_id' => $greenLeaf->id, 'name' => 'Marketing', 'code' => 'MKT', 'is_active' => true]);
        $finDept = Department::create(['company_id' => $greenLeaf->id, 'name' => 'Finance', 'code' => 'FIN', 'is_active' => true]);
        $hrDept = Department::create(['company_id' => $greenLeaf->id, 'name' => 'Human Resources', 'code' => 'HR', 'is_active' => true]);
        $logDept = Department::create(['company_id' => $greenLeaf->id, 'name' => 'Logistics', 'code' => 'LOG', 'is_active' => true]);
        $csDept = Department::create(['company_id' => $greenLeaf->id, 'name' => 'Customer Service', 'code' => 'CS', 'is_active' => true]);

        $opsMgr = Designation::create(['company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'title' => 'Operations Manager', 'level' => 4, 'is_active' => true]);
        $coord = Designation::create(['company_id' => $greenLeaf->id, 'department_id' => $mktDept->id, 'title' => 'Marketing Coordinator', 'level' => 1, 'is_active' => true]);
        $acct = Designation::create(['company_id' => $greenLeaf->id, 'department_id' => $finDept->id, 'title' => 'Accountant', 'level' => 1, 'is_active' => true]);
        $hrExec = Designation::create(['company_id' => $greenLeaf->id, 'department_id' => $hrDept->id, 'title' => 'HR Executive', 'level' => 1, 'is_active' => true]);
        $logCoord = Designation::create(['company_id' => $greenLeaf->id, 'department_id' => $logDept->id, 'title' => 'Logistics Coordinator', 'level' => 1, 'is_active' => true]);
        $csRep = Designation::create(['company_id' => $greenLeaf->id, 'department_id' => $csDept->id, 'title' => 'Customer Service Rep', 'level' => 0, 'is_active' => true]);

        $glAdmin = User::create(['name' => 'GreenLeaf Admin', 'email' => 'admin@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'designation_id' => $opsMgr->id, 'employee_id' => 'GL-001', 'email_verified_at' => now(), 'is_active' => true]);
        $glAdmin->assignRole('company_admin');

        $glOps = User::create(['name' => 'Hasan Mahmud', 'email' => 'hasan@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'designation_id' => $opsMgr->id, 'employee_id' => 'GL-002', 'job_title' => 'Operations Manager', 'email_verified_at' => now(), 'is_active' => true]);
        $glOps->assignRole('employee');

        $glMkt = User::create(['name' => 'Sadia Rahman', 'email' => 'sadia@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $mktDept->id, 'designation_id' => $coord->id, 'employee_id' => 'GL-003', 'job_title' => 'Marketing Coordinator', 'email_verified_at' => now(), 'is_active' => true]);
        $glMkt->assignRole('employee');

        $glFin = User::create(['name' => 'Imran Hossain', 'email' => 'imran@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $finDept->id, 'designation_id' => $acct->id, 'employee_id' => 'GL-004', 'job_title' => 'Accountant', 'email_verified_at' => now(), 'is_active' => true]);
        $glFin->assignRole('employee');

        $glHr = User::create(['name' => 'Tasnim Akter', 'email' => 'tasnim@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $hrDept->id, 'designation_id' => $hrExec->id, 'employee_id' => 'GL-005', 'job_title' => 'HR Executive', 'email_verified_at' => now(), 'is_active' => true]);
        $glHr->assignRole('employee');

        $glLog = User::create(['name' => 'Kamal Uddin', 'email' => 'kamal@greenleaf.com', 'password' => Hash::make('password'), 'company_id' => $greenLeaf->id, 'department_id' => $logDept->id, 'designation_id' => $logCoord->id, 'employee_id' => 'GL-006', 'job_title' => 'Logistics Coordinator', 'email_verified_at' => now(), 'is_active' => true]);
        $glLog->assignRole('employee');

        $gc1 = Contract::create(['user_id' => $glOps->id, 'company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'contract_type' => 'full_time', 'position' => 'Operations Manager', 'start_date' => '2024-03-01', 'salary' => 85000, 'currency' => 'BDT', 'status' => 'active']);
        $gc2 = Contract::create(['user_id' => $glMkt->id, 'company_id' => $greenLeaf->id, 'department_id' => $mktDept->id, 'contract_type' => 'full_time', 'position' => 'Marketing Coordinator', 'start_date' => '2024-07-01', 'salary' => 45000, 'currency' => 'BDT', 'status' => 'active']);
        $gc3 = Contract::create(['user_id' => $glAdmin->id, 'company_id' => $greenLeaf->id, 'department_id' => $opsDept->id, 'contract_type' => 'full_time', 'position' => 'Company Admin', 'start_date' => '2023-06-01', 'salary' => 110000, 'currency' => 'BDT', 'status' => 'active']);
        $gc4 = Contract::create(['user_id' => $glFin->id, 'company_id' => $greenLeaf->id, 'department_id' => $finDept->id, 'contract_type' => 'full_time', 'position' => 'Accountant', 'start_date' => '2024-04-01', 'salary' => 50000, 'currency' => 'BDT', 'status' => 'active']);
        $gc5 = Contract::create(['user_id' => $glHr->id, 'company_id' => $greenLeaf->id, 'department_id' => $hrDept->id, 'contract_type' => 'full_time', 'position' => 'HR Executive', 'start_date' => '2024-05-01', 'salary' => 42000, 'currency' => 'BDT', 'status' => 'active']);
        $gc6 = Contract::create(['user_id' => $glLog->id, 'company_id' => $greenLeaf->id, 'department_id' => $logDept->id, 'contract_type' => 'full_time', 'position' => 'Logistics Coordinator', 'start_date' => '2024-06-01', 'salary' => 40000, 'currency' => 'BDT', 'status' => 'active']);

        $this->createSalary($glOps, $greenLeaf, $opsDept, $opsMgr, $gc1, 85000, 8000, 3000, $glAdmin);
        $this->createSalary($glMkt, $greenLeaf, $mktDept, $coord, $gc2, 45000, 3000, 1000, $glAdmin);
        $this->createSalary($glAdmin, $greenLeaf, $opsDept, $opsMgr, $gc3, 110000, 15000, 5000, $glAdmin);
        $this->createSalary($glFin, $greenLeaf, $finDept, $acct, $gc4, 50000, 3000, 1500, $glAdmin);
        $this->createSalary($glHr, $greenLeaf, $hrDept, $hrExec, $gc5, 42000, 3000, 1000, $glAdmin);
        $this->createSalary($glLog, $greenLeaf, $logDept, $logCoord, $gc6, 40000, 3000, 1000, $glAdmin);

        // === Company 3: DataFlow Inc (Subsidiary of TechCorp) ===
        $dataFlow = Company::create([
            'name' => 'DataFlow Inc', 'slug' => 'dataflow',
            'domain' => 'dataflow.io', 'country' => 'BD', 'timezone' => 'Asia/Dhaka',
            'parent_company_id' => $techCorp->id, 'is_active' => true,
        ]);

        $dsDept = Department::create(['company_id' => $dataFlow->id, 'name' => 'Data Science', 'code' => 'DS', 'is_active' => true]);
        $mlDept = Department::create(['company_id' => $dataFlow->id, 'name' => 'Machine Learning', 'code' => 'ML', 'is_active' => true]);
        $enggDept = Department::create(['company_id' => $dataFlow->id, 'name' => 'Data Engineering', 'code' => 'DE', 'is_active' => true]);
        $analyticsDept = Department::create(['company_id' => $dataFlow->id, 'name' => 'Analytics', 'code' => 'ANA', 'is_active' => true]);

        $dsLead = Designation::create(['company_id' => $dataFlow->id, 'department_id' => $dsDept->id, 'title' => 'Data Scientist', 'level' => 2, 'is_active' => true]);
        $mlEng = Designation::create(['company_id' => $dataFlow->id, 'department_id' => $mlDept->id, 'title' => 'ML Engineer', 'level' => 2, 'is_active' => true]);
        $dataEng = Designation::create(['company_id' => $dataFlow->id, 'department_id' => $enggDept->id, 'title' => 'Data Engineer', 'level' => 2, 'is_active' => true]);
        $analyst = Designation::create(['company_id' => $dataFlow->id, 'department_id' => $analyticsDept->id, 'title' => 'Data Analyst', 'level' => 1, 'is_active' => true]);

        $dfLead = User::create(['name' => 'Tanvir Hassan', 'email' => 'tanvir@dataflow.io', 'password' => Hash::make('password'), 'company_id' => $dataFlow->id, 'department_id' => $dsDept->id, 'designation_id' => $dsLead->id, 'employee_id' => 'DF-001', 'job_title' => 'Lead Data Scientist', 'email_verified_at' => now(), 'is_active' => true]);
        $dfLead->assignRole('department_head');

        $dfMl = User::create(['name' => 'Nadia Karim', 'email' => 'nadia@dataflow.io', 'password' => Hash::make('password'), 'company_id' => $dataFlow->id, 'department_id' => $mlDept->id, 'designation_id' => $mlEng->id, 'employee_id' => 'DF-002', 'job_title' => 'ML Engineer', 'email_verified_at' => now(), 'is_active' => true]);
        $dfMl->assignRole('employee');

        $dfEng = User::create(['name' => 'Farhan Ahmed', 'email' => 'farhan@dataflow.io', 'password' => Hash::make('password'), 'company_id' => $dataFlow->id, 'department_id' => $enggDept->id, 'designation_id' => $dataEng->id, 'employee_id' => 'DF-003', 'job_title' => 'Data Engineer', 'email_verified_at' => now(), 'is_active' => true]);
        $dfEng->assignRole('employee');

        $dfAnalyst = User::create(['name' => 'Priya Das', 'email' => 'priya@dataflow.io', 'password' => Hash::make('password'), 'company_id' => $dataFlow->id, 'department_id' => $analyticsDept->id, 'designation_id' => $analyst->id, 'employee_id' => 'DF-004', 'job_title' => 'Data Analyst', 'email_verified_at' => now(), 'is_active' => true]);
        $dfAnalyst->assignRole('employee');

        $dc1 = Contract::create(['user_id' => $dfLead->id, 'company_id' => $dataFlow->id, 'department_id' => $dsDept->id, 'contract_type' => 'full_time', 'position' => 'Lead Data Scientist', 'start_date' => '2024-04-01', 'salary' => 130000, 'currency' => 'BDT', 'status' => 'active']);
        $dc2 = Contract::create(['user_id' => $dfMl->id, 'company_id' => $dataFlow->id, 'department_id' => $mlDept->id, 'contract_type' => 'full_time', 'position' => 'ML Engineer', 'start_date' => '2024-05-01', 'salary' => 110000, 'currency' => 'BDT', 'status' => 'active']);
        $dc3 = Contract::create(['user_id' => $dfEng->id, 'company_id' => $dataFlow->id, 'department_id' => $enggDept->id, 'contract_type' => 'full_time', 'position' => 'Data Engineer', 'start_date' => '2024-06-01', 'salary' => 105000, 'currency' => 'BDT', 'status' => 'active']);
        $dc4 = Contract::create(['user_id' => $dfAnalyst->id, 'company_id' => $dataFlow->id, 'department_id' => $analyticsDept->id, 'contract_type' => 'full_time', 'position' => 'Data Analyst', 'start_date' => '2024-07-01', 'salary' => 55000, 'currency' => 'BDT', 'status' => 'active']);

        $this->createSalary($dfLead, $dataFlow, $dsDept, $dsLead, $dc1, 130000, 18000, 6000, $admin);
        $this->createSalary($dfMl, $dataFlow, $mlDept, $mlEng, $dc2, 110000, 15000, 5000, $admin);
        $this->createSalary($dfEng, $dataFlow, $enggDept, $dataEng, $dc3, 105000, 12000, 4000, $admin);
        $this->createSalary($dfAnalyst, $dataFlow, $analyticsDept, $analyst, $dc4, 55000, 5000, 2000, $admin);

        // === Seed Job Applicants for all companies ===
        $this->seedApplicants($techCorp, $engDept, $hrDept, $finDept, $srEng, $jrEng, $hrMgr, $acct);
        $this->seedApplicants($greenLeaf, $opsDept, $mktDept, null, $opsMgr, $coord, null, null);
        $this->seedApplicants($dataFlow, $dsDept, null, null, $dsLead, null, null, null);
    }

    private function seedApplicants(Company $company, ?Department $dept1, ?Department $dept2, ?Department $dept3, ?Designation $desig1, ?Designation $desig2, ?Designation $desig3, ?Designation $desig4): void
    {
        $firstNames = ['Arif', 'Nusrat', 'Kamal', 'Sabbir', 'Mithila', 'Tanvir', 'Priya', 'Farhan', 'Riya', 'Jahangir', 'Sneha', 'Imran'];
        $lastNames = ['Hossain', 'Begum', 'Rahman', 'Ahmed', 'Sarkar', 'Islam', 'Das', 'Khan', 'Akter', 'Alam', 'Roy', 'Haque'];
        $sources = ['Website', 'LinkedIn', 'Indeed', 'Referral', 'Glassdoor'];
        $cities = ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Comilla', 'Rangpur'];
        $statuses = ['pending', 'pending', 'pending', 'reviewing', 'reviewing', 'shortlisted', 'shortlisted', 'hired', 'rejected', 'rejected'];

        $depts = array_filter([$dept1, $dept2, $dept3]);
        $desigs = array_filter([$desig1, $desig2, $desig3, $desig4]);
        $usedEmails = [];

        for ($i = 0; $i < 12; $i++) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $company->slug));
            $email = strtolower($firstNames[$i]).'.'.$slug.'@email.com';

            // Ensure unique email
            if (in_array($email, $usedEmails)) {
                $email = strtolower($firstNames[$i]).rand(10, 99).'.'.$slug.'@email.com';
            }
            $usedEmails[] = $email;

            $dept = ! empty($depts) ? $depts[array_rand($depts)] : null;
            $desig = ! empty($desigs) ? $desigs[array_rand($desigs)] : null;
            $status = $statuses[array_rand($statuses)];
            $salary = rand(35000, 150000);
            $createdDaysAgo = rand(1, 45);
            $createdAt = now()->subDays($createdDaysAgo);

            $applicantData = [
                'company_id' => $company->id,
                'department_id' => $dept?->id,
                'designation_id' => $desig?->id,
                'first_name' => $firstNames[$i],
                'last_name' => $lastNames[$i],
                'email' => $email,
                'phone' => '+880 1'.rand(7, 9).rand(10000000, 99999999),
                'address' => rand(1, 99).' '.['Road', 'Avenue', 'Lane', 'Street'][array_rand(['Road', 'Avenue', 'Lane', 'Street'])].', '.$cities[array_rand($cities)],
                'city' => $cities[array_rand($cities)],
                'country' => 'BD',
                'cover_letter' => 'I am excited to apply for this position at '.$company->name.'. I have relevant experience and skills that make me a strong candidate for this role. I am eager to contribute to the team and grow with the organization.',
                'source' => $sources[array_rand($sources)],
                'expected_salary' => $salary,
                'currency' => 'BDT',
                'available_from' => now()->addDays(rand(7, 90)),
                'status' => $status,
                'notes' => 'Auto-seeded applicant record for testing.',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            if (in_array($status, ['reviewing', 'shortlisted', 'hired', 'rejected'])) {
                $applicantData['reviewed_at'] = $createdAt->copy()->addDays(rand(1, 5));
            }

            JobApplicant::create($applicantData);
        }
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
