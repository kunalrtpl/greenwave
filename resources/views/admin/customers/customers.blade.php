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
                <h1>Customers Management</h1>
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
                            <span class="caption-subject font-green-sharp bold uppercase">Customers</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="btn-group">
                                       <a href="{{action('Admin\CustomerController@addEditCustomer')}}" class="btn btn-primary">Add Customer</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th>
                                            Id
                                        </th>
                                        <!-- <th >
                                            Category
                                        </th> -->
                                         <th>
                                            Customer Name
                                        </th>
                                        <th>
                                            City
                                        </th>
                                        <th>
                                            Business Linking
                                        </th>
                                        <th>
                                            Linked Executive
                                        </th>
                                        <th>Email</th>
                                        <th class="text-center">B. Card</th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    <tr role="row" class="filter">
                                        <td></td>
                                        <!-- <td>
                                            <input type="text" class="form-control form-filter input-sm" name="category" placeholder="Category">
                                        </td> -->
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="name" placeholder="Customer Name">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="city_name" placeholder="City">
                                        </td>
                                        <td>
                                            <select class="form-control form-filter input-sm" name="business_linking">
                                                    <option value="All">All</option>
                                                    <option value="Open">Open</option>
                                                    <option value="Direct Customer">Direct Customer</option>
                                                    @foreach($linkedDealers as $dealer)
                                                        <option value="{{$dealer['id']}}">{{$dealer['business_name']}}</option>
                                                    @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <!-- <input type="text" class="form-control form-filter input-sm" name="linked_executive" placeholder="Linked Exceutive"> -->
                                            <select class="form-control form-filter input-sm" name="linked_executive">
                                            <option value="All">All</option>
                                            @foreach($executives as $executive)
                                                <option value="{{$executive->name}}">{{$executive->name}}</option>
                                            @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control form-filter input-sm" name="email_status">
                                                    <option value="All">All</option>
                                                    <option value="tick">&#10004;</option>
                                                    <option value="cross">&#10006;</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control form-filter input-sm" name="b_card_status">
                                                    <option value="All">All</option>
                                                    <option value="tick">&#10004;</option>
                                                    <option value="cross">&#10006;</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control form-filter input-sm" name="status">
                                                    <option value="All">All</option>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="margin-bottom-5">
                                                <button class="btn btn-sm yellow filter-submit margin-bottom"><i title="Search" class="fa fa-search"></i></button>
                                                <button class="btn btn-sm red filter-cancel"><i title="Reset" class="fa fa-refresh"></i></button>
                                            </div>
                                        </td>
                                    </tr>
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
<script type="text/javascript">
    window.history.pushState("", "", "/admin/customers");
</script>
@stop


