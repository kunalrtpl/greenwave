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
        @if(Session::has('flash_message_success'))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">Dealer Incentives</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="btn-group">
                                       <a href="{{action('Admin\DealerController@addEditDealerIncentive')}}" class="btn btn-primary">Add Dealer Incentive</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="20%">Month</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incentives as $incentiveDate)
                                        <tr>
                                            <td>{{date('M, Y',strtotime($incentiveDate))}}</td>
                                            <td>
                                                <table class="table table-striped table-bordered table-hover">
                                                    <tr>
                                                        <th>From - To (Rs.)</th>
                                                        <th>Discount</th>
                                                        <th>Actions</th>
                                                    </tr>
                                            <?php
                                                $date_wise_incentive = \App\DealerIncentive::get_incentives($incentiveDate)
                                            ?>
                                                    @foreach($date_wise_incentive as $inc)
                                                    <tr>
                                                        <td>{{$inc['range_from']}} - {{$inc['range_to']}}</td>
                                                        <td>
                                                           {{$inc['discount']}}% 
                                                        </td>
                                                        <td>
                                                                <a title="Edit" class="btn btn-xs green" href="{{url('/admin/add-edit-dealer-incentive/'.$inc['id'])}}"> <i class="fa fa-edit"></i>
                                                            </a>
                                                            <a title="Delete" class="btn btn-xs red" onclick="return confirm('Are you sure you want to delete?');" href="{{url('/admin/delete-dealer-incentive/'.$inc['id'])}}" > <i class="fa fa-times"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </table>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    window.history.pushState("", "", "/admin/dealer-incentives");
</script>
@stop





