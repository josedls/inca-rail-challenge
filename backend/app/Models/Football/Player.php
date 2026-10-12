<?php

namespace App\Models\Football;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Player extends Model
{
    protected $table = 'football_players';

    protected $fillable = [
        'external_id',
        'name',
        'position',
        'shirt_number',
        'date_of_birth',
        'nationality',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'last_synced_at' => 'datetime',
        ];
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_player');
    }
}
