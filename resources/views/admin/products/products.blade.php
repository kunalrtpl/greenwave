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
                <h1>Products Management</h1>
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
                            <span class="caption-subject font-green-sharp bold uppercase">Products</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="btn-group">
                                       <a href="{{action('Admin\ProductsController@addEditProduct')}}" class="btn btn-primary">Add Product</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th class="text-center" width="4%">
                                            S.No.
                                        </th>
                                        <th >
                                            Product Type
                                        </th>
                                        <th>
                                            Product Name
                                        </th>
                                         <th>
                                            Version
                                        </th>
                                       <!--  <th>
                                            Dealer Markup (%)
                                        </th> -->
                                        <th class="text-right">
                                            Dealer Price
                                        </th>
                                        <th class="text-right">
                                            Market Price
                                        </th>
                                        <th class="text-center">
                                            Status
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    <tr role="row" class="filter">
                                        <td></td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control form-filter input-sm" name="product_type">
                                                    <option value="-1">All</option>
                                                    @foreach(product_types() as $key=> $productType)
                                                        <option value="{{$key}}">{{$productType}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="product_name" placeholder="Product Name">
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
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
    window.history.pushState("", "", "/admin/products");
</script>
@stop





