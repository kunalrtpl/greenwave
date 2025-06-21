@extends('layouts.adminLayout.backendLayout')
@section('content')
<style>
.form-control-feedback {
      top: 9px !important;
    }
</style>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Send Push Notification </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        <div class="row">
            @if(Session::has('flash_message_error'))
                <div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
            @endif
            @if(Session::has('flash_message_success'))
                <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
            @endif
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>Send Push Notification
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form id="Notificationform" role="form" class="form-horizontal" method="post" action="javascript:;"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Notification :</label>
                                    <div class="col-md-4">
                                        <select id="Notification" name="notification_id" required> 
                                            <option value="">Select</option>
                                            @foreach($notifications as $key=> $notification)
                                                <option value="{{$notification['id']}}">{{$notification['title']}} - ({{$notification['type']}})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Send To :</label>
                                    <div class="col-md-4">
                                        <select id="SenTo" name="sendto" required> 
                                            <option value="">Select</option>
                                            <option value="android">Android Users</option>
                                            <option value="ios">IOS</option>
                                        </select>
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
<div class="PleaseWaitDiv" style="display:none;">
    <b><p style="color: #000;">Please wait... Don't refersh or close the window. it will take some time to process notifications</p></b>
</div>
<style type="text/css">
    .form-group select {
    float:left;
    display: inline-block;
    width:100%;
    padding: 6px 12px;
}
/*ajax loading div*/
.PleaseWaitDiv{background: #ffffff;color: #666666;position: fixed;height: 100%;width: 100%;z-index: 5000;top: 0;left: 0;float: left;text-align: center;opacity: .80;}
.PleaseWaitDiv p{margin: 0;}
.PleaseWaitDiv b{position: absolute; top: 50%; left: 0; width: 100%; text-align: center; display: inline-block; float: left; -webkit-transform: translateY(-50%); -moz-transform: translateY(-50%); -ms-transform: translateY(-50%); -o-transform: translateY(-50%); transform: translateY(-50%);}
/*ajax loading div*/
.PleaseWaitDiv{z-index: 10009;}
</style>
<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('submit','#Notificationform',function(e){
            $('.PleaseWaitDiv').show();
            var notification_id = $('#Notification').val();
            var sendto = $('#SenTo').val();
            var data = {};
            data['notification_id'] = notification_id;
            data['skip'] = 0;
            data['sendto'] = sendto;
            data['_token']  = "{{csrf_token()}}";
            data['push_in_table']  = "yes";
            queueNotification(data);
        });

        function queueNotification(data){
            var newdata = {};
            $.ajax({
                data : data,
                url : '/admin/process-notifications',
                type : 'post',
                success:function(resp){
                    if(resp.status){
                        if(resp.having_more_data =='yes'){
                            newdata['notification_id'] = resp.notification_id;
                            newdata['skip'] = resp.skip;
                            newdata['sendto'] = resp.sendto;
                            newdata['_token']  = "{{csrf_token()}}";
                            newdata['push_in_table'] = 'no';
                            queueNotification(newdata);
                        }else{
                            $('.PleaseWaitDiv').hide();
                            alert('Notifications Send successfully to users');
                            window.location.reload();
                            console.log('done');
                        }
                    }
                },
                error:function(){
                }
            })
        }

    })
</script>
@endsection