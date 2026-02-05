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
                    Sample Finalize D.O.
                    </span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-container">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="15%">Type</th>
                                <th width="15%">Name</th>
                                <th width="70%">Products</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>Executive</td>
                                <td>{{ $user['name'] }}</td>
                                <td>
                                    @foreach($requiredThroughs as $required)
                                    @php
                                    $data = $user['invoices'][$required];
                                    @endphp
                                    @if(!empty($data['sale_invoices']))
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th colspan="5" style="background: green; color:#fff;">
                                                {{ strtoupper($required) }}
                                            </th>
                                        </tr>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Qty (Pack)</th>
                                            <th>Comments</th>
                                            <th>#</th>
                                            <th>Action</th>
                                        </tr>
                                        @php $totalQty = 0; @endphp
                                        @foreach($data['sale_invoices'] as $key => $row)
                                        @php $totalQty += $row['qty']; @endphp
                                        <tr>
                                            <td>{{ $row['product_name'] }}</td>
                                            <td>
                                                {{ $row['qty'] }} kg ({{ $row['actual_pack_size'] }} kg)
                                            </td>
                                            <td>
                                                {{ $row['comments'] }}
                                            </td>
                                            <td>
                                                <a class="btn btn-xs btn-danger"
                                                    href="{{ url('admin/undo-sampling-finalize-do/'.$row['sale_invoice_id'].'/'.$row['sampling_item_id']) }}">
                                                <i class="fa fa-times"></i>
                                                </a>
                                            </td>
                                            @if($key == 0)
                                            <td rowspan="{{ count($data['sale_invoices']) }}">
                                                <a href="javascript:;"
                                                    data-saleinvoiceids="{{ $data['sale_invoice_ids'] }}"
                                                    class="btn btn-sm btn-primary green generateSampleDO">
                                                Generate D.O.
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <th>Total</th>
                                            <th>{{ $totalQty }} kg</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </table>
                                    @endif
                                    @endforeach
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
<script>
    $(document).on('click','.generateSampleDO',function(){
        $('.loadingDiv').show();
        $.post('/admin/sampling-generate-do-numbers',{
            sale_invoice_ids : $(this).data('saleinvoiceids'),
            type : '{{ $type }}'
        },function(resp){
            window.location.href = resp.url;
        });
    });
</script>
@stop