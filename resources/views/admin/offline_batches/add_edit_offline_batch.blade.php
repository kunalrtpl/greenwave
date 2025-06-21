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
                <a href="{{ url('admin/offline-batches') }}">Offline Batches </a>
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
                        <form id="OfflineBatchForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Batch No.<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Batch No" name="batch_no" style="color:gray" class="form-control" value="{{(!empty($batchData['batch_no']))?$batchData['batch_no']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="OfflineBatch-batch_no"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Attach COA </label>
                                    <div class="col-md-4">
                                        <input class="form-control" type="file" name="coa">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="OfflineBatch-coa"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Attach QC Report </label>
                                    <div class="col-md-4">
                                        <input class="form-control" type="file" name="qc_report">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="OfflineBatch-qc_report"></h4>
                                    </div>
                                </div> 
                                @if(!empty($batchData['id']))
                                    <input type="hidden" name="offlinebatchid" value="{{$batchData['id']}}">
                                @else
                                    <input type="hidden" name="offlinebatchid" value="">
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
    $("#OfflineBatchForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = new FormData(this);
        $.ajax({
            url: '/admin/save-offline-batch',
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#OfflineBatch-'+i).addClass('error-triggered');
                        $('#OfflineBatch-'+i).attr('style', '');
                        $('#OfflineBatch-'+i).html(error);
                        setTimeout(function () {
                            $('#OfflineBatch-'+i).css({
                                'display': 'none'
                            });
                        $('#OfflineBatch-'+i).removeClass('error-triggered');
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