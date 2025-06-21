@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Purchase Orders Management</h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{!! url('admin/dashboard') !!}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        @if(Session::has('flash_message_error'))
        <div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
        @endif
        @if(Session::has('flash_message_success'))
        <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-basket font-green-sharp"></i>
                            <span class="caption-subject font-green-sharp bold uppercase">
                            Order #{{$poDetail['id']}} </span>
                            <span class="caption-helper">{{ date('d F Y h:ia',strtotime($poDetail['created_at'])) }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="portlet blue-hoki box">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-cogs"></i>Dealer Details
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Name:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$poDetail['business_name']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Mobile:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$poDetail['dealer_mobile']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Email:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$poDetail['dealer_email']}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($poDetail['customer_name']))
                                <div class="col-md-6 col-sm-12">
                                    <div class="portlet blue-hoki box">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                <i class="fa fa-cogs"></i>Customer Information
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <div class="row static-info">
                                                <div class="col-md-5 name">
                                                    Name:
                                                </div>
                                                <div class="col-md-7 value">
                                                    {{$poDetail['customer_name']}}
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-5 name">
                                                    Mobile Number:
                                                </div>
                                                <div class="col-md-7 value">
                                                    {{$poDetail['customer_mobile']}}
                                                </div>
                                            </div>
                                            <div class="row static-info">
                                                <div class="col-md-5 name">
                                                    Email:
                                                </div>
                                                <div class="col-md-7 value">
                                                    {{$poDetail['customer_email']}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="portlet blue-hoki box">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-cogs"></i>PO Details
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Purchase Order Id:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$poDetail['id']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Purchase Order Number:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$poDetail['customer_purchase_order_no']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Mode:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$poDetail['mode']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Remarks:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$poDetail['remarks']}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="portlet blue-hoki box">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-cogs"></i>PO Products
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            Product Name
                                                        </th>
                                                        <th>
                                                            Product Code
                                                        </th>
                                                        <th>
                                                            HSN Code
                                                        </th>
                                                        <th>
                                                            Market Price
                                                        </th>
                                                        <th>
                                                            Qty
                                                        </th>
                                                        <th>
                                                            Subtotal
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($poDetail['orderitems'] as $key => $orderItemInfo)
                                                    <tr>
                                                        <td>
                                                            {{$orderItemInfo['product']['product_name']}}
                                                        </td>
                                                        <td>
                                                            {{$orderItemInfo['product']['product_code']}}
                                                        </td>
                                                        <td>
                                                            {{$orderItemInfo['product']['hsn_code']}}
                                                        </td>
                                                        <td>
                                                            Rs. {{$orderItemInfo['market_price']}}
                                                        </td>
                                                        <td>
                                                            {{$orderItemInfo['qty']}}
                                                        </td>
                                                        <?php $subTotal = $orderItemInfo['qty']  * $orderItemInfo['market_price'] ?>
                                                        <td>
                                                           Rs. {{$subTotal}}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td style="text-align: right;" colspan="7">Subotal :- Rs.&nbsp;{{$poDetail['price']}} &nbsp; &nbsp;&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align: right;" colspan="7">Corporate Discount :- (-) Rs.&nbsp;{{$poDetail['corporate_discount']}} &nbsp; &nbsp;&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align: right;" colspan="7">Payment Discount :- (-) Rs.&nbsp;{{$poDetail['payment_discount']}} &nbsp; &nbsp;&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align: right;" colspan="7">GST :- (+) Rs.&nbsp;{{$poDetail['gst']}} &nbsp; &nbsp;&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="text-align: right;" colspan="7">Grand Total :-  Rs.&nbsp;{{$poDetail['grand_total']}} &nbsp; &nbsp;&nbsp;</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(!empty($poDetail['sale_invoices']))
                            @foreach($poDetail['sale_invoices'] as  $skey=> $saleInvoice)
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="portlet blue-hoki box">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-cogs"></i>Sale Invoice Details ({{++$skey}})
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name">
                                                        Sale Invoice Id:
                                                    </div>
                                                    <div class="col-md-7 value">
                                                        {{$saleInvoice['id']}}
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name">
                                                        Invoice No:
                                                    </div>
                                                    <div class="col-md-7 value">
                                                        {{$saleInvoice['dealer_invoice_no']}}
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name">
                                                        Sale Invoice Date:
                                                    </div>
                                                    <div class="col-md-7 value">
                                                        {{$saleInvoice['sale_invoice_date']}}
                                                    </div>
                                                </div>
                                                @if(!empty($saleInvoice['payment_term_type']))
                                                    <div class="row static-info">
                                                        <div class="col-md-5 name">
                                                            Payment Term Type:
                                                        </div>
                                                        <div class="col-md-7 value">
                                                            {{$saleInvoice['payment_term_type']}}
                                                        </div>
                                                    </div>
                                                    <div class="row static-info">
                                                        <div class="col-md-5 name">
                                                            Payment Term:
                                                        </div>
                                                        <div class="col-md-7 value">
                                                            {{$saleInvoice['payment_term']}} ({{$saleInvoice['payment_discount_per']}} %)
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="portlet blue-hoki box">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-cogs"></i>Sale Invoice Products
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                    Product Name
                                                                </th>
                                                                <th>
                                                                    Product Code
                                                                </th>
                                                                <th>
                                                                    HSN Code
                                                                </th>
                                                                <th>
                                                                    Market Price
                                                                </th>
                                                                <th>
                                                                    Qty
                                                                </th>
                                                                <th>
                                                                    Subtotal
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($saleInvoice['invoice_items'] as $key => $saleInvoiceInfo)
                                                            <tr>
                                                                <td>
                                                                    {{$saleInvoiceInfo['productinfo']['product_name']}}
                                                                </td>
                                                                <td>
                                                                    {{$saleInvoiceInfo['productinfo']['product_code']}}
                                                                </td>
                                                                <td>
                                                                    {{$saleInvoiceInfo['productinfo']['hsn_code']}}
                                                                </td>
                                                                <td>
                                                                    Rs. {{$saleInvoiceInfo['purchase_order_item']['market_price']}}
                                                                </td>
                                                                <td>
                                                                    {{$saleInvoiceInfo['qty']}}
                                                                </td>
                                                                <?php $subTotal = $saleInvoiceInfo['qty']  * $saleInvoiceInfo['price'] ?>
                                                                <td>
                                                                   Rs. {{$subTotal}}
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                            <tr>
                                                                <td style="text-align: right;" colspan="7">Subotal :- Rs.&nbsp;{{$saleInvoice['price']}} &nbsp; &nbsp;&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="text-align: right;" colspan="7">Corporate Discount :- (-) Rs.&nbsp;{{$saleInvoice['corporate_discount']}} &nbsp; &nbsp;&nbsp;</td>
                                                            </tr>
                                                            @if($saleInvoice['payment_term_type']=="On Bill")
                                                                <tr>
                                                                    <td style="text-align: right;" colspan="7">Payment Discount :- (-) Rs.&nbsp;{{$saleInvoice['payment_discount']}} &nbsp; &nbsp;&nbsp;</td>
                                                                </tr>
                                                            @endif
                                                            <tr>
                                                                <td style="text-align: right;" colspan="7">GST :- (+) Rs.&nbsp;{{$saleInvoice['gst']}} &nbsp; &nbsp;&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="text-align: right;" colspan="7">Grand Total :-  Rs.&nbsp;{{$saleInvoice['grand_total']}} &nbsp; &nbsp;&nbsp;</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop