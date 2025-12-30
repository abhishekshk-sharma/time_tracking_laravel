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
        // Create applications table
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->string('req_type', 30);
            $table->string('subject');
            $table->string('description');
            $table->string('half_day', 50)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->string('file')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('action_by', 20)->nullable();
            $table->timestamps();
            
            $table->index('employee_id');
            $table->fullText('half_day');
        });

        // Create departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create employees table
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id', 20)->unique();
            $table->string('full_name');
            $table->string('username', 100)->unique();
            $table->string('email', 100)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('department', 50)->nullable();
            $table->string('position', 50)->nullable();
            $table->date('hire_date')->nullable();
            $table->date('end_date')->nullable();
            $table->dateTime('dob')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->string('password_hash');
            $table->enum('role', ['employee', 'admin'])->default('employee');
            $table->timestamps();
            
            $table->index('emp_id');
        });

        // Create leavecount table
        Schema::create('leavecount', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->integer('casual_leave');
            $table->integer('sick_leave');
            $table->timestamps();
            
            $table->index('employee_id');
        });

        // Create notification table
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->integer('App_id');
            $table->string('created_by', 20);
            $table->string('notify_to', 20);
            $table->enum('status', ['pending', 'checked'])->default('pending');
            $table->timestamps();
            
            $table->index('App_id');
            $table->index('created_by');
            $table->index('notify_to');
        });

        // Create system_settings table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 50)->unique();
            $table->text('setting_value');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create time_entries table
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->set('entry_type', ['punch_in', 'punch_out', 'lunch_start', 'lunch_end', 'half_day', 'holiday', 'sick_leave', 'casual_leave', 'regularization'])->nullable();
            $table->dateTime('entry_time')->default(now());
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'entry_time'], 'idx_employee_date');
        });

        // Create wfh table
        Schema::create('wfh', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->string('title');
            $table->string('description');
            $table->dateTime('date')->nullable();
            $table->timestamps();
            
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfh');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('notification');
        Schema::dropIfExists('leavecount');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('applications');
    }
};