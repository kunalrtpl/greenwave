<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Pricing</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #2d3748; background: #fff; }

        /* Header */
        .pdf-header { background: #2b6cb0; color: #fff; padding: 12px 18px; margin-bottom: 14px; }
        .pdf-header h1 { font-size: 15px; font-weight: bold; margin-bottom: 3px; }
        .pdf-header .meta { display: table; width: 100%; margin-top: 6px; }
        .pdf-header .meta-left  { display: table-cell; text-align: left;  font-size: 9px; opacity: 0.9; }
        .pdf-header .meta-right { display: table-cell; text-align: right; font-size: 9px; opacity: 0.9; }

        /* Filter tags */
        .filter-row { padding: 0 18px 10px; }
        .ftag {
            display: inline-block; background: #ebf8ff; color: #2b6cb0;
            border: 1px solid #bee3f8; border-radius: 4px;
            padding: 2px 8px; font-size: 8px; font-weight: bold;
            margin-right: 5px; text-transform: uppercase; letter-spacing: 0.3px;
        }

        /* Stats bar */
        .stats { background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 3px; margin: 0 18px 12px; padding: 6px 12px; font-size: 9px; color: #4a5568; }
        .stats strong { color: #2b6cb0; }

        /* Table */
        .wrap { padding: 0 18px 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: #2b6cb0; color: #fff; }
        thead th { padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.4px; border: 1px solid #2c5282; }
        thead th.center { text-align: center; }
        tbody tr:nth-child(even) { background: #f7fafc; }
        tbody tr:nth-child(odd)  { background: #ffffff; }
        tbody td { padding: 5px 8px; border: 1px solid #e2e8f0; vertical-align: middle; }
        tbody td.center { text-align: center; }

        .prod-name { font-weight: bold; color: #2d3748; }
        .prod-code { font-size: 8px; color: #a0aec0; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 6px; font-size: 7px; font-weight: bold; }
        .pd-today { background: #e6fffa; color: #276749; border: 1px solid #9ae6b4; }
        .pd-old   { background: #f7fafc; color: #718096; border: 1px solid #e2e8f0; }
        .pd-none  { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
        .na-yes   { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
        .na-no    { background: #f0fff4; color: #276749; border: 1px solid #9ae6b4; }

        /* Footer */
        .pdf-footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f7fafc; border-top: 1px solid #e2e8f0; padding: 5px 18px; font-size: 8px; color: #a0aec0; display: table; width: 100%; }
        .footer-left  { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

<div class="pdf-header">
    <h1>Product Pricing Report</h1>
    <div class="meta">
        <div class="meta-left">Generated: {{ $generatedAt }}</div>
        <div class="meta-right">Total Records: {{ count($products) }}</div>
    </div>
</div>

@if(count($filterLabels) > 0)
<div class="filter-row">
    <span style="font-size:8px; color:#718096; font-weight:bold; margin-right:4px;">FILTERS:</span>
    @foreach($filterLabels as $label)
        <span class="ftag">{{ $label }}</span>
    @endforeach
</div>
@endif

<div class="stats">
    Showing <strong>{{ count($products) }}</strong> product(s)
    @if(count($filterLabels) > 0) &bull; Filtered by: {{ implode(', ', $filterLabels) }} @endif
</div>

<div class="wrap">
    @if(count($products) === 0)
        <p style="text-align:center; color:#a0aec0; padding:30px; font-style:italic;">No products found for the selected filters.</p>
    @else
    <table>
        <thead>
            <tr>
                <th class="center" style="width:28px;">#</th>
                <th style="width:22%;">Product Name</th>
                <th style="width:10%;">Code</th>
                <th style="width:12%;">MOQ</th>
                <th style="width:10%;">Dispatch (days)</th>
                <th class="center" style="width:10%;">Not Avail.</th>
                <th style="width:12%;">Dealer Price (₹)</th>
                <th class="center" style="width:14%;">Price Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $i => $p)
            @php
                $hasPrice = !is_null($p->dealer_price);
                $isToday  = $hasPrice && $p->price_date === $today;
            @endphp
            <tr>
                <td class="center" style="color:#a0aec0;">{{ $i + 1 }}</td>
                <td>
                    <div class="prod-name">{{ $p->product_name }}</div>
                </td>
                <td><span class="prod-code">{{ $p->product_code }}</span></td>
                <td>{{ $p->moq ?? '—' }}</td>
                <td class="center">{{ $p->average_dispatch_time ?? '—' }}</td>
                <td class="center">
                    @if($p->not_available)
                        <span class="badge na-yes">Yes</span>
                    @else
                        <span class="badge na-no">No</span>
                    @endif
                </td>
                <td>
                    @if($hasPrice) ₹ {{ number_format((float)$p->dealer_price, 2) }}
                    @else <span style="color:#c53030;">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($hasPrice)
                        <span class="badge {{ $isToday ? 'pd-today' : 'pd-old' }}">
                            {{ $isToday ? 'Today' : \Carbon\Carbon::parse($p->price_date)->format('d/m/Y') }}
                        </span>
                    @else
                        <span class="badge pd-none">No Price</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<div class="pdf-footer">
    <div class="footer-left">Product Pricing &mdash; Confidential</div>
    <div class="footer-right">{{ $generatedAt }}</div>
</div>

</body>
</html>