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
        Schema::table('entry_images', function (Blueprint $table) {
            $table->unsignedInteger('entry_id')->nullable()->after('id');
        });
        
        // Add foreign key separately
        Schema::table('entry_images', function (Blueprint $table) {
            $table->foreign('entry_id')->references('id')->on('time_entries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entry_images', function (Blueprint $table) {
            $table->dropForeign(['entry_id']);
            $table->dropColumn('entry_id');
        });
    }
};
