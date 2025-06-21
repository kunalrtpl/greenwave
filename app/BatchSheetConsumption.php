<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BatchSheetConsumption extends Model
{
    //
    public function batchsheet(){
        return $this->belongsto('App\BatchSheet','batch_sheet_id');
    }

    public function packing_type(){
        return $this->belongsto('App\PackingType');
    }
}
