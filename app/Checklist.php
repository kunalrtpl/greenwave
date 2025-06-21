<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    //
    public function subchecklists(){
    	return $this->hasMany('App\Checklist','parent_id')->select('id','name','parent_id')->orderby('id','ASC')->where('status',1);
    }
}
