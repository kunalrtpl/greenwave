@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    .page-content { padding-bottom: 40px !important; }

    .portlet.light.bordered {
        border-radius: 6px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.08);
        border: 1px solid #dde3ec;
    }

    /* ── Summary bar ── */
    .summary-bar { display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
    .sum-card {
        background: #f4f6fa; border: 1px solid #dde3ec; border-radius: 6px;
        padding: 8px 16px; font-size: 12px; color: #5a6a85;
        display: flex; align-items: center; gap: 6px;
    }
    .sum-card strong { font-size: 15px; color: #2d3748; }
    .sum-card .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .dot-total { background: #3598dc; }
    .dot-qty   { background: #38a169; }
    .dot-value { background: #ed8936; }

    /* ── Filter strip — same pattern as Product Pricing ── */
    .filter-strip {
        display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap;
        background: #f4f6fa; border: 1px solid #dde3ec;
        border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;
    }
    .filter-strip .fg {
        display: flex; flex-direction: column; gap: 3px; flex-shrink: 0;
    }
    .filter-strip .fg > label {
        font-size: 10px; font-weight: 700; color: #5a6a85;
        text-transform: uppercase; letter-spacing: 0.5px;
        margin: 0; white-space: nowrap;
    }
    .filter-strip select.form-control,
    .filter-strip .select2-container {
        min-height: 30px; font-size: 12px;
    }
    .filter-strip .select2-container .select2-selection--single,
    .filter-strip .select2-container .select2-selection--multiple {
        border: 1px solid #c8d0dc !important; border-radius: 4px !important;
        min-height: 30px;
    }
    #filter-type      { width: 260px; height: 30px; border: 1px solid #c8d0dc; border-radius: 4px !important; font-size: 12px; padding: 0 8px; }
    #filter-products,
    #filter-dealers,
    #filter-customers { width: 220px; }

    .f-sep { width: 1px; height: 30px; background: #d5dbe8; flex-shrink: 0; margin: 0 2px; }

    .btn-generate {
        height: 30px; padding: 0 16px; font-size: 12px; font-weight: 700;
        border-radius: 4px !important; border: 1px solid #3598dc;
        background: #3598dc; color: #fff; cursor: pointer;
        text-transform: uppercase; letter-spacing: 0.4px;
        display: inline-flex; align-items: center; gap: 5px;
        transition: all 0.15s; white-space: nowrap;
    }
    .btn-generate:hover { background: #2a80c0; color: #fff; }

    .btn-clear-filters {
        height: 30px; padding: 0 10px; font-size: 11px; font-weight: 700;
        border-radius: 4px !important; border: 1px solid #c8d0dc;
        background: #fff; color: #718096; cursor: pointer;
        text-transform: uppercase; letter-spacing: 0.4px;
        display: inline-flex; align-items: center; gap: 4px;
        transition: all 0.15s; white-space: nowrap; text-decoration: none;
    }
    .btn-clear-filters:hover { background: #fed7d7; border-color: #fc8181; color: #c53030; text-decoration: none; }

    .btn-export-pdf {
        display: inline-flex; align-items: center; gap: 5px;
        height: 30px; padding: 0 12px; font-size: 11px; font-weight: 600;
        border-radius: 4px !important; border: 1px solid #e53e3e;
        background: #fff5f5; color: #c53030; cursor: pointer;
        text-decoration: none; white-space: nowrap; transition: all 0.15s;
    }
    .btn-export-pdf:hover { background: #e53e3e; color: #fff; text-decoration: none; }

    /* ── Results table ── */
    .report-wrap { width: 100%; overflow-x: auto; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 22px; }
    .report-table thead tr th {
        background: #eef1f7; color: #4a5568; font-weight: 700; font-size: 11px;
        text-transform: uppercase; letter-spacing: 0.55px; padding: 10px 12px;
        border: 1px solid #d5dbe8; white-space: nowrap; text-align: left;
    }
    .report-table thead tr th.right  { text-align: right; }
    .report-table thead tr th.center { text-align: center; }
    .report-table tbody td {
        padding: 8px 12px; border: 1px solid #e4e9f2; vertical-align: middle;
        color: #2d3748; background: #fff;
    }
    .report-table tbody td.right  { text-align: right; }
    .report-table tbody td.center { text-align: center; }
    .report-table tbody tr:nth-child(even) td { background: #fafbfd; }
    .report-table tbody tr:hover td { background: #f8faff; }
    .report-table tfoot tr th {
        background: #eef1f7; border: 1px solid #d5dbe8; padding: 8px 12px;
        font-size: 12px; color: #2d3748;
    }

    .prod-name { font-weight: 600; color: #2d3748; }
    .prod-pack { font-size: 11px; color: #a0aec0; margin-left: 4px; }

    .product-block-title {
        display: flex; align-items: center; justify-content: space-between;
        background: #eef1f7; border: 1px solid #d5dbe8; border-radius: 4px;
        padding: 8px 12px; margin: 18px 0 0;
        font-size: 12.5px; font-weight: 700; color: #2d3748;
    }
    .product-block-title .pend-total { color: #276749; font-weight: 700; }

    .empty-state {
        text-align: center; padding: 40px 20px; color: #a0aec0; font-style: italic;
    }

    .grand-total-bar {
        text-align: right; font-size: 13px; color: #2d3748; padding: 8px 4px;
    }
    .grand-total-bar strong { color: #276749; }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">

            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-file-text-o font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">Pending Orders Report</span>
                </div>
            </div>

            <div class="portlet-body">

                {{-- Summary cards — shown only once a report has been generated --}}
                @if(!is_null($reportData))
                <div class="summary-bar">
                    <div class="sum-card">
                        <span class="dot dot-total"></span>
                        Products &nbsp;<strong>{{ isset($reportData['rows']) ? count($reportData['rows']) : count($reportData['products']) }}</strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-qty"></span>
                        Total Pending Qty &nbsp;<strong>{{ number_format($reportData['total_qty']) }} kg</strong>
                    </div>
                    @if(isset($reportData['total_value']))
                    <div class="sum-card">
                        <span class="dot dot-value"></span>
                        Total Value &nbsp;<strong>&#8377; {{ format_indian_number($reportData['total_value']) }}</strong>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Filters --}}
                <form method="POST" action="{{ route('admin.pending-orders-report.generate') }}" id="report-filter-form">
                    @csrf
                    <div class="filter-strip">

                        <div class="fg">
                            <label><i class="fa fa-file-text-o"></i> Report Type</label>
                            <select name="type" id="filter-type" required>
                                <option value="">— Please Select —</option>
                                <option value="admin_pending_orders_product_consolidated" {{ $selectedType == 'admin_pending_orders_product_consolidated' ? 'selected' : '' }}>
                                    Product Wise (Consolidated)
                                </option>
                                <option value="admin_pending_orders_product_detailed" {{ $selectedType == 'admin_pending_orders_product_detailed' ? 'selected' : '' }}>
                                    Product Wise (Detailed)
                                </option>
                            </select>
                        </div>

                        <span class="f-sep"></span>

                        <div class="fg">
                            <label><i class="fa fa-cube"></i> Products</label>
                            <select name="product_ids[]" id="filter-products" class="select2" multiple="multiple" data-placeholder="All Products">
                                @foreach($products as $p)
                                    <option value="{{ $p['id'] }}" {{ in_array($p['id'], $selectedProductIds) ? 'selected' : '' }}>
                                        {{ $p['product_name'] }} ({{ $p['size'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fg">
                            <label><i class="fa fa-truck"></i> Dealers</label>
                            <select name="filter_dealer_ids[]" id="filter-dealers" class="select2" multiple="multiple" data-placeholder="All Dealers">
                                @foreach($dealers as $d)
                                    <option value="{{ $d['id'] }}" {{ in_array($d['id'], $selectedDealerIds) ? 'selected' : '' }}>
                                        {{ $d['business_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fg">
                            <label><i class="fa fa-user"></i> Customers</label>
                            <select name="filter_customer_ids[]" id="filter-customers" class="select2" multiple="multiple" data-placeholder="All Customers">
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ in_array($c->id, $selectedCustomerIds) ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <span class="f-sep"></span>

                        <div class="fg">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn-generate">
                                <i class="fa fa-search"></i> Generate
                            </button>
                        </div>

                        <div class="fg">
                            <label>&nbsp;</label>
                            <a href="{{ route('admin.pending-orders-report.index') }}" class="btn-clear-filters">
                                <i class="fa fa-times"></i> Clear
                            </a>
                        </div>

                        @if(!is_null($reportData))
                        <div class="fg">
                            <label>&nbsp;</label>
                            <a href="#" id="btn-export-pdf" class="btn-export-pdf" target="_blank">
                                <i class="fa fa-file-pdf-o"></i> Download PDF
                            </a>
                        </div>
                        @endif

                    </div>
                </form>

                {{-- Results --}}
                @if(!is_null($reportData))

                    {{-- ── CONSOLIDATED ─────────────────────────────────────────── --}}
                    @if($selectedType == 'admin_pending_orders_product_consolidated')
                        <div class="report-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th class="center" style="width:50px">#</th>
                                        <th>Product Name</th>
                                        <th class="right" style="width:150px">Qty (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData['rows'] as $i => $row)
                                    <tr>
                                        <td class="center">{{ $i + 1 }}</td>
                                        <td>
                                            <span class="prod-name">{{ $row['product_name'] }}</span>
                                            <span class="prod-pack">({{ $row['packing_size'] }})</span>
                                        </td>
                                        <td class="right">{{ number_format($row['total_qty']) }} kg</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="empty-state">No pending orders found for the selected filters.</td></tr>
                                    @endforelse
                                </tbody>
                                @if(count($reportData['rows']))
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="right">Grand Total</th>
                                        <th class="right">{{ number_format($reportData['total_qty']) }} kg</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>

                    {{-- ── DETAILED ─────────────────────────────────────────────── --}}
                    @else
                        @forelse($reportData['products'] as $product)
                        <div class="product-block-title">
                            <span>{{ $product['product_name'] }} <span class="prod-pack">({{ $product['packing_size'] }})</span></span>
                            <span>Total Pending: <span class="pend-total">{{ number_format($product['total_pending_qty']) }} kg</span></span>
                        </div>
                        <div class="report-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th style="width:100px">PO Date</th>
                                        <th>Dealer / Customer</th>
                                        <th>PO Ref No.</th>
                                        <th class="right" style="width:100px">Order Qty</th>
                                        <th class="right" style="width:100px">Pending Qty</th>
                                        <th class="center" style="width:90px">Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product['orders'] as $order)
                                    <tr>
                                        <td>{{ $order['po_date'] }}</td>
                                        <td>{{ $order['actor_name'] ?: '—' }}</td>
                                        <td>{{ $order['po_ref_no'] ?: '—' }}</td>
                                        <td class="right">{{ number_format($order['ordered_qty']) }}</td>
                                        <td class="right"><strong>{{ number_format($order['pending_qty']) }}</strong></td>
                                        <td class="center">{{ $order['age_days'] }} days</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @empty
                        <p class="empty-state">No pending orders found for the selected filters.</p>
                        @endforelse

                        @if(count($reportData['products']))
                        <div class="grand-total-bar">
                            Grand Total Pending Qty: <strong>{{ number_format($reportData['total_qty']) }} kg</strong>
                        </div>
                        @endif
                    @endif

                @endif

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    $('#filter-products, #filter-dealers, #filter-customers').select2({ width: 'resolve' });

    function updatePdfLink() {
        var params = new URLSearchParams();
        var type = $('#filter-type').val();
        if (type) params.set('type', type);

        $('#filter-products').val()  && $('#filter-products').val().forEach(function (v)  { params.append('product_ids[]', v); });
        $('#filter-dealers').val()   && $('#filter-dealers').val().forEach(function (v)   { params.append('filter_dealer_ids[]', v); });
        $('#filter-customers').val() && $('#filter-customers').val().forEach(function (v) { params.append('filter_customer_ids[]', v); });

        $('#btn-export-pdf').attr('href', '{{ route("admin.pending-orders-report.pdf") }}?' + params.toString());
    }

    updatePdfLink();
    $('#report-filter-form select').on('change', updatePdfLink);

});
</script>

@endsection
