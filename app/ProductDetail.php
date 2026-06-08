<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductDetail extends Model
{
    public function subcats()
    {
        return $this->hasMany('App\ProductDetail', 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(ProductDetail::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'product_detail_id')
                    ->where('status', 1);
    }

    /**
     * Build full hierarchy with product pricing data included.
     * Fetches moq, average_dispatch_time, not_available, and latest dealer_price.
     */
    public static function fullHierarchy()
    {
        $allCategories = self::all();

        $today = Carbon::today()->toDateString();

        // ── Fetch latest pricing per product (same subquery as ProductPricingController) ──
        $latestPrices = DB::table('product_pricings as pp')
            ->select('pp.product_id', 'pp.dealer_price', 'pp.price_date')
            ->whereRaw('pp.id = (
                SELECT pp2.id FROM product_pricings pp2
                WHERE pp2.product_id = pp.product_id
                  AND pp2.price_date <= ?
                ORDER BY pp2.price_date DESC, pp2.id DESC
                LIMIT 1
            )', [$today])
            ->get()
            ->keyBy('product_id');

        // ── Fetch products with all needed fields ──
        $allProducts = DB::table('products')
            ->where('is_trader_product', 0)
            ->where('status', 1)
            ->orderBy('product_name', 'ASC')
            ->select(
                'id',
                'product_name',
                'product_detail_id',
                'moq',
                'average_dispatch_time',
                'not_available'
            )
            ->get();

        // Attach dealer_price from pricing lookup
        $allProducts = $allProducts->map(function ($product) use ($latestPrices) {
            $pricing = $latestPrices->get($product->id);
            $product->dealer_price = $pricing ? $pricing->dealer_price : null;
            $product->price_date   = $pricing ? $pricing->price_date   : null;
            return $product;
        });

        $productsGrouped = $allProducts->groupBy('product_detail_id');
        $categories      = $allCategories->keyBy('id');
        $groupedParents  = [];

        foreach ($allCategories as $category) {

            if ($category->parent_id === 'ROOT') {
                continue;
            }

            $parent = $categories[$category->parent_id] ?? null;

            if ($parent && $parent->parent_id === 'ROOT') {

                $node = self::buildTree($category, $categories, $productsGrouped);

                if (empty($node)) {
                    continue;
                }

                $parentKey = strtolower(trim($category->name));

                if (!isset($groupedParents[$parentKey])) {

                    $groupedParents[$parentKey] = $node;

                } else {

                    foreach ($node['children'] ?? [] as $childNode) {

                        $childKey = strtolower(trim($childNode['name']));
                        $found    = false;

                        foreach ($groupedParents[$parentKey]['children'] as &$existingChild) {

                            if (strtolower(trim($existingChild['name'])) === $childKey) {

                                $existingChild['products'] = collect(
                                    array_merge(
                                        $existingChild['products'] ?? [],
                                        $childNode['products'] ?? []
                                    )
                                )->unique('id')
                                 ->sortBy('product_name')
                                 ->values()
                                 ->toArray();

                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            $groupedParents[$parentKey]['children'][] = $childNode;
                        }
                    }
                }
            }
        }

        return array_values($groupedParents);
    }

    private static function buildTree($category, $categories, $productsGrouped)
    {
        $products      = [];
        $childrenArray = [];

        foreach ($categories as $child) {

            if ($child->parent_id == $category->id) {

                if ($child->type === 'child') {

                    if (isset($productsGrouped[$child->id])) {

                        foreach ($productsGrouped[$child->id] as $product) {

                            $products[] = [
                                'id'                    => $product->id,
                                'product_name'          => $product->product_name,
                                'description'           => $child->name,

                                // ── New fields ──
                                'moq'                   => $product->moq,
                                'average_dispatch_time' => $product->average_dispatch_time,
                                'not_available'         => $product->not_available,
                                'dealer_price'          => $product->dealer_price,
                                'price_date'            => $product->price_date,
                            ];
                        }
                    }

                } else {

                    $childNode = self::buildTree($child, $categories, $productsGrouped);

                    if (!empty($childNode)) {
                        $childrenArray[] = $childNode;
                    }
                }
            }
        }

        if (empty($products) && empty($childrenArray)) {
            return [];
        }

        if (!empty($products)) {
            usort($products, function ($a, $b) {
                return strcasecmp($a['product_name'], $b['product_name']);
            });
        }

        return [
            'id'       => $category->id,
            'name'     => $category->name,
            'type'     => $category->type,
            'products' => $products,
            'children' => $childrenArray,
        ];
    }
}