<?php

namespace App\Http\Resources\Football;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CompetitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'code' => $this->resource->code,
            'emblem' => $this->resource->emblem,
            'import_status' => $this->resource->importStatus,
            'import_error' => $this->resource->importError,
            'last_synced_at' => $this->resource->lastSyncedAt,
        ];
    }
}
