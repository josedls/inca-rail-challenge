<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_team', function (Blueprint $table): void {
            $table->foreignId('competition_id')
                ->constrained('football_competitions')
                ->cascadeOnDelete();
            $table->foreignId('team_id')
                ->constrained('football_teams')
                ->cascadeOnDelete();
            $table->primary(['competition_id', 'team_id']);
        });

        Schema::create('team_player', function (Blueprint $table): void {
            $table->foreignId('team_id')
                ->constrained('football_teams')
                ->cascadeOnDelete();
            $table->foreignId('player_id')
                ->constrained('football_players')
                ->cascadeOnDelete();
            $table->primary(['team_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_player');
        Schema::dropIfExists('competition_team');
    }
};
