# HRMS — Human Resource Management System

A full-featured HRMS built with **Laravel 13**, **Livewire 4**, **Flux UI v2**, and **Spatie Laravel Permission**. Manages companies, departments, designations, employees, contracts, salaries, and job applicants with role-based access control.

## Tech Stack

| Layer | Stack |
|---|---|
| Backend | Laravel 13 (PHP 8.3) |
| Frontend | Livewire 4 + Flux UI v2 + Tailwind CSS 4 |
| Auth | Laravel Fortify (with Passkeys + 2FA) |
| Permissions | Spatie Laravel Permission |
| Database | SQLite (default) / MySQL / PostgreSQL |
| Testing | Pest PHP v4 |

## Features

### Multi-Company Support
- Companies with parent-child hierarchy (HQ + subsidiaries)
- Super admins see all companies; company admins see their own tree
- Company-level data filtering across all modules

### Role-Based Access Control (6 roles, 30+ permissions)

| Role | Access |
|---|---|
| **Super Admin** | Full access across all companies. Only one super admin can exist at a time. |
| **Company Admin** | Full access within own company tree. One admin per company. |
| **HR Manager** | Employees, contracts, applicants, payroll |
| **HR Executive** | View-only + limited create/edit |
| **Department Head** | Department employees, leave approval |
| **Employee** | Own profile and leave only |

### Admin Management (Super Admin Only)
- **Transfer Super Admin** — Transfer your super admin role to another active user. Only one super admin can exist at a time. After transfer, you lose all super admin privileges.
- **Assign Company Admin** — Assign a user as admin of their company. Each company can have at most one admin. If a company already has an admin, the existing admin must be removed first. Assigning company admin replaces any existing role.
- **Remove Company Admin** — Remove a company admin. The user is automatically downgraded to employee role.

### Modules

- **Dashboard** — Role-specific stats (employee count, payroll, expiring contracts, recent hires)
- **Companies** — CRUD with hierarchy, user/department/contract counts
- **Departments** — CRUD with parent-child nesting, head assignment, user counts
- **Designations** — Job titles with level hierarchy, department mapping
- **Employees (Users)** — Full CRUD with role assignment, department/designation linking
- **Contracts** — Full CRUD with type (full-time/part-time/internship/freelance), expiry tracking, salary
- **Salaries** — Full CRUD with base/allowances/deductions/net calculation, pay frequency
- **Job Applicants** — Full recruitment pipeline:
  - Status workflow: Pending → Reviewing → Shortlisted → Hired (or Rejected)
  - One-click hire: creates Employee + Contract + Salary in one action
  - 24-hour undo window for hires
  - Rejection undo + permanent delete for rejected applicants
  - Company and department filters (super admin)

## Installation

```bash
# Clone the repository
git clone git@github.com:MohammadMuntasirKabir/hrms-system.git
cd hrms-system

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed

# Build frontend assets
npm run build

# Or for development
npm run dev
```

## Default Login

After seeding:

| Field | Value |
|---|---|
| Email | `admin@hrms.local` |
| Password | `password` |

## Database Schema

