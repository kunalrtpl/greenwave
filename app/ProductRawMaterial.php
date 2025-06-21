<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductRawMaterial extends Model
{
    //
    public function rawmaterial(){
    	return $this->belongsTo('App\RawMaterial','raw_material_id','id');
    }
}
