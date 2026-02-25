<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('upload_id')->constrained()->onDelete('cascade');

            $table->string('vertical')->nullable();
            $table->string('team')->nullable();
            $table->string('project')->nullable();
            $table->string('billing_type')->nullable();
            $table->string('external_task_id')->nullable(); // Task ID from Excel
            $table->string('task_name')->nullable();        // Task
            $table->string('module')->nullable();
            $table->string('platform')->nullable();

            $table->date('estimate_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('work_date')->nullable();          // Task Work Date

            $table->integer('estimated_minutes')->default(0);
            $table->integer('actual_minutes')->default(0);
            $table->integer('variance_minutes')->default(0);
            $table->decimal('efficiency_percent', 8, 2)->default(0);
            $table->enum('time_category', ['overdue', 'ontime', 'underdue'])->nullable();

            $table->string('timer_status')->nullable();
            $table->string('task_status')->nullable();
            $table->string('reason')->nullable();
            $table->date('report_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
