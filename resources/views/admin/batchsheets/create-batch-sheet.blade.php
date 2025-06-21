@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>BatchSheet Management </h1>
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
                        <form id="BatchSheetForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Product <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="product_id">
                                            <option value="">Please Select</option>
                                                @foreach(products('Inhouse') as $pkey=> $product)
                                                    <option data-packingsize="{{$product['size']}}" data-packingsizeid="{{$product['packing_size_id']}}" value="{{$product['id']}}">{{$product['product_name']}}</option>
                                                @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-product_id"></h4>
                                    </div>
                                </div>
                                <div id="BatchSizeDiv" style="display: none;">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Batch Size (in Kgs.) <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <input  type="number" placeholder="Batch Size" name="batch_size" style="color:gray" class="form-control"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-batch_size"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                    <label class="col-md-3 control-label">Standard Packing Size/ Type <span class="asteric">*</span></label>
                                    <div class="col-md-6">
                                        <?php $sizes = \App\PackingSize::sizes();
                                        ?>
                                        <select class="form-control select2" name="packing_size_id">
                                            <option value="">Please Select</option>
                                            @foreach($sizes as $sizeinfo)
                                                <optgroup label="{{$sizeinfo['type']}}">
                                                   @foreach($sizeinfo['sizes'] as $size)
                                                        <option data-size="{{$size['size']}}" data-currentstock="{{$size['current_stock']}}" value="{{$size['id']}}">{{$size['size']}} kg ({{$sizeinfo['type']}}) (Current Stock: {{$size['current_stock']}})</option>
                                                   @endforeach 
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-packing_size_id"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">   No. of Packings Required</label>
                                    <div class="col-md-4">
                                        <p class="form-control" id="PackingsRequired">0</p>
                                        <input type="hidden" name="no_packings_required">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-no_packings_required"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Machine No. <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Machine No." name="machine_number" style="color:gray" class="form-control"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-machine_number"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Machine Capacity: <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Machine Capacity" name="machine_capacity" style="color:gray" class="form-control"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-machine_capacity"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Operator/ Worker Name: <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Operator/ Worker Name" name="operator_name" style="color:gray" class="form-control"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheet-operator_name"></h4>
                                    </div>
                                </div>
                                <div class="form-group" id="RMrequirements">
                                       
                                </div>           
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div> 
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
        var packingsizeid = $(this).find(':selected').data('packingsizeid');
        var packingsize = $(this).find(':selected').data('packingsize');
        if(proid ==""){
            $('#BatchSizeDiv').hide();
        }else{
            $('#BatchSizeDiv').show();
            $('[name=packing_size_id] option[value='+packingsizeid+']').attr('selected','selected');
        }
        refreshSelect2();
        $('[name=batch_size]').val('');
        $('#RMrequirements').html('');
        $('#PackingsRequired').text('0');
        $('[name=no_packings_required]').val('');
        
    })

    $(document).on('keyup','[name=batch_size]',function(){
        var batchsize = $(this).val();
        var packingsize = $("[name=product_id]").find(':selected').data('packingsize');
        var PackingsRequired = batchsize/packingsize;
        if(batchsize == ""){
            $('#RMrequirements').html('');
            $('#PackingsRequired').text('');
            $('[name=no_packings_required]').val('');
        }else{
            var proid = $('[name=product_id]').val();
            $.ajax({
                url : '/admin/append-batch-rm-requirements',
                data : {batchsize :batchsize,proid:proid},
                type : 'POST',
                success:function(resp){
                    $('#RMrequirements').html(resp);
                    $('#PackingsRequired').text(PackingsRequired);
                    $('[name=no_packings_required]').val(PackingsRequired);
                },
                error:function(){
                }
            })
        }
        
    })

    $(document).on('change','[name=packing_size_id]',function(){
        var batchsize = $('[name=batch_size]').val();
        var packingsize = $(this).find(':selected').data('size');
        var currentstock = $(this).find(':selected').data('currentstock');
        var PackingsRequired = batchsize/packingsize;
        $('#PackingsRequired').text(PackingsRequired);
        $('[name=no_packings_required]').val(PackingsRequired);
    })


    $("#BatchSheetForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#BatchSheetForm").serialize();
        $.ajax({
            url: '/admin/create-batch-sheet',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#BatchSheet-'+i).addClass('error-triggered');
                        $('#BatchSheet-'+i).attr('style', '');
                        $('#BatchSheet-'+i).html(error);
                        setTimeout(function () {
                            $('#BatchSheet-'+i).css({
                                'display': 'none'
                            });
                        $('#BatchSheet-'+i).removeClass('error-triggered');
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
</script>
<script type="text/javascript">
    window.history.pushState("", "", "/admin/create-batch-sheet");
</script>
@endsection