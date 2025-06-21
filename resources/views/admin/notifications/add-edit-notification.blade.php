@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Notifications Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/notifications') }}">Notifications </a>
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
                        <form id="CategoryForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Title <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Title" name="title" style="color:gray" class="form-control" value="{{(!empty($notifydata['title']))?$notifydata['title']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Notification-title"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Body <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <textarea placeholder="Body" name="body"  style="color:gray" class="form-control">{{(!empty($notifydata['body']))?$notifydata['body']: '' }}</textarea>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Notification-body"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Type <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $typeArr = array('dealer','customer') ?>
                                        @foreach($typeArr as $typeInfo)
                                            <label>
                                            <input type="radio" name="type" value="{{$typeInfo}}" @if(!empty($notifydata) && $notifydata['type'] ==$typeInfo ) checked @endif />&nbsp;{{ucwords($typeInfo)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Notification-type"></h4>
                                    </div>
                                </div> 
                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label">Link </label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Link" name="link" style="color:gray" class="form-control" value="{{(!empty($notifydata['link']))?$notifydata['link']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Notification-link"></h4>
                                    </div>
                                </div> -->
                               <!--  <div class="form-group ">
                                    <label class="col-md-3 control-label">Image</label>
                                    <div class="col-md-5">
                                        <div data-provides="fileinput" class="fileinput fileinput-new">
                                            <div style="" class="fileinput-new thumbnail">
                                            <?php if(!empty($notifydata['image'])){
                                                $path = "images/NotificationImages/".$notifydata['image']; 
                                            if(file_exists($path)) { ?>
                                                <img style="height:100px;widtyh:100px;" class="img-responsive"  src="{{ asset('images/NotificationImages/'.$notifydata['image'])}}">
                                            <?php }else{?>
                                                    <img style="height:100px;widtyh:100px;" class="img-responsive"  src="{{ asset('images/default.png') }}">
                                            <?php } } else { ?>
                                            <img style="height:100px;widtyh:100px;" class="img-responsive"  src="{{ asset('images/default.png') }}">
                                            <?php } ?>
                                        </div>
                                            <div style="max-width: 200px; max-height: 150px; line-height: 10px;" class="fileinput-preview fileinput-exists thumbnail">
                                            </div>
                                            <div>
                                                <div class="form-group">
                                                    <span class="btn default btn-file">
                                                    <span class="fileinput-new">
                                                    Select Image </span>
                                                    <span class="fileinput-exists">
                                                    Select Image </span>
                                                    <input type="file" id="Image" name="image">
                                                    </span>
                                                    <a data-dismiss="fileinput" class="btn default fileinput-exists" href="#">
                                                    Remove </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($notifydata) && $notifydata['status'] ==$skey ) checked @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Notification-status"></h4>
                                    </div>
                                </div>  
                                @if(!empty($notifydata['id']))
                                    <input type="hidden" name="notificationid" value="{{$notifydata['id']}}">
                                @else
                                    <input type="hidden" name="notificationid" value="">
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
    $("#CategoryForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
         var formdata = new FormData(this);
        $.ajax({
            url: "{{url('/admin/save-notification')}}",
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Notification-'+i).addClass('error-triggered');
                        $('#Notification-'+i).attr('style', '');
                        $('#Notification-'+i).html(error);
                        setTimeout(function () {
                            $('#Notification-'+i).css({
                                'display': 'none'
                            });
                        $('#Notification-'+i).removeClass('error-triggered');
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