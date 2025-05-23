<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('factures', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->string('status')->after('note');
        });
    }

    public function down()
    {
        Schema::table('factures', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('status');
        });
    }

};
