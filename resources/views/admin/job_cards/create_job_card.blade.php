@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Working Section </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        @if(isset($_GET['m']))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> {!! $_GET['m'] !!} </div>
        @endif
        <div class="row">
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form id="JobCardForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Type <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="job_card_type">
                                            <option value="">Please Select</option>
                                            @foreach(job_card_types() as $jkey=>  $jobype)
                                                <option value="{{$jkey}}">{{$jobype}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="JobCard-job_card_type"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Date <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="date" name="job_card_date" style="color:gray" class="form-control"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="JobCard-job_card_date"></h4>
                                    </div>
                                </div>
                                 <div class="form-group">
                                    <label class="col-md-3 control-label">Batch No. <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <p class="form-control">{{$batch_no}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Product <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="product_id">
                                            <option value="">Please Select</option>
                                            @foreach($products as $pkey=> $product)
                                                <option data-standard_fill_size="{{$product['standard_fill_size']}}" data-standard_packing_type="{{$product['standard_packing_type']}}" data-packingtypeid="{{$product['packing_type_id']}}" data-packing_available_stock="{{$product['packing_available_stock']}}" value="{{$product['id']}}">{{$product['product_name']}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="JobCard-product_id"></h4>
                                    </div>
                                </div>
                                <div id="BatchSizeDiv" style="display: none;">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Batch Size (in Kgs.) <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <input  type="number" placeholder="Batch Size" name="batch_size" style="color:gray" class="form-control"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="JobCard-batch_size"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Standard Packing Type <span class="asteric">*</span></label>
                                        <div class="col-md-6">
                                            <p class="form-control" id="StandardPackingType"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Standard Fill Per Packing<span class="asteric">*</span></label>
                                        <div class="col-md-6">
                                            <p class="form-control" id="StandardFillPerPacking"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                       <label class="col-md-3 control-label">   No. of Packings Required</label>
                                        <div class="col-md-4">
                                            <p class="form-control" id="PackingsRequired">0</p>
                                            <input type="hidden" name="no_packings_required">
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="JobCard-no_packings_required"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Available Stock <span class="asteric">*</span></label>
                                        <div class="col-md-6">
                                            <p class="form-control" id="PackingaAvailableStock"></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Machine No. <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="machine_number">
                                                <option value="">Please Select</option>
                                                @foreach($machines as $machine)
                                                    <option data-capacity="{{$machine['capacity']}}" value="{{$machine['machine_number']}}">{{$machine['machine_number']}}</option>
                                                @endforeach
                                            </select>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="JobCard-machine_number"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Machine Capacity: <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <p class="form-control" id="MachineCapacity"></p>
                                            <input type="hidden" name="machine_capacity">
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Operator/ Worker Name: <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <input  type="text" placeholder="Operator/ Worker Name" name="operator_name" style="color:gray" class="form-control"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="JobCard-operator_name"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div> 
                                </div>
                                <div class="form-group" id="RMrequirements">
                                       
                                </div>
                                <h4 class="text-center text-danger pt-3" style="display: none;" id="JobCard-rm_errors"></h4>
                            </div>
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
<script type="text/javascript">
    $(document).on('change','[name=product_id]',function(){
        var proid = $(this).val();
        var standard_packing_type = $(this).find(':selected').data('standard_packing_type');
        var standard_fill_size = $(this).find(':selected').data('standard_fill_size');
        var packingtypeid = $(this).find(':selected').data('packingtypeid');
        $('#StandardPackingType').text(standard_packing_type);
        $('#StandardFillPerPacking').text(standard_fill_size+' kg');
        if(proid ==""){
            $('#BatchSizeDiv').hide();
        }else{
            $('#BatchSizeDiv').show();
            $('[name=packing_size_id] option[value='+packingtypeid+']').attr('selected','selected');
        }
        refreshSelect2();
        $('[name=batch_size]').val('');
        $('#RMrequirements').html('');
        $('#PackingsRequired').text('0');
        $('[name=no_packings_required]').val('');
        
    })

    $(document).on('change','#RequirementType',function(){
        var type = $(this).val();
        if(type ===""){
            $('#appendTypeList').html('');
            $('#appendTypeList').parent().hide();
        }else{
            $('.loadingDiv').show();
            $.ajax({
                url : '/admin/append-requirement-list',
                data : {type :type},
                type : 'POST',
                success:function(resp){
                    $('#appendTypeList').html(resp);
                    $('#appendTypeList').parent().show();
                    refreshSelect2();
                    $('.loadingDiv').hide();
                },
                error:function(){
                }
            })
        }
    })

    $(document).on('click','#addRmRequirement',function(){
        var type = $('#RequirementType').find(":selected").val();
        if(type==""){
            alert('Please Select Type');
            return false;
        }else{
            var rm = $('#RequirementList').find(":selected").val();
            if(rm ===""){
                if(type=="RM"){
                    alert('Please Select Raw Material');
                    return false;
                }else{
                    alert('Please Select Product');
                    return false;
                }
            }
        }
        $('.loadingDiv').show();
        $.ajax({
            url : '/admin/add-rm-requirement',
            data : {type :type,rm_id:rm},
            type : 'POST',
            success:function(resp){
                $('.loadingDiv').hide();
                $('#appendRmRequirements').append(resp.view);
                $('#RequirementType').prop('selectedIndex',0);
                $('#appendTypeList').html('');
                $('#appendTypeList').parent().hide();
            },
            error:function(){
            }
        })
    })

    $(document).on('click','.deleteRmRequirement',function(){
        $(this).parent().parent().parent().parent().remove();
        calculateTotalQty();
    })

    $(document).on('keyup','[name=batch_size]',function(){
        var batchsize = $(this).val();
        var standard_fill_size = $("[name=product_id]").find(':selected').data('standard_fill_size');
        var packing_available_stock = $("[name=product_id]").find(':selected').data('packing_available_stock');
        var PackingsRequired = batchsize/standard_fill_size;
        if(batchsize == ""){
            $('#RMrequirements').html('');
            $('#PackingsRequired').text('');
            $('[name=no_packings_required]').val('');
        }else{
            //alert(packing_available_stock);
            if(packing_available_stock >=PackingsRequired ){
                $('#PackingaAvailableStock').text(packing_available_stock+' (Sufficent Stock)');
            }else{
                $('#PackingaAvailableStock').text(packing_available_stock + ' (Not Sufficent Stock)');
            }
            var proid = $('[name=product_id]').val();
            $.ajax({
                url : '/admin/append-standard-recipe',
                data : {batchsize :batchsize,proid:proid},
                type : 'POST',
                success:function(resp){
                    $('#RMrequirements').html(resp.view);
                    $('#PackingsRequired').text(PackingsRequired);
                    $('[name=no_packings_required]').val(PackingsRequired);
                },
                error:function(){
                }
            })
        }
        
    })

    $("#JobCardForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#JobCardForm").serialize();
        $.ajax({
            url: '/admin/create-production-job-card',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#JobCard-'+i).addClass('error-triggered');
                        $('#JobCard-'+i).attr('style', '');
                        $('#JobCard-'+i).html(error);
                        setTimeout(function () {
                            $('#JobCard-'+i).css({
                                'display': 'none'
                            });
                        $('#JobCard-'+i).removeClass('error-triggered');
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


    $(document).on("keyup",'.reqQty',function(){  
        var qtyPercentage = $(this).val();
        if(qtyPercentage>100){
            $(this).val(100);
            qtyPercentage = 100;
        } 
        batchsize  = $('[name=batch_size]').val();
        qtyInKg = (batchsize * qtyPercentage) /100;
        if(qtyInKg ==0){
            $(this).closest('tr').children('td:eq(5)').text('');
        }else{
            $(this).closest('tr').children('td:eq(5)').text(qtyInKg+' kg');
        }
        calculateTotalQty();
    });

    function calculateTotalQty(){
        var totalQtyPer = 0;
        $('.reqQty').each(function (key,elem) {
            if(!isNaN(parseFloat($(elem).val()))) {
                var index = $(this).data('index');
                var qtyper = $(elem).val();
                totalQtyPer += parseFloat($(elem).val());
            }
        });
        $('#qtyPercentage').text('Total: '+totalQtyPer+'%');
        $('#TotalqtyTable').show();
    }

    $(document).on('change','[name=machine_number]',function(){
        var value = $(this).find(':selected').val()
        if(value ===""){
             $('[name=machine_capacity]').val('');
            $('#MachineCapacity').text('');
        }else{
            var capacity = $(this).find(':selected').data('capacity');
            $('[name=machine_capacity]').val(capacity);
            $('#MachineCapacity').text(capacity);
        }
    })
</script>
@endsection