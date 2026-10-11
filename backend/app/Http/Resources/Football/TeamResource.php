<?php

namespace App\Http\Resources\Football;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'short_name' => $this->resource->shortName,
            'crest' => $this->resource->crest,
        ];
    }
}
