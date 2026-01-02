<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salary_reports', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->string('emp_name');
            $table->string('designation');
            $table->string('department');
            $table->string('admin_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->date('report_date');
            $table->integer('month');
            $table->integer('year');
            $table->integer('total_working_days');
            $table->integer('present_days');
            $table->integer('absent_days');
            $table->integer('half_days');
            $table->integer('sick_leave');
            $table->integer('casual_leave');
            $table->integer('week_off');
            $table->integer('holidays');
            $table->integer('short_attendance');
            $table->decimal('payable_days', 8, 2);
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('hra', 10, 2);
            $table->decimal('conveyance_allowance', 10, 2);
            $table->decimal('pf', 10, 2);
            $table->decimal('pt', 10, 2);
            $table->decimal('payable_basic_salary', 10, 2);
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('total_deductions', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->boolean('has_negative_salary')->default(false);
            $table->boolean('has_missing_data')->default(false);
            $table->boolean('needs_review')->default(false);
            $table->enum('status', ['generated', 'reviewed', 'approved'])->default('generated');
            $table->timestamps();
            
            $table->index(['month', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_reports');
    }
};