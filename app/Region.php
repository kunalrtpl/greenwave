<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    //
    public function subregions(){
    	return $this->hasMany('App\Region','parent_id')->where('status',1);
    }

    public function parent_region(){
        return $this->belongsto('App\Region','parent_id');
    }

    public function states(){
    	return $this->hasMany('App\RegionState');
    }

    public function cities(){
    	return $this->hasMany('App\RegionCity');
    }
}
