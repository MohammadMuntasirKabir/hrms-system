<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()->isSuperAdmin()) {
                abort(403, 'Only super admins can manage companies.');
            }
            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $companies = Company::withCount(['users', 'departments', 'designations', 'contracts'])
            ->with('childCompanies')
            ->orderBy('name')
            ->paginate(20);

        return view('companies.index', ['companies' => $companies]);
    }

    public function show(Request $request, Company $company): View
    {
        $user = $request->user();

        if (!$user->canViewCompany($company)) {
            abort(403);
        }

        $company->load(['users.roles', 'users.designation', 'users.department', 'childCompanies'])
            ->loadCount(['departments', 'designations', 'contracts']);

        $departments = $company->departments()->withCount(['users', 'designations', 'contracts'])->orderBy('name')->get();
        $designations = $company->designations()->withCount('users')->orderBy('level')->take(10)->get();
        $contracts = $company->contracts()->with(['user.designation'])->latest('start_date')->take(10)->get();

        return view('companies.show', [
            'company' => $company,
            'departments' => $departments,
            'designations' => $designations,
            'contracts' => $contracts,
        ]);
    }
}
