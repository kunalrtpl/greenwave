<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDvrAttachment extends Model
{
    protected $table = 'user_dvr_attachments';
    protected $guarded = [];

    public function dvr()
    {
        return $this->belongsTo(UserDvr::class);
    }

    public function trial()
    {
        return $this->belongsTo(UserDvrTrial::class, 'trial_id');
    }

    // ✅ Append file_url in API response
    protected $appends = ['file_url'];

    /**
     * Get full URL of attachment file
     *
     * @return string|null
     */
    public function getFileUrlAttribute()
    {
        if (!$this->file) {
            return null;
        }

        return url(
            'DvrAttachments/' . $this->user_dvr_id . '/' . $this->file
        );
    }
}
