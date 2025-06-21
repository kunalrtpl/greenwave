<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedbackReply extends Model
{
    //
    public function reply_by(){
        return $this->belongsto('App\User','created_by','id')->select('id','name','designation');
    }
}
