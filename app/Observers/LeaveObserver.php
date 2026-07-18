<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Leave;
use Illuminate\Support\Facades\Log;

class LeaveObserver
{
    public function created(Leave $leave): void
    {
        Log::info('Leave requested', [
            'leave_id' => $leave->id,
            'user_id' => $leave->user_id,
            'company_id' => $leave->company_id,
            'type' => $leave->type,
            'total_days' => $leave->total_days,
        ]);
        AuditLog::record('created', "Leave request ({$leave->type}) submitted for {$leave->user->name}.", $leave);
    }

    public function updated(Leave $leave): void
    {
        if ($leave->wasChanged('status')) {
            Log::info('Leave status changed', [
                'leave_id' => $leave->id,
                'user_id' => $leave->user_id,
                'company_id' => $leave->company_id,
                'status' => $leave->status,
                'approved_by' => $leave->approved_by,
            ]);
            AuditLog::record('updated', "Leave request for {$leave->user->name} was {$leave->status}.", $leave, ['status' => $leave->getOriginal('status')], ['status' => $leave->status]);
        }
    }

    public function deleting(Leave $leave): void
    {
        Log::info('Leave deleted', [
            'leave_id' => $leave->id,
            'user_id' => $leave->user_id,
            'company_id' => $leave->company_id,
        ]);
        AuditLog::record('deleted', "Leave request for {$leave->user->name} was deleted.", $leave);
    }
}
