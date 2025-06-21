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
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Status:
                                            </div>
                                            <div class="col-md-7 value">
                                                @if($poDetail['po_status'] =="pending" || $poDetail['po_status'] =="on hold")
                                                    {{ucwords($poDetail['po_status'])}} <br>
                                                    <a href="javascript:;" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#DealerPOstatusModal">Update Status</a>
                                                @elseif($poDetail['po_status'] =="completed")
                                                    @if(empty($poDetail['saleinvoices']))
                                                        @if(!empty($poDetail['adjust_items']))
                                                           Adjusted 
                                                        @elseif(!empty($poDetail['cancel_items']))
                                                           Cancelled 
                                                        @endif
                                                    @else
                                                        @if(!empty($poDetail['adjust_items']))
                                                           Partially Adjusted
                                                        @elseif(!empty($poDetail['cancel_items']))
                                                           Partially Cancelled
                                                        @endif
                                                    @endif
                                                @else
                                                    {{ucwords($poDetail['po_status'])}}
                                                @endif
                                            </div>
                                        </div>
                                        @if($poDetail['po_status'] =="rejecetd" || $poDetail['po_status'] =="on hold")

                                            <div class="row static-info">
                                                <div class="col-md-5 name">
                                                    Reason:
                                                </div>
                                                <div class="col-md-7 value">
                                                    {{$poDetail['reason']}}
                                                </div>
                                            </div>
                                        @endif
                                        @if($poDetail['po_status'] =="approved")
                                            <a class="btn btn-xs btn-primary openSaleInvoiceModal" href="javascript:;">Create Sale Invoice</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
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
            <form method="post" action="{{url('/admin/update-trader-po-qty')}}">@csrf
                <input type="hidden" name="purchase_order_id" value="{{$poDetail['id']}}">
                <table class="table table-hover table-bordered table-striped">
                <thead>
                    <tr>
                        <th>
                            Product Name
                        </th>
                        <!-- <th>
                            Product Code
                        </th>
                        <th>
                            HSN Code
                        </th> -->
                        <th>
                            Dealer Price
                        </th>
                        <th>
                            Dealer Ordered Qty
                        </th>
                        <th width="25%">
                            Actual Qty
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
                        <!-- <td>
                            {{$orderItemInfo['product']['product_code']}}
                        </td>
                        <td>
                            {{$orderItemInfo['product']['hsn_code']}}
                        </td> -->
                        <td>
                            Rs. {{$orderItemInfo['product_price']}}
                        </td>
                        <td>
                            {{$orderItemInfo['qty']}}
                        </td>
                        <td>
                            @if($poDetail['po_edited']=="no")
                            <input type="hidden" name="item_ids[]" value="{{$orderItemInfo['id']}}">
                            <input class="form-control" type="number" name="actual_qtys[]" value="{{$orderItemInfo['actual_qty']}}" required>
                            <br>
                            <textarea placeholder="Enter comments..." class="form-control" type="number" name="comments[]" value="{{$orderItemInfo['comments']}}"></textarea>
                            @else
                                {{$orderItemInfo['actual_qty']}}
                                @if(!empty($orderItemInfo['comments']))
                                    <br>
                                    Comments :- {{$orderItemInfo['comments']}}
                                @endif
                            @endif
                        </td>
                        <?php $subTotal = $orderItemInfo['actual_qty']  * $orderItemInfo['product_price'] ?>
                        <td>
                           Rs. {{$subTotal}}
                        </td>
                    </tr>
                    @endforeach
                    @if($poDetail['po_edited']=="no" && $poDetail['po_status'] !="rejected")
                        <input type="hidden" name="dealer_id" value="{{$poDetail['dealer_id']}}">
                        <tr>
                            <td style="text-align: right;" colspan="6">
                                <button class="btn btn-xs btn-success" type="submit">Submit & Approved</button>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="text-align: right;" colspan="5">Subotal :- Rs.&nbsp;{{$poDetail['price']}} &nbsp; &nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;" colspan="5">GST :- (+) Rs.&nbsp;{{$poDetail['gst']}} &nbsp; &nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;" colspan="5">Grand Total :-  Rs.&nbsp;{{$poDetail['grand_total']}} &nbsp; &nbsp;&nbsp;</td>
                    </tr>
                </tbody>
            </table>
            </form>
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
                                                                    Dealer Price
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
                                                                    Rs. {{$saleInvoiceInfo['purchase_order_item']['product_price']}}
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
<div class="modal fade" id="DealerPOstatusModal" tabindex="-1" role="dialog" aria-labelledby="DealerPOstatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="DealerPOstatusModalLabel">Update Status</h5>
            </div>
            <form action="{{url('/admin/update-dealer-po-status')}}" method="post">@csrf
                <div class="modal-body">
                    <input type="hidden" name="purchase_order_id" value="{{$poDetail['id']}}">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Status:</label>
                        <select class="form-control" name="po_status" required>
                            <option value="">Please Select</option>
                            <option value="rejected">Rejected</option>
                            <option value="on hold">On Hold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Reason:</label>
                        <select class="form-control" name="reason" required>
                            <option value="">Please Select</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Comments:</label>
                        <textarea class="form-control" name="comments"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<span id="AppendTraderSaleInvoice">
    
</span>
<script type="text/javascript">
    $(document).on('click','.openSaleInvoiceModal',function(){
        $.ajax({
            data : {purchase_order_id : '{{$poDetail['id']}}'},
            type : 'GET',
            url  :  '/admin/open-trader-sale-invoice-modal',
            success:function(resp){
                $('#AppendTraderSaleInvoice').html(resp.view);
                $('#TraderSaleInvoiceModal').modal('show');
            },
            error:function(){

            }
        }) 
    });

    $(document).on('change','[name=po_status]',function(){
        var status = $(this).val();
        if(status =="rejected"){
            $('[name=reason]').html('');
            $('[name=reason]').append('<option value="">Please Select</option><option value="Order declined due to unavailablity of material">Order declined due to unavailablity of material</option><option value="Order declined as the Product has been stopped">Order declined as the Product has been stopped</option><option value="Other">Other</option>');
        }else if(status =="on hold"){
            $('[name=reason]').html('');
            $('[name=reason]').append('<option value="">Please Select</option><option value="Order has been put on hold pending due payment">Order has been put on hold pending due payment</option><option value="Order has been put on hold as material availability/ price can not be confirmed right now">Order has been put on hold as material availability/ price can not be confirmed right now</option><option value="Other">Other</option>');
        }   
    })
</script>
@stop