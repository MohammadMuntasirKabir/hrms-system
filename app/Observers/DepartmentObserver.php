<?php

namespace App\Observers;

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
    }

    public function updated(Department $department): void
    {
        Log::info('Department updated', [
            'department_id' => $department->id,
            'changes' => $department->getChanges(),
            'user_id' => auth()->id(),
        ]);
    }

    public function deleted(Department $department): void
    {
        Log::info('Department deleted', [
            'department_id' => $department->id,
            'name' => $department->name,
            'user_id' => auth()->id(),
        ]);
    }
}
