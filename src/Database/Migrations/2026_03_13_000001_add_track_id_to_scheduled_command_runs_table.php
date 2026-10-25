<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $runsTableName = config('command-scheduler.tables.scheduled_command_runs', 'cs_scheduled_command_runs');

        Schema::table($runsTableName, function (Blueprint $table): void {
            $table->string('track_id', 36)->unique()->nullable()->after('id')->index()->comment('UUID for tracking execution instance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $runsTableName = config('command-scheduler.tables.scheduled_command_runs', 'cs_scheduled_command_runs');

        Schema::table($runsTableName, function (Blueprint $table): void {
            $table->dropUnique('cs_scheduled_command_runs_track_id_unique');
            $table->dropIndex('cs_scheduled_command_runs_track_id_index');
            $table->dropColumn('track_id');
        });
    }
};
