<?php

namespace App\Models;

use Database\Factories\SalaryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    /** @use HasFactory<SalaryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'designation_id',
        'contract_id',
        'base_salary',
        'allowances',
        'deductions',
        'net_salary',
        'currency',
        'pay_frequency',
        'effective_from',
        'effective_until',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'allowances' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'effective_from' => 'date',
            'effective_until' => 'date',
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

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculateNetSalary(): float
    {
        return (float) $this->base_salary + (float) $this->allowances - (float) $this->deductions;
    }

    public function getAnnualSalary(): float
    {
        $multiplier = match ($this->pay_frequency) {
            'weekly' => 52,
            'bi_weekly' => 26,
            default => 12,
        };

        return $this->net_salary * $multiplier;
    }
}
