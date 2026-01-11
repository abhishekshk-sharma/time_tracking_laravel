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
        Schema::table('salary_reports', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('status');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->string('ifsc_code', 11)->nullable()->after('bank_account');
            $table->string('bank_branch')->nullable()->after('ifsc_code');
            $table->string('uan', 12)->nullable()->after('bank_branch');
            $table->string('pf_no')->nullable()->after('uan');
            $table->string('esic_no', 17)->nullable()->after('pf_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_reports', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name', 'bank_account', 'ifsc_code', 'bank_branch', 
                'uan', 'pf_no', 'esic_no'
            ]);
        });
    }
};