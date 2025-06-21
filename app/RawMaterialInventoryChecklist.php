<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RawMaterialInventoryChecklist extends Model
{
    //
    public function checklist(){
		return $this->belongsTo('App\Checklist');
	}
}
