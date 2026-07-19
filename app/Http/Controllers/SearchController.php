<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\Department;
use App\Models\JobApplicant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Global, permission-aware search across people, departments,
     * companies, contracts and applicants.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $term = trim((string) $request->input('q', ''));

        $results = [
            'users' => collect(),
            'departments' => collect(),
            'companies' => collect(),
            'contracts' => collect(),
            'applicants' => collect(),
        ];

        if ($term !== '' && strlen($term) >= 2) {
            $companyIds = $this->allowedCompanyIds($user);
            $escaped = preg_replace('/[^a-zA-Z0-9@.\s_-]/', '', $term);
            $likeTerm = '%'.strtolower($escaped).'%';

            if ($user->can('users.view')) {
                $results['users'] = User::query()
                    ->whereIn('company_id', $companyIds)
                    ->where(function ($q) use ($likeTerm) {
                        $q->whereRaw('LOWER(name) LIKE ?', [$likeTerm])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$likeTerm])
                            ->orWhereRaw('LOWER(employee_id) LIKE ?', [$likeTerm]);
                    })
                    ->with(['company', 'department'])
                    ->orderBy('name')
                    ->limit(15)
                    ->get();
            }

            if ($user->can('departments.view')) {
                $results['departments'] = Department::query()
                    ->whereIn('company_id', $companyIds)
                    ->where(function ($q) use ($likeTerm) {
                        $q->whereRaw('LOWER(name) LIKE ?', [$likeTerm])
                            ->orWhereRaw('LOWER(code) LIKE ?', [$likeTerm]);
                    })
                    ->with('company')
                    ->orderBy('name')
                    ->limit(15)
                    ->get();
            }

            if ($user->can('companies.view')) {
                $results['companies'] = Company::query()
                    ->whereIn('id', $companyIds)
                    ->where(function ($q) use ($likeTerm) {
                        $q->whereRaw('LOWER(name) LIKE ?', [$likeTerm])
                            ->orWhereRaw('LOWER(domain) LIKE ?', [$likeTerm]);
                    })
                    ->orderBy('name')
                    ->limit(15)
                    ->get();
            }

            if ($user->can('contracts.view')) {
                $results['contracts'] = Contract::query()
                    ->whereIn('company_id', $companyIds)
                    ->whereRaw('LOWER(position) LIKE ?', [$likeTerm])
                    ->with(['user', 'company'])
                    ->orderBy('position')
                    ->limit(15)
                    ->get();
            }

            if ($user->can('applicants.view')) {
                $results['applicants'] = JobApplicant::query()
                    ->whereIn('company_id', $companyIds)
                    ->where(function ($q) use ($likeTerm) {
                        $q->whereRaw('LOWER(first_name) LIKE ?', [$likeTerm])
                            ->orWhereRaw('LOWER(last_name) LIKE ?', [$likeTerm])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$likeTerm]);
                    })
                    ->with('company')
                    ->orderBy('first_name')
                    ->limit(15)
                    ->get();
            }
        }

        return view('search.index', [
            'term' => $term,
            'results' => $results,
            'total' => $results['users']->count()
                + $results['departments']->count()
                + $results['companies']->count()
                + $results['contracts']->count()
                + $results['applicants']->count(),
        ]);
    }

    /**
     * Company IDs the current user is allowed to search within.
     */
    private function allowedCompanyIds(User $user): array
    {
        return $user->isSuperAdmin()
            ? Company::pluck('id')->toArray()
            : $user->getAllowedCompanyIds();
    }
}
