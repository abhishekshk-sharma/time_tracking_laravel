<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->boolean('is_pf')->default(false)->after('pf');
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn('is_pf');
        });
    }
};