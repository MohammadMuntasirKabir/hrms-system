<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'parent_company_id',
        'country',
        'timezone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'parent_company_id');
    }

    public function childCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'parent_company_id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function activeContracts(): HasMany
    {
        return $this->hasMany(Contract::class)->where('status', 'active');
    }

    public function isHq(): bool
    {
        return is_null($this->parent_company_id);
    }

    public function isSubCompany(): bool
    {
        return !is_null($this->parent_company_id);
    }

    public function getMainCompany(): Company
    {
        if ($this->isHq()) {
            return $this;
        }
        return $this->parentCompany;
    }

    public function getAllSubCompanyIds(): array
    {
        $ids = [$this->id];
        foreach ($this->childCompanies as $child) {
            $ids = array_merge($ids, $child->getAllSubCompanyIds());
        }
        return $ids;
    }
}
