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
        $tableName = config('command-scheduler.tables.scheduled_tasks', 'cs_scheduled_tasks');

        Schema::table($tableName, function (Blueprint $table): void {
            $table->string('output_log', 500)->nullable()->after('metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('command-scheduler.tables.scheduled_tasks', 'cs_scheduled_tasks');

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('output_log');
        });
    }
};
