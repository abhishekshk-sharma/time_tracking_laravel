<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing 'weekend' values to 'WFH'
        DB::table('schedule_exceptions')
            ->where('type', 'weekend')
            ->update(['type' => 'WFH']);
            
        // Alter the enum to replace 'weekend' with 'WFH'
        DB::statement("ALTER TABLE schedule_exceptions MODIFY COLUMN type ENUM('holiday', 'working_day', 'WFH')");
    }

    public function down(): void
    {
        // Update existing 'WFH' values back to 'weekend'
        DB::table('schedule_exceptions')
            ->where('type', 'WFH')
            ->update(['type' => 'weekend']);
            
        // Alter the enum back to original
        DB::statement("ALTER TABLE schedule_exceptions MODIFY COLUMN type ENUM('holiday', 'working_day', 'weekend')");
    }
};