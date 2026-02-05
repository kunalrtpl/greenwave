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
                    Sample D.O. Ready Invoice / Challan Required
                    </span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-container">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="15%">D.O. NO<br>(Date)</th>
                                <th width="15%">Executive</th>
                                <th width="50%">Products</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dos as $do)
                            <tr>
                                <td>
                                    {{ $do['do_ref_no'] }}
                                    <br>
                                    <small>({{ date('d M Y', strtotime($do['do_date'])) }})</small>
                                </td>
                                <td>{{ $do['name'] }}</td>
                                <td>
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Qty</th>
                                            <th>Required Through</th>
                                            <th>Action</th>
                                        </tr>
                                        @php $totalQty = 0; @endphp
                                        @foreach($do['invoices']['sale_invoices'] as $key => $row)
                                        @php $totalQty += $row['qty']; @endphp
                                        <tr>
                                            <td>{{ $row['product_name'] }}</td>
                                            <td>{{ $row['qty'] }} kg</td>
                                            <td>{{ ucwords($row['required_through']) }}</td>
                                            @if($key == 0)
                                            <td rowspan="{{ count($do['invoices']['sale_invoices']) }}">
                                                <a href="javascript:;"
                                                    class="btn btn-sm btn-primary green DoBulkUpdate"
                                                    data-saleinvoiceids="{{ $do['invoices']['sale_invoice_ids'] }}"
                                                    data-user_id="{{ $do['user_id'] }}">
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
<div class="modal fade" id="BulkUpdatesaleInvoiceModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ url('/admin/update-bulk-sample-sale-invoice') }}" method="post">
                @csrf
                <input type="hidden" name="sale_invoice_ids">
                <input type="hidden" name="user_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="sale_invoice_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control" required>
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
        $('#BulkUpdatesaleInvoiceModal').modal('show');
    });
</script>
@stop