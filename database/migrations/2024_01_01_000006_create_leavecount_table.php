<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leavecount', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20);
            $table->integer('casual_leave', false, true)->length(2);
            $table->integer('sick_leave', false, true)->length(2);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('employee_id')->references('emp_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leavecount');
    }
};