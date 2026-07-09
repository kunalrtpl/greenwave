<?php
/**
 * ─────────────────────────────────────────────────────────────────────
 *  CUSTOMER PRICING HELPERS — paste into app/Helpers/helper.php
 *  (or wherever getcities(), buisnesModels(), product_types() live)
 * ─────────────────────────────────────────────────────────────────────
 *
 *  IMPORTANT — also update your existing buisnesModels() helper to
 *  include the new model, e.g.:
 *
 *      function buisnesModels(){
 *          return array('Open','Dealer','Direct Customer','Hybrid');
 *      }
 */

if (!function_exists('payment_terms')) {
    /** Payment Terms dropdown (Business Model = Direct Customer / Hybrid) */
    function payment_terms()
    {
        return ['Advance', '1-7 days', '15 days', '30 days', '45 days', '60 days'];
    }
}

if (!function_exists('direct_sales_premium')) {
    /**
     * Direct Sales Premium (%) — static, driven by Payment Term.
     * 60 days - 5%, 45 days - 4%, 30 days - 3%, 15 days - 2%,
     * 1-7 days - 2%, Advance - 1%
     */
    function direct_sales_premium($paymentTerm)
    {
        $map = [
            'Advance'  => 1,
            '1-7 days' => 2,
            '15 days'  => 2,
            '30 days'  => 3,
            '45 days'  => 4,
            '60 days'  => 5,
        ];
        return $map[$paymentTerm] ?? 0;
    }
}

if (!function_exists('packing_sizes')) {
    /** Packing Size dropdown. Default = Standard */
    function packing_sizes()
    {
        return [
            'Standard' => 'Standard',
            '5kg*2'    => '5kg*2',
            '1kg*10'   => '1kg*10',
        ];
    }
}

if (!function_exists('additional_packing_cost')) {
    /**
     * Additional Packing Cost (Rs./kg) — static figures.
     * Standard = 0, 5kg*2 = 25, 1kg*10 = 35
     */
    function additional_packing_cost($packingSize)
    {
        $map = [
            'Standard' => 0,
            '5kg*2'    => 25,
            '1kg*10'   => 35,
        ];
        return $map[$packingSize] ?? 0;
    }
}

if (!function_exists('selling_expense_label')) {
    /** "ORC" when Business Model is Hybrid; "Selling Expense" for Direct Customer (and everything else) */
    function selling_expense_label($businessModel)
    {
        return ($businessModel === 'Hybrid') ? 'ORC' : 'Selling Expense';
    }
}

if (!function_exists('customer_pricing_masters')) {
    /**
     * All static master values for the Customer Pricing screen —
     * meant to be returned as-is from an API endpoint so the
     * mobile/frontend can build its dropdowns + do live calculations.
     */
    function customer_pricing_masters()
    {
        // payment terms with their premium % merged in
        $paymentTerms = [];
        foreach (payment_terms() as $term) {
            $paymentTerms[] = [
                'value'           => $term,
                'label'           => $term,
                'premium_percent' => direct_sales_premium($term),
            ];
        }

        // packing sizes with their additional cost merged in
        $packingSizes = [];
        foreach (packing_sizes() as $value => $label) {
            $packingSizes[] = [
                'value'           => $value,
                'label'           => $label,
                'additional_cost' => additional_packing_cost($value), // Rs./kg
            ];
        }

        return [
            'business_models' => buisnesModels(),
            'payment_terms'   => $paymentTerms,
            'packing_sizes'   => $packingSizes,
            'freight_basis'   => ['Paid by Company', 'Paid by Customer'],
            'expense_basis'   => ['%', 'Rs/kg'],
            'labels'          => [
                'Hybrid'          => selling_expense_label('Hybrid'),          // ORC
                'Direct Customer' => selling_expense_label('Direct Customer'), // Selling Expense
            ],
        ];
    }
}

