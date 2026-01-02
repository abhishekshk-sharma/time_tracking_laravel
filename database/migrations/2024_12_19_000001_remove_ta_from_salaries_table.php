<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn('ta');
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('ta', 10, 2)->default(0)->after('hra');
        });
    }
};