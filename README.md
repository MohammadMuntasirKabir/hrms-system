# HRMS — Human Resource Management System

A full-featured HRMS built with **Laravel 13**, **Livewire 4**, **Flux UI v2**, **Tailwind CSS 4**, and **Spatie Laravel Permission**. Manages companies, departments, designations, employees, contracts, salaries, and job applicants with role-based access control.

🌐 **Live Demo:** https://hrms-system-prod.vercel.app

> Deployed via Git-connected push to `main` (Vercel builds server-side). Vercel Authentication is disabled on this project so the public demo is reachable.

## Tech Stack

| Layer | Stack |
|---|---|
| Backend | Laravel 13 (PHP 8.5+) |
| Frontend | Livewire 4 + Flux UI v2 + Tailwind CSS 4 |
| Auth | Laravel Fortify (Passkeys + 2FA) |
| Permissions | Spatie Laravel Permission |
| Database | SQLite (default) / MySQL / PostgreSQL |
| Testing | Pest PHP v4 |
| Build | Vite 8 |
| Deploy | Vercel (PHP runtime `vercel-php@0.9.0`) |

## Features

### Multi-Company Support
- Companies with parent-child hierarchy (HQ + subsidiaries)
- Super admins see all companies; company admins see their own tree
- Company-level data filtering across all modules

### Role-Based Access Control (6 roles, 32+ permissions)

| Role | Access |
|---|---|
| **Super Admin** | Full access across all companies. Only one at a time. |
| **Company Admin** | Full access within own company tree. One per company. |
| **HR Manager** | Employees, contracts, applicants, payroll |
| **HR Executive** | View-only + limited create/edit |
| **Department Head** | Department employees, leave approval |
| **Employee** | Own profile and leave only |

### Modules

- **Dashboard** — Role-specific stats with 5-minute cache (employee count, payroll, expiring contracts, recent hires)
- **Companies** — CRUD with hierarchy, user/department/contract counts
- **Departments** — CRUD with parent-child nesting, head assignment
- **Designations** — Job titles with level hierarchy
- **Employees (Users)** — Full CRUD with role assignment
- **Contracts** — Full CRUD with type, expiry tracking, salary
- **Salaries** — Full CRUD with base/allowances/deductions/net calculation
- **Job Applicants** — Full recruitment pipeline (Pending → Reviewing → Shortlisted → Hired/Rejected)
- **Audit Logging** — Company & Department CRUD operations logged via Observers
- **API Resources** — CompanyResource and DepartmentResource for API responses

## Installation

```bash
git clone git@github.com:MohammadMuntasirKabir/hrms-system.git
cd HRMS/HRM-System   # (or: cd hrms-system if cloned as the repo root)
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
# Or for development:
composer run dev
```

## Deploying to Vercel

This project is deployed to Vercel using the PHP runtime (`vercel-php@0.9.0`). The
`vercel.json` at the repo root routes all requests through a serverless PHP function
(`api/index.php`) and serves the built `public/` directory. The GitHub repo
(`MohammadMuntasirKabir/hrms-system`) is connected to the Vercel project, so
**pushing to `main` triggers a server-side production build automatically** — no local
CLI upload needed.

```bash
# One-time: link the project locally (optional; Git connection handles deploys)
vercel link

# Deploy: just push to main
git push origin main        # Vercel builds & promotes automatically

# Or force a manual CLI build (slower — re-uploads the project)
vercel --prod
```

> Note: `.vercelignore` excludes `vendor/`, `node_modules/`, and `.git/` from
> uploads — Vercel runs `composer install && npm run build` itself during the build
> step (see `buildCommand` in `vercel.json`).

Environment variables required on Vercel: `APP_KEY`, `DB_CONNECTION`, `DB_DATABASE`
(or your MySQL/PostgreSQL credentials), and any mail/cache config used in production.

## Default Login

| Field | Value |
|---|---|
| Email | `admin@hrms.local` |
| Password | `password` |

## Running Tests

```bash
php artisan test                    # All tests
php artisan test --compact          # Compact output
php artisan test --filter="CompanyTest"  # Specific test
```

**209+ tests** covering all CRUD operations, authorization, role-based access, applicant workflow, audit logging, and dashboard per role.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ApplicantController.php
│   │   ├── CompanyController.php
│   │   ├── ContractController.php
│   │   ├── DashboardController.php    # With cached stats
│   │   ├── DepartmentController.php
│   │   ├── DesignationController.php
│   │   ├── SalaryController.php
│   │   └── UserManagementController.php
│   ├── Resources/
│   │   ├── CompanyResource.php
│   │   └── DepartmentResource.php
│   └── Middleware/
│       └── StoreCompanyFilter.php
├── Models/
│   ├── Company.php
│   ├── Contract.php
│   ├── Department.php
│   ├── Designation.php
│   ├── JobApplicant.php
│   ├── Salary.php
│   └── User.php
├── Observers/
│   ├── CompanyObserver.php
│   └── DepartmentObserver.php
├── Providers/
│   └── AppServiceProvider.php     # Observers registered here
└── ...

database/
├── factories/
│   ├── CompanyFactory.php
│   ├── DepartmentFactory.php      # Includes company_id
│   └── UserFactory.php
├── seeders/
│   ├── DatabaseSeeder.php
│   └── RolePermissionSeeder.php   # 32 permissions, 6 roles
└── migrations/

tests/Feature/
├── Auth/
├── Settings/
├── CompanyTest.php                # 7 tests
├── DepartmentTest.php             # 5 tests
└── HRFeatureTest.php              # 191 comprehensive tests

resources/views/
├── applicants/
├── companies/
├── contracts/
├── dashboard/                     # Role-specific dashboards
├── departments/
├── designations/
├── salaries/
└── users/
```

## License

This project is open-source software.
