<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'company_id', 'department_id', 'designation_id', 'employee_id', 'job_title', 'is_active'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected string $guard_name = 'web';

    /**
     * Senior roles that can view all company data.
     */
    private const SENIOR_ROLES = ['super_admin', 'company_admin', 'hr_manager'];

    /**
     * Roles that can view salary information.
     */
    private const SALARY_ROLES = ['super_admin', 'company_admin', 'hr_manager'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn (string $word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // === Relations ===

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

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function activeContract(): ?Contract
    {
        return $this->contracts()->where('status', 'active')->latest('start_date')->first();
    }

    public function activeSalary(): ?Salary
    {
        return $this->salaries()->where('status', 'active')->latest('effective_from')->first();
    }

    // === Role checks ===

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('company_admin');
    }

    public function isHrManager(): bool
    {
        return $this->hasRole('hr_manager');
    }

    public function isDepartmentHead(): bool
    {
        return $this->hasRole('department_head');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    /**
     * Check if user is a senior member (can view all company data).
     */
    public function isSeniorMember(): bool
    {
        return $this->hasAnyRole(self::SENIOR_ROLES);
    }

    /**
     * Check if user can view salary information.
     */
    public function canViewSalaries(): bool
    {
        return $this->hasAnyRole(self::SALARY_ROLES);
    }

    /**
     * Check if user can view data for a specific company.
     * Super admin sees all, others only their own company (and sub-companies).
     */
    public function canViewCompany(Company $company): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $userCompanyId = $this->company_id;
        if ($company->id === $userCompanyId) {
            return true;
        }

        // Check if the target company is a sub-company of user's company
        if ($company->parent_company_id === $userCompanyId) {
            return true;
        }

        // Check if user's company is a sub of the target (for viewing parent)
        $mainCompany = $this->company->getMainCompany();
        return $mainCompany->id === $company->id || $mainCompany->id === $company->parent_company_id;
    }

    /**
     * Get the company IDs this user can view.
     */
    public function getAllowedCompanyIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Company::pluck('id')->toArray();
        }

        $company = $this->company;
        if ($company->isHq()) {
            return $company->getAllSubCompanyIds();
        }

        return [$company->id, $company->parent_company_id];
    }
}
