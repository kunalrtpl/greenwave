<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;
use DB;
use Session;
use App\QuickEnquiry;
use App\DealershipEnquiry;
use App\JobEnquiry;
use Validator;
class EnquiryController extends Controller
{
    //
    public function quickEnquiries(Request $Request){
        Session::put('active','quickEnquiries'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = QuickEnquiry::query();
            /*if(!empty($data['state_name'])){
                $querys = $querys->where('state_name','like','%'.$data['state_name'].'%');
            }*/
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('quick_enquiries.id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $enquiry){
                $actionValues='';
                $num = ++$i;
                $records["data"][] = array(      
                    $enquiry['id'],
                    $enquiry['name'],
                    $enquiry['email'],  
                    $enquiry['phone'],  
                    $enquiry['message'],  
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Quick Enquiries";
        return View::make('admin.enquiries.quick-enquiry')->with(compact('title'));
    }

    public function dealershipEnquiries(Request $Request){
        Session::put('active','dealershipEnquiries'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = DealershipEnquiry::query();
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $enquiry){
                $actionValues='';
                $num = ++$i;
                $records["data"][] = array(      
                    $enquiry['id'],
                    $enquiry['business_name'],
                    $enquiry['city'],  
                    $enquiry['contact_person'],  
                    $enquiry['email'],  
                    $enquiry['phone'],  
                    $enquiry['message'],  
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Dealership Enquiries";
        return View::make('admin.enquiries.dealership-enquiry')->with(compact('title'));
    }

    public function jobEnquiries(Request $Request){
        Session::put('active','jobEnquiries'); 
        if($Request->ajax()){
            $conditions = array();
            $data = $Request->input();
            $querys = JobEnquiry::query();
            $iDisplayLength = intval($_REQUEST['length']);
            $iDisplayStart = intval($_REQUEST['start']);
            $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength; 
            $iTotalRecords = $querys->where($conditions)->count();
            $querys =  $querys->where($conditions)
                		->skip($iDisplayStart)->take($iDisplayLength)
                		->OrderBy('id','DESC')
                		->get();
            $sEcho = intval($_REQUEST['draw']);
            $records = array();
            $records["data"] = array(); 
            $end = $iDisplayStart + $iDisplayLength;
            $end = $end > $iTotalRecords ? $iTotalRecords : $end;
            $i=$iDisplayStart;
            $querys=json_decode( json_encode($querys), true);
            foreach($querys as $enquiry){
                $actionValues='';
                $num = ++$i;
                $records["data"][] = array(      
                    $enquiry['id'],
                    $enquiry['name'],
                    $enquiry['currently_working'],  
                    $enquiry['placed_at'],  
                    $enquiry['email'],  
                    $enquiry['phone'],  
                    $enquiry['message'],  
                    $actionValues
                );
            }
            $records["draw"] = $sEcho;
            $records["recordsTotal"] = $iTotalRecords;
            $records["recordsFiltered"] = $iTotalRecords;
            return response()->json($records);
        }
        $title = "Job Enquiries";
        return View::make('admin.enquiries.job-enquiry')->with(compact('title'));
    }
}
