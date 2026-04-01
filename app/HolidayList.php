<?php
// App/HolidayList.php
namespace App;

use Illuminate\Database\Eloquent\Model;

class HolidayList extends Model
{
    protected $fillable = [
        'name', 'date', 'city', 'is_national',
        'type', 'description', 'is_active'
    ];

    protected $casts = [
        'is_national' => 'boolean',
        'is_active'   => 'boolean',
    ];
}