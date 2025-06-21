@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Countries Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/countries') }}">Countries </a>
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
                        <form id="CountryForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Country Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Country Name" name="name" style="color:gray" class="form-control" value="{{(!empty($countrydata['country_name']))?$countrydata['country_name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Country-name"></h4>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="col-md-3 control-label">Sort <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input type="number" placeholder="Sort" name="sort"  style="color:gray" class="form-control " value="{{(!empty($countrydata['sort']))?$countrydata['sort']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Country-sort"></h4>
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
    var countryid =""; 
    <?php if(!empty($countrydata['id'])){?>
        countryid = "<?php echo $countrydata['id']; ?>";
    <?php } ?>
    $("#CountryForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#CountryForm").serialize()+"&countryid="+countryid;;
        $.ajax({
            url: '/admin/save-country',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Country-'+i).addClass('error-triggered');
                        $('#Country-'+i).attr('style', '');
                        $('#Country-'+i).html(error);
                        setTimeout(function () {
                            $('#Country-'+i).css({
                                'display': 'none'
                            });
                        $('#Country-'+i).removeClass('error-triggered');
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