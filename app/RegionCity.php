<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegionCity extends Model
{
    //

    public static function cities($subregionArr){
        $regionIds = \App\Region::wherein('region',$subregionArr)->pluck('id')->toArray();
        $cities =   RegionCity::wherein('region_id',$regionIds)->pluck('city')->toArray();
        $cities = implode(', ',$cities);
        return $cities;
    }
}
