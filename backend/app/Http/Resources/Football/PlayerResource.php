<?php

namespace App\Http\Resources\Football;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'position' => $this->resource->position,
            'date_of_birth' => $this->resource->dateOfBirth,
            'nationality' => $this->resource->nationality,
        ];
    }
}
