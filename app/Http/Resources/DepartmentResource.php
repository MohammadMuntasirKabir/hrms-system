<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'company_id' => $this->company_id,
            'company' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ]),
            'parent_department_id' => $this->parent_department_id,
            'parent_department' => $this->whenLoaded('parentDepartment', fn () => [
                'id' => $this->parentDepartment->id,
                'name' => $this->parentDepartment->name,
            ]),
            'head_user' => $this->whenLoaded('headUser', fn () => [
                'id' => $this->headUser->id,
                'name' => $this->headUser->name,
            ]),
            'users_count' => $this->whenCounted('users'),
            'designations_count' => $this->whenCounted('designations'),
            'contracts_count' => $this->whenCounted('contracts'),
            'child_departments_count' => $this->whenCounted('childDepartments'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
