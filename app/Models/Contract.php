<?php

namespace App\Models;

use Database\Factories\ContractFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    /** @use HasFactory<ContractFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'contract_type',
        'position',
        'start_date',
        'end_date',
        'salary',
        'currency',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        if ($this->end_date === null) {
            return false;
        }
        return $this->end_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        if ($this->end_date === null) {
            return false;
        }

        return $this->end_date->isFuture() && now()->diffInDays($this->end_date) <= 30;
    }
}
