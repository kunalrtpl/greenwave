<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Product;
use App\PackingSize;
use App\PackingType;
use App\Label;
use Session;
class AdditionalCostController extends Controller
{
    /**
     * Page 1:
     * Show all active products
     */
    public function index()
    {
        Session::put('active','additionalCost');
        $title = 'Additional Cost';
        $products = Product::where('status', 1)->orderBy('product_name')->get();

        return view('admin.additional_cost.index', compact('title', 'products'));
    }

    /**
     * Page 2:
     * Preview additional cost
     */
    public function preview(Product $product)
    {
        // MODULE 1
        $standardPack = $this->standardPackingCostCalculation($product);
        if($product->physical_form == "Liquid"){
            $miniPack1kg10 = $this->MiniPackCost1kg10($product);
            $miniPack5kg2 = $this->MiniPackCost5kg2($product);
            return view('admin.additional_cost.preview', compact(
                'product',
                'standardPack',
                'miniPack1kg10',
                'miniPack5kg2',
            ));

        }else if($product->physical_form == "Powder"){
            $miniPack1kg12 = $this->MiniPackCost1kg12($product);
            return view('admin.additional_cost.preview', compact(
                'product',
                'standardPack',
                'miniPack1kg12',
            ));
        }
    }


    /**
     * STANDARD PACK COST (PER KG)
     * Dedicated function ✔
     */
    private function standardPackingCostCalculation(Product $product)
    {
        // Order Size (kg)
        $packingSize = PackingSize::find($product->packing_size_id);
        $orderSizeKg = $packingSize ? (float) $packingSize->size : null;

        // Related master data
        $basicPackingType      = PackingType::find($product->packing_type_id);
        $additionalPackingType = PackingType::find($product->additional_packing_type_id);
        $label                 = Label::find($product->label_id);

        $rows = [];
        $totalPerKg = 0;

        /**
         * Number formatting rule:
         * 320      -> 320
         * 320.00   -> 320
         * 320.5    -> 320.50
         * 320.25   -> 320.25
         * null     -> null (blank in view)
         */
        $nf = function ($value) {
            if (!is_numeric($value)) {
                return null;
            }

            $value = (float) $value;

            // Whole number → no decimals
            if (floor($value) == $value) {
                return (string) (int) $value;
            }

            // Decimal exists → force 2 decimals
            return number_format($value, 2, '.', '');
        };

        /* =========================
           1. BASIC PACKING
        ==========================*/
        $rows[] = [
            'description' => 'Basic Packing',
            'details'     => $basicPackingType->name ?? null,
            'units'       => 1,
            'unit_price'  => $nf($basicPackingType->price ?? null),
            'order_size'  => $orderSizeKg,
            'cost_per_kg' => $nf($product->basic_packing_material_cost ?? null),
        ];

        $totalPerKg += (float) ($product->basic_packing_material_cost ?? 0);

        /* =========================
           2. ADDITIONAL PACKING
        ==========================*/
        $rows[] = [
            'description' => 'Additional Packing',
            'details'     => $additionalPackingType->name ?? null,
            'units'       => $product->additional_packing_type_id ? 1 : null,
            'unit_price'  => $nf($additionalPackingType->price ?? null),
            'order_size'  => $product->additional_packing_type_id ? $orderSizeKg : null,
            'cost_per_kg' => $nf($product->additional_packing_material_cost ?? null),
        ];

        $totalPerKg += (float) ($product->additional_packing_material_cost ?? 0);

        /* =========================
           3. PACKING LABEL
        ==========================*/
        $rows[] = [
            'description' => 'Packing Label',
            'details'     => $label->name ?? null,
            'units'       => $product->label_id ? 1 : null,
            'unit_price'  => $nf($label->price ?? null),
            'order_size'  => $product->label_id ? $orderSizeKg : null,
            'cost_per_kg' => $nf($product->label_cost ?? null),
        ];

        $totalPerKg += (float) ($product->label_cost ?? 0);

        /* =========================
           4. PACKING FACILITATION
        ==========================*/
        $rows[] = [
            'description' => 'Packing Facilitation',
            'details'     => null,
            'units'       => null,
            'unit_price'  => null,
            'order_size'  => null,
            'cost_per_kg' => $nf($product->facilitation_cost ?? null),
        ];

        $totalPerKg += (float) ($product->facilitation_cost ?? 0);

        return [
            'standardPackKg' => $orderSizeKg,
            'rows'           => $rows,
            'totalPerKg'     => $nf($totalPerKg),
            'packing_loss'     => $nf($basicPackingType->packing_loss ?? 0),
        ];
    }

    private function MiniPackCost1kg10(Product $product)
    {
        return $this->calculateMiniPackCost($product, [
            'basic_packing_type_id'      => 6,
            'additional_packing_type_id' => 15,
            'standard_fill_size'         => 1,
            'units'                      => 10,
            'order_size_kg'              => 10,
            'label_id'                   => 2,
            'pack_label'                 => '1kg × 10',
        ]);
    }


    private function MiniPackCost5kg2(Product $product)
    {
        return $this->calculateMiniPackCost($product, [
            'basic_packing_type_id'      => 11,   // 5kg Can
            'additional_packing_type_id' => 16,   // Carton (5kg×2)
            'standard_fill_size'         => 5,
            'units'                      => 2,
            'order_size_kg'              => 10,
            'label_id'                   => 2,
            'pack_label'                 => '5kg × 2',
        ]);
    }


