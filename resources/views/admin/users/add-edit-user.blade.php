@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Employees Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/users') }}">Employees </a>
            </li>
        </ul>
        <div class="row">
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form id="Employeeform" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Name" name="name" style="color:gray" class="form-control" value="{{(!empty($empdata['name']))?$empdata['name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-name"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Mobile <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Mobile" name="mobile" style="color:gray" class="form-control" value="{{(!empty($empdata['mobile']))?$empdata['mobile']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-mobile"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Email <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Email" name="email" style="color:gray" class="form-control" value="{{(!empty($empdata['email']))?$empdata['email']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-email"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Designation <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Designation" name="designation" style="color:gray" class="form-control" value="{{(!empty($empdata['designation']))?$empdata['designation']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-designation"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">DOB</label>
                                    <div class="col-md-4">
                                        <div class="input-group input-append date datePicker">
                                            <input placeholder="YYYY-MM-DD"  type="text" name="dob" style="color:gray" class="form-control datePicker" value="{{(!empty($empdata['dob']))?$empdata['dob']: '' }}" />
                                            <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
                                        </div>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-dob"></h4>
                                    </div>     
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Gender </label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $genderArr = array('Male','Female') ?>
                                        @foreach($genderArr as $gender)
                                            <label>
                                                <input type="radio" name="gender" value="{{$gender}}" @if(!empty($empdata) && $empdata['gender'] == $gender ) checked @endif />&nbsp;{{ucwords($gender)}}&nbsp;
                                            </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-gender"></h4>
                                    </div>
                                </div> 
                                
                                <div class="form-group ">
                                    <label class="col-md-3 control-label">Profile Photo </label>
                                    <div class="col-md-4">
                                        <div data-provides="fileinput" class="fileinput fileinput-new">
                                            <div style="" class="fileinput-new thumbnail">
                                            <?php if(!empty($empdata['image'])){
                                                $path = "images/AdminImages/".$empdata['image']; 
                                            if(file_exists($path)) { ?>
                                                <img style="height:100px;widtyh:100px;" class="img-responsive"  src="{{ asset('images/AdminImages/'.$empdata['image'])}}">
                                            <?php }else{?>
                                                    <img style="height:100px;widtyh:100px;" class="img-responsive"  src="{{ asset('images/default.png') }}">
                                            <?php } } else { ?>
                                            <img style="height:100px;widtyh:100px;" class="img-responsive"  src="{{ asset('images/default.png') }}">
                                            <?php } ?>
                                        </div>
                                            <div style="max-width: 200px; max-height: 150px; line-height: 10px;" class="fileinput-preview fileinput-exists thumbnail">
                                            </div>
                                            <div>
                                                <div class="form-group">
                                                    <span class="btn default btn-file">
                                                    <span class="fileinput-new">
                                                    Select Image </span>
                                                    <span class="fileinput-exists">
                                                    Select Image </span>
                                                    <input type="file" id="Image" name="image">
                                                    </span>
                                                    <a data-dismiss="fileinput" class="btn default fileinput-exists" href="#">
                                                    Remove </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Home Landline No. </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Home Landline No" name="home_landline_no" style="color:gray" class="form-control" value="{{(!empty($empdata['home_landline_no']))?$empdata['home_landline_no']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-home_landline_no"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Emergency Contact Person Name </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Emergency Contact Person Name" name="emergency_contact_person" style="color:gray" class="form-control" value="{{(!empty($empdata['emergency_contact_person']))?$empdata['emergency_contact_person']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-emergency_contact_person"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Emergency Contact Person Mob. </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Emergency Contact Person Mobile" name="emergency_contact_person_mobile" style="color:gray" class="form-control" value="{{(!empty($empdata['emergency_contact_person_mobile']))?$empdata['emergency_contact_person_mobile']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-emergency_contact_person_mobile"></h4>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Correspondence Address</label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Correspondence Address" name="correspondence_address" style="color:gray" class="form-control" value="{{(!empty($empdata['correspondence_address']))?$empdata['correspondence_address']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-correspondence_address"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Permanent Address</label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Permanent Address" name="permanent_address" style="color:gray" class="form-control" value="{{(!empty($empdata['permanent_address']))?$empdata['permanent_address']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-permanent_address"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">PAN </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="PAN" name="pan" style="color:gray" class="form-control" value="{{(!empty($empdata['pan']))?$empdata['pan']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-pan"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">PAN Proof</label>
                                    <div class="col-md-4">
                                        <input  type="file" name="pan_proof" style="color:gray" class="form-control"/>
                                        @if(!empty($empdata['pan_proof']))
                                            <a target="_blank" href="{{url('/images/UserProofs/'.$empdata['pan_proof'])}}">View Proof</a>
                                        @endif
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-pan_proof"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Driving License </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Driving License" name="driving_license" style="color:gray" class="form-control" value="{{(!empty($empdata['driving_license']))?$empdata['driving_license']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-driving_license"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Driving License Proof</label>
                                    <div class="col-md-4">
                                        <input  type="file" name="driving_license_proof" style="color:gray" class="form-control"/>
                                        @if(!empty($empdata['driving_license_proof']))
                                            <a target="_blank" href="{{url('/images/UserProofs/'.$empdata['driving_license_proof'])}}">View Proof</a>
                                        @endif
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-driving_license_proof"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Aadhar No. </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Aadhar" name="aadhar" style="color:gray" class="form-control" value="{{(!empty($empdata['aadhar']))?$empdata['aadhar']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-aadhar"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Aadhar Proof</label>
                                    <div class="col-md-4">
                                        <input  type="file" name="aadhar_proof" style="color:gray" class="form-control"/>
                                        @if(!empty($empdata['aadhar_proof']))
                                            <a target="_blank" href="{{url('/images/UserProofs/'.$empdata['aadhar_proof'])}}">View Proof</a>
                                        @endif
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-aadhar_proof"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Joining Date</label>
                                    <div class="col-md-4">
                                        <div class="input-group input-append date datePicker">
                                            <input placeholder="YYYY-MM-DD"  type="text" name="joining_date" style="color:gray" class="form-control datePicker" value="{{(!empty($empdata['joining_date']))?$empdata['joining_date']: '' }}" />
                                            <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
                                        </div>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-joining_date"></h4>
                                    </div>     
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Probation Period</label>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="probation_period" placeholder="(in days)">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-joining_date"></h4>
                                    </div>     
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Salary Account No. </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Salary Account No." name="salary_account_no" style="color:gray" class="form-control" value="{{(!empty($empdata['salary_account_no']))?$empdata['salary_account_no']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-salary_account_no"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($empdata) && $empdata['status'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="User-status"></h4>
                                    </div>
                                </div>
                                <hr class="bold-hr">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">User Departments</label>
                                    <div class="col-md-9" style="margin-top:8px;">
                                        <table class="table table-bordered table-dark">
                                            <thead>
                                                <tr>
                                                    <th  scope="col">Department</th>
                                                    <!-- <th  scope="col">Designation</th> -->
                                                    <th  scope="col">Report To</th>
                                                    <!-- <th  scope="col">Products</th> -->
                                                    <!-- <th  scope="col">Regions</th> -->
                                                    <th  scope="col">Regions</th>
                                                    <th width="25%"  scope="col">Cities</th>
                                                    <!-- <th  scope="col"> Customers</th> -->
                                                    <th  scope="col">Actions</th>
                                                </tr>
                                            </thead>
<tbody id="UserDepts">
    @if(!empty($empdata) && !empty($empdata['departments']))
        @foreach($empdata['departments'] as $departmentinfo)
            <?php 

                //$requestArr['designation_id'] = $departmentinfo['designation_id'];
                $requestArr['department'] = $departmentinfo['department_id'];
                $requestArr['report_to'] = $departmentinfo['report_to'];
            $subregions =array();
            $products = array();
            ?> 
            @if(!empty($departmentinfo['subregions']))
                <?php $subregions = array_column($departmentinfo['subregions'],'sub_region_id');
                
                ?>
            @endif
            @if(!empty($departmentinfo['products']))
                <?php $products = array_column($departmentinfo['products'],'product_id');
                ?>
            @endif
            <?php 
            $requestArr['subregions'] =$subregions; 
            $requestArr['products'] =$products;  
            $requestArr['customers'] =  \App\UserDepartment::custids($departmentinfo['designation_id'],$empdata['id']);
                $resp = \App\UserDepartment::userdeptinfo($requestArr);
                /*$designationInfo = $resp['designationInfo'];*/
                $departmentInfo = $resp['departmentInfo'];
                $reportToInfo = $resp['reportToInfo'];
                $subRegions = $resp['subRegions'];
                $products = $resp['products'];
                $customers = $resp['customers'];
            ?>
            @include('admin.users.user-dept-list')
        @endforeach
    @endif
</tbody>
                                        </table>
                                    </div>
                                </div>
                                <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-user_depts"></h4>  
                                <!-- Button -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label"></label>
                                    <div class="col-md-3">
                                        <button type="button" id="addUserDept" class="btn btn-primary"><i class="fa fa-plus"></i> Add User Department</button>
                                    </div>
                                </div> 
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label"> Product Types</label>
                                <div class="col-md-4">
                                    <select class="form-control select2" name="product_types[]" multiple="" required>
                                        @foreach(product_types() as $pkey=> $protype)
                                            <option value="{{$pkey}}" @if(in_array($pkey,$selProductTypes)) selected @endif >{{$protype}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if(isset($empdata['shares']) && !empty($empdata['shares']))
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Linked Customers <b class="red">({{count($empdata['shares'])}})</b> </label>
                                    <div class="col-md-5">
                                        <div class="panel-group" id="accordion">
                                          <div class="panel panel-default">
                                            <div class="panel-heading text-center">
                                              <h4 class="panel-title">
                                                <a style="text-decoration:none;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                                </a>
                                              </h4>
                                            </div>
                                            <div id="collapseOne" class="panel-collapse collapse">
                                              <div class="panel-body">
                                                <div class="col-md-12" style="margin-top:8px;">
                                                    <table class="table table-stripped table-bordered">
                                                        <tr>
                                                            <td>Sr. No.</td>
                                                            <td>Customer</td>
                                                            <!-- <td>Responsibility (%)</td> -->
                                                        </tr>
                                                        @foreach($empdata['shares'] as $skey=> $share)
                                                            <tr>
                                                                <td>{{++$skey}}</td>
                                                                <td>{{$share['customer']['name']}}</td>
                                                                <!-- <td>
                                                                    {{$share['share']}}%
                                                                </td> -->
                                                            </tr> 
                                                        @endforeach 
                                                    </table>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                         
                                          </div>
                                    </div>
                                </div>
                            @endif
                            <hr class="bold-hr">
                            <div class="form-group">
                                <label class="col-md-3 control-label"> Web Access</label>
                                <div class="col-md-4">
                                    <select class="form-control" name="web_access" required>
                                        <option value="">Please Select</option>
                                        @foreach(classes() as $key=> $webAccess)
                                            <option value="{{$webAccess}}" @if(empty($empdata) ) @if($key==1) selected @endif @else @if($empdata['web_access'] ==$webAccess) selected @endif @endif>{{$webAccess}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" id="web-password-group" style="display: none;">
                                <label class="col-md-3 control-label">Web Password</label>
                                <div class="col-md-4">
                                    <input type="password" placeholder="Password" name="password" style="color:gray" class="form-control"/>
                                    @if(!empty($empdata))
                                        <h5>Leave empty if you don't want to update password</h5>
                                    @endif
                                    <h4 class="text-center text-danger pt-3" style="display: none;" id="Employee-password"></h4>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-3 control-label"> App Access</label>
                                <div class="col-md-4">
                                    <select class="form-control" name="app_access" required>
                                        <option value="">Please Select</option>
                                        @foreach(classes() as $key=> $appAccess)
                                            <option value="{{$appAccess}}" @if(empty($empdata) ) @if($key==1) selected @endif @else @if($empdata['app_access'] ==$appAccess) selected @endif @endif>{{$appAccess}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            
                            <span id="AppAccessArea" @if(isset($empdata['app_access']) && $empdata['app_access'] =="Yes") @else style="display:none;" @endif>
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Show Weightage <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="show_weightage">
                                            <option value="">Please Select</option>
                                            @foreach(classes() as $form=> $showWeightage)
                                                <option value="{{$showWeightage}}" @if(empty($empdata) ) @if($pkey==1) selected @endif @else @if($empdata['show_weightage'] ==$showWeightage) selected @endif @endif>{{$showWeightage}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Show Class <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="show_class">
                                            <option value="">Please Select</option>
                                            @foreach(classes() as $pkey=> $showclass)
                                                <option value="{{$showclass}}" @if(empty($empdata) ) @if($pkey==1) selected @endif @else @if($empdata['show_class'] ==$showclass) selected @endif @endif>{{$showclass}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Modules to Access? <b class="red">({{count($selAppRoles)}})</b> <span class="asteric">*</span></label>
                                    <div class="col-md-6">
                                        <div class="panel-group" id="accordion-module">
                                            <div class="panel panel-default">
                                                <div class="panel-heading text-center">
                                                    <h4 class="panel-title">
                                                        <a style="text-decoration:none;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-module" href="#collapseTwo">
                                                        </a>
                                                    </h4>
                                                </div>
                                                <div id="collapseTwo" class="panel-collapse collapse">
                                                    <div class="panel-body">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>Sr. No.</th>
                                                                <th>Role</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                            @foreach(app_roles('executive') as $pkey=> $role)
                                                                <tr>
                                                                    <td>{{++$pkey}}</td>
                                                                   <td>
                                                                       {{$role['name_admin']}}
                                                                   </td> 
                                                                   <td>
                                                                       <input type="checkbox" name="app_roles[]" value="{{$role['key']}}" @if(in_array($role['key'],$selAppRoles)) checked @endif>
                                                                   </td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                       
                                    </div>
                                </div>
                            </span>
                            @if(!empty($empdata['id']))
                                <input type="hidden" name="employeeid" value="{{$empdata['id']}}">
                            @else
                                <input type="hidden" name="employeeid" value="">
                            @endif
                            <div class="form-actions right1 text-center">
                                <button class="btn green" type="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.users.user-dept-modal')
<script type="text/javascript">
    $(document).ready(function(){

        $('input[name="correspondence_address"]').on('keyup', function() {
            var correspondenceAddress = $(this).val();
            $('input[name="permanent_address"]').val(correspondenceAddress);
        });
        
        $(document).on('click','#addUserDept',function(){
            $.ajax({
                url : '/admin/open-user-dept-modal',
                type : 'GET',
                success:function(){
                    $('#UserDeptModal').modal('show');
                    refreshSelect2();
                }                
            })
        });

        $('#UserDeptModal').on('hidden.bs.modal', function () {
            $('#DesignationDetails').html('');
            $("[name=designation_id]").prop("selectedIndex", 0);
            $("[name=department_id]").prop("selectedIndex", 0);
        });
        //not using anymore
        $(document).on('change','[name=designation_id]',function(){
            var designation = $(this).val();
            var empid = $('[name=employeeid]').val();
            if(designation ==""){
                $('#DesignationDetails').html('');
            }else{
                $('.loadingDiv').show();
                $.ajax({
                    data : {designation :designation,empid:empid},
                    url  : '/admin/append-designation-info',
                    type : 'POST',
                    success:function(resp){
                        $('#DesignationDetails').html(resp.view);
                        $('.loadingDiv').hide();
                        refreshSelect2();
                    },
                    error:function(){

                    }
                })
            }
        });

        $(document).on('change','[name=department_id]',function(){
            var department = $(this).val();
            var empid = $('[name=employeeid]').val();
            if(department ==""){
                $('#DesignationDetails').html('');
            }else{
                $('.loadingDiv').show();
                $.ajax({
                    data : {department :department,empid:empid},
                    url  : '/admin/append-designation-info',
                    type : 'POST',
                    success:function(resp){
                        $('#DesignationDetails').html(resp.view);
                        $('.loadingDiv').hide();
                        refreshSelect2();
                    },
                    error:function(){

                    }
                })
            }
        });

        $(document).on('change','.getRegions',function(){
            $('.loadingDiv').show();
            var regions = $(this).val();
            $.ajax({
                data : {regions:regions},
                url : '/admin/get-sub-regions',
                type : 'POST',
                success:function(resp){
                    $('.subRegions').html(resp);
                    $('.getCustomers').html('');
                    $('.loadingDiv').hide();
                },
                error:function(){

                }
            });
        });

        $(document).on('change','.fetchCustomers',function(){
            $('.loadingDiv').show();
            var subRegions = $(this).val();
            $.ajax({
                data : {subRegions:subRegions},
                url : '/admin/append-customers',
                type : 'POST',
                success:function(resp){
                    $('.getCustomers').html(resp);
                    $('.loadingDiv').hide();
                },
                error:function(){

                }
            });
        })

        $(document).on('change','[name=joining_type]',function(){
            var type = $(this).val();
            if(type=="Permanent"){
                $('#PermanentFromDiv').show();
            }else{
                $('#PermanentFromDiv').hide();
                $('[name=permanent_from]').val('');
            }
        });

        $(document).on('change','[name=app_access]',function(){
            var type = $(this).val();
            $('#AppAccessArea').hide();
            if(type=="Yes"){
                $('#AppAccessArea').show();
            }
        });

        $('#AddUserDeptDesgForm').submit(function(e){
            $('.loadingDiv').show();
            e.preventDefault();
            var formdata = new FormData(this);
            $.ajax({
                data : formdata,
                type : 'POST',
                url  : '/admin/add-user-dept-designation',
                processData: false,
                contentType: false,
                success:function(resp){
                    $('#UserDepts').append(resp.view);
                    $('#UserDeptModal').modal('hide');
                    refreshSelect2();
                    $('.loadingDiv').hide();
                },
                error:function(){

                }
            });
        })

        $(document).on('click','#SelectAllRegion',function(){
            if($("#SelectAllRegion").is(':checked') ){
                $(".getRegions > option").prop("selected","selected");
                $(".getRegions").trigger("change");
            }else{
                $(".getRegions > option").removeAttr("selected");
                $(".getRegions").trigger("change");
             }
        });

        $(document).on('click','#SelectAllSubRegion',function(){
            if($("#SelectAllSubRegion").is(':checked') ){
                $(".subRegions > option").prop("selected","selected");
                $(".subRegions").trigger("change");
            }else{
                $(".subRegions > option").removeAttr("selected");
                $(".subRegions").trigger("change");
             }
        });

        $(document).on('click', 'button.removeRow', function () {
            if (confirm("Are you sure you want to delete this?")) {
                $(this).closest('tr').remove();
                return false;
            }
            return false;
        });

        $("#Employeeform").submit(function(e){
            $('.loadingDiv').show();
            e.preventDefault();
            var formdata = new FormData(this);
            $.ajax({
                url: '/admin/save-user',
                type:'POST',
                data: formdata,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('.loadingDiv').hide();
                    if(!data.status){
                        $.each(data.errors, function (i, error) {
                            $('#Employee-'+i).addClass('error-triggered');
                            $('#Employee-'+i).attr('style', '');
                            $('#Employee-'+i).html(error);
                            setTimeout(function () {
                                $('#Employee-'+i).css({
                                    'display': 'none'
                                });
                            $('#Employee-'+i).removeClass('error-triggered');
                            }, 5000);
                        });
                        $('html,body').animate({
                            scrollTop: $('.error-triggered').first().stop().offset().top - 200
                        }, 1000);
                    }else{
                        window.location.href= data.url;
                    }
                }
            });
        });
    })
</script>
<script>
    $(document).ready(function () {
        function togglePasswordField() {
            var access = $('[name="web_access"]').val();
            if (access.toLowerCase() === 'yes') {
                $('#web-password-group').show();
            } else {
                $('#web-password-group').hide();
            }
        }

        // Trigger on page load
        togglePasswordField();

        // Trigger on dropdown change
        $('[name="web_access"]').on('change', function () {
            togglePasswordField();
        });
    });
</script>

<style>
    .panel-heading .accordion-toggle:after {
    /* symbol for "opening" panels */
    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
    content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    float: left;        /* adjust as needed */
    color: #4a8c17 !important;         /* adjust as needed */
}
.panel-heading .accordion-toggle.collapsed:after {
    /* symbol for "collapsed" panels */
    content: "\e080";    /* adjust as needed, taken from bootstrap.css */
}
.panel-default>.panel-heading {
    background-color: transparent !important;
    height: 40px;
}
.panel-heading .accordion-toggle:after
{
    color:#fff;
}
.panel-title>a:hover
{
    color:#fff;
}

</style>
@endsection