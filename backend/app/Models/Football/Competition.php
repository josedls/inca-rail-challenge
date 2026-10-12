<?php

namespace App\Models\Football;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Competition extends Model
{
    protected $table = 'football_competitions';

    protected $fillable = [
        'external_id',
        'name',
        'code',
        'emblem',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'competition_team');
    }
}
