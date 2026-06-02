<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Session;
use PDF;
class ProductPricingController extends Controller
{
    /**
     * Show product list with latest dealer prices.
     * GET /admin/product-pricing
     */
    public function index()
    {
        Session::put('active','productPricing'); 
        $title = 'Product Pricing';
        $today = Carbon::today()->toDateString();

        // Latest product_pricings row per product where price_date <= today
        $latestPricing = DB::table('product_pricings as pp')
            ->select('pp.product_id', 'pp.dealer_price', 'pp.market_price', 'pp.dealer_markup', 'pp.price_date', 'pp.id as pricing_id')
            ->whereRaw('pp.id = (
                SELECT pp2.id FROM product_pricings pp2
                WHERE pp2.product_id = pp.product_id
                  AND pp2.price_date <= ?
                ORDER BY pp2.price_date DESC, pp2.id DESC
                LIMIT 1
            )', [$today]);

        $products = DB::table('products')
            ->leftJoinSub($latestPricing, 'lp', function ($join) {
                $join->on('lp.product_id', '=', 'products.id');
            })
            ->where('products.status', 1)
            ->orderBy('products.product_name')
            ->select(
                'products.id',
                'products.product_name',
                'products.product_code',
                'products.moq',
                'products.average_dispatch_time',
                'products.not_available',
                'lp.dealer_price',
                'lp.market_price',
                'lp.dealer_markup',
                'lp.price_date',
                'lp.pricing_id'
            )
            ->get();

        return view('admin.product_pricing.index', compact('title', 'products', 'today'));
    }

    /**
     * Update a single product row (dealer_price, not_available, moq, avg_dispatch_time).
     * POST /admin/product-pricing/update/{id}
     */
    public function update(Request $request, $productId)
    {
        $request->validate([
            'dealer_price'          => 'nullable|numeric|min:0',
            'not_available'         => 'nullable|boolean',
            'moq'                   => 'nullable|string|max:191',
            'average_dispatch_time' => 'nullable|numeric|min:0',
        ]);

        $product = DB::table('products')->where('id', $productId)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $today         = Carbon::today()->toDateString();
        $newDealerPrice = $request->input('dealer_price');
        $dpChanged      = $request->input('dp_changed', false); // JS tells us if DP was touched

        DB::beginTransaction();
        try {

            // ── 1. Update products table (MOQ, dispatch time, not_available) ──
            DB::table('products')->where('id', $productId)->update([
                'moq'                   => $request->input('moq', $product->moq),
                'average_dispatch_time' => $request->input('average_dispatch_time', $product->average_dispatch_time),
                'not_available'         => $request->input('not_available', $product->not_available) ? 1 : 0,
                'updated_at'            => now(),
            ]);

            // ── 2. Only create new pricing row if dealer_price actually changed ──
            $newPricingId  = $request->input('pricing_id'); // existing pricing id (for reference)
            $pricingRecord = null;

            if ($dpChanged && $newDealerPrice !== null) {
                // Check if a pricing row already exists for TODAY for this product
                $existingToday = DB::table('product_pricings')
                    ->where('product_id', $productId)
                    ->where('price_date', $today)
                    ->first();

                if ($existingToday) {
                    // Update today's row instead of inserting a duplicate
                    DB::table('product_pricings')
                        ->where('id', $existingToday->id)
                        ->update([
                            'dealer_price'  => $newDealerPrice,
                            'market_price'  => $request->input('market_price', $existingToday->market_price),
                            'dealer_markup' => $request->input('dealer_markup', $existingToday->dealer_markup),
                            'updated_at'    => now(),
                        ]);
                    $newPricingId = $existingToday->id;
                } else {
                    // Carry forward market_price & dealer_markup from last pricing row
                    $lastPricing = DB::table('product_pricings')
                        ->where('product_id', $productId)
                        ->where('price_date', '<=', $today)
                        ->orderBy('price_date', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    $newPricingId = DB::table('product_pricings')->insertGetId([
                        'product_id'    => $productId,
                        'dealer_price'  => $newDealerPrice,
                        'market_price'  => $lastPricing ? $lastPricing->market_price : 0,
                        'dealer_markup' => $lastPricing ? $lastPricing->dealer_markup : 0,
                        'price_date'    => $today,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                $pricingRecord = DB::table('product_pricings')->where('id', $newPricingId)->first();
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Updated successfully.',
                'pricing_id' => $newPricingId,
                'price_date' => $pricingRecord ? $pricingRecord->price_date : null,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Export filtered product pricing list as PDF.
     * GET /admin/product-pricing/export-pdf
     */
    public function exportPdf(Request $request)
    {
        $today       = Carbon::today()->toDateString();
        $productId   = $request->get('product_id');
        $priceStatus = $request->get('price_status');
        $search      = $request->get('search', '');
        $naOnly      = $request->get('not_available', 0);

        // Same base query as index()
        $latestPricing = DB::table('product_pricings as pp')
            ->select('pp.product_id', 'pp.dealer_price', 'pp.market_price', 'pp.dealer_markup', 'pp.price_date', 'pp.id as pricing_id')
            ->whereRaw('pp.id = (
                SELECT pp2.id FROM product_pricings pp2
                WHERE pp2.product_id = pp.product_id
                  AND pp2.price_date <= ?
                ORDER BY pp2.price_date DESC, pp2.id DESC
                LIMIT 1
            )', [$today]);

        $query = DB::table('products')
            ->leftJoinSub($latestPricing, 'lp', function ($join) {
                $join->on('lp.product_id', '=', 'products.id');
            })
            ->where('products.status', 1)
            ->select(
                'products.id', 'products.product_name', 'products.product_code',
                'products.moq', 'products.average_dispatch_time', 'products.not_available',
                'lp.dealer_price', 'lp.market_price', 'lp.dealer_markup',
                'lp.price_date', 'lp.pricing_id'
            );

        // Apply filters
        if ($productId) {
            $query->where('products.id', $productId);
        }

        if ($priceStatus === 'has_price') {
            $query->whereNotNull('lp.dealer_price');
        } elseif ($priceStatus === 'no_price') {
            $query->whereNull('lp.dealer_price');
        } elseif ($priceStatus === 'today') {
            $query->where('lp.price_date', $today);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.product_name', 'like', '%' . $search . '%')
                  ->orWhere('products.product_code', 'like', '%' . $search . '%');
            });
        }

        if ($naOnly) {
            $query->where('products.not_available', 1);
        }

        $products = $query->orderBy('products.product_name')->get();

        // Build filter labels for PDF header
        $filterLabels = [];
        if ($productId) {
            $prod = DB::table('products')->where('id', $productId)->first();
            if ($prod) $filterLabels[] = 'Product: ' . $prod->product_name;
        }
        if ($priceStatus) {
            $map = ['has_price' => 'Has Price', 'no_price' => 'No Price', 'today' => 'Updated Today'];
            $filterLabels[] = 'Price Status: ' . ($map[$priceStatus] ?? $priceStatus);
        }
        if ($search)  $filterLabels[] = 'Search: ' . $search;
        if ($naOnly)  $filterLabels[] = 'Not Available Only';

        $pdf = PDF::loadView('admin.product_pricing.pdf', [
            'products'     => $products,
            'today'        => $today,
            'filterLabels' => $filterLabels,
            'generatedAt'  => now()->format('d M Y, h:i A'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('product-pricing-' . now()->format('Ymd-His') . '.pdf');
    }
}