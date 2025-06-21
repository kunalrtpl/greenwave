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
                <a href="{{ url('admin/checklists') }}">Checklists </a>
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
                        <form id="ChecklistForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Parameter Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Checklist Name" name="name" style="color:gray" class="form-control" value="{{(!empty($checklistdata['name']))?$checklistdata['name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Checklist-name"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Checklist <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <?php $checklists = qc_checklists(); ?>
                                        <select name="checklist" class="selectbox"> 
    <option value="">Select</option>
    <option value="ROOT" @if( empty($checklistdata['parent_id'])) selected @endif>Main Category</option>
    <?php foreach ($checklists as $key => $category) {?>
    <option value="{{$category['id']}}"@if(isset($checklistdata['parent_id']) && $checklistdata['parent_id'] ==$category['id']) selected @endif>&#9679;&nbsp;{{$category['name']}}</option>
    <?php if(!empty($category['subchecklists'])){
        foreach ($category['subchecklists'] as $key => $subcat) { ?>
            <option value="{{$category['id']}}">&nbsp;&nbsp;&nbsp;&nbsp;&raquo; &nbsp;{{$subcat['name']}}</option>
        <?php 
        }
    }
} ?>
</select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Checklist-category"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($checklistdata) && $checklistdata['status'] ==$skey ) checked @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Checklist-status"></h4>
                                    </div>
                                </div>  
                                @if(!empty($checklistdata['id']))
                                    <input type="hidden" name="checklistid" value="{{$checklistdata['id']}}">
                                @else
                                    <input type="hidden" name="checklistid" value="">
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
    $("#ChecklistForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
         var formdata = new FormData(this);
        $.ajax({
            url: "{{url('/admin/save-checklist')}}",
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Checklist-'+i).addClass('error-triggered');
                        $('#Checklist-'+i).attr('style', '');
                        $('#Checklist-'+i).html(error);
                        setTimeout(function () {
                            $('#Checklist-'+i).css({
                                'display': 'none'
                            });
                        $('#Checklist-'+i).removeClass('error-triggered');
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