<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BatchSheet extends Model
{
    //
    public function addedby(){
		return $this->belongsTo('App\User','created_by','id')->select('id','name');
	}

	public function materials(){
		return $this->hasMany('App\BatchSheetMaterial')->with('rawmaterial');
	}

	public function batch_history(){
		return $this->hasMany('App\BatchSheetHistory','batch_sheet_id','id');
	}

	public function matching_batch_history()
	{
	    return $this->hasMany('App\BatchSheetHistory')
	        ->whereColumn('status', 'batch_sheets.status');
	}

	public function batchsheet_requirements(){
		return $this->hasMany('App\BatchSheetRequirement','batch_sheet_id','id')->with(['rawmaterial_inventory','product_inventory']);
	}

	public function product(){
		return $this->belongsTo('App\Product')->select('id','product_name','packing_size_id','batch_out_duration','product_code','is_trader_product')->with('productpacking');
	}

	public function standard_packing(){
		return $this->belongsTo('App\PackingType','standard_packing_type_id','id');
	}

	public function medias(){
		return $this->hasMany('App\BatchSheetMedia','batch_sheet_id','id');
	}
}
