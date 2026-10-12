<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('football_competitions', function (Blueprint $table): void {
            $table->string('import_status')->default('available')->after('last_synced_at');
            $table->text('import_error')->nullable()->after('import_status');
            $table->unsignedInteger('pending_player_imports')->default(0)->after('import_error');
            $table->timestamp('import_started_at')->nullable()->after('pending_player_imports');
            $table->timestamp('import_completed_at')->nullable()->after('import_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('football_competitions', function (Blueprint $table): void {
            $table->dropColumn([
                'import_status',
                'import_error',
                'pending_player_imports',
                'import_started_at',
                'import_completed_at',
            ]);
        });
    }
};
