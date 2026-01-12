<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            $table->renameColumn('date', 'exception_date');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_exceptions', function (Blueprint $table) {
            $table->renameColumn('exception_date', 'date');
        });
    }
};