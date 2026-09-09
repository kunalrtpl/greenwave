<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQtyAndOwnStockToUserDvrProductsTable extends Migration
{
    public function up()
    {
        Schema::table('user_dvr_products', function (Blueprint $table) {
            $table->decimal('qty', 10, 3)
                  ->nullable()
                  ->after('product_id');

            // 1 = quantity was issued from the user's own free sampling stock
            $table->boolean('own_stock')
                  ->default(0)
                  ->after('qty');
        });
    }

    public function down()
    {
        Schema::table('user_dvr_products', function (Blueprint $table) {
            $table->dropColumn(['qty', 'own_stock']);
        });
    }
}
