@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>States Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/states') }}">States </a>
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
                        <form id="StateForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <?php $countries = countries();
                                    ?>
                                    <label class="col-md-3 control-label">Country <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="country">
                                            <option value="">Please Select</option>
                                            @foreach($countries as $country)
                                                <option value="{{$country->country_name}}" {{(!empty($statedata['country_name']) && $statedata['country_name']== $country->country_name )?'selected': '' }}>{{$country->country_name}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="State-country"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">State Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="State Name" name="state_name" style="color:gray" class="form-control" value="{{(!empty($statedata['state_name']))?$statedata['state_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="State-state_name"></h4>
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
    var stateid =""; 
    <?php if(!empty($statedata['id'])){?>
        stateid = "<?php echo $statedata['id']; ?>";
    <?php } ?>
    $("#StateForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#StateForm").serialize()+"&stateid="+stateid;;
        $.ajax({
            url: '/admin/save-state',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#State-'+i).addClass('error-triggered');
                        $('#State-'+i).attr('style', '');
                        $('#State-'+i).html(error);
                        setTimeout(function () {
                            $('#State-'+i).css({
                                'display': 'none'
                            });
                        $('#State-'+i).removeClass('error-triggered');
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