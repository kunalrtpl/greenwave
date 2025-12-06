<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Product;
use App\PackingType;
use DB;

class UpdatePackingCostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan packingcost:update
     */
    protected $signature = 'packingcost:update';

    /**
     * The console command description.
     */
    protected $description = 'Update packing costs for all products, adjust label_id=2, and reset packing_loss.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("==== Starting Packing Cost Update Job ====");

        DB::beginTransaction();

        try {

            /**
             * ----------------------------------------------------------
             * 1️⃣ UPDATE ALL PRODUCTS WHERE label_id = 2
             * ----------------------------------------------------------
             */
            $this->info("Updating products where label_id = 2 ...");

            Product::query()->update([
                'label_id' => 2
            ]);


            /**
             * ----------------------------------------------------------
             * 2️⃣ UPDATE packing_types TABLE SET packing_loss = 0
             * ----------------------------------------------------------
             */
            $this->info("Updating packing_types.packing_loss = 0 ...");

            PackingType::query()->update(['packing_loss' => 0]);


            /**
             * ----------------------------------------------------------
             * 3️⃣ RECALCULATE PACKING COST FOR ALL PRODUCTS
             * ----------------------------------------------------------
             */
            $this->info("Recalculating packing costs for all products...");

            $products = Product::get();

            foreach ($products as $product) {

                $this->info("➡ Processing Product ID: {$product->id}");

                $data = [
                    'packing_type_id'            => $product->packing_type_id,
                    'additional_packing_type_id' => $product->additional_packing_type_id,
                    'packing_size_id'            => $product->packing_size_id,
                    'standard_fill_size'         => $product->standard_fill_size,
                    'label_id'                   => $product->label_id,
                ];

                // Call user function
                $response = productPackingCost($data);

                // 🛑 NULL RESPONSE CHECK
                if (!$response || !is_array($response)) {
                    $this->error("❌ ERROR: productPackingCost() returned NULL for Product ID: {$product->id}");
                    continue;
                }

                // 🛑 REQUIRED KEYS CHECK
                $requiredKeys = [
                    'basic_packing_material_cost',
                    'additional_packing_material_cost',
                    'label_cost',
                    'facilitation_cost',
                    'packing_cost'
                ];

                foreach ($requiredKeys as $key) {
                    if (!array_key_exists($key, $response)) {
                        $this->error("❌ ERROR: Missing '{$key}' in response for Product ID: {$product->id}");
                        continue 2; // skip this product
                    }
                }

                // ✅ SAVE COSTS
                $product->basic_packing_material_cost      = $response['basic_packing_material_cost'];
                $product->additional_packing_material_cost = $response['additional_packing_material_cost'];
                $product->label_cost                       = $response['label_cost'];
                $product->facilitation_cost                = $response['facilitation_cost'];
                $product->packing_cost                     = $response['packing_cost'];
                $product->save();

                $this->info("✔ Updated Product ID {$product->id}");
            }

            DB::commit();

            $this->info("==== Packing Cost Update Completed Successfully ====");

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("❌ FATAL ERROR: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
