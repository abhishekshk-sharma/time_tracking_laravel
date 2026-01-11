<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('hra', 10, 2)->default(0);
            $table->decimal('pf', 10, 2)->default(0);
            $table->decimal('pt', 10, 2)->default(0);
            // $table->decimal('ta', 10, 2)->default(0);
            $table->decimal('conveyance_allowance', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2);
            $table->date('effective_from');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['emp_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salaries');
    }
};