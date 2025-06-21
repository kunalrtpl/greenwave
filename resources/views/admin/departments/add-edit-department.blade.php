@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Departments Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/departments') }}">Departments </a>
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
                        <form id="DeptForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Department Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Department Name" name="department" style="color:gray" class="form-control" value="{{(!empty($departmentdata['department']))?$departmentdata['department']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Department-department"></h4>
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
    var departmentid =""; 
    <?php if(!empty($departmentdata['id'])){?>
        departmentid = "<?php echo $departmentdata['id']; ?>";
    <?php } ?>
    $("#DeptForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#DeptForm").serialize()+"&departmentid="+departmentid;;
        $.ajax({
            url: '/admin/save-department',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Department-'+i).addClass('error-triggered');
                        $('#Department-'+i).attr('style', '');
                        $('#Department-'+i).html(error);
                        setTimeout(function () {
                            $('#Department-'+i).css({
                                'display': 'none'
                            });
                        $('#Department-'+i).removeClass('error-triggered');
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