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
                <a href="{{ url('admin/qcfs') }}">QCFS </a>
            </li>
        </ul>
        <div class="row">
            @if(Session::has('flash_message_success'))
                <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
            @endif
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form role="form" class="form-horizontal" method="post" action="{{url('/admin/qcfs-reply/'.$qcfs_detail['id'])}}" enctype="multipart/form-data" autocomplete="off">@csrf
                            <div class="form-body">
                                
                                @if(count($qcfs_detail['replies']) >0)
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Replies</label>
                                        <div class="col-md-9">
                                            <table class="table table-bordered table-stripped">
                                                <tr>
                                                    <td>Sr No.</td>
                                                    <td>Reply</td>
                                                    <td>Submit On</td>
                                                </tr>
                                                @foreach($qcfs_detail['replies'] as $key=>  $reply)
                                                    <tr>
                                                        <td>{{++$key}}</td>
                                                        <td>{{$reply['reply']}}</td>
                                                        <td>{{date('d M Y',strtotime($reply['created_at']))}}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Reply <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <textarea  type="text" placeholder="Reply" name="admin_reply" style="color:gray" class="form-control" required></textarea>
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
@endsection