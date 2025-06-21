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
                <h1>Orders Management</h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
            </li>
        </ul>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase"> Dispatched Material </span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <form class="form-inline" method="get" action="{{url('admin/dispatched-material')}}">
                        <div class="form-group mb-4">
                            <label class="sr-only">Dealer/Csutomer</label>
                            <input type="text" class="form-control" name="name" placeholder="Serach By Dealer/ Csutomer Name">
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
                                        <th>
                                            Invoice NO<br><small>(Date)</small>
                                        </th>
                                        <th>
                                            Type
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th width="40%">Products</th>
                                        <th>LR NO.
                                            <br><small>(Date)</small>
                                        </th>
                                        <th>Transport</th>
                                    </tr>
                                </thead>
                                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{$user['dealer_invoice_no']}} <br><small>({{date('d M Y',strtotime($user['sale_invoice_date']))}})</small></td>
                        <td>
                            {{ucwords($user['action'])}}
                        </td>
                        <td>
                            {{$user['name']}}
                        </td>
                        <td>
<?php  $do_invoices = \App\SaleInvoice::dispatchedMaterials($user['dealer_invoice_no'],$data); ?>
    <table class="table table-bordered table-striped">
        <tr>
            <th width="40%">Product Name</th>
            <th width="20%">Qty</th>
            <th width="20%">Batch No.</th>
            <th width="20%">DP</th>
        </tr>
        <?php $totalQty =0; ?>
        @foreach($do_invoices['sale_invoices'] as $key=> $doinvoice)
            @foreach($doinvoice['invoice_items'] as $invItem)
            <?php $totalQty += $invItem['qty'] ?>
            <tr>
                <td>{{$invItem['productinfo']['product_name']}}</td>
                <td>{{$invItem['qty']}} kg</td>
                <td>{{$invItem['batch_no']}}</td>
                <td>{{$invItem['price']}}</td>
            </tr>
        @endforeach 
         @endforeach
            <tr>
                <th>Total</th>
                <th>{{$totalQty}}kg</th>
                <th></th>
            </tr>
    </table>
                        </td>
                        <td>{{$user['lr_no']}} <br><small>({{$user['dispatch_date']}})</small></td>
                        <td>{{$user['transport_name']}}</td>
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

