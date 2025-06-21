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
                <a href="{{ url('admin/machines') }}">Machines </a>
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
                        <form id="Machineform" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Machine No.<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Machine No" name="machine_number" style="color:gray" class="form-control" value="{{(!empty($machinedata['machine_number']))?$machinedata['machine_number']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Machine-machine_number"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Capacity<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Capacity" name="capacity" style="color:gray" class="form-control" value="{{(!empty($machinedata['capacity']))?$machinedata['capacity']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Machine-capacity"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($machinedata) && $machinedata['status'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($status)}}&nbsp;
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
    var machineid =""; 
    <?php if(!empty($machinedata['id'])){?>
        machineid = "<?php echo $machinedata['id']; ?>";
    <?php } ?>
    $("#Machineform").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#Machineform").serialize()+"&machineid="+machineid;;
        $.ajax({
            url: '/admin/save-machine',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Machine-'+i).addClass('error-triggered');
                        $('#Machine-'+i).attr('style', '');
                        $('#Machine-'+i).html(error);
                        setTimeout(function () {
                            $('#Machine-'+i).css({
                                'display': 'none'
                            });
                        $('#Machine-'+i).removeClass('error-triggered');
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