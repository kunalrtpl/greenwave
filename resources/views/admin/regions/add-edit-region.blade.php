@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Regions Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/regions') }}">Regions </a>
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
                        <form id="Regionform" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Region <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Region Name" name="region" style="color:gray" class="form-control" value="{{(!empty($regiondata['region']))?$regiondata['region']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Region-region"></h4>
                                    </div>
                                </div>
                                @if(isset($regiondata['parent_id']))
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Parent Region</label>
                                        <div class="col-md-4">
                                            <p style="margin-top: 8px;">{{getRegionParent($regiondata['parent_id'])}}</p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="parent_id" value="{{$regiondata['parent_id']}}">
                                @else
                                    <div class="form-group">
                                        <?php $regions = regions();
                                        ?>
                                        <label class="col-md-3 control-label">Parent Region <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control select2" name="parent_id">
                                                <option value="ROOT">ROOT</option>
                                                @foreach($regions as $region)
                                                    <option value="{{$region['id']}}" @if(!empty($regiondata['parent_id']) && $regiondata['parent_id'] == $region['id']) selected @endif>{{$region['region']}}</option>
                                                    <!-- @foreach($region['subregions'] as $subregion)
                                                        <option value="{{$subregion['id']}}" @if(!empty($regiondata['parent_id']) && $region['parent_id'] == $region['id']) selected @endif>&nbsp; &nbsp;»» {{$subregion['region']}}</option>
                                                    @endforeach -->
                                                @endforeach
                                            </select>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Region-parent_id"></h4>
                                        </div>
                                    </div>
                                @endif
                                <div id="RegionStates" @if(empty($SelStates)) style="display:none;" @endif>
                                    <div class="form-group">
                                        <?php $states = states('India');
                                        ?>
                                        <label class="col-md-3 control-label">States <span class="asteric">*</span></label>
                                        <div class="col-md-6">
                                            <select class="form-control getStates select2" name="states[]" multiple>
                                                @foreach($states as $state)
                                                    <option value="{{$state->state_name}}" @if(in_array($state->state_name,$SelStates)) selected @endif>{{$state->state_name}}</option>
                                                @endforeach
                                            </select>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Region-states"></h4>
                                        </div>
                                    </div>
                                </div>
                                <div id="RegionCities">
                                    @if(!empty($selCities))
                                        @include('admin.regions.region-cities')
                                    @endif
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
    var regionid =""; 
    <?php if(!empty($regiondata['id'])){?>
        regionid = "<?php echo $regiondata['id']; ?>";
    <?php } ?>


    $(document).on('change', '.getStates', function () {
    $('.loadingDiv').show();
    $('#RegionCities').show();

    // Save currently selected cities
    var selectedCities = $('.getCities').val() || [];
    var states = $(this).val();

    $.ajax({
        url: '/admin/get-state-cities',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        data: {
            states: states,
            selCities: selectedCities
        },
        success: function (resp) {
            $('#RegionCities').html(resp.view);
            $('.loadingDiv').hide();
            refreshSelect2(); // Re-initialize Select2 if needed
        },
        error: function () {
            $('.loadingDiv').hide();
            alert('Error loading cities.');
        }
    });
});



    $(document).on('change','[name=parent_id]',function(){
        //$('.loadingDiv').show();
        var parentid = $(this).val();
        if(parentid =="ROOT"){
            $('#RegionStates').hide();
            $('#RegionCities').hide();
            $('.getStates').val('');
            $('.getCities').val('');
            $(".getStates").prop('required',false);
        }else{
            $(".getStates").prop('required',true);
            $('#RegionStates').show();
            $('#RegionCities').hide();
        }
        refreshSelect2();
    })

    $("#Regionform").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#Regionform").serialize()+"&regionid="+regionid;;
        $.ajax({
            url: '/admin/save-region',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Region-'+i).addClass('error-triggered');
                        $('#Region-'+i).attr('style', '');
                        $('#Region-'+i).html(error);
                        setTimeout(function () {
                            $('#Region-'+i).css({
                                'display': 'none'
                            });
                        $('#Region-'+i).removeClass('error-triggered');
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