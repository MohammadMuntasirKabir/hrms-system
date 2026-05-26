<?php

namespace App\Models;

use Database\Factories\JobApplicantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicant extends Model
{
    /** @use HasFactory<JobApplicantFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'department_id',
        'designation_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'cover_letter',
        'resume_path',
        'source',
        'expected_salary',
        'currency',
        'available_from',
        'status',
        'notes',
        'reviewed_by',
        'reviewed_at',
        'hired_as_user_id',
    ];

    protected function casts(): array
    {
        return [
            'expected_salary' => 'decimal:2',
            'available_from' => 'date',
            'reviewed_at' => 'datetime',
        ];
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function hiredAsUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hired_as_user_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function isHired(): bool
    {
        return $this->status === 'hired';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReviewing(): bool
    {
        return $this->status === 'reviewing';
    }

    public function isShortlisted(): bool
    {
        return $this->status === 'shortlisted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
