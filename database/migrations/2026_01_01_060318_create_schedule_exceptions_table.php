<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->enum('type', ['working_day', 'holiday', 'weekend']);
            $table->unsignedBigInteger('region_id');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
