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
        Schema::table('salaries', function (Blueprint $table) {
            $table->integer("special_allowance")->default(0)->after('conveyance_allowance');
            $table->double("tds")->default(0)->after('special_allowance');
            $table->double("healthcare_cess")->default(0)->after('tds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['special_allowance', 'tds']);
        });
    }
};
