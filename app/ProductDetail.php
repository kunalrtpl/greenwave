<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    //

    public function subcats(){
    	return $this->hasMany('App\ProductDetail','parent_id','id');
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

    public static function fullHierarchy()
    {
        $allCategories = self::all();

        $allProducts = Product::select('id','product_name','product_detail_id','is_trader_product')
            ->where('is_trader_product', 0)
            ->where('status', 1)
            ->orderBy('product_name', 'ASC')
            ->get();

        $productsGrouped = $allProducts->groupBy('product_detail_id');
        $categories = $allCategories->keyBy('id');

        $groupedParents = [];

        foreach ($allCategories as $category) {

            // Ignore ROOT
            if ($category->parent_id === 'ROOT') {
                continue;
            }

            $parent = $categories[$category->parent_id] ?? null;

            // Only take level directly under ROOT
            if ($parent && $parent->parent_id === 'ROOT') {

                $node = self::buildTree($category, $categories, $productsGrouped);

                if (empty($node)) {
                    continue;
                }

                $parentKey = strtolower(trim($category->name));

                if (!isset($groupedParents[$parentKey])) {

                    $groupedParents[$parentKey] = $node;

                } else {

                    // 🔥 Merge children by name
                    foreach ($node['children'] ?? [] as $childNode) {

                        $childKey = strtolower(trim($childNode['name']));
                        $found = false;

                        foreach ($groupedParents[$parentKey]['children'] as &$existingChild) {

                            if (strtolower(trim($existingChild['name'])) === $childKey) {

                                // Merge products
                                $existingChild['products'] = collect(
                                    array_merge(
                                        $existingChild['products'] ?? [],
                                        $childNode['products'] ?? []
                                    )
                                )->unique('id')
                                ->sortBy('product_name')->values()->toArray();

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
        $products = [];
        $childrenArray = [];

        foreach ($categories as $child) {

            if ($child->parent_id == $category->id) {

                // If leaf category
                if ($child->type === 'child') {

                    if (isset($productsGrouped[$child->id])) {

                        foreach ($productsGrouped[$child->id] as $product) {

                            $products[] = [
                                'id' => $product->id,
                                'product_name' => $product->product_name,
                                'description' => $child->name
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

        // Remove empty nodes
        if (empty($products) && empty($childrenArray)) {
            return [];
        }
        if (!empty($products)) {
            usort($products, function($a, $b) {
                return strcasecmp($a['product_name'], $b['product_name']);
            });
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'type' => $category->type,
            'products' => $products,
            'children' => $childrenArray
        ];
    }

}
