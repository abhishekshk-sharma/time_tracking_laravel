<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->integer('App_id', false, true)->length(20);
            $table->string('created_by', 20);
            $table->string('notify_to', 20);
            $table->enum('status', ['pending', 'checked'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('App_id')->references('id')->on('applications')->onDelete('cascade');
            $table->foreign('created_by')->references('emp_id')->on('employees')->onDelete('cascade');
            $table->foreign('notify_to')->references('emp_id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};