<?php

namespace App\Models\Football;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Team extends Model
{
    protected $table = 'football_teams';

    protected $fillable = [
        'external_id',
        'name',
        'short_name',
        'crest',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class, 'competition_team');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'team_player');
    }
}
