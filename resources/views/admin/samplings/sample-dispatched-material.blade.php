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
                    Dispatched Sampling
                    </span>
                </div>
            </div>
            <form class="form-inline" method="get" action="{{ url('/admin/sample-dispatched-material') }}">
                <div class="form-group">
                    <input type="text" class="form-control" name="name"
                        placeholder="Search by Executive Name"
                        value="{{ $data['name'] ?? '' }}">
                </div>
                <div class="form-group">
                    <select name="product_id" class="form-control select2">
                        <option value="">Select Product</option>
                        @foreach(products() as $product)
                        <option value="{{ $product['id'] }}"
                        {{ ($data['product_id'] ?? '') == $product['id'] ? 'selected' : '' }}>
                        {{ $product['product_name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="batch_no"
                        placeholder="Search by Batch No"
                        value="{{ $data['batch_no'] ?? '' }}">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
            <div class="portlet-body">
                <div class="table-container">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Invoice No.<br>(Date)</th>
                                <th>Ref No.</th>
                                <th>Executive</th>
                                <th>Products</th>
                                <th>LR No.<br>(Date)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    {{ $user->invoice_no }}
                                    <br>
                                    <small>({{ date('d M Y',strtotime($user->sale_invoice_date)) }})</small>
                                </td>
                                <td>{{ $user->sample_ref_no_string }}</td>
                                <td>{{ $user->name }}</td>
                                <td>
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Batch</th>
                                            <th>Required Through</th>
                                        </tr>
                                        @php $totalQty = 0; @endphp
                                        @foreach($user->items as $row)
                                        @php $totalQty += $row['qty']; @endphp
                                        <tr>
                                            <td>{{ $row['product_name'] }}</td>
                                            <td>{{ $row['qty'] }} kg</td>
                                            <td>{{ $row['batch_no'] }}</td>
                                            <td>{{ ucwords($row['required_through']) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <th>Total</th>
                                            <th>{{ $totalQty }} kg</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    {{ $user->lr_no }}
                                    <br>
                                    <small>({{ date('d M Y',strtotime($user->dispatch_date)) }})</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop