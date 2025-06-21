@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Cities Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/cities') }}">Cities </a>
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
                        <form id="CityForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <?php $countries = countries();
                                    ?>
                                    <label class="col-md-3 control-label">Country <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="country_name">
                                            <option value="">Please Select</option>
                                            @foreach($countries as $country)
                                                <option value="{{$country->country_name}}" {{(!empty($citydata['country_name']) && $citydata['country_name']== $country->country_name )?'selected': '' }}>{{$country->country_name}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="City-country_name"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    @if($citydata && $citydata['country_name'])
                                        <?php $states = states($citydata['country_name']);?>
                                    @endif
                                    <label class="col-md-3 control-label">State <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="state_name">
                                            <option value="">Please Select</option>
                                            @if(isset($states))
                                                @foreach($states as $state)
                                                    <option value="{{$state->state_name}}" {{(!empty($citydata['state_name']) && $citydata['state_name']== $state->state_name )?'selected': '' }}>{{$state->state_name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="City-state_name"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">City Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="City Name" name="city_name" style="color:gray" class="form-control" value="{{(!empty($citydata['city_name']))?$citydata['city_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="City-city_name"></h4>
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
    var cityid =""; 
    <?php if(!empty($citydata['id'])){?>
        cityid = "<?php echo $citydata['id']; ?>";
    <?php } ?>
    $("#CityForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#CityForm").serialize()+"&cityid="+cityid;;
        $.ajax({
            url: '/admin/save-city',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#City-'+i).addClass('error-triggered');
                        $('#City-'+i).attr('style', '');
                        $('#City-'+i).html(error);
                        setTimeout(function () {
                            $('#City-'+i).css({
                                'display': 'none'
                            });
                        $('#City-'+i).removeClass('error-triggered');
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