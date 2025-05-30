<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToClientsAndOrdersTables extends Migration
{
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            // Add a normal index on 'company' for faster LIKE 'term%' searches
            $table->index('company', 'idx_clients_company');
            
            // Uncomment below if you want a fulltext index and your DB supports it (MySQL)
            // $table->fullText('company', 'ft_clients_company');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Add a normal index on 'reference' for faster LIKE 'term%' searches
            $table->index('reference', 'idx_orders_reference');
            
            // Uncomment below if you want a fulltext index and your DB supports it (MySQL)
            // $table->fullText('reference', 'ft_orders_reference');
        });
    }

    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('idx_clients_company');
            // $table->dropFullText('ft_clients_company');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_reference');
            // $table->dropFullText('ft_orders_reference');
        });
    }
}
