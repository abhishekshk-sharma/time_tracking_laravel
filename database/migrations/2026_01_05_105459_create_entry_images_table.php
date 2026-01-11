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
        Schema::create('entry_images', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id', 20);
            $table->string('entry_type');
            $table->timestamp('entry_time');
            $table->string('imageFile');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('emp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_images');
    }
};
