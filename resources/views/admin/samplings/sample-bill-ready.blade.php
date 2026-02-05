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
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject font-green-sharp bold uppercase">
                    Sample Bill Ready Dispatched Required
                    </span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-container">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="15%">Invoice No.<br>(Date)</th>
                                <th width="15%">Executive</th>
                                <th width="50%">Products</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    {{ $invoice['invoice_no'] }}
                                    <br>
                                    <small>({{ date('d M Y', strtotime($invoice['sale_invoice_date'])) }})</small>
                                </td>
                                <td>{{ $invoice['name'] }}</td>
                                <td>
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Qty</th>
                                            <th>Required Through</th>
                                            <th>Action</th>
                                        </tr>
                                        @php $totalQty = 0; @endphp
                                        @foreach($invoice['items']['sale_invoices'] as $key => $row)
                                        @php $totalQty += $row['qty']; @endphp
                                        <tr>
                                            <td>{{ $row['product_name'] }}</td>
                                            <td>{{ $row['qty'] }} kg</td>
                                            <td>{{ ucwords($row['required_through']) }}</td>
                                            @if($key == 0)
                                            <td rowspan="{{ count($invoice['items']['sale_invoices']) }}">
                                                <a href="javascript:;"
                                                    class="btn btn-sm btn-primary green DoBulkUpdate"
                                                    data-saleinvoiceids="{{ $invoice['items']['sale_invoice_ids'] }}"
                                                    data-user_id="{{ $invoice['user_id'] }}">
                                                Update
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <th>Total</th>
                                            <th>{{ $totalQty }} kg</th>
                                            <th></th>
                                        </tr>
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
<div class="modal fade" id="BulkUpdateLrNosaleInvoiceModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="BulkUpdateLrNosaleInvoiceModalLabel">Update Dispatch Details</h5>
            </div>
            <form action="{{ url('/admin/update-bulk-sample-lr-sale-invoice') }}" method="post">
                @csrf
                <input type="hidden" name="sale_invoice_ids">
                <input type="hidden" name="user_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="invoice-number" class="col-form-label">Transport/ Courier Name:</label>
                        <input type="text" name="transport_name" placeholder="Enter Transport Name" class="form-control" id="invoice-number" required>
                    </div>
                    <div class="form-group">
                        <label>Dispatch Date</label>
                        <input type="date" name="dispatch_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>LR Number</label>
                        <input type="text" name="lr_no" class="form-control" required placeholder="Enter LR Number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).on('click','.DoBulkUpdate',function(){
        $('[name=sale_invoice_ids]').val($(this).data('saleinvoiceids'));
        $('[name=user_id]').val($(this).data('user_id'));
        $('#BulkUpdateLrNosaleInvoiceModal').modal('show');
    });
</script>
@stop