```
companies
  ├── id, name, slug, domain, parent_company_id, country, timezone, is_active
  └── childCompanies (self-referential)

departments
  ├── id, company_id, name, code, description, parent_department_id, head_user_id, is_active
  └── childDepartments (self-referential), users, designations, contracts

designations
  ├── id, company_id, department_id, title, description, level, is_active
  └── users

users
  ├── id, name, email, password, company_id, department_id, designation_id
  ├── employee_id, job_title, is_active
  ├── roles (spatie), contracts, salaries
  └── activeContract (HasOne), activeSalary (HasOne)

contracts
  ├── id, user_id, company_id, department_id, contract_type, position
  ├── start_date, end_date, salary, currency, status, notes
  └── is_expired (accessor), is_expiring_soon (accessor)

salaries
  ├── id, user_id, company_id, department_id, designation_id, contract_id
  ├── base_salary, allowances, deductions, net_salary, currency, pay_frequency
  ├── effective_from, effective_until, status, notes, created_by
  └── calculateNetSalary(), getAnnualSalary()

job_applicants
  ├── id, company_id, department_id, designation_id
  ├── first_name, last_name, email, phone, address, city, country
  ├── cover_letter, source, expected_salary, currency, available_from
  ├── status (pending/reviewing/shortlisted/hired/rejected)
  ├── reviewed_by, reviewed_at, hired_as_user_id
  └── full_name (accessor), isHired/isPending/isRejected/etc (accessors)
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run with compact output
php artisan test --compact

# Run specific test file
php artisan test tests/Feature/HRFeatureTest.php

# Run specific test by name
php artisan test --filter="super admin can hire an applicant"
```

**191 tests, 399 assertions** covering:
- All CRUD operations for every resource
- Authorization and role-based access control
- Cross-company data isolation
- Applicant recruitment workflow (review → shortlist → hire → undo)
- **Admin management** (super admin transfer, company admin assignment/removal, one-admin-per-company enforcement)
- Session flash messages and validation errors
- Dashboard per role

## Project Structure

```
app/
├── Http/Controllers/
│   ├── ApplicantController.php        # Job applicant CRUD + hire workflow
│   ├── CompanyController.php          # Company CRUD with hierarchy
│   ├── ContractController.php         # Contract CRUD
│   ├── DashboardController.php        # Role-specific dashboards
│   ├── DepartmentController.php       # Department CRUD with nesting
│   ├── DesignationController.php      # Designation CRUD
│   ├── SalaryController.php           # Salary CRUD
│   └── UserManagementController.php   # Employee CRUD + role assignment + admin management
├── Models/
│   ├── Company.php                    # Hierarchy: parentCompany, childCompanies
│   ├── Contract.php                   # Accessors: is_expired, is_expiring_soon
│   ├── Department.php                 # Self-nesting: parentDepartment, childDepartments
│   ├── Designation.php                # Level-based job titles
│   ├── JobApplicant.php               # Status checks: isHired, isPending, etc.
│   ├── Salary.php                     # Calculators: net salary, annual salary
│   └── User.php                       # Roles, activeContract, activeSalary
├── Livewire/Actions/Logout.php
├── View/Composers/SidebarComposer.php
└── ...

resources/views/
├── applicants/                        # Job applicant pages
├── companies/                         # Company pages
├── contracts/                         # Contract pages
├── dashboard/                         # Role-specific dashboards (super-admin, admin, dept-head, employee)
├── departments/                       # Department pages
├── designations/                      # Designation pages
├── salaries/                          # Salary pages
└── users/                             # Employee pages (with admin management section)

tests/Feature/
├── Auth/                              # Authentication tests (login, register, 2FA, password reset)
├── Settings/                          # Profile and security settings tests
├── DashboardTest.php                  # Dashboard access tests
└── HRFeatureTest.php                  # 191 comprehensive feature tests
```

## Key Design Decisions

- **Session-based company filter**: Super admins can filter by company; the filter persists via session middleware (`StoreCompanyFilter`)
- **View Composers don't work with Livewire**: Sidebar data is queried directly in blade templates
- **Eager loading**: `activeContract` and `activeSalary` are `HasOne` relationships (not methods) to support `with()` eager loading
- **Applicant hire**: Atomic operation that creates User + Contract + Salary in one request
- **24-hour undo**: Hired applicants can be reverted within 24 hours, deleting all created records
- **Single super admin**: Only one super admin can exist. Transfer is required to delegate super admin privileges
- **One company admin per company**: Enforced at assignment time. Existing admin must be removed before assigning a new one
- **Role replacement**: Assigning company admin replaces any existing senior role (hr_manager, hr_executive, etc.)

## License

This project is open-source software.
