<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
class PackingSizeInventory extends Model
{
    //
    public function addedby(){
		return $this->belongsTo('App\User','added_by','id')->select('id','name');
	}
	
    public static function createPMInventory($data){
    	//$packinginfo = DB::table('packing_sizes')->where('id',$data['packing_size_id'])->first();
    	$createPmInv = new PackingSizeInventory;
		//$createPmInv->packing_size_id = $data['packing_size_id'];
		$createPmInv->packing_type_id = $data['packing_type_id'];
		$createPmInv->incoming_date = $data['incoming_date'];
		//$createPmInv->type            = $packinginfo->type;
		$createPmInv->stock           = $data['stock'];
		$createPmInv->remarks         = $data['remarks'];
		$createPmInv->added_by        = Auth::user()->id;
		$createPmInv->save();
		DB::table('packing_types')->where('id',$data['packing_type_id'])->increment('stock',$data['stock']);
		return 'ok';
    }
}
