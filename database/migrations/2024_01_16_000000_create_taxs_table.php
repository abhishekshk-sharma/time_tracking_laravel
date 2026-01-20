<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('taxs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('income_from');
            $table->bigInteger('income_to')->nullable();
            $table->decimal('tax_rate', 5, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('taxs');
    }
};