@extends('layouts.adminLayout.backendLayout')
@section('content')
<style>
.table-scrollable table tbody tr td{
    vertical-align: middle;
}
</style>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Dealers Management</h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
            </li>
        </ul>
         @if(Session::has('flash_message_error'))
            <div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
        @endif
        @if(isset($_GET['s']))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> Record has been updated Sucessfully. </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">Add-on Users ({{$title}})</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="btn-group">
                                       <a href="{{url('/admin/add-edit-dealer-user?dealer_id='.$dealerId)}}" class="btn btn-primary">Add </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" >
                                <thead>
                                    <tr role="row" class="heading">
                                        <th>
                                            Sr.No.
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            Department
                                        </th>
                                        <th>
                                            Designation
                                        </th>
                                        <th>
                                            Email
                                        </th>
                                        <th>
                                            Mobile
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    @if(!empty($dealerUsers))
                                        @foreach($dealerUsers as $key=> $dealer)
                                            <tr>
                                                <td>{{++$key}}</td>  
                                                <td>{{$dealer['name']}}</td>  
                                                <td>{{$dealer['department']}}</td>  
                                                <td>{{$dealer['designation']}}</td>  
                                                <td>{{$dealer['email']}}</td>  
                                                <td>{{$dealer['owner_mobile']}}</td>  
                                                <td>
                                                    @if($dealer['status'] == 1)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>  
                                                <td>
                                                    <a title="Edit" class="btn btn-xs green " href="{{url('/admin/add-edit-dealer-user/'.$dealer['id'])}}"> <i class="fa fa-edit"></i></a>
                                                    <a title="Delete" class="btn btn-xs red " href="{{url('/admin/delete-dealer-user/'.$dealer['id'])}}"> <i class="fa fa-times"></i></a>
                                                </td>  
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr >
                                            <td class="text-center" colspan="7">No users found.</td>
                                        </tr>
                                    @endif
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop





