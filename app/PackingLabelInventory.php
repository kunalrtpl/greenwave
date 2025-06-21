<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
class PackingLabelInventory extends Model
{
    //
	public function addedby(){
		return $this->belongsTo('App\User','added_by','id')->select('id','name');
	}
    
    public static function createPLInventory($data){
    	$createPmInv = new PackingLabelInventory;
		$createPmInv->label_id = $data['label_id'];
		$createPmInv->incoming_date = $data['incoming_date'];
		$createPmInv->stock           = $data['stock'];
		$createPmInv->remarks         = $data['remarks'];
		$createPmInv->added_by        = Auth::user()->id;
		$createPmInv->save();
		DB::table('labels')->where('id',$data['label_id'])->increment('stock',$data['stock']);
		return 'ok';
    }
}
