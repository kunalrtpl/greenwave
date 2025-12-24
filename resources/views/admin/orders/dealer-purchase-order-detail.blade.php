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
                                            @if(empty($poDetail['adjust_cancel_items']))
                                                <a data-status="adjustment" class="btn btn-xs btn-primary poAdjustment" href="javascript:;">Adjust</a>
                                                <a data-status="cancel" class="btn btn-xs btn-danger poAdjustment" href="javascript:;">Cancel</a>
                                            @endif
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
                        @include('admin.orders.po-adjustment-list')
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
            <form method="post" action="{{url('/admin/update-dealer-po-qty')}}">@csrf
                <input type="hidden" name="purchase_order_id" value="{{$poDetail['id']}}">
                <table class="table table-hover table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="25%">
                            Product Name
                        </th>
                        <th>
                            Dealer Price
                        </th>
                        <th>
                            Discount / Charges
                        </th>
                        <th>
                            Net Price
                        </th>
                        <th>
                            Ordered Qty
                        </th>
                        <th width="15%">
                            Approved Qty
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
                            <b>{{$orderItemInfo['product']['product_name']}}</b> 
                            @if(in_array($orderItemInfo['product']['id'],$linkedProducts))
                                <span class="badge badge-success">Linked</span>
                            @else
                                @if($poDetail['po_edited']=="no")
                                    <span id="NotLinked-{{$orderItemInfo['id']}}">
                                        <a data-itemid="{{$orderItemInfo['id']}}" data-productid="{{$orderItemInfo['product_id']}}" data-dealerid="{{$poDetail['dealer_id']}}"  href="javascript:;" class="linkDealerProduct">
                                            <span class="badge badge-danger">Not Linked</span>
                                        </a>
                                    </span>
                                @else
                                    <span class="badge badge-danger">Not Linked</span>
                                @endif
                            @endif
                            @if(isset($productCounts[$orderItemInfo['product_id']]))
                                <span class="badge badge-success text">{{$productCounts[$orderItemInfo['product_id']]}}</span>
                            @endif
                            <br><br>
                            @if($poDetail['is_mini_pack_order'] == 1)
                                <small>(Pack Size: {{$orderItemInfo['mini_pack_size']}}</small>
                            @else
                                @if(isset($orderItemInfo['packingsize']['size']))
                                    <small>(Pack Size: {{$orderItemInfo['packingsize']['size']}} kg)</small>
                                @endif
                            @endif
                             <hr class="bold-hr">
                            <?php $statuses = array('On Hold','Cancel', 'Urgent'); ?>
                            @foreach($statuses as $skey => $status)
                                <div class="form-check">
                                    <input data-orderitemid="{{$orderItemInfo['id']}}"
                                           class="form-check-input urgentOrderItem"
                                           type="radio"
                                           name="orderitemstatus[{{$orderItemInfo['id']}}]"
                                           id="{{$orderItemInfo['id']}}{{$skey}}"
                                           value="{{$status}}"
                                           @if($orderItemInfo['item_action'] == $status) checked @endif>

                                    <label class="form-check-label" for="{{$orderItemInfo['id']}}{{$skey}}">
                                        {{$status}}
                                    </label>
                                </div>

                                @if($status == 'On Hold')
                                    <div class="onHoldDateBox mt-2"
                                         id="onHoldDateBox-{{$orderItemInfo['id']}}"
                                         style="display: {{ $orderItemInfo['item_action'] == 'On Hold' ? 'block' : 'none' }};">
                                        <input id="onHoldDate-{{ $orderItemInfo['id'] }}" type="date"
                                               class="form-control onHoldDate"
                                               data-orderitemid="{{$orderItemInfo['id']}}"
                                               value="{{$orderItemInfo['on_hold_until'] ?? ''}}">
                                    </div>
                                @endif
                            @endforeach

                            <a data-orderitemid="{{$orderItemInfo['id']}}" class="btn btn-xs btn-danger clearItemStatus" href="javascript:;">Clear</a>
                        </td>
                        <td class="text-center">
                            <b>Rs. {{$orderItemInfo['product_price']}}</b>
                            @if(!empty($orderItemInfo['product']['qty_discounts']))
                            <br><br>
                            <a href="javascript:;" class="fetchProductQtyDiscounts" data-product_id="{{$orderItemInfo['product_id']}}">Qty Discounts</a>
                            @endif
                        </td>
                        <td>
                            <table class="table  table-bordered">
                                <tr>
                                    <td>Qty Disc</td>
                                    <td>{{$orderItemInfo['dealer_qty_discount']}}%</td>
                                </tr>
                                <tr>
                                    <td>Special Disc.</td>
                                    <td>{{$orderItemInfo['dealer_special_discount']}}%</td>
                                </tr>
                                <tr>
                                    <td><b>Total</b></td>
                                    <td><b>{{$orderItemInfo['dealer_qty_discount'] + $orderItemInfo['dealer_special_discount']}}%</b></td>
                                </tr>
                            </table>
                            {{-- Additional Charges --}}
                            @if($poDetail['is_mini_pack_order'] == 1)
                                @if(!empty($orderItemInfo['additional_charges']) && $orderItemInfo['additional_charges'] > 0)
                                    <table class="table  table-bordered">
                                        <tr>
                                            <td>Add. Charges</td>
                                            <td>Rs. {{ $orderItemInfo['additional_charges'] }}</td>
                                        </tr>
                                    </table>
                                @else
                                    <table class="table  table-bordered">
                                        <tr>
                                            <td>Add. Charges</td>
                                            <td>Rs. 0</td>
                                        </tr>
                                    </table>
                                @endif
                            @endif
                        </td>
                        <td class="text-center"><b>Rs. {{$orderItemInfo['net_price']}}</b></td>
                        <td>
                            @if($poDetail['is_mini_pack_order'] == 1)
                                <b><p>{{$orderItemInfo['qty']}} kg</p></b>
                            @else
                                @if($orderItemInfo['qty'] < $orderItemInfo['product']['moq'])
                                    <b><p style="color:red">{{$orderItemInfo['qty']}} kg</p></b>
                                @else
                                   <b>{{$orderItemInfo['qty']}} kg</b>
                                @endif
                                <hr class="bold-hr">
                                <p>MOQ : {{!empty($orderItemInfo['product']['moq'])? $orderItemInfo['product']['moq'] :0}} kg</p>
                                @if($orderItemInfo['qty'] < $orderItemInfo['product']['moq'])
                                    <p style="color:red"><b>Ordered Qty is less then MOQ</b></p>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($poDetail['po_edited']=="no")
                            @if(in_array($orderItemInfo['product']['id'],$linkedProducts))
                                <input type="hidden" name="product_links[]" value="1">
                            @else
                                <input type="hidden" id="ProLink-{{$orderItemInfo['id']}}" name="product_links[]" value="0">
                            @endif
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
                        <?php $subTotal = $orderItemInfo['actual_qty']  * $orderItemInfo['net_price'] ?>
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
                        <td style="text-align: right;" colspan="7">Subotal :- Rs.&nbsp;{{$poDetail['price']}} &nbsp; &nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;" colspan="7">GST :- (+) Rs.&nbsp;{{$poDetail['gst']}} &nbsp; &nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align: right;" colspan="7">Grand Total :-  Rs.&nbsp;{{$poDetail['grand_total']}} &nbsp; &nbsp;&nbsp;</td>
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
                                                        @if($saleInvoice['sale_invoice_date'] !="0000-00-00")
                                                        {{$saleInvoice['sale_invoice_date']}}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name">
                                                        Transport Name:
                                                    </div>
                                                    <div class="col-md-7 value">
                                                        {{$saleInvoice['transport_name']}}
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name">
                                                        LR No.:
                                                    </div>
                                                    <div class="col-md-7 value">
                                                        {{$saleInvoice['lr_no']}}
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name">
                                                        Dispatch Date:
                                                    </div>
                                                    <div class="col-md-7 value">
                                                        @if($saleInvoice['dispatch_date'] !="0000-00-00")
                                                        {{$saleInvoice['dispatch_date']}}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-5 name">
                                                        Delivered:
                                                    </div>
                                                    <div class="col-md-7 value">
                                                        @if($saleInvoice['is_delivered'] )
                                                            Yes
                                                        @else 
                                                            No
                                                        @endif
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
<span id="AppendAdjustModal">
        
</span>
<span id="AppendProductQtyDiscountModal">
        
</span>
<script type="text/javascript">
    $(document).on('change', '.urgentOrderItem', function () {
        var orderitemid = $(this).data('orderitemid');
        var value = $(this).val();

        // Send original AJAX request
        $.ajax({
            data: {
                value: value,
                orderitemid: orderitemid,
                _token: '{{ csrf_token() }}'
            },
            url: '/admin/mark-urgent-po-item',
            type: 'post',
            success: function (resp) {
                // You can show a toast or console log here
            }
        });

        // Show or hide the date input
        if (value === 'On Hold') {
            $('#onHoldDateBox-' + orderitemid).show();
        } else {
            $('#onHoldDateBox-' + orderitemid).hide();
        }
    });

    // Handle On Hold date selection
    $(document).on('change', '.onHoldDate', function () {
        var orderitemid = $(this).data('orderitemid');
        var date = $(this).val();

        if (!date) return;

        $.ajax({
            data: {
                orderitemid: orderitemid,
                on_hold_until: date,
                _token: '{{ csrf_token() }}'
            },
            url: '/admin/mark-po-item-on-hold-date',
            type: 'post',
            success: function (resp) {
                // Add your success message or log
            }
        });
    });

</script>
<script type="text/javascript">
    $(document).on('click', '.clearItemStatus', function () {
        var orderitemid = $(this).data('orderitemid');
        var value = '';

        $.ajax({
            data: {
                value: value,
                orderitemid: orderitemid,
                _token: '{{ csrf_token() }}'
            },
            url: '/admin/mark-urgent-po-item',
            type: 'post',
            success: function (resp) {
                // Uncheck all radios
                $('#'+orderitemid+'0').prop("checked", false);
                $('#'+orderitemid+'1').prop("checked", false);
                $('#'+orderitemid+'2').prop("checked", false);

                // Hide the on hold date field
                $('#onHoldDateBox-' + orderitemid).hide();
                $('#onHoldDate-' + orderitemid).val('');
            },
            error: function () {
                // Optional: handle error
            }
        });
    });

</script>
<script type="text/javascript">
    $(document).on('click','.linkDealerProduct',function(){
        if (confirm('Are you sure you want to link?')) {
            var dealerid  = $(this).data('dealerid');
            var productid = $(this).data('productid');
            var itemid    = $(this).data('itemid');
            $.ajax({
                data: {dealerid:dealerid,productid:productid},
                type : 'POST',
                url  : '/admin/link-dealer-product',
                success:function(resp){
                    if(resp.status){
                        $('#ProLink-'+itemid).val(1);
                        $('#NotLinked-'+itemid).html('<span class="badge badge-success">Linked</span>');
                    }
                }
            })
        }
    })

    $(document).on('click','.poAdjustment',function(){
        var status = $(this).data('status');
        $.ajax({
            data : {status:status,po_id: '{{$poDetail['id']}}'},
            url : '/admin/open-po-adjust-modal',
            type :'post',
            success:function(resp){
                $('#AppendAdjustModal').html(resp.view);
                $('#PoAdjustModal').modal('show');
            },
            error:function(){

            }

        })
    })

    $(document).on('click','.fetchProductQtyDiscounts',function(){
        var productId = $(this).data('product_id');
        $.ajax({
            data : {productId:productId},
            url : '/admin/fetch-product-qty-discounts',
            type :'post',
            success:function(resp){
                $('#AppendProductQtyDiscountModal').html(resp.view);
                $('#QtyDiscountModal').modal('show');
            },
            error:function(){

            }

        })
    })
</script>
<script type="text/javascript">
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