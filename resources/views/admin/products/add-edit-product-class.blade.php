@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Products Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/product-class') }}">Product Class </a>
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
                        <form id="Classform" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Class Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Class Name" name="class_name" style="color:gray" class="form-control" value="{{(!empty($productclassdata['class_name']))?$productclassdata['class_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ProClass-class_name"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">From (%) <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="From" name="from" style="color:gray" class="form-control" value="{{(!empty($productclassdata['from']))?$productclassdata['from']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ProClass-from"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">To (%)<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="To" name="to" style="color:gray" class="form-control" value="{{(!empty($productclassdata['to']))?$productclassdata['to']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ProClass-to"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Standard Mark-up (%)<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Standard Mark-up" name="standard" style="color:gray" class="form-control" value="{{(!empty($productclassdata['standard']))?$productclassdata['standard']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ProClass-standard"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">E.R</label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="E.R" name="er" style="color:gray" class="form-control" value="{{(!empty($productclassdata['er']))?$productclassdata['er']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ProClass-er"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($productclassdata) && $productclassdata['status'] ==$skey ) checked @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ProClass-status"></h4>
                                    </div>
                                </div>  
                                @if(!empty($productclassdata['id']))
                                    <input type="hidden" name="productclassid" value="{{$productclassdata['id']}}">
                                @else
                                    <input type="hidden" name="productclassid" value="">
                                @endif           
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
    $("#Classform").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
         var formdata = new FormData(this);
        $.ajax({
            url: "{{url('/admin/save-product-class')}}",
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#ProClass-'+i).addClass('error-triggered');
                        $('#ProClass-'+i).attr('style', '');
                        $('#ProClass-'+i).html(error);
                        setTimeout(function () {
                            $('#ProClass-'+i).css({
                                'display': 'none'
                            });
                        $('#ProClass-'+i).removeClass('error-triggered');
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
@endsection