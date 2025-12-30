<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->datetime('dob')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->string('password_hash');
            $table->enum('role', ['employee', 'admin'])->default('employee');
            $table->timestamps();
            
            $table->index('emp_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};