<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait SharedCustomerTrait
{
    /**
     * Scope a query to include records for the user or their shared customers.
     * Use this manually by calling ->forUserSharedData($userId)
     */
    public function scopeForUserSharedData($query, $userId)
    {
        // Fetch shared customer IDs
        $sharedCustomerIds = DB::table('user_customer_shares')
            ->where('user_id', $userId)
            ->pluck('customer_id')
            ->toArray();
            
        // Apply the OR condition logic
        return $query->where(function ($q) use ($sharedCustomerIds, $userId) {
            $q->whereIn('customer_id', $sharedCustomerIds)
              ->orWhere('user_id', $userId);
        });
    }
}