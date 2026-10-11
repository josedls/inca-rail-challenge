<?php

namespace App\Http\Resources\Roles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class RoleSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'guard_name' => $this->resource->guardName,
            'permissions' => $this->resource->permissions,
        ];
    }
}
