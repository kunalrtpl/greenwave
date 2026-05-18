<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFaceImage extends Model
{
    protected $table = 'user_face_images';
    protected $fillable = ['user_id', 'angle', 'file_path'];
}