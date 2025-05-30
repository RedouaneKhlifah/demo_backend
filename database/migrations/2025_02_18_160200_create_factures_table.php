<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); 
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique();
            $table->date('facture_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->decimal('tva', 10, 2)->default(0);
            $table->string('remise_type')->nullable();
            $table->decimal('remise', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->string('bcn')->nullable();
            $table->text('note')->nullable();
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down()
     {
         Schema::dropIfExists('factures');
     }
};
