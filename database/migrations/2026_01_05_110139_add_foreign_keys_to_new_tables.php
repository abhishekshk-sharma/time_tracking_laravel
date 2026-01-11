<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the collation of emp_id columns to match employees table
        DB::statement('ALTER TABLE locations MODIFY emp_id VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
        DB::statement('ALTER TABLE entry_images MODIFY emp_id VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
        
        // Then add foreign key constraints
        Schema::table('locations', function (Blueprint $table) {
            $table->foreign('emp_id')->references('emp_id')->on('employees')->onDelete('cascade');
        });
        
        Schema::table('entry_images', function (Blueprint $table) {
            $table->foreign('emp_id')->references('emp_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['emp_id']);
        });
        
        Schema::table('entry_images', function (Blueprint $table) {
            $table->dropForeign(['emp_id']);
        });
    }
};
