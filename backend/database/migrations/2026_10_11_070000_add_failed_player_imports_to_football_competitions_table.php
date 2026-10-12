<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('football_competitions', function (Blueprint $table): void {
            $table->unsignedInteger('failed_player_imports')
                ->default(0)
                ->after('pending_player_imports');
        });
    }

    public function down(): void
    {
        Schema::table('football_competitions', function (Blueprint $table): void {
            $table->dropColumn('failed_player_imports');
        });
    }
};
