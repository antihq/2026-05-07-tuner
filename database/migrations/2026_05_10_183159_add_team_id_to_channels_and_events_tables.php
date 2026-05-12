<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('project_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('channel_id')->constrained()->cascadeOnDelete();
        });

        DB::statement('UPDATE channels SET team_id = (SELECT team_id FROM projects WHERE projects.id = channels.project_id)');
        DB::statement('UPDATE events SET team_id = (SELECT team_id FROM channels WHERE channels.id = events.channel_id)');

        Schema::table('channels', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable(false)->change();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });
    }
};
