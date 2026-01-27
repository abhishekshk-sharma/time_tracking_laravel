<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lunch_alarms', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->datetime('lunch_start_time');
            $table->datetime('alarm_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['employee_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lunch_alarms');
    }
};