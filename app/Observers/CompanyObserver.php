<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Support\Facades\Log;

class CompanyObserver
{
    public function created(Company $company): void
    {
        Log::info('Company created', [
            'company_id' => $company->id,
            'name' => $company->name,
            'user_id' => auth()->id(),
        ]);
        AuditLog::record('created', "Company \"{$company->name}\" was created.", $company);
    }

    public function updated(Company $company): void
    {
        Log::info('Company updated', [
            'company_id' => $company->id,
            'changes' => $company->getChanges(),
            'user_id' => auth()->id(),
        ]);
        AuditLog::record('updated', "Company \"{$company->name}\" was updated.", $company, $company->getOriginal(), $company->getChanges());
    }

    public function deleting(Company $company): void
    {
        Log::info('Company deleted', [
            'company_id' => $company->id,
            'name' => $company->name,
            'user_id' => auth()->id(),
        ]);
        AuditLog::record('deleted', "Company \"{$company->name}\" was deleted.", $company);
    }
}