    private function MiniPackCost1kg12(Product $product)
    {
        return $this->calculateMiniPackCost($product, [
            'basic_packing_type_id'      => 14,
            'additional_packing_type_id' => 13,
            'standard_fill_size'         => 1,
            'units'                      => 12,
            'order_size_kg'              => 12,
            'label_id'                   => 2,
            'pack_label'                 => '1kg × 12',
        ]);
    }



    private function calculateMiniPackCost(Product $product, array $cfg)
    {
        /*
         |--------------------------------------------------------------------------
         | CONFIG VALUES
         |--------------------------------------------------------------------------
         */
        $basicPackingTypeId      = $cfg['basic_packing_type_id'];
        $additionalPackingTypeId = $cfg['additional_packing_type_id'];
        $standardFillSize        = $cfg['standard_fill_size'];
        $units                   = $cfg['units'];
        $orderSizeKg             = $cfg['order_size_kg'];
        $labelId                 = $cfg['label_id'];
        $packLabel               = $cfg['pack_label'];

        /*
         |--------------------------------------------------------------------------
         | MASTER DATA
         |--------------------------------------------------------------------------
         */
        $basicPackingType      = PackingType::find($basicPackingTypeId);
        $additionalPackingType = PackingType::find($additionalPackingTypeId);
        $label                 = Label::find($labelId);

        /*
         |--------------------------------------------------------------------------
         | DEALER PRICE (LATEST)
         |--------------------------------------------------------------------------
         */
        $pricing = \App\ProductPricing::where('product_id', $product->id)
            ->orderBy('price_date', 'desc')
            ->first();

        $dealerPrice = $pricing ? (float) $pricing->dealer_price : 0;

        /*
         |--------------------------------------------------------------------------
         | NUMBER FORMATTER
         |--------------------------------------------------------------------------
         */
        $nf = function ($value) {
            if (!is_numeric($value)) return null;
            $value = (float)$value;
            return (floor($value) == $value)
                ? (string)(int)$value
                : number_format($value, 2, '.', '');
        };

        /*
         |--------------------------------------------------------------------------
         | ROW CALCULATION
         |--------------------------------------------------------------------------
         */
        $rows = [];
        $packingCostPerKg = 0;

        /* 1️⃣ BASIC PACKING */
        $basicCostPerKg = $basicPackingType
            ? ($basicPackingType->price / $standardFillSize)
            : 0;

        $rows[] = [
            'description' => 'Basic Packing',
            'details'     => $basicPackingType->name ?? '',
            'units'       => $units,
            'unit_price'  => $nf($basicPackingType->price ?? null),
            'order_size'  => $orderSizeKg,
            'cost_per_kg' => $nf($basicCostPerKg),
        ];
        $packingCostPerKg += $basicCostPerKg;

        /* 2️⃣ ADDITIONAL PACKING */
        $additionalCostPerKg = $additionalPackingType
            ? ($additionalPackingType->price / $orderSizeKg)
            : 0;

        $rows[] = [
            'description' => 'Additional Packing',
            'details'     => $additionalPackingType->name ?? '',
            'units'       => 1,
            'unit_price'  => $nf($additionalPackingType->price ?? null),
            'order_size'  => $orderSizeKg,
            'cost_per_kg' => $nf($additionalCostPerKg),
        ];
        $packingCostPerKg += $additionalCostPerKg;

        /* 3️⃣ PACKING LABEL */
        $labelUnits = $units + 1; // bottles + carton
        $labelCostPerKg = $label
            ? (($label->price * $labelUnits) / $orderSizeKg)
            : 0;

        $rows[] = [
            'description' => 'Packing Label',
            'details'     => null,
            'units'       => $labelUnits,
            'unit_price'  => $nf($label->price ?? null),
            'order_size'  => $orderSizeKg,
            'cost_per_kg' => $nf($labelCostPerKg),
        ];
        $packingCostPerKg += $labelCostPerKg;

        /* 4️⃣ PACKING FACILITATION + LOSS */
        $selectedLoss = $basicPackingType->packing_loss ?? 0;
        $productLoss  = $product->packing_type->packing_loss ?? 0;

        $lossDifference = $selectedLoss - $productLoss;

        $extraLossCost = 0;
        if ($lossDifference > 0) {
            $extraLossCost = ($dealerPrice * $lossDifference) / 100;
        }

        $facilitationCost = $additionalPackingType->facilitation_cost ?? 0;

        $rows[] = [
            'description' => 'Packing Facilitation',
            'details'     => null,
            'units'       => null,
            'unit_price'  => null,
            'order_size'  => null,
            'cost_per_kg' => $nf($facilitationCost),
        ];
        $packingCostPerKg += $facilitationCost;

        /*
         |--------------------------------------------------------------------------
         | FINAL TOTALS (API MATCH)
         |--------------------------------------------------------------------------
         */
        $totalMiniPackCost = $packingCostPerKg + $extraLossCost;

        $additionalCost = ($packingCostPerKg - ($product->packing_cost ?? 0)) + $extraLossCost;

        return [
            'pack_label'           => $packLabel,
            'rows'                 => $rows,
            'packing_cost_per_kg'  => $nf($packingCostPerKg),
            'packing_loss'         => $nf($selectedLoss) . '%',
            'loss_difference'      => $nf($lossDifference) . '%',
            'dealer_price'         => $nf($dealerPrice),
            'packing_loss_cost'    => $nf($extraLossCost),
            'total_mini_pack_cost' => $nf($totalMiniPackCost),
            'additional_cost'      => $nf($additionalCost),
        ];
    }



}
