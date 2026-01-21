<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\UserDvr;
use App\User;

/**
 * Class UserDvrController
 *
 * Handles Admin side DVR listing & detail view
 */
class UserDvrController extends Controller
{
    /**
     * DVR Listing Page
     *
     * Filters:
     * - Employee
     * - Month
     * - Year
     *
     * Uses withCount('trials') to avoid ambiguous SQL
     */
    public function index(Request $request)
    {
        $query = UserDvr::with([
            'user:id,name',
            'customer:id,name',
            'customer_register_request:id,name',
            'customer_contact_info:id,name,mobile_number',
            'products.productinfo:id,product_name',
        ])
        ->withCount('trials')
        ->orderBy('dvr_date', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('dvr_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('dvr_date', $request->year);
        }

        $dvrs = $query->paginate(10)->appends($request->all());

        $users = User::whereIn(
            'id',
            UserDvr::select('user_id')->distinct()
        )->orderBy('name')->get(['id','name']);

        $title = 'Dvrs';

        return view('admin.dvrs.index', compact('dvrs','users','title'));
    }


    /**
     * DVR Detail Page
     */
    public function show($id)
    {
        $dvr = UserDvr::with([
            'user',
            'customer',
            'customer_register_request',
            'customerContacts.customerContact',
            'attachments',
            'products.productinfo',
            'trials',
            'complaint_sample',
            'market_sample',
            'sample_submission',
            'user_scheduler',
            'query_info'
        ])->findOrFail($id);

        return view('admin.dvrs.show', compact('dvr'));
    }
}
