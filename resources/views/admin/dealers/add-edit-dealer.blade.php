@extends('layouts.adminLayout.backendLayout')
@section('content')
 <?php $designationsArr = array('Owner','Manager') ?>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Dealers Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/dealers') }}">Dealers </a>
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
                        <form id="DealerForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Dealer Type <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $dealerTypeArr = array('dealer','sub dealer') ?>
                                        @foreach($dealerTypeArr as $dealerType)
                                            <label>
                                                <input type="radio" name="dealer_type" value="{{$dealerType}}" @if(!empty($dealerdata) && $dealerdata['dealer_type'] == $dealerType ) checked @endif />&nbsp;{{ucwords($dealerType)}}&nbsp;
                                            </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-dealer_type"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Business Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Business Name" name="business_name" style="color:gray" class="form-control" value="{{(!empty($dealerdata['business_name']))?$dealerdata['business_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-business_name"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Designation</label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Designation" name="designation" style="color:gray" class="form-control" value="{{(!empty($dealerdata['designation']))?$dealerdata['designation']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-designation"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Short Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Short Name" name="short_name" style="color:gray" class="form-control" value="{{(!empty($dealerdata['short_name']))?$dealerdata['short_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-short_name"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Address <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Address" name="address" style="color:gray" class="form-control" value="{{(!empty($dealerdata['address']))?$dealerdata['address']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-address"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">City <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="city">
                                            <option value="">Please Select</option>
                                            @foreach(getcities() as $city)
                                                <option value="{{$city['city_name']}}" {{(!empty($dealerdata) && $dealerdata['city'] == $city['city_name'])?'selected': '' }}>{{$city['city_name']}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-city"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">
                                        Area of Operations <span class="asteric">*</span>
                                    </label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="operating_cities[]" multiple required>
                                            @foreach(getcities() as $city)
                                                <option value="{{ $city['city_name'] }}"
                                                    {{ (!empty($operatingCities) && in_array($city['city_name'], $operatingCities)) ? 'selected' : '' }}>
                                                    {{ $city['city_name'] }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-operating_cities"></h4>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Office Phone (if any) </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Owner Mobile" name="office_phone" style="color:gray" class="form-control" value="{{(!empty($dealerdata['office_phone']))?$dealerdata['office_phone']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-office_phone"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">GST (No.) </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="GST (No.)" name="gst_no" style="color:gray" class="form-control" value="{{(!empty($dealerdata['gst_no']))?$dealerdata['gst_no']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-gst_no"></h4>
                                    </div>
                                </div>
                                <hr class="bold-hr">
                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label">Owner Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Owner Name" name="owner_name" style="color:gray" class="form-control" value="{{(!empty($dealerdata['owner_name']))?$dealerdata['owner_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-owner_name"></h4>
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Primary User Name  <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Primary User Name" name="name" style="color:gray" class="form-control" value="{{(!empty($dealerdata['name']))?$dealerdata['name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-name"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Registered Mobile <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Owner Mobile" name="owner_mobile" style="color:gray" class="form-control" value="{{(!empty($dealerdata['owner_mobile']))?$dealerdata['owner_mobile']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-owner_mobile"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Registered Email <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Owner Email" name="email" style="color:gray" class="form-control" value="{{(!empty($dealerdata['email']))?$dealerdata['email']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-email"></h4>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label">Password <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="password" placeholder="Password" name="password" style="color:gray" class="form-control"/>
                                        @if(!empty($dealerdata))
                                            <h5>Leave empty if don't want to update password</h5>
                                        @endif
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-password"></h4>
                                    </div>
                                </div> -->
                                <hr class="bold-hr">
                                <div id="dealer-type-fields">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Payment Term (in days) </label>
                                        <div class="col-md-4">
                                            <input type="text" placeholder="Payment Term" name="payment_term" style="color:gray" class="form-control" value="{{(!empty($dealerdata['payment_term']))?$dealerdata['payment_term']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-payment_term"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Security Amount (in Rs.) </label>
                                        <div class="col-md-4">
                                            <input type="text" placeholder="Security Amount" name="security_amount" style="color:gray" class="form-control" value="{{(!empty($dealerdata['security_amount']))?$dealerdata['security_amount']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-security_amount"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Interest Rate on Security (in %.) </label>
                                        <div class="col-md-4">
                                            <input type="text" placeholder="Interest Rate on Security" name="interest_rate_on_security" style="color:gray" class="form-control" value="{{(!empty($dealerdata['interest_rate_on_security']))?$dealerdata['interest_rate_on_security']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-interest_rate_on_security"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Credit Multiple </label>
                                        <div class="col-md-4">
                                            <input type="text" placeholder="Credit Multiple" name="credit_multiple" style="color:gray" class="form-control" value="{{(!empty($dealerdata['credit_multiple']))?$dealerdata['credit_multiple']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-credit_multiple"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Credit Allowed (in Rs.) </label>
                                        <div class="col-md-4">
                                            <input type="text" placeholder="Credit Allowed" name="credit_allowed" style="color:gray" class="form-control" value="{{(!empty($dealerdata['credit_allowed']))?$dealerdata['credit_allowed']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-credit_allowed"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Linked Products <b class="red">({{count($selLinkedProids)}})</b></label>
                                        <div class="col-md-9">
                                            <select class="form-control select2" name="linked_products[]" multiple="">
                                                @foreach(products() as $product)
                                                    <option value="{{$product['id']}}" @if(in_array($product['id'],$selLinkedProids)) selected @endif>{{$product['product_name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label">Freight to be compansated <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Freight to be compansated" name="freight" style="color:gray" class="form-control" value="{{(!empty($dealerdata['freight']))?$dealerdata['freight']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-freight"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Base sale margin lock <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $baseSaleArr = array('Applicable','Not Applicable') ?>
                                        @foreach($baseSaleArr as $baseSale)
                                            <label>
                                                <input type="radio" name="base_sale_margin_lock" value="{{$baseSale}}" @if(!empty($dealerdata) && $dealerdata['base_sale_margin_lock'] == $baseSale ) checked @endif />&nbsp;{{ucwords($baseSale)}}&nbsp;
                                            </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-base_sale_margin_lock"></h4>
                                    </div>
                                </div> 
                                <div id="BaseSaleApplicable" @if(!empty($dealerdata) && $dealerdata['base_sale_margin_lock'] =="Applicable" ) @else style="display: none;" @endif>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Base sale level to archive <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <input  type="text" placeholder="Base sale level to archive" name="base_sale_level_to_archive" style="color:gray" class="form-control" value="{{(!empty($dealerdata['base_sale_level_to_archive']))?$dealerdata['base_sale_level_to_archive']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-base_sale_level_to_archive"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Margin Lock (in %.) <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <input  type="text" placeholder="Margin Lock" name="margin_lock" style="color:gray" class="form-control" value="{{(!empty($dealerdata['margin_lock']))?$dealerdata['margin_lock']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-margin_lock"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Applicable From <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <div class="input-group input-append date datePicker">
                                                <input placeholder="YYYY-MM-DD"  type="text" name="applicable_from" style="color:gray" class="form-control datePicker" value="{{(!empty($dealerdata['applicable_from']))?$dealerdata['applicable_from']: '' }}" />
                                                <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
                                            </div>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-applicable_from"></h4>
                                        </div>     
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Applicable To <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <div class="input-group input-append date datePicker">
                                                <input placeholder="YYYY-MM-DD"  type="text" name="applicable_to" style="color:gray" class="form-control datePicker" value="{{(!empty($dealerdata['applicable_to']))?$dealerdata['applicable_to']: '' }}" />
                                                <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
                                            </div>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-applicable_to"></h4>
                                        </div>     
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Product Types <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="product_types[]" multiple="" required>
                                            @foreach(product_types() as $pkey=> $protype)
                                                <option value="{{$pkey}}" @if(in_array($pkey,$selProductTypes)) selected @endif >{{$protype}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label">Trader Products <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $taderStatusArr = array('1'=>'Yes','0'=>'No') ?>
                                        @foreach($taderStatusArr as $skey=> $trader_product)
                                            <label>
                                            <input type="radio" name="trader_product" value="{{$skey}}" @if(!empty($dealerdata) && $dealerdata['trader_product'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($trader_product)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="User-status"></h4>
                                    </div>
                                </div> -->
                                
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Linked Dealers</label>
                                    <?php $selDealers = array(); ?>
                                    @if(!empty($dealerdata) && !empty($dealerdata['linked_dealers']))
                                        <?php $selDealers = explode(',',$dealerdata['linked_dealers']); ?>
                                    @endif
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="linked_dealers[]" multiple="">
                                            @foreach($otherDealers as $dealer)
                                                <option value="{{$dealer['id']}}" @if(in_array($dealer['id'],$selDealers)) selected @endif >{{$dealer['business_name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if(!empty($linkedCustomers))
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Linked Customers <b class="red">({{count($linkedCustomers)}})</b> </label>
                                    <div class="col-md-6">
                                        <div class="panel-group" id="accordion-customer-module">
                                            <div class="panel panel-default">
                                                <div class="panel-heading text-center">
                                                    <h4 class="panel-title">
                                                        <a style="text-decoration:none;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-customer-module" href="#collapseCustomer">
                                                        </a>
                                                    </h4>
                                                </div>
                                                <div id="collapseCustomer" class="panel-collapse collapse">
                                                    <div class="panel-body">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th>Sr. No.</th>
                                                                <th>Name</th>
                                                            </tr>
                                                            @foreach($linkedCustomers as $key=> $customer)
                                                                <tr>
                                                                    <td>{{++$key}}</td>
                                                                   <td>
                                                                       {{$customer['name']}}
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
                                @endif
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Modules to Access? <b class="red">({{count($selAppRoles)}})</b> </label>
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
                                                            @foreach(app_roles('dealer') as $pkey => $role)
                                                                @php
                                                                    $hideRole51 = $role['id'] == 51 && (empty($dealerdata['id']) || $dealerdata['id'] != 3);
                                                                @endphp
                                                                @if(!$hideRole51)
                                                                    <tr id="role-row-{{ $role['id'] }}">
                                                                        <td>{{ ++$pkey }}</td>
                                                                        <td>{{ $role['name_admin'] }}</td> 
                                                                        <td>
                                                                            <input type="checkbox" name="app_roles[]" value="{{ $role['key'] }}" @if(in_array($role['key'], $selAppRoles)) checked @endif>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Show Class </label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="show_class">
                                            <option value="">Please Select</option>
                                            @foreach(classes() as $pkey=> $showclass)
                                                <option value="{{$showclass}}" @if(empty($dealerdata) ) @if($pkey==1) selected @endif @else @if($dealerdata['show_class'] ==$showclass) selected @endif @endif>{{$showclass}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($dealerdata) && $dealerdata['status'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="User-status"></h4>
                                    </div>
                                </div>
                                <?php /*
                                @include('admin.dealers.contact-persons')  */?>
                            </div>
                            @if(!empty($dealerdata['id']))
                                <input type="hidden" name="dealerid" value="{{$dealerdata['id']}}">
                            @else
                                <input type="hidden" name="dealerid" value="">
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
<table class="table table-hover table-bordered table-striped assignsamplerow" style="display:none;">
    <tbody>
        <tr class="appenderTr blockIdWrap">
            <td>
                <select class="form-control" name="designations[]" required>
                <option value="">Please Select</option>
                    @foreach($designationsArr as $designation)
                        <option value="{{$designation}}">{{$designation}}</option>
                   @endforeach
                </select>
            </td>
            <td>
                <input type="text" placeholder="Name" name="names[]" class="form-control" required>
            </td>
            <td>
                <input type="number" placeholder="Mobile" name="mobiles[]" class="form-control" required>
            </td>
            <td>
                <input type="email" placeholder="Email" name="emails[]" class="form-control" required>
            </td>
            <td>
                <a title="Remove" class="btn btn-sm red AssignRowRemove" href="javascript:;"> <i class="fa fa-times"></i></a>
            </td>
        </tr>
    </tbody>
</table>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('keyup','[name=credit_multiple]', function() {
            var securityAmt = $('[name=security_amount]').val();
            var creditAllowed = securityAmt * $(this).val();
            $('[name=credit_allowed]').val(creditAllowed);
        });

        $(document).on('keyup','[name=security_amount]', function() {
            var creditMultiple = $('[name=credit_multiple]').val();
            var creditAllowed = creditMultiple * $(this).val();
            $('[name=credit_allowed]').val(creditAllowed);
        });


        $(document).on('change','[name=base_sale_margin_lock]',function(){
            var value = $(this).val();
            if(value=="Applicable"){
                $('#BaseSaleApplicable').show();
            }else{
                $('#BaseSaleApplicable').hide();
                $('[name=applicable_from]').val('');
                $('[name=applicable_to]').val('');
                $('[name=base_sale_level_to_archive]').val('');
                $('[name=margin_lock]').val('');
            }
        })

        $("#DealerForm").submit(function(e){
            $('.loadingDiv').show();
            e.preventDefault();
            var formdata = new FormData(this);
            $.ajax({
                url: '/admin/save-dealer',
                type:'POST',
                data: formdata,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('.loadingDiv').hide();
                    if(!data.status){
                        $.each(data.errors, function (i, error) {
                            $('#Dealer-'+i).addClass('error-triggered');
                            $('#Dealer-'+i).attr('style', '');
                            $('#Dealer-'+i).html(error);
                            setTimeout(function () {
                                $('#Dealer-'+i).css({
                                    'display': 'none'
                                });
                            $('#Dealer-'+i).removeClass('error-triggered');
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
<script type="text/javascript">
    var rowid = 1;
    jQuery("#addAssignRow").click(function() {        
        var row = jQuery('.assignsamplerow tr').clone(true);
        row.appendTo('#ProductSearchRow'); 
    });
    $('.AssignRowRemove').on("click", function() {
        $(this).parents("tr").remove();
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
<script>
    function toggleDealerFields() {
        const selectedType = document.querySelector('input[name="dealer_type"]:checked')?.value;
        const fieldsDiv = document.getElementById('dealer-type-fields');

        // Show/hide dealer-specific financial fields
        if (selectedType === 'sub dealer') {
            fieldsDiv.style.display = 'none';
        } else {
            fieldsDiv.style.display = 'block';
        }

        // List of role IDs to hide/uncheck for sub dealer
        const restrictedRoleIds = [28, 32, 33, 34, 35, 40, 50, 52];

        restrictedRoleIds.forEach(function(id) {
            const row = document.getElementById('role-row-' + id);
            if (row) {
                const checkbox = row.querySelector('input[type="checkbox"]');

                if (selectedType === 'sub dealer') {
                    // Hide row and uncheck checkbox
                    row.style.display = 'none';
                    if (checkbox) checkbox.checked = false;
                } else {
                    // Show row
                    row.style.display = 'table-row';
                }
            }
        });
    }

    // Run on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleDealerFields();
        document.querySelectorAll('input[name="dealer_type"]').forEach(function(el) {
            el.addEventListener('change', toggleDealerFields);
        });
    });
</script>



@endsection