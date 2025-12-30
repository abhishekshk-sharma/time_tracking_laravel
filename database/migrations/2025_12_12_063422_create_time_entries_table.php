<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->set('entry_type', ['punch_in', 'punch_out', 'lunch_start', 'lunch_end', 'half_day', 'holiday', 'sick_leave', 'casual_leave', 'regularization'])->nullable();
            $table->datetime('entry_time')->useCurrent();
            $table->text('notes')->nullable();
            
            $table->foreign('employee_id')->references('emp_id')->on('employees')->onDelete('cascade');
            $table->index(['employee_id'], 'idx_employee_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};