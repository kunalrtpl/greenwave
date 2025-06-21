@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Inventory Management </h1>
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
                        <form id="InventoryForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Incoming Date<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control" name="incoming_date" value="{{date('Y-m-d')}}">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-incoming_date"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Type<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <?php $materialArr = getMaterialTypes();?>
                                        <select class="form-control" name="type">
                                            <option value="">Please Select</option>
                                            @foreach($materialArr as $mkey=> $type)
                                                <option value="{{$mkey}}">{{$type}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-type"></h4>
                                    </div>
                                </div>
                                <div id="AppendIncomingTypeDetails">
                                    
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
        var productId = $(this).val();
        var type = $(this).find(':selected').attr('data-product_type');
        if(type===""){
            $('#AppendProductType').html('');
        }else{
            $('#AppendProductType').html(type);
            $.ajax({
                data : {product_id: productId},
                type : 'GET',
                url : '/admin/inventory/get-product-labels',
                success:function(resp){
                    $('[name=label_id]').html(resp);
                },
                error:function(){

                }
            })
        }
    })

    $(document).on('change','[name=type]',function(){
        var type = $(this).val();
        if(type==""){
            $('#AppendIncomingTypeDetails').html('');
        }else{
            $('.loadingDiv').show();
            $.ajax({
                url : '/admin/append-material-details',
                data : {type :type},
                type : 'POST',
                success:function(resp){
                    $('#AppendIncomingTypeDetails').html(resp.view);
                    refreshSelect2();
                    $('.loadingDiv').hide();
                },
                error:function(){
                }
            })
        }
    })

    $("#InventoryForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = new FormData(this);
        $.ajax({
            url: '/admin/add-incoming-material',
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Inventory-'+i).addClass('error-triggered');
                        $('#Inventory-'+i).attr('style', '');
                        $('#Inventory-'+i).html(error);
                        setTimeout(function () {
                            $('#Inventory-'+i).css({
                                'display': 'none'
                            });
                        $('#Inventory-'+i).removeClass('error-triggered');
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
    $(document).on("change",'.finalPackingType',function(){ 
        var packingType = $(this).find(':selected').text();  
        var tareWeight =  $(this).find(':selected').data('tare_weight');
        var stock =  $(this).find(':selected').data('stock');
        if(tareWeight >= 0){
            $(this).closest('tr').children('td:eq(0)').find('p').html('<small>(Tare Weight: '+tareWeight+'kg) <br>'+'(Available Stock: '+stock+')</small>');
            $(this).closest('tr').children('td:eq(2)').children().val('');
            $(this).closest('tr').children('td:eq(3)').find('p').html('');
            $(this).closest('tr').children('td:eq(2)').find('p').html('');
        }else{
            $(this).closest('tr').children('td:eq(0)').find('p').html('');

        }
        $(this).closest('tr').children('td:eq(4)').find('p').html(packingType);
        //alert(tareWeight);
    });

    $(document).on("keyup",'.finalNetFillSize',function(){  
        var netfillsize = $(this).val();
        var noofpacks = $(this).closest('tr').children('td:eq(1)').children().val();
        var packingtype = $(this).closest('tr').children('td:eq(0)').children().val();
        var tareWeight = $(this).closest('tr').children('td:eq(0)').children().find(':selected').data('tare_weight');
        if(packingtype ==""){
            $(this).val('');
            alert('Please select packing type');
            return false;
        }else if(noofpacks <=0){
            $(this).val('');
            alert('Please enter No of packs');
            return false;
        }
        var materialFilled = noofpacks * netfillsize;
        var totalnetfillsize =  (parseFloat(tareWeight)+parseFloat(netfillsize)).toFixed(2);

        $(this).closest('tr').children('td:eq(2)').find('p').html('('+totalnetfillsize+'kg)');

        $(this).closest('tr').children('td:eq(3)').find('p').html(materialFilled+' kg');
        
    });
    
    $(document).on("keyup",'.finalNoOfPacks',function(){  
        $(this).closest('tr').children('td:eq(2)').children().val('');
        $(this).closest('tr').children('td:eq(3)').find('p').html('');
    });
</script>
<script type="text/javascript">
    window.history.pushState("", "", "/admin/add-incoming-material");
</script>
@endsection