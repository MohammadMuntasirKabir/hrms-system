# HRMS — Human Resource Management System

A full-featured, multi-company **Human Resource Management System** built with **Laravel 13**, **Livewire 4**, **Flux UI v2**, **Tailwind CSS 4**, and **Spatie Laravel Permission**. Manages companies, departments, designations, employees, contracts, salaries, leave, and job applicants with fine-grained, role-based access control.

🌐 **Live Demo:** https://hrms-system-prod.vercel.app

> Deployed via Git-connected push to `main`. Vercel builds server-side and self-runs database migrations on cold start, so every deploy stays in sync with the schema.

## Tech Stack

| Layer | Stack |
|---|---|
| Backend | Laravel 13 (PHP 8.5+) |
| Frontend | Livewire 4 + Flux UI v2 + Tailwind CSS 4 |
| Auth | Laravel Fortify (Passkeys + 2FA) |
| Permissions | Spatie Laravel Permission |
| Database | **Neon Postgres** (serverless PostgreSQL) — also supports SQLite (local dev) / MySQL |
| Testing | Pest PHP v4 |
| Build | Vite |
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
- **Global Search** — Permission- and company-scoped search across users, departments, companies, contracts, and applicants from one box in the sidebar.
- **Dashboard** — Role-specific, cached stats with actionable panels:
  - *Super Admin / Company Admin / HR Manager*: **Action Required** panel (pending leave requests + contracts expiring in 30 days), recent activity.
  - *Department Head*: **My Team** overview with team members, their pending leaves, and expiring contracts.
  - *Employee*: **My Active Contract**, **My Leave** summary (total / pending / approved), and **Leave Balance** (configurable annual allowance).
- **Companies** — CRUD with hierarchy, user/department/contract counts
- **Departments** — CRUD with parent-child nesting, head assignment
- **Designations** — Job titles with level hierarchy
- **Employees (Users)** — Full CRUD with role assignment
- **Contracts** — Full CRUD with type, expiry tracking, salary
- **Salaries** — Full CRUD with base/allowances/deductions/net calculation
- **Leaves** — Full lifecycle (request → pending → approved/rejected → cancel) with per-user **leave balance** helper
- **Job Applicants** — Full recruitment pipeline (Pending → Reviewing → Shortlisted → Hired/Rejected) with a visual **pipeline** bar
- **Reports** — Workforce analytics: headcount by department, leave summary, **leave utilization**, and CSV exports (employees + **expiring contracts**)
- **Audit Logging** — Company & Department CRUD operations logged via Observers
- **API Resources** — CompanyResource and DepartmentResource for API responses

## Screenshots

| | |
|---|---|
| ![Login](docs/screenshots/login.png) | ![Dashboard](docs/screenshots/dashboard.png) |
| ![Employees](docs/screenshots/employees.png) | ![Leave Management](docs/screenshots/leaves.png) |
| ![Reports](docs/screenshots/reports.png) | ![Applicants Pipeline](docs/screenshots/applicants.png) |

## Installation

### Local development (SQLite)

```bash
git clone git@github.com:MohammadMuntasirKabir/hrms-system.git
cd HRMS/HRM-System
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
# Or for development (with Vite HMR + queue + log tail):
composer run dev
```

### Production with Neon Postgres

1. Create a **Neon** project and copy its connection string.
2. Set the following environment variables (Vercel project settings or `.env`):

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:...            # php artisan key:generate
   DB_CONNECTION=pgsql
   DB_URL=postgresql://USER:PASSWORD@HOST/DBNAME?sslmode=require
   NEON_ENDPOINT_ID=ep-xxxx      # required by some clients without SNI support
   CACHE_STORE=database
   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   ```

3. Migrations run automatically on the first request after deploy (self-healing), so no manual `migrate` step is needed. If you prefer to run them manually: `php artisan migrate --force`.

```bash
composer install
npm install
npm run build
php artisan migrate --seed --force
```

## Deploying to Vercel

This project is deployed to Vercel using the PHP runtime (`vercel-php@0.9.0`). The
`vercel.json` at the repo root routes all requests through a serverless PHP function
(`api/index.php`) and serves the built `public/` directory. The GitHub repo
(`MohammadMuntasirKabir/hrms-system`) is connected to the Vercel project, so
**pushing to `main` triggers a server-side production build automatically** — no local
CLI upload needed.

```bash
# Deploy: just push to main
git push origin main        # Vercel builds & promotes automatically
```

> `.vercelignore` excludes `vendor/`, `node_modules/`, and `.git/` from uploads — Vercel
> runs `composer install && npm run build` itself during the build step. Database
> migrations are applied automatically on cold start via `api/index.php`.

Environment variables required on Vercel: `APP_KEY`, `DB_CONNECTION=pgsql`, `DB_URL`
(Neon connection string), `NEON_ENDPOINT_ID`, `CACHE_STORE`, `SESSION_DRIVER`,
`QUEUE_CONNECTION`, and any mail config used in production.

## Default Login

| Field | Value |
|---|---|
| Email | `admin@hrms.local` |
| Password | `password` |

## Running Tests

```bash
composer test                  # config:clear + pint --test + pest (full suite)
./vendor/bin/pest              # All tests
./vendor/bin/pest --filter="CompanyTest"   # Specific test
```

**312 tests** covering all CRUD operations, authorization, role-based access, the
applicant workflow, audit logging, dashboard per role, and global search.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ApplicantController.php
│   │   ├── AuditLogController.php
│   │   ├── CompanyController.php
│   │   ├── ContractController.php
│   │   ├── DashboardController.php    # Role-specific, cached stats + action panels
│   │   ├── DepartmentController.php
│   │   ├── DesignationController.php
│   │   ├── LeaveController.php
│   │   ├── ReportController.php
│   │   ├── SalaryController.php
│   │   ├── SearchController.php        # Global, scoped search
│   │   └── UserManagementController.php
│   ├── Middleware/
│   │   ├── EnsureUserHasPermission.php
│   │   ├── EnsureUserIsActive.php
│   │   └── StoreCompanyFilter.php
│   └── Resources/
│       ├── CompanyResource.php
│       └── DepartmentResource.php
├── Models/  (Company, Contract, Department, Designation, JobApplicant,
│             Leave, Salary, User, AuditLog)
├── Observers/  (CompanyObserver, DepartmentObserver, LeaveObserver)
└── Providers/AppServiceProvider.php     # Observers registered here
```

## License

This project is open-source software.
