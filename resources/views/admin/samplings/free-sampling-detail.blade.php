@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Samplings Management</h1>
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
                            Sampling #{{$sampleDetails['id']}} </span>
                            <span class="caption-helper">{{ date('d F Y h:ia',strtotime($sampleDetails['created_at'])) }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            
                            <div class="col-md-6 col-sm-12">
                                <div class="portlet blue-hoki box">
                                    @if(!empty($sampleDetails['dealer']))
                                        <div class="portlet-title">
                                            <div class="caption">
                                                <i class="fa fa-cogs"></i>Dealer Details
                                            </div>
                                        </div>
                                    @else
                                        <div class="portlet-title">
                                            <div class="caption">
                                                <i class="fa fa-cogs"></i>Executive Details
                                            </div>
                                        </div>
                                    @endif

                                    <div class="portlet-body">
                                        @if(!empty($sampleDetails['dealer']))
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Name:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['dealer']['business_name']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Mobile:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['dealer']['owner_mobile']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Email:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['dealer']['email']}}
                                            </div>
                                        </div>
                                        @else
                                            <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Name:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['user']['name']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Mobile:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['user']['mobile']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Email:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['user']['email']}}
                                            </div>
                                        </div>
                                        @endif
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Status:
                                            </div>
                                            <div class="col-md-7 value">
                                                @if($sampleDetails['sample_status'] =="pending" || $sampleDetails['sample_status'] =="on hold")
                                                    {{ucwords($sampleDetails['sample_status'])}} <br>
                                                    <a href="javascript:;" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#DealerPOstatusModal">Update Status</a>
                                                @elseif($sampleDetails['sample_status'] =="completed")
                                                    @if(empty($sampleDetails['saleinvoices']))
                                                        @if(!empty($sampleDetails['adjust_items']))
                                                           Adjusted 
                                                        @elseif(!empty($sampleDetails['cancel_items']))
                                                           Cancelled 
                                                        @endif
                                                    @else
                                                        @if(!empty($sampleDetails['adjust_items']))
                                                           Partially Adjusted
                                                        @elseif(!empty($sampleDetails['cancel_items']))
                                                           Partially Cancelled
                                                        @endif
                                                    @endif
                                                @else
                                                    {{ucwords($sampleDetails['sample_status'])}}
                                                @endif
                                            </div>
                                        </div>
                                        @if($sampleDetails['sample_status'] =="rejecetd" || $sampleDetails['sample_status'] =="on hold")

                                            <div class="row static-info">
                                                <div class="col-md-5 name">
                                                    Reason:
                                                </div>
                                                <div class="col-md-7 value">
                                                    {{$sampleDetails['reason']}}
                                                </div>
                                            </div>
                                        @endif
                                        <!-- @if($sampleDetails['sample_status'] =="approved")
                                            @if(empty($sampleDetails['adjust_cancel_items']))
                                                <a data-status="adjustment" class="btn btn-xs btn-primary poAdjustment" href="javascript:;">Adjust</a>
                                                <a data-status="cancel" class="btn btn-xs btn-danger poAdjustment" href="javascript:;">Cancel</a>
                                            @endif
                                        @endif -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="portlet blue-hoki box">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-cogs"></i>Sample Details
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Sample Request Id:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['id']}}
                                            </div>
                                        </div>
                                        <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Sample Ref Number:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['sample_ref_no_string']}}
                                            </div>
                                        </div>
                                        <!-- <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Remarks:
                                            </div>
                                            <div class="col-md-7 value" style="color:red;">
                                                {{$sampleDetails['remarks']}}
                                            </div>
                                        </div> -->
                                         <div class="row static-info">
                                            <div class="col-md-5 name">
                                                Mode of Transaport:
                                            </div>
                                            <div class="col-md-7 value">
                                                {{$sampleDetails['required_through']}}
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
                                            <i class="fa fa-cogs"></i> Products
                                        </div>
                                    </div>
    <div class="portlet-body">
        <div class="table-responsive">
            <form method="post" action="{{url('/admin/update-sampling-qty')}}">@csrf
                <input type="hidden" name="sampling_id" value="{{$sampleDetails['id']}}">
                <table class="table table-hover table-bordered table-striped">
                <thead>
                    <tr>
                        <th>
                            Product Name
                        </th>
                        <th>
                            Requested Qty
                        </th>
                        <th width="25%">
                            Approved Qty
                            <br>
                            (Approved Pack Size)
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sampleDetails['sampleitems'] as $key => $orderItemInfo)
                    <tr>
                        <td>
                            {{$orderItemInfo['product']['product_name']}}
                                @if(!empty($sampleDetails['dealer']))
                                @if(in_array($orderItemInfo['product']['id'],$linkedProducts))
                                    <span class="badge badge-success">Linked</span>
                                @else
                                    @if($sampleDetails['sample_edited']=="no")
                                        <span id="NotLinked-{{$orderItemInfo['id']}}">
                                            <a data-itemid="{{$orderItemInfo['id']}}" data-productid="{{$orderItemInfo['product_id']}}" data-dealerid="{{$sampleDetails['dealer_id']}}"  href="javascript:;" class="linkDealerProduct">
                                                <span class="badge badge-danger">Not Linked</span>
                                            </a>
                                        </span>
                                    @else
                                        <span class="badge badge-danger">Not Linked</span>
                                    @endif
                                @endif
                            <br><br>
                            @endif
                            <?php $statuses = array('On Hold','Cancel', 'Urgent'); ?>
                            @foreach($statuses as $skey=> $status)
                            <div class="form-check">
                                <input data-orderitemid="{{$orderItemInfo['id']}}" class="form-check-input urgentOrderItem" type="radio" name="orderitemstatus[{{$orderItemInfo['id']}}]" id="{{$orderItemInfo['id']}}{{$skey}}" value="{{$status}}" @if($orderItemInfo['item_action']==$status) checked @endif>
                                <label class="form-check-label" for="{{$orderItemInfo['id']}}{{$skey}}">
                                {{$status}}
                                </label>
                            </div>
                            @endforeach
                            <a data-orderitemid="{{$orderItemInfo['id']}}" class="btn btn-xs btn-danger clearItemStatus" href="javascript:;">Clear</a>
                        </td>
                        <td>
                            {{$orderItemInfo['qty']}} kg
                            <br>
                            Remarks :- <b style="color:red;">
                            {{$sampleDetails['remarks']}}</b>
                        </td>
                        <td>
                            @if($sampleDetails['sample_edited']=="no")
                            @if(in_array($orderItemInfo['product']['id'],$linkedProducts))
                                <input type="hidden" name="product_links[]" value="1">
                            @else
                                <input type="hidden" id="ProLink-{{$orderItemInfo['id']}}" name="product_links[]" value="0">
                            @endif
                            <input type="hidden" name="item_ids[]" value="{{$orderItemInfo['id']}}">
                            <input class="form-control" type="number" name="actual_qtys[]" value="{{$orderItemInfo['actual_qty']}}" min="1" step="1" required>
                             <br>
                            <input class="form-control packSize" type="number" name="actual_pack_sizes[]" value="" placeholder="Fill Pack Size"  oninput="check(this)" required>
                            <br>
                            <textarea placeholder="Enter comments..." class="form-control" type="text" name="comments[]" value="{{$orderItemInfo['comments']}}"></textarea>
                            <br>
                            <select name="required_through" class="form-control" required>
                                <option value="">Mode of Transport</option>
                                <option value="courier">Courier</option>
                                <option value="transport">Transport</option>
                            </select>
                            @else
                                {{$orderItemInfo['actual_qty']}}kg
                                <br>
                                <small>({{$orderItemInfo['actual_pack_size']}}kg Packing)</small>
                                @if(!empty($orderItemInfo['comments']))
                                    <br>
                                    Comments :- {{$orderItemInfo['comments']}}
                                @endif
                            @endif
                            
                        </td>
                    </tr>
                    @endforeach
                    @if($sampleDetails['sample_edited']=="no" && $sampleDetails['sample_status'] !="rejected")
                        <tr>
                            <td style="text-align: right;" colspan="6">
                                <button onclick="return confirm('Are you sure?')" class="btn btn-xs btn-success" type="submit">Submit & Approved</button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            </form>
        </div>
    </div>
                                </div>
                            </div>
                        </div>
                        @if(!empty($sampleDetails['sale_invoices']))
