<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * product_inventories.stock / remaining_stock were bigint, so half a kilo of a
 * sample rounded away the moment it was saved. Sample dispatch now subtracts
 * from remaining_stock, which makes three decimals necessary — the same ones
 * raw_material_inventories.stock has always had.
 */
class MakeProductInventoryStockDecimal extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE product_inventories MODIFY stock DECIMAL(15,3) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE product_inventories MODIFY remaining_stock DECIMAL(15,3) NOT NULL DEFAULT 0');
    }

    public function down()
    {
        DB::statement('ALTER TABLE product_inventories MODIFY stock BIGINT(20) NOT NULL');
        DB::statement('ALTER TABLE product_inventories MODIFY remaining_stock BIGINT(20) NOT NULL DEFAULT 0');
    }
}
