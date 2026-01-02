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
        Schema::dropIfExists('schedule_exceptions');
        
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('exception_date');
            $table->enum('type', ['holiday', 'working_day']);
            $table->string('description')->nullable();
            $table->string('admin_id')->nullable();
            $table->unsignedBigInteger('superadmin_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
