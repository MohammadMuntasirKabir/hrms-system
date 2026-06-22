<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'domain' => $this->domain,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
            'is_hq' => $this->isHq(),
            'parent_company_id' => $this->parent_company_id,
            'parent_company' => $this->whenLoaded('parentCompany', fn () => [
                'id' => $this->parentCompany->id,
                'name' => $this->parentCompany->name,
            ]),
            'child_companies' => $this->whenLoaded('childCompanies', fn () =>
                $this->childCompanies->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])
            ),
            'departments_count' => $this->whenCounted('departments'),
            'users_count' => $this->whenCounted('users'),
            'designations_count' => $this->whenCounted('designations'),
            'contracts_count' => $this->whenCounted('contracts'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
