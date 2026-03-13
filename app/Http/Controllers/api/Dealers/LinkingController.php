<?php

namespace App\Http\Controllers\api\Dealers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Dealer;
use App\Product;
use App\DealerLinkedProduct; // Assuming this model exists for the mapping table
use App\AuthToken;
use Validator;

class LinkingController extends Controller
{
    public function __construct(Request $request)
    {
        if ($request->header('Authorization')) {
            $token = $request->header('Authorization');
            $this->resp = AuthToken::verifyUser($token);
        }
    }

    /**
     * 1) List of dealers linked to the current logged-in Parent Dealer
     */
    public function linkedDealersList(Request $request)
    {
        $resp = $this->resp;
        if ($resp['status'] && isset($resp['dealer'])) {
            // Get current dealer ID (handles sub-user context if applicable)
            $parentId = Dealer::getParentDealer($resp['dealer']);

            $dealers = Dealer::where('linked_dealer_id', $parentId)
                ->select('id', 'business_name', 'email', 'owner_mobile', 'dealer_type', 'city','owner_name')
                ->get();
            $message = "Linked dealers fetched successfully";
            $result['dealers'] = $dealers;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
        $message = "Unauthorized";
        return response()->json(apiErrorResponse($message),422);
    }

    /**
     * 2) List products linked to a specific dealer
     */
    public function dealerLinkedProducts(Request $request)
    {
        $resp = $this->resp;
        if ($resp['status'] && isset($resp['dealer'])) {
            $data = $request->all();
            $validator = Validator::make($data, [
                'dealer_id' => 'required|exists:dealers,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
            }

            // Fetch products associated with this specific dealer
            // Assuming the relationship is defined in the Dealer model or via a pivot
            $linkedProductIds = DealerLinkedProduct::where('dealer_id', $data['dealer_id'])
                ->pluck('product_id')
                ->toArray();

            $products = Product::whereIn('id', $linkedProductIds)
                ->select('id', 'product_name', 'product_detail_info','product_code','lab_recipe_number')
                ->get();

            $message = "Products fetched successfully";
            $result['products'] = $products;
            return response()->json(apiSuccessResponse($message,$result),200);
        }
        $message = "Unauthorized";
        return response()->json(apiErrorResponse($message),422);
    }

    /**
     * 3) Save and Update products for a dealer using sync()
     */
    public function saveDealerProducts(Request $request)
    {
        $resp = $this->resp;
        if ($resp['status'] && isset($resp['dealer'])) {
            $data = $request->all();
            
            // Validate input
            $validator = Validator::make($data, [
                'dealer_id' => 'required|exists:dealers,id',
                'product_ids' => 'required|array',
                'product_ids.*' => 'exists:products,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
            }

            // Find the dealer we want to update
            $dealerToUpdate = Dealer::find($data['dealer_id']);

            // Use sync() to handle the relationship efficiently
            // This will only delete what's missing and add what's new
            $dealerToUpdate->products()->sync($data['product_ids']);

            $message = "Dealer products synced successfully";
            return response()->json(apiSuccessResponse($message),200);
        }
        
        return response()->json(['status' => false, 'message' => 'Unauthorized'], 422);
    }
}