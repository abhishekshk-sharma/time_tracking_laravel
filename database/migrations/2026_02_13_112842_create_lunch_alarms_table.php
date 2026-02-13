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
        Schema::create('lunch_alarms', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->dateTime('lunch_start_time');
            $table->dateTime('alarm_time');
            $table->boolean('is_active')->default(false);
            $table->text('message')->nullable();
            $table->dateTime('notification_sent_at')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('emp_id')->on('employees')->onDelete('cascade');
            $table->index(['employee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lunch_alarms');
    }
};
