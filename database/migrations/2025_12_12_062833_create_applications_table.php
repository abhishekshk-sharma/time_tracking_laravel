<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->string('req_type', 30);
            $table->string('subject');
            $table->string('description');
            $table->string('half_day', 50)->default('');
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->string('file')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('action_by', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('employee_id')->references('emp_id')->on('employees')->onDelete('cascade');
            $table->fullText('half_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};