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
                <h1>Samplings Management</h1>
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
        @if(Session::has('flash_message_success'))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
        @endif
        @if(isset($_GET['s']))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> Record has been updated Sucessfully. </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">Dispatched Sampling</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <form class="form-inline" method="get" action="{{url('/admin/sample-dispatched-material')}}">
                        <div class="form-group mb-4">
                            <label class="sr-only">Dealer/Executive Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Serach By Dealer/ Executive Name">
                        </div>
                        <div class="form-group mb-2">
                            <label for="staticEmail2" class="sr-only">Select Product</label>
                            <select name="product_id" class="form-control select2">
                                <option value="">Select Product</option>
                                @foreach(products() as $product)
                                    <option value="{{$product['id']}}">{{$product['product_name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label class="sr-only">Batch No</label>
                            <input type="text" class="form-control" name="batch_no" placeholder="Serach By Batch No.">
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">Filter</button>
                    </form>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th width="15%">
                                            Invoice No.<br>(Date)
                                        </th>
                                        <th width="15%">
                                            Ref No.
                                        </th>
                                        <th width="15%">
                                            Dealer/Executive
                                        </th>
                                        <th width="10%">
                                            Free/ Paid
                                        </th>
                                        <th width="30%">Products</th>
                                        <th width="20%">LR NO. <br>(Date)</th>
                                        <th width="10%">Transport/ Courier</th>
                                    </tr>
                                </thead>
                                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{$user['invoice_no']}} <br><small>({{date('d M Y',strtotime($user['sale_invoice_date']))}})</small></td>
                        <td>{{$user['sample_ref_no_string']}}</td>
                        <td>
                            {{$user['name']}}
                        </td>
                        <td>{{ucwords($user['sample_type'])}}</td>
                        <td>
                        <?php  $dispacthed_materials = \App\SamplingSaleInvoice::dispatchedMaterials($user['invoice_no'],$data); ?>
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th width="40%">Product Name</th>
                                    <th width="20%">Qty</th>
                                    <th width="20%">Batch No.</th>
                                    <th width="20%">Price</th>
                                </tr>
                                <?php $totalQty =0; ?>
                                @foreach($dispacthed_materials['sale_invoices'] as $key=> $dispatched)
                                    <?php $totalQty += $dispatched['qty'] ?>
                                    <tr>
                                        <td>{{$dispatched['product_name']}}</td>
                                        <td>{{$dispatched['qty']}}kg</td>
                                        <td>{{$dispatched['batch_no']}}</td>
                                        <td>{{$dispatched['price']}}</td>
                                    </tr>
                                @endforeach 
                                    <tr>
                                        <th>Total</th>
                                        <th>{{$totalQty}}kg</th>
                                    </tr>
                            </table>
                        </td>
                        <td>{{$user['lr_no']}}<br><small>({{date('d M Y',strtotime($user['dispatch_date']))}})</small></td>
                        <td>{{ucwords($user['required_through'])}}</td>
                    </tr>
                @endforeach
                                </tbody>
                            </table>
                            {{$users->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop


