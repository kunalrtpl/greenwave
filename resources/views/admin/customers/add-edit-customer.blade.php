@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Customers Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/customers') }}">Customers </a>
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
                        <form id="Customerform" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            @if(isset($_GET['ref']))
                                <input type="hidden" name="register_request_id" value="{{$_GET['ref']}}">
                            @endif
                            @if(isset($_GET['empref']))
                                <input type="hidden" name="customer_register_request_id" value="{{$_GET['empref']}}">
                            @endif
                            <div class="form-body">
                                <p class="highlight-label">Business Details</p>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Business Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Name" name="name" style="color:gray" class="form-control" value="{{(!empty($customerdata['name']))?$customerdata['name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-name"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Address </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Address" name="address" style="color:gray" class="form-control" value="{{(!empty($customerdata['address']))?$customerdata['address']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-address"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">City <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="cities[]" required>
                                            @foreach(getcities() as $city)
                                                <option value="{{$city['city_name']}}" @if(in_array($city['city_name'],$selCities)) selected @endif>{{$city['city_name']}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-cities"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Business Activity</label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $selActivites = array(); ?>
                                        @if(!empty($customerdata) && !empty($customerdata['activity']))
                                            <?php $selActivites = explode(',',$customerdata['activity']) ?>
                                        @endif
                                        <select class="form-control select2" name="activity[]" multiple="">
                                            @foreach(activities() as $activity)
                                                <option value="{{$activity}}" @if(in_array($activity,$selActivites)) selected @endif>{{$activity}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-activity"></h4>
                                    </div>
                                </div> 
                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label">Category <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $catARr = array('Corporate','Non Corporate') ?>
                                        @foreach($catARr as $skey=> $category)
                                            <label>
                                            <input type="radio" name="category" value="{{$category}}" @if(!empty($customerdata['category']) && $customerdata['category'] ==$category ) checked @endif />&nbsp;{{ucwords($category)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-category"></h4>
                                    </div>
                                </div> -->
                                <hr class="bold-hr">
                                <p class="highlight-label">Primary User Details</p>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Name </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Name" name="contact_person_name" style="color:gray" class="form-control" value="{{(!empty($customerdata['contact_person_name']))?$customerdata['contact_person_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-contact_person_name"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Designation </label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="designation">
                                            <option value="">Please Select</option>
                                            @foreach(getDesignations() as $dkey=>  $designation)
                                                <option value="{{$dkey}}" @if(!empty($customerdata['designation']) && $customerdata['designation']==$dkey) selected @endif>{{$designation}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-designation"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Mobile No.<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Mobile" name="mobile" style="color:gray" class="form-control" value="{{(!empty($customerdata['mobile']))?$customerdata['mobile']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-mobile"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">E-mail ID </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Email" name="email" style="color:gray" class="form-control" value="{{(!empty($customerdata['email']))?$customerdata['email']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-email"></h4>
                                    </div>
                                </div>
                                <hr class="bold-hr">
                                <p class="highlight-label">Add-on Users</p>
                                <div class="form-group">
                                    <label class="col-md-2 control-label"></label>
                                    <div class="col-md-10">
    <table id="ProductSearchRow" class="table table-hover table-bordered table-striped">
        <tbody>
            <tr>
                <th>Designation</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <!-- <th>Password</th> -->
                <th>Actions</th>
            </tr>
            @if(!empty($customerdata) && !empty($customerdata['employees']))
                @foreach($customerdata['employees'] as $custekey=> $custEmp)
                    <input type="hidden" name="cust_emp_id[]" value="{{$custEmp['id']}}">
                    <tr class="blockIdWrap">
                        <td>
                           <select class="form-control" name="designations[]" required>
                                <option value="">Please Select</option>
                           @foreach(getDesignations() as  $dkey=> $designation)
                            <option value="{{$dkey}}" @if($custEmp['designation']== $dkey) selected @endif>{{$designation}}</option>
                           @endforeach
                            </select> 
                        </td>
                        <td>
                            <input type="text" placeholder="Name" name="names[]" class="form-control" required value="{{$custEmp['name']}}">
                        </td>
                        <td>
                            <input type="number" placeholder="Mobile" name="mobiles[]" class="form-control" required value="{{$custEmp['mobile']}}">
                        </td>
                        <td>
                            <input type="emails[]" placeholder="Email" name="emails[]" class="form-control" required value="{{$custEmp['email']}}">
                        </td>
                        
                        <!-- <td>
                            <input type="text" placeholder="Password" name="passwords[]" class="form-control" required value="{{$custEmp['decrypt_password']}}">
                        </td> -->
                        <td>
                            <input type="checkbox" name="is_delete[{{$custekey}}]" value="1">Delete
                                <!-- <a title="Remove" class="btn btn-sm red AssignRowRemove" href="javascript:;"> <i class="fa fa-times"></i></a> -->
                            
                        </td>
                    </tr>
                @endforeach
            @else
                @for ($i=1; $i <=0; $i++)
                    <tr class="blockIdWrap">
                        <td>
                            <select class="form-control" name="designations[]" required>
                                <option value="">Please Select</option>
<option value="Owner">Owner</option>
<option value="G.M.">G.M.</option>
<option value="Production In-charge">Production In-charge</option>
<option value="Purchase In-charge">Purchase In-charge</option>
</select>
                        </td>
                        <td>
                            <input type="text" placeholder="Name" name="names[]" class="form-control" required>
                        </td>
                        <td>
                            <input type="number" placeholder="Mobile" name="mobiles[]" class="form-control" required>
                        </td>
                        <td>
                            <input type="emails[]" placeholder="Email" name="emails[]" class="form-control" required>
                        </td>
                        
                        <!-- <td>
                            <input type="text" placeholder="Password" name="passwords[]" class="form-control" required>
                        </td> -->
                        <td></td>
                    </tr>
                @endfor
            @endif
        </tbody>
    </table>
    <input type="button" id="addAssignRow" value="Add More" />
</div>
                                </div>  
                                <hr class="bold-hr">
                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label">Password <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="password" placeholder="Password" name="password" style="color:gray" class="form-control"/>
                                        @if(!empty($customerdata))
                                            <h5>Leave empty if don't want to update password</h5>
                                        @endif
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-password"></h4>
                                    </div>
                                </div> -->
                                <p class="highlight-label">Business Model & Linking</p>
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Product Type <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="customer_product_type">
                                            @foreach(product_types() as $pkey=> $protype)
                                                <option value="{{$pkey}}" @if(empty($customerdata) ) @if($pkey==0) selected @endif @else @if(isset($customerdata['customer_product_type']) && $customerdata['customer_product_type'] ==$pkey) selected @endif @endif>{{$protype}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Business Model <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="business_model">
                                            <option value="">Please Select</option>
                                            @foreach(buisnesModels() as $businessModel)
                                                <option value="{{$businessModel}}" @if(!empty($customerdata['business_model']) && $customerdata['business_model']==$businessModel) selected @endif>{{$businessModel}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-business_model"></h4>
                                    </div>
                                </div>
                                <div class="form-group" id="DealerDiv" @if(!empty($customerdata['business_model']) && $customerdata['business_model']=='Dealer') @else  style="display: none;" @endif >
                                    <label class="col-md-3 control-label">Linked Dealer <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="dealer_id" >
                                            <option value="">Please Select</option>
                                            @foreach(dealers() as $dealer)
                                                <option value="{{$dealer['id']}}" @if(!empty($customerdata['dealer_id']) && $customerdata['dealer_id']==$dealer['id']) selected @endif>{{$dealer['business_name']}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-dealer_id"></h4>
                                    </div>
                                </div> 
                                @include('admin.customers.user-share')
                                @php $style = ""; @endphp
                                @if(isset($customerdata['business_model']) && $customerdata['business_model'] =="Direct Customer")

                                @else
                                    <?php $style ="display:none;"; ?>
                                @endif
                                @include('admin.customers.partials.direct_customer_product_linking')

                                <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-linking_error"></h4>
                               
                                <hr class="bold-hr">
                                <p class="highlight-label">Business Card Upload</p>

                                {{-- Business Card (1) --}}
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Business Card (1)</label>
                                    <div class="col-md-4">
                                        <input type="file" name="business_card" class="form-control">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-business_card"></h4>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        @if(isset($customerdata['business_card_url']) && $customerdata['business_card_url'])
                                            <a href="{{ asset($customerdata['business_card_url']) }}" target="_blank">
                                                <img src="{{ asset($customerdata['business_card_url']) }}" alt="Business Card Front"
                                                     style="max-height: 150px; width:80%; border: 1px solid #ccc; padding: 4px;">
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- Business Card (2) --}}
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Business Card (2)</label>
                                    <div class="col-md-4">
                                        <input type="file" name="business_card_two" class="form-control">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Customer-business_card_two"></h4>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        @if(isset($customerdata['business_card_two_url']) &&  $customerdata['business_card_two_url'])
                                            <a href="{{ asset($customerdata['business_card_two_url']) }}" target="_blank">
                                                <img src="{{ asset($customerdata['business_card_two_url']) }}" alt="Business Card Back"
                                                     style="max-height: 150px; width:80%; border: 1px solid #ccc; padding: 4px;">
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <hr class="bold-hr">
                                @if(!empty($requestReceivedFrom))
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Request Received from </label>
                                        <div class="col-md-4">
                                            <p class="form-control">{{$requestReceivedFrom}}</p>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($customerCreatedBy))
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Customer Created by </label>
                                        <div class="col-md-4">
                                            <p class="form-control">{{$customerCreatedBy}}</p>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($customerdata['status']) && $customerdata['status'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="User-status"></h4>
                                    </div>
                                </div>  
                            </div>
                            @if(!empty($customerdata['id']))
                                <input type="hidden" name="customerid" value="{{$customerdata['id']}}">
                            @else
                                <input type="hidden" name="customerid" value="">
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
@include('admin.customers.customer-discount-modal')
<table class="table table-hover table-bordered table-striped assignsamplerow" style="display:none;">
    <tbody>
        <tr class="appenderTr blockIdWrap">
            <td>
                <select class="form-control" name="designations[]" required>
                <option value="">Please Select</option>
                    @foreach(getDesignations() as $dkey => $designation)
                        <option value="{{$dkey}}">{{$designation}}</option>
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
            <!-- <td>
                <input type="text" placeholder="Password" name="passwords[]" class="form-control" required>
            </td> -->
            <td>
                <a title="Remove" class="btn btn-sm red AssignRowRemove" href="javascript:;"> <i class="fa fa-times"></i></a>
            </td>
        </tr>
    </tbody>
</table>
<script type="text/javascript">
    $(document).on('change','[name=is_monthly_turnover_discount]',function(){
        var value = $(this).val();
        $('#customMtod').hide();
        if(value==="no"){
            $('#customMtod').show();
        }
    })
</script>
<!-- Append Table Rows -->
<script type="text/javascript">
    var rowid = 1;
    jQuery("#addAssignRow").click(function() {        
        var row = jQuery('.assignsamplerow tr').clone(true);
        row.appendTo('#ProductSearchRow'); 
    });
    $('.AssignRowRemove').on("click", function() {
        $(this).parents("tr").remove();;
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){

        $(document).on('click','#addCustDiscount',function(){
            var discounTypes = [];
            $('#AppendCustomerDiscounts td:first-child').each(function() {
                discounTypes.push($(this).text());
            });
            if(jQuery.isEmptyObject(discounTypes)){
                //$('[name=discount_type]').html('<option value="">Please Select</option><option value="Turnover">Turnover</option><option value="Corporate">Corporate</option><option value="Product Base">Product Base</option>');
                $('[name=discount_type]').html('<option value="">Please Select</option><option value="Corporate">Exclusive Corporate Discount</option><option value="Product Base">Special Product Discount</option>');
            }else{
                console.log(discounTypes);
                var availTypes = [];
                if(jQuery.inArray("Turnover", discounTypes) !== -1){
                    //nothing to do
                }else{
                    //availTypes.push('Turnover');
                }
                if(jQuery.inArray("Corporate", discounTypes) !== -1){
                    //nothing to do
                }else{
                    //availTypes.push('Corporate');
                }
                availTypes.push('Product Base');
                var options = '<option value="">Please Seelect</option>';
                for (var i = 0; i < availTypes.length; i++) {
                    var value = availTypes[i];
                    if(value =="Product Base"){
                        options += '<option value="'+value+'">Special Product Discount</option>';
                    }else{
                        options += '<option value="'+value+'">' + value + '</option>';
                    }
                }
                $('[name=discount_type]').html(options);
            }
            $('#CustDisModal').modal('show');
            refreshSelect2();
        });

        $('#CustDisModal').on('hidden.bs.modal', function () {
            $('#AppendDisDetails').html('');
            $("[name=discount_type]").prop("selectedIndex", 0);
        });

        $(document).on('change','[name=discount_type]',function(){
            var discount = $(this).val();
            if(discount ==""){
                $('#AppendDisDetails').html('');
            }else{
                $('.loadingDiv').show();
                $.ajax({
                    data : {discount:discount},
                    url  : '/admin/append-discount-details',
                    type : 'POST',
                    success:function(resp){
                        $('#AppendDisDetails').html(resp.view);
                        refreshSelect2();
                        $('.loadingDiv').hide();
                    },
                    error:function(){

                    }
                })
            }
            
        })

        $(document).on('keyup','[name=discount]', function() {
            $('[name=company_share]').val('');
            $('#DealerShare').text('');
            $('[name=dealer_share]').val('');
            
        });

        $(document).on('keyup','[name=company_share]', function() {
            if($('[name=discount]').length){
                if($('#DealerShare').length){
                    var discount = $('[name=discount]').val();
                    var companyShare  = $(this).val();
                    var dealerShare = parseInt(discount) -  parseInt(companyShare);
                    if(!isNaN(dealerShare)) {
                        $('#DealerShare').text(dealerShare+'%');
                        $('[name=dealer_share]').val(dealerShare);
                    }
                }
            }else{
                var companyShare  = $(this).val();
                var dealerShare = 100 -  parseInt(companyShare);
                if(!isNaN(dealerShare)) {
                    if(companyShare >100){
                        alert('Please enter value less then or equal to 100');
                        $('[name=company_share]').val('');
                        $('#DealerShare').text('');
                        $('[name=dealer_share]').val('');
                    }else{
                        $('#DealerShare').text(dealerShare+'%');
                        $('[name=dealer_share]').val(dealerShare);
                    }
                }
            }
        });

        $(document).on('change','[name=business_model]',function(){
            var model = $(this).val();
            $('#DealerDiv').hide();
            $("[name=dealer_id]").prop("selectedIndex", 0);
            $('#DirectCustomerDiscounts').hide();
            $('#ApplicableDiscountsSpan').hide();
            if(model =="Dealer"){
                $('#DealerDiv').show();
            }else if(model  =="Direct Customer"){
                $('#DirectCustomerDiscounts').show();
                $('#ApplicableDiscountsSpan').show();
            }
            refreshSelect2();
        })

        $("#Customerform").submit(function(e){
            $('.loadingDiv').show();
            e.preventDefault();
            var formdata = new FormData(this);
            $.ajax({
                url: '/admin/save-customer',
                type:'POST',
                data: formdata,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('.loadingDiv').hide();
                    if(!data.status){
                        $.each(data.errors, function (i, error) {
                            $('#Customer-'+i).addClass('error-triggered');
                            $('#Customer-'+i).attr('style', '');
                            $('#Customer-'+i).html(error);
                            setTimeout(function () {
                                $('#Customer-'+i).css({
                                    'display': 'none'
                                });
                            $('#Customer-'+i).removeClass('error-triggered');
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
    $("#AddCustDisForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = new FormData(this);
        $.ajax({
            url: '/admin/add-customer-discount',
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Discount-'+i).addClass('error-triggered');
                        $('#Discount-'+i).attr('style', '');
                        $('#Discount-'+i).html(error);
                        setTimeout(function () {
                            $('#Discount-'+i).css({
                                'display': 'none'
                            });
                        $('#Discount-'+i).removeClass('error-triggered');
                        }, 5000);
                    });
                }else{
                    $('#AppendCustomerDiscounts').append(data.view);
                    $('#CustDisModal').modal('hide');
                    refreshSelect2();
                }
            }
        });
    });
    $(document).on('click', 'button.removeRow', function () {
        if (confirm("Are you sure you want to delete this?")) {
            $(this).closest('tr').remove();
            return false;
        }
        return false;
    });
</script>
<script type="text/javascript">
    $(document).on('click','#addMarketingEmployee',function(){
        $('.loadingDiv').show();
        $.ajax({
            url : '/admin/append-marketing-users',
            type : 'GET',
            success:function(resp){
                $('#MarketingEmployeeTable tr:last').after(resp.view);
                $('.loadingDiv').hide();
            },
            error:function(){

            }
        })
    })
</script>

@endsection