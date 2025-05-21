<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('facture_product', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('facture_product', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }
};
