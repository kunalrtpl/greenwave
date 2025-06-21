@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Employee Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/user-incentives') }}">Employee Incentives</a>
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
                        <form id="UserIncentiveForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Employee <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="user_id">
                                            <option value="">Please Select</option>
                                            @foreach($users as $user)
                                                <option value="{{$user['id']}}" {{(!empty($userincentivedata['user_id']) && $userincentivedata['user_id']==$user['id'] )?'selected': '' }}>{{$user['name']}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-user_id"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Month/ Year <span class="asteric">*</span></label>
                                    <div class="col-md-2">
                                        <select class="form-control select2" name="month" required>
                                            <option value="">Select Month</option>
                                            <?php $months = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'); ?>
                                            @foreach($months as $mkey=> $month)
                                                <option value="{{$mkey}}" {{(!empty($userincentivedata['month']) && $userincentivedata['month']==$mkey )?'selected': '' }}>{{$month}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-control select2" name="year" required>
                                            <option value="">Select Year</option>
                                            @for($y=2022; $y<=date('Y'); $y++)
                                                <option value="{{$y}}" {{(!empty($userincentivedata['year']) && $userincentivedata['year']==$y )?'selected': '' }}>{{$y}}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Range From<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Range From" name="range_from" style="color:gray" class="form-control" @if($userincentiveid) value="{{(!empty($userincentivedata['range_from']))?$userincentivedata['range_from']: '0' }}" @endif/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-range_from"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Range To<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Range To" name="range_to" style="color:gray" class="form-control" value="{{(!empty($userincentivedata['range_to']))?$userincentivedata['range_to']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-range_to"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Incentive (in %.) <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Incentive" name="discount" style="color:gray" class="form-control" value="{{(!empty($userincentivedata['discount']))?$userincentivedata['discount']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-discount"></h4>
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
    var userincentiveid =""; 
    <?php if(!empty($userincentivedata['id'])){?>
        userincentiveid = "<?php echo $userincentivedata['id']; ?>";
    <?php } ?>
    $("#UserIncentiveForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#UserIncentiveForm").serialize()+"&userincentiveid="+userincentiveid;
        $.ajax({
            url: '/admin/save-user-incentive',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Incentive-'+i).addClass('error-triggered');
                        $('#Incentive-'+i).attr('style', '');
                        $('#Incentive-'+i).html(error);
                        setTimeout(function () {
                            $('#Incentive-'+i).css({
                                'display': 'none'
                            });
                        $('#Incentive-'+i).removeClass('error-triggered');
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