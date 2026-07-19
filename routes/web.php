<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Company;

// TEMP DIAGNOSTIC ROUTE — renders the dashboard as the first super admin
// against the live DB so we can read the real exception. Also supports
// running pending migrations (?migrate=1). Remove after fix.
Route::get('/__diag_dash', function () {
    if (request('k') !== 'hrms_diag_2026') {
        abort(404);
    }

    if (request('migrate') === '1') {
        try {
            \Artisan::call('migrate', ['--force' => true]);
            $migrateOut = \Artisan::output();
        } catch (\Throwable $e) {
            $migrateOut = 'MIGRATE ERROR: '.$e->getMessage();
        }
    } else {
        $migrateOut = '(skip — pass ?migrate=1)';
    }

    $u = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first()
        ?? User::whereHas('roles', fn ($q) => $q->whereIn('name', ['company_admin', 'hr_manager']))->first()
        ?? User::first();
    if (! $u) {
        return 'no user found. Migrate output: '.$migrateOut;
    }
    Auth::login($u);
    try {
        $resp = app(\App\Http\Controllers\DashboardController::class)->index(request());
        return 'OK status '.$resp->getStatusCode()."\n\nMigrate: ".$migrateOut;
    } catch (\Throwable $e) {
        return response('<pre>Migrate: '.$migrateOut."\n\n".get_class($e).": ".$e->getMessage()."\n\n".$e->getTraceAsString().'</pre>');
    }
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search (any authenticated user)
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Companies
    Route::middleware(['permission:companies.view'])->group(function () {
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/create', [CompanyController::class, 'create'])->middleware('permission:companies.create')->name('companies.create');
        Route::post('/companies', [CompanyController::class, 'store'])->middleware('permission:companies.create')->name('companies.store');
        Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->middleware('permission:companies.edit')->name('companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->middleware('permission:companies.edit')->name('companies.update');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->middleware('permission:companies.delete')->name('companies.destroy');
    });

    // Departments
    Route::middleware(['permission:departments.view'])->group(function () {
        Route::resource('departments', DepartmentController::class)->except(['destroy']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:departments.delete')->name('departments.destroy');
    });

    // Designations
    Route::middleware(['permission:designations.view'])->group(function () {
        Route::resource('designations', DesignationController::class)->except(['destroy']);
        Route::delete('/designations/{designation}', [DesignationController::class, 'destroy'])->middleware('permission:designations.delete')->name('designations.destroy');
    });

    // Contracts
    Route::middleware(['permission:contracts.view'])->group(function () {
        Route::resource('contracts', ContractController::class)->except(['destroy']);
        Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])->middleware('permission:contracts.delete')->name('contracts.destroy');
    });

    // Salaries
    Route::middleware(['permission:payroll.view'])->group(function () {
        Route::resource('salaries', SalaryController::class)->except(['destroy']);
        Route::delete('/salaries/{salary}', [SalaryController::class, 'destroy'])->middleware('permission:payroll.manage')->name('salaries.destroy');
    });

    // Leave Management
    Route::middleware(['permission:leave.view'])->group(function () {
        Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
        Route::get('/leaves/create', [LeaveController::class, 'create'])->middleware('permission:leave.manage')->name('leaves.create');
        Route::post('/leaves', [LeaveController::class, 'store'])->middleware('permission:leave.manage')->name('leaves.store');
        Route::get('/leaves/{leave}', [LeaveController::class, 'show'])->name('leaves.show');
        Route::get('/leaves/{leave}/edit', [LeaveController::class, 'edit'])->middleware('permission:leave.manage')->name('leaves.edit');
        Route::put('/leaves/{leave}', [LeaveController::class, 'update'])->middleware('permission:leave.manage')->name('leaves.update');
        Route::delete('/leaves/{leave}', [LeaveController::class, 'destroy'])->middleware('permission:leave.manage')->name('leaves.destroy');
        Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->middleware('permission:leave.approve')->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->middleware('permission:leave.approve')->name('leaves.reject');
        Route::post('/leaves/{leave}/cancel', [LeaveController::class, 'cancel'])->middleware('permission:leave.manage')->name('leaves.cancel');
    });

    // Reports
    Route::middleware(['permission:reports.view'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->middleware('permission:reports.export')->name('reports.export');
        Route::get('/reports/export-expiring', [ReportController::class, 'exportExpiringContracts'])->middleware('permission:reports.export')->name('reports.export-expiring');
    });

    // Audit Log (super admin / company admin)
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Job Applicants
    Route::middleware(['permission:applicants.view'])->group(function () {
        Route::get('/applicants', [ApplicantController::class, 'index'])->name('applicants.index');
        Route::get('/applicants/create', [ApplicantController::class, 'create'])->middleware('permission:applicants.create')->name('applicants.create');
        Route::post('/applicants', [ApplicantController::class, 'store'])->middleware('permission:applicants.create')->name('applicants.store');
        Route::get('/applicants/{applicant}', [ApplicantController::class, 'show'])->name('applicants.show');
        Route::get('/applicants/{applicant}/edit', [ApplicantController::class, 'edit'])->middleware('permission:applicants.create')->name('applicants.edit');
        Route::put('/applicants/{applicant}', [ApplicantController::class, 'update'])->middleware('permission:applicants.create')->name('applicants.update');
        Route::delete('/applicants/{applicant}', [ApplicantController::class, 'destroy'])->middleware('permission:applicants.create')->name('applicants.destroy');
        Route::post('/applicants/{applicant}/hire', [ApplicantController::class, 'hire'])->middleware('permission:applicants.hire')->name('applicants.hire');
        Route::post('/applicants/{applicant}/reject', [ApplicantController::class, 'reject'])->middleware('permission:applicants.reject')->name('applicants.reject');
        Route::post('/applicants/{applicant}/shortlist', [ApplicantController::class, 'shortlist'])->middleware('permission:applicants.hire')->name('applicants.shortlist');
        Route::post('/applicants/{applicant}/review', [ApplicantController::class, 'review'])->middleware('permission:applicants.hire')->name('applicants.review');
        Route::post('/applicants/{applicant}/undo-hire', [ApplicantController::class, 'undoHire'])->middleware('permission:applicants.hire')->name('applicants.undo-hire');
        Route::post('/applicants/{applicant}/undo-reject', [ApplicantController::class, 'undoReject'])->middleware('permission:applicants.reject')->name('applicants.undo-reject');
        Route::delete('/applicants/{applicant}/force-delete', [ApplicantController::class, 'forceDelete'])->middleware('permission:applicants.reject')->name('applicants.force-delete');
    });

    // Employees
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->middleware('permission:users.create')->name('users.store');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->middleware('permission:users.edit')->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
    });

    // Admin Management (super admin only)
    Route::middleware(['auth', 'verified', 'active'])->group(function () {
        Route::post('/admin/transfer-superadmin', [UserManagementController::class, 'transferSuperAdmin'])->name('admin.transfer-superadmin');
        Route::post('/admin/assign-company-admin', [UserManagementController::class, 'assignCompanyAdmin'])->name('admin.assign-company-admin');
        Route::post('/admin/remove-company-admin', [UserManagementController::class, 'removeCompanyAdmin'])->name('admin.remove-company-admin');
    });
});

require __DIR__.'/settings.php';
