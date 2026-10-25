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
        $taskTableName = config('command-scheduler.tables.scheduled_tasks', 'cs_scheduled_tasks');
        $runsTableName = config('command-scheduler.tables.scheduled_command_runs', 'cs_scheduled_command_runs');

        Schema::create($runsTableName, function (Blueprint $table) use ($taskTableName): void {
            $table->id();
            $table->foreignId('scheduled_task_id')
                ->constrained($taskTableName)
                ->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('duration')->default(0)->comment('Duration in milliseconds');
            $table->integer('exit_code')->default(0);
            $table->text('output')->nullable();
            $table->unsignedBigInteger('memory_usage')->default(0)->comment('Memory usage in bytes');
            $table->string('triggered_by', 50)->default('schedule')->comment('schedule, manual');
            $table->timestamps();

            $table->index(['scheduled_task_id', 'started_at']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $runsTableName = config('command-scheduler.tables.scheduled_command_runs', 'cs_scheduled_command_runs');

        Schema::dropIfExists($runsTableName);
    }
};