@foreach($sampleDetails['sale_invoices'] as  $skey=> $saleInvoice)
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
                        {{$saleInvoice['invoice_no']}}
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
                                    Price
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
                            
                            <tr>
                                <td>
                                    {{$saleInvoice['productinfo']['product_name']}}
                                </td>
                                <td>
                                    {{$saleInvoice['productinfo']['product_code']}}
                                </td>
                                <td>
                                    Rs. {{$saleInvoice['price']}}
                                </td>
                                <td>
                                    {{$saleInvoice['qty']}}
                                </td>
                                <td>
                                    Rs. {{$saleInvoice['subtotal']}}
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: right;" colspan="7">Subotal :- Rs.&nbsp;{{$saleInvoice['subtotal']}} &nbsp; &nbsp;&nbsp;</td>
                            </tr>
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
            <form action="{{url('/admin/update-sampling-status')}}" method="post">@csrf
                <div class="modal-body">
                    <input type="hidden" name="sampling_id" value="{{$sampleDetails['id']}}">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Status:</label>
                        <select class="form-control" name="sample_status" required>
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
<script type="text/javascript">
    $(document).on('change','.urgentOrderItem',function(){
        var orderitemid = $(this).data('orderitemid');
        var value = $( this ).val();
         $.ajax({
            data : {value:value,orderitemid: orderitemid},
            url : '/admin/mark-urgent-sample-item',
            type :'post',
            success:function(resp){
            },
            error:function(){

            }

        })
    })
</script>
<script type="text/javascript">
    $(document).on('click','.clearItemStatus',function(){
        var orderitemid = $(this).data('orderitemid');
        var value = '';
         $.ajax({
            data : {value:value,orderitemid: orderitemid},
            url : '/admin/mark-urgent-sample-item',
            type :'post',
            success:function(resp){
                $('#'+orderitemid+'0').attr("checked" , false );
                $('#'+orderitemid+'1').attr("checked" , false );
                $('#'+orderitemid+'2').attr("checked" , false );
            },
            error:function(){

            }

        })
    })
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
            data : {status:status,sampling_id: '{{$sampleDetails['id']}}'},
            url : '/admin/open-sample-adjust-modal',
            type :'post',
            success:function(resp){
                $('#AppendAdjustModal').html(resp.view);
                $('#PoAdjustModal').modal('show');
            },
            error:function(){

            }

        })
    })
</script>
<script type="text/javascript">
    $(document).on('change','[name=sample_status]',function(){
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
<script>
 function check(input) {
   if (input.value == 0) {
        $('.packSize').val(1);
   }
 }
</script>
@stop