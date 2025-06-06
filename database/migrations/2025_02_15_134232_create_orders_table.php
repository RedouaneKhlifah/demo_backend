<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id'); 
            $table->string('reference');
            $table->date('order_date');
            $table->date('expiration_date');
            $table->decimal('tva', 5, 2);
            $table->enum('remise_type', ['PERCENT', 'FIXED']);
            $table->decimal('remise', 10, 2)->default(0);
            $table->string('bcn')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_published')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down()
     {
         Schema::dropIfExists('orders');
     }

};
