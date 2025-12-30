<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wfh', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->string('title');
            $table->text('description');
            $table->date('date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_remarks')->nullable();
            $table->string('action_by', 20)->nullable();
            $table->timestamp('action_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('employee_id')->references('emp_id')->on('employees')->onDelete('cascade');
            $table->foreign('action_by')->references('emp_id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wfh');
    }
};