if (!function_exists('getProductStandardDp')) {
    /**
     * Standard DP (Rs./kg) for a product — the LATEST Dealer Price whose
     * date is <= TODAY. Future-dated prices are ignored until their date
     * arrives (e.g. a price of 120 dated 5th July does not apply on 2nd July).
     *
     * ⚠️  CONFIG — set these three to match your Product Pricing table
     *     (the table behind admin/product-pricing with Dealer Price /
     *      Market Price / Dealer Markup / Product Class / Date columns).
     */
    function getProductStandardDp($productId)
    {
        if (empty($productId)) return 0;

        $table    = 'product_pricings';   // ← your pricing table name
        $priceCol = null;                 // auto-detected below, or hardcode e.g. 'dealer_price'
        $dateCol  = null;                 // auto-detected below, or hardcode e.g. 'date'

        if (\Schema::hasTable($table)) {
            foreach (['dealer_price', 'dp', 'price'] as $c) {
                if (\Schema::hasColumn($table, $c)) { $priceCol = $c; break; }
            }
            foreach (['date', 'price_date', 'start_date', 'effective_date', 'created_at'] as $c) {
                if (\Schema::hasColumn($table, $c)) { $dateCol = $c; break; }
            }
            if ($priceCol && $dateCol) {
                $row = \DB::table($table)
                    ->where('product_id', $productId)
                    ->whereDate($dateCol, '<=', date('Y-m-d'))   // ← key: only prices effective till today
                    ->orderBy($dateCol, 'DESC')                   // latest effective date first
                    ->orderBy('id', 'DESC')                       // tie-break: newest row wins
                    ->first();
                if ($row) return (float) $row->{$priceCol};
            }
        }

        // Fallback: a price column directly on products
        $product = \DB::table('products')->where('id', $productId)->first();
        foreach (['dp', 'dealer_price', 'price'] as $col) {
            if ($product && isset($product->{$col}) && $product->{$col} !== null) {
                return (float) $product->{$col};
            }
        }
        return 0;
    }
}

if (!function_exists('getProductStandardPacking')) {
    /**
     * Label for the "Standard" packing option — pulled from the PRODUCT
     * MASTER (products.packing_size_id → packing_sizes), NOT static.
     * Only the label changes per product; the stored value stays 'Standard'
     * and the other two options (5kg*2, 1kg*10) remain fixed.
     */
    function getProductStandardPacking($productId)
    {
        if (empty($productId)) return '50 kg';

        $product = \DB::table('products')->where('id', $productId)->first();
        if ($product && !empty($product->packing_size_id)) {
            $ps = \DB::table('packing_sizes')->where('id', $product->packing_size_id)->first();
            if ($ps) {
                if (!empty($ps->size)) {
                    $size = rtrim(rtrim(number_format((float) $ps->size, 2, '.', ''), '0'), '.');
                    return $size . ' kg';
                }
                if (!empty($ps->type)) return $ps->type;
            }
        }
        return '50 kg';
    }
}

if (!function_exists('customer_viability_check')) {
    /**
     * Viability Check — pure calculation, informational only.
     * The same math is mirrored in JS on the add/edit screen; keep both in sync.
     *
     * @param float  $standardDp           Standard DP (from Product Pricing)
     * @param string $paymentTerm          Advance / 1-7 days / ... / 60 days
     * @param string $packingSize          Standard / 5kg*2 / 1kg*10
     * @param string $freightBasis         'Paid by Company' | 'Paid by Customer'
     * @param float  $freight              Rs./kg (used only when Paid by Company)
     * @param string $expenseBasis         '%' | 'Rs/kg'
     * @param float  $expenseValue         8 (=8%) or Rs./kg figure
     * @param float  $sellingPrice         Customer Selling Price (or Net Price after
     *                                     discount for the Special block)
     * @return array standard_dp, premium_percent, base_price, packing_cost,
     *               freight, selling_expenses, minimum_selling_price,
     *               additional_realization, viable (bool)
     */
    function customer_viability_check(
        $standardDp,
        $paymentTerm,
        $packingSize,
        $freightBasis,
        $freight,
        $expenseBasis,
        $expenseValue,
        $sellingPrice
    ) {
        $premium   = direct_sales_premium($paymentTerm);
        $basePrice = round($standardDp * (1 + $premium / 100), 2);
        $packing   = additional_packing_cost($packingSize);
        $freightRs = ($freightBasis === 'Paid by Company') ? (float) $freight : 0;

        $expenses = ($expenseBasis === '%')
            ? round($sellingPrice * ((float) $expenseValue / 100), 3)
            : (float) $expenseValue;

        $msp = round($basePrice + $packing + $freightRs + $expenses, 2);
        $realization = round($sellingPrice - $msp, 2);

        return [
            'standard_dp'            => (float) $standardDp,
            'premium_percent'        => $premium,
            'base_price'             => $basePrice,
            'packing_cost'           => $packing,
            'freight'                => $freightRs,
            'selling_expenses'       => $expenses,
            'minimum_selling_price'  => $msp,
            'additional_realization' => $realization,
            'viable'                 => $realization >= 0,
        ];
    }
}

if (!function_exists('net_price_after_discount')) {
    /** Net Price when Special Basis = 'Special Discount' (value is a %) */
    function net_price_after_discount($customerSellingPrice, $discountPercent)
    {
        return round((float) $customerSellingPrice * (1 - (float) $discountPercent / 100), 2);
    }
}