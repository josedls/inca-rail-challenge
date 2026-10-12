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
            'import_status' => $this->resource->importStatus?->value,
            'import_error' => $this->resource->importError,
            'last_synced_at' => $this->resource->lastSyncedAt,
            'import_progress' => $this->importProgress(),
        ];
    }

    private function importProgress(): array
    {
        $total = $this->resource->totalTeams;
        $pending = min($this->resource->pendingPlayerImports, $total);
        $failed = min($this->resource->failedPlayerImports, $total - $pending);
        $completed = max(0, $total - $pending - $failed);
        $processed = $completed + $failed;

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'pending' => $pending,
            'percentage' => $total === 0 ? 0 : (int) round(($processed / $total) * 100),
        ];
    }
}
