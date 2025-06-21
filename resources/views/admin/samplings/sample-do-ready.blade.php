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
                            <span class="caption-subject font-green-sharp bold uppercase">Sample D.O. Ready Invoice/ Challan Required</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th width="15%">
                                            D.O. NO<br>(Date)
                                        </th>
                                        <th width="15%">
                                            Dealer/Executive
                                        </th>
                                        <th width="10%">
                                            Free/ Paid
                                        </th>
                                        <th width="50%">Products</th>
                                        <th width="10%">Transport/ Courier</th>
                                    </tr>
                                </thead>
                                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{$user['do_ref_no']}} <br><small>({{date('d M Y',strtotime($user['do_date']))}})</small></td>
                        <td>
                            {{$user['name']}}
                        </td>
                        <td>{{ucwords($user['sample_type'])}}</td>
                        <td>
<?php  $do_invoices = \App\SamplingSaleInvoice::doinvoices($user['do_ref_no']); ?>
    <table class="table table-bordered table-striped">
        <tr>
            <th width="40%">Product Name</th>
            <th width="40%">Qty</th>
            <th width="20%">Actions</th>
        </tr>
        <?php $totalQty =0; ?>
        @foreach($do_invoices['sale_invoices'] as $key=> $doinvoice)
            <?php $totalQty += $doinvoice['qty'] ?>
            <tr>
                <td>{{$doinvoice['product_name']}}</td>
                <td>{{$doinvoice['qty']}}kg</td>
                @if($key==0) 
                    <td rowspan="{{count($do_invoices['sale_invoices'])}}">
                        <a data-dealer_id="{{$user['dealer_id']}}" data-user_id="{{$user['user_id']}}" data-saleinvoiceids="{{$do_invoices['sale_invoice_ids']}}" href="javascript:;" class="btn btn-sm btn-primary green DoBulkUpdate">Update</a>
                    </td>
                @endif
            </tr>
        @endforeach 
            <tr>
                <th>Total</th>
                <th>{{$totalQty}}kg</th>
                <th></th>
            </tr>
    </table>
                        </td>
                        <td>{{ucwords($user['required_through'])}}</td>
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
<div class="modal fade" id="BulkUpdatesaleInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="BulkUpdatesaleInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="BulkUpdatesaleInvoiceModalLabel">Sale Invoice</h5>
            </div>
            <form action="{{url('/admin/update-bulk-sample-sale-invoice')}}" method="post" autocomplete="off">@csrf
                <input type="hidden" name="sale_invoice_ids" value="">
                <input type="hidden" name="dealer_id" value="">
                <input type="hidden" name="user_id" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sale-date" class="col-form-label">Date:</label>
                        <input type="date" name="sale_invoice_date" class="form-control" id="sale-date" required>
                    </div>
                    <div class="form-group">
                        <label for="invoice-number" class="col-form-label">Invoice Number:</label>
                        <input type="text" name="invoice_number" placeholder="Enter Invoice Number" class="form-control" id="invoice-number" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click','.DoBulkUpdate',function(){
        var saleinvoiceids = $(this).data('saleinvoiceids');
        $('[name=sale_invoice_ids]').val(saleinvoiceids);
        $('[name=dealer_id]').val($(this).data('dealer_id'));
        $('[name=user_id]').val($(this).data('user_id'));
        $('#BulkUpdatesaleInvoiceModal').modal('show');
    })
</script>
@stop


