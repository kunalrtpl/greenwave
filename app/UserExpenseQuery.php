<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserExpenseQuery extends Model
{
    protected $table = 'user_expenses_queries';

    protected $fillable = [
        'expense_id',
        'sender_id',
        'sender_type',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ---- Relationships ---- */

    public function expense()
    {
        return $this->belongsTo(\App\UserExpense::class, 'expense_id');
    }

    public function sender()
    {
        return $this->belongsTo(\App\User::class, 'sender_id');
    }
}