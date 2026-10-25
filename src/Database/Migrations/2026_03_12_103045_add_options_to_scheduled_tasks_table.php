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
            $table->json('options')->after('expression')->nullable()->comment('Command options and arguments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('command-scheduler.tables.scheduled_tasks', 'cs_scheduled_tasks');

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('options');
        });
    }
};
