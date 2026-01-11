<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('uan')->nullable();
            $table->string('pf_no')->nullable();
            $table->string('esic_no')->nullable();
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account', 'ifsc_code', 'bank_branch', 'uan', 'pf_no', 'esic_no']);
        });
    }
};