<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyReferenceOnFacturesTable extends Migration
{
    public function up()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropUnique(['reference']);
        });
    }

    public function down()
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->unique('reference');
        });
    }
}
