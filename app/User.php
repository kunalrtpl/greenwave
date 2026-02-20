<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use DB;
use App\UserDepartmentRegion;
use App\RegionCity;
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

    public static function getReportingUsers($userid){
        $reportToUserIds =  DB::table('user_departments')->where('user_id',$userid)->pluck('report_to');
        $reportToUserIds = array_unique(json_decode(json_encode($reportToUserIds),true));
        $report_to_users = User::wherein('id',$reportToUserIds)->select('id','name','mobile','email','correspondence_address','correspondence_address','emergency_contact_person','emergency_contact_person_mobile','designation')->get();

        $reportingFromUserIds =  DB::table('user_departments')->where('report_to',$userid)->pluck('user_id');
        $reportingFromUserIds = array_unique(json_decode(json_encode($reportingFromUserIds),true));
        $report_from_users = User::wherein('id',$reportingFromUserIds)->select('id','name','mobile','email','correspondence_address','correspondence_address','emergency_contact_person','emergency_contact_person_mobile','designation')->get();
        $incentives = UserIncentive::where('user_id',$userid)->orderby('user_incentives.start_date','DESC')->get();

        $subRegions = UserDepartmentRegion::where('user_id',$userid)->pluck('sub_region_id')->toArray();
        $cities = RegionCity::wherein('region_id',$subRegions)->pluck('city')->toArray();
        return array('report_to_users'=>$report_to_users,'report_from_users'=>$report_from_users,'incentives'=>$incentives,'cities'=>$cities,'sub_region_ids'=>$subRegions);
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
}
