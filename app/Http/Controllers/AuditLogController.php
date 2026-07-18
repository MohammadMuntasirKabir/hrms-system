<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    private function getCompanyFilter(Request $request): ?int
    {
        $companyId = $request->input('company_id') ?? session('filter_company_id');

        return $companyId ? (int) $companyId : null;
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = $this->getCompanyFilter($request);

        $query = AuditLog::query()->with(['user', 'company'])->latest();

        if (! $user->isSuperAdmin()) {
            $query->whereIn('company_id', $user->getAllowedCompanyIds());
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->paginate(25);

        $companies = $user->isSuperAdmin()
            ? Company::where('is_active', true)->orderBy('name')->get()
            : [];

        return view('audit-logs.index', [
            'logs' => $logs,
            'currentUser' => $user,
            'companies' => $companies,
            'filterCompany' => $companyId,
            'actionFilter' => $request->input('action'),
        ]);
    }
}
