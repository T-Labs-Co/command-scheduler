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

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('name', 255);
            $table->string('signature', 255)->unique()->index();
            $table->string('class_name', 255);
            $table->text('description')->nullable();
            $table->string('expression', 100); // cron expression
            $table->integer('priority')->default(100);
            $table->string('run_window', 50)->default('always');
            $table->boolean('enabled')->default(true)->index();
            $table->string('timezone', 50)->nullable();
            $table->boolean('without_overlapping')->default(false);
            $table->boolean('on_one_server')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->unsignedInteger('run_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedBigInteger('average_duration')->default(0)->comment('Duration in milliseconds');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('command-scheduler.tables.scheduled_tasks', 'cs_scheduled_tasks');

        Schema::dropIfExists($tableName);
    }
};
