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
                <a href="{{ url('admin/labels') }}">Labels </a>
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
                        <form id="LabelForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Label Type<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Label Type" name="label_type" style="color:gray" class="form-control" value="{{(!empty($labeldata['label_type']))?$labeldata['label_type']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Label-label_type"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Height<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="number" placeholder="Height" name="height" style="color:gray" class="form-control" value="{{(!empty($labeldata['height']))?$labeldata['height']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Label-height"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Width<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="number" placeholder="Width" name="width" style="color:gray" class="form-control" value="{{(!empty($labeldata['width']))?$labeldata['width']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Label-width"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Price<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="number" placeholder="Price" name="price" style="color:gray" class="form-control" value="{{(!empty($labeldata['price']))?$labeldata['price']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Label-price"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">For Product Type<span class="asteric">*</span></label>
                                    <div class="col-md-6">
                                        <select class="form-control select2" name="for_product_types[]" required multiple>
                                            @foreach(product_types() as $pkey=> $protype)
                                                <option value="{{$pkey}}" @if(in_array($pkey,$selLabels)) selected @endif>{{$protype}}</option>
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
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($labeldata) && $labeldata['status'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="User-status"></h4>
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
    var labelid =""; 
    <?php if(!empty($labeldata['id'])){?>
        labelid = "<?php echo $labeldata['id']; ?>";
    <?php } ?>
    $("#LabelForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#LabelForm").serialize()+"&labelid="+labelid;;
        $.ajax({
            url: '/admin/save-label',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Label-'+i).addClass('error-triggered');
                        $('#Label-'+i).attr('style', '');
                        $('#Label-'+i).html(error);
                        setTimeout(function () {
                            $('#Label-'+i).css({
                                'display': 'none'
                            });
                        $('#Label-'+i).removeClass('error-triggered');
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