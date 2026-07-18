<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Support\Facades\Log;

class DepartmentObserver
{
    public function created(Department $department): void
    {
        Log::info('Department created', [
            'department_id' => $department->id,
            'name' => $department->name,
            'company_id' => $department->company_id,
            'user_id' => auth()->id(),
        ]);
        AuditLog::record('created', "Department \"{$department->name}\" was created.", $department);
    }

    public function updated(Department $department): void
    {
        Log::info('Department updated', [
            'department_id' => $department->id,
            'changes' => $department->getChanges(),
            'user_id' => auth()->id(),
        ]);
        AuditLog::record('updated', "Department \"{$department->name}\" was updated.", $department, $department->getOriginal(), $department->getChanges());
    }

    public function deleting(Department $department): void
    {
        Log::info('Department deleted', [
            'department_id' => $department->id,
            'name' => $department->name,
            'user_id' => auth()->id(),
        ]);
        AuditLog::record('deleted', "Department \"{$department->name}\" was deleted.", $department);
    }
}
