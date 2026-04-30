<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAttachment extends Model
{
    //
    protected $table = 'user_attachments';
 
    protected $fillable = [
        'user_id',
        'label',
        'original_name',
        'file_path',
        'show_in_app',
    ];
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
