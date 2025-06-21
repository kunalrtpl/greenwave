<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    //
    public function contact_persons(){
    	return $this->hasMany('App\DealerContactPerson','dealer_id');
    }

    public function linked_products(){
    	return $this->hasMany('App\DealerLinkedProduct','dealer_id')->with('product');
    }

    public function customers(){
        return $this->hasMany('App\Customer','dealer_id')->where('business_model','Dealer');
    }

    public function special_discounts(){
        return $this->hasMany('App\DealerSpecialDiscount','dealer_id');
    }

    public function parent_dealer_info(){
        return $this->belongsTo('App\Dealer','parent_id','id')->with(['contact_persons','linked_products']);
    }

    public static function addOnUsers($dealerid){
        $addOnUsers = Dealer::where('parent_id',$dealerid)->where('is_delete',0)->get();
        $addOnUsers = json_decode(json_encode($addOnUsers),true);
        foreach($addOnUsers as $key=> $user){
            $addOnUsers[$key]['dealer_roles'] = getUserRoles($user['app_roles'],'dealer');
        }
        return $addOnUsers;
    }

    public static function getParentChildDealers($dealer)
    {
        $dealerId = $dealer['id']; // Get the logged-in dealer ID
        $parentId = $dealer['parent_id']; // Get the parent ID

        // If parent_id is NULL, then the dealer is a parent
        if (is_null($parentId)) {
            // Get all child dealers of this parent
            return self::where('parent_id', $dealerId)->pluck('id')->prepend($dealerId)->toArray();
        } else {
            // If logged in as a child, find the root parent
            $parentDealer = self::where('id', $parentId)->first();

            if ($parentDealer) {
                // Get all dealers under the parent, including the parent
                return self::where('parent_id', $parentDealer->id)
                    ->orWhere('id', $parentDealer->id)
                    ->pluck('id')
                    ->toArray();
            }
        }
        return [$dealerId]; // Return the dealer itself if no relations found
    }

    public static function getParentDealer($dealer){
        $dealerId = $dealer['id']; // Get the logged-in dealer ID
        $parentId = $dealer['parent_id']; // Get the parent ID
        if (is_null($parentId)) {
            return $dealerId;
        }
        return $parentId;
    }
}
