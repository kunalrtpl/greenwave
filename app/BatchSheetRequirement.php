<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BatchSheetRequirement extends Model
{
    //
    public function rawmaterial_inventory(){
		return $this->belongsTo('App\RawMaterialInventory','raw_material_inventory_id','id')->with('rawmaterial');
	}

	public function product_inventory(){
		return $this->belongsTo('App\ProductInventory','product_inventory_id','id')->with('product');
	}
}
