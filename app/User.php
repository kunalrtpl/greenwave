<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use DB;
use App\UserDepartmentRegion;
use App\RegionCity;
use App\Product;
class User extends Authenticatable
{
    use Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'first_name','last_name','email', 'password','company_name','notification_token','status','register_device','login_device','alerts','product_types'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'hash_salt' => 'array'
    ];

    public function departments(){
        return $this->hasMany('App\UserDepartment')->with(['subregions','products']);
    }

    public function customers(){
        return $this->belongsToMany('App\UserCustomer','user_customers','user_id','customer_id');
    }

    public function linked_customers(){
        return $this->hasMany('App\UserCustomer');
    }

    public function shares(){
        return $this->hasMany('App\UserCustomerShare')->join('customers','customers.id','=','user_customer_shares.customer_id')->select('user_customer_shares.*')->orderby('customers.name','ASC')->with('customer');
    }

    public static function getReportingUsers($userid)
    {
        // Single query to get both report_to and report_from user IDs
        $userDepartments = DB::table('user_departments')
            ->where('user_id', $userid)
            ->orWhere('report_to', $userid)
            ->select('user_id', 'report_to')
            ->get();

        // Separate and deduplicate IDs using collections (Laravel 5.8 compatible)
        $reportToUserIds = $userDepartments
            ->where('user_id', $userid)
            ->pluck('report_to')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $reportingFromUserIds = $userDepartments
            ->where('report_to', $userid)
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Common select columns
        $userSelect = ['id', 'name', 'mobile', 'email', 'correspondence_address',
                       'emergency_contact_person', 'emergency_contact_person_mobile', 'designation'];

        // Fetch both user sets — only query if IDs exist
        $report_to_users = !empty($reportToUserIds)
            ? User::whereIn('id', $reportToUserIds)->select($userSelect)->get()
            : collect();

        $report_from_users = !empty($reportingFromUserIds)
            ? User::whereIn('id', $reportingFromUserIds)->select($userSelect)->get()
            : collect();

        // Fetch incentives
        $incentives = UserIncentive::where('user_id', $userid)
            ->orderBy('start_date', 'DESC')
            ->get();

        // Fetch sub-regions and cities
        $subRegions = UserDepartmentRegion::where('user_id', $userid)
            ->pluck('sub_region_id')
            ->filter()
            ->unique()
            ->toArray();

        $cities = !empty($subRegions)
            ? RegionCity::whereIn('region_id', $subRegions)->pluck('city')->toArray()
            : [];

        return [
            'report_to_users'  => $report_to_users,
            'report_from_users' => $report_from_users,
            'incentives'       => $incentives,
            'cities'           => $cities,
            'sub_region_ids'   => $subRegions,
        ];
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'user_products',
            'user_id',
            'product_id'
        );
    }

    /**
     * Base query with eager loading and active filter (no user scope).
     */
    private static function baseProductQuery()
    {
        return Product::with([
                'productpacking',
                'pricings',
                'product_stages',
                'product_weightages'
            ])
            ->where('products.is_trader_product', 0)
            ->where('products.status', 1);
    }

    /**
     * Attach class names to pricings in memory.
     */
    private static function attachClassToProducts($products)
    {
        $allClasses = \App\ProductClass::where('status', 1)->get();

        foreach ($products as $product) {
            foreach ($product->pricings as $pricing) {
                $markup = $pricing->dealer_markup;
                $matchedClass = $allClasses->first(function ($class) use ($markup) {
                    return $markup >= $class->from && $markup <= $class->to;
                });
                $pricing->class = $matchedClass ? $matchedClass->class_name : '';
            }
        }

        return $products;
    }

    /**
     * All active products (no user filter).
     */
    public static function fetchAllProducts()
    {
        $products = self::baseProductQuery()->get();

        return self::attachClassToProducts($products);
    }

    /**
     * Products linked to a specific user (existing behavior, untouched signature).
     */
    public static function fetchUserProducts($userId)
    {
        $products = self::baseProductQuery()
            ->select('products.*')
            ->join('user_products', 'user_products.product_id', '=', 'products.id')
            ->where('user_products.user_id', $userId)
            ->get();

        return self::attachClassToProducts($products);
    }
}
