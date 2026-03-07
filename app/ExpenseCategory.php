<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'details', 'is_travel'];

    public function expenses()
    {
        return $this->hasMany('App\UserExpense', 'category_id');
    }
}