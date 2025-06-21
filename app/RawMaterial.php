<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    //
    public function latest_raw_material(){
    	return $this->hasONe('App\RawMaterialInventory')->orderby('id','desc');
    }
}
