<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer List</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2d3748;
            background: #fff;
        }

        /* ── Header ── */
        .pdf-header {
            background: #2b6cb0;
            color: #fff;
            padding: 14px 20px;
            margin-bottom: 16px;
        }
        .pdf-header h1 {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .pdf-header .sub {
            font-size: 10px;
            opacity: 0.85;
        }
        .pdf-header .meta-row {
            margin-top: 8px;
            display: table;
            width: 100%;
        }
        .pdf-header .meta-left  { display: table-cell; text-align: left;  font-size: 10px; opacity: 0.9; }
        .pdf-header .meta-right { display: table-cell; text-align: right; font-size: 10px; opacity: 0.9; }

        /* ── Filter Tags ── */
        .filter-tags {
            padding: 6px 20px 10px;
        }
        .filter-tag {
            display: inline-block;
            background: #ebf8ff;
            color: #2b6cb0;
            border: 1px solid #bee3f8;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: bold;
            margin-right: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* ── Stats Bar ── */
        .stats-bar {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin: 0 20px 14px;
            padding: 7px 14px;
            font-size: 10px;
            color: #4a5568;
        }
        .stats-bar strong { color: #2b6cb0; }

        /* ── Table ── */
        .wrap { padding: 0 20px 20px; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        thead tr {
            background: #2b6cb0;
            color: #fff;
        }
        thead th {
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #2c5282;
        }
        tbody tr:nth-child(even) { background: #f7fafc; }
        tbody tr:nth-child(odd)  { background: #ffffff; }
        tbody td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .no-col  { width: 28px; text-align: center; color: #a0aec0; font-size: 9px; }
        .name-col  { width: 18%; }
        .contact-col { width: 18%; }
        .city-col  { width: 10%; }
        .bm-col    { width: 13%; }
        .user-col  { width: 16%; }

        .cust-name    { font-weight: bold; color: #2d3748; }
        .cust-meta    { font-size: 9px; color: #718096; margin-top: 2px; }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
        .bm-direct { background: #e6fffa; color: #276749; border: 1px solid #9ae6b4; }
        .bm-open   { background: #fffbeb; color: #975a16; border: 1px solid #fbd38d; }
        .bm-dealer { background: #ebf8ff; color: #2b6cb0; border: 1px solid #90cdf4; }

        .user-name { font-weight: bold; color: #2b6cb0; }
        .user-desig { font-size: 9px; color: #718096; margin-top: 1px; }
        .no-user { color: #c0c8d4; font-style: italic; font-size: 9px; }

        .city-text { color: #276749; font-weight: 600; }

        /* ── Footer ── */
        .pdf-footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            padding: 6px 20px;
            font-size: 8px;
            color: #a0aec0;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="pdf-header">
    <h1>Customer List &mdash; {{ $sourceLabel }}</h1>
    <div class="sub">Move Customers by Dealer / Business Model</div>
    <div class="meta-row">
        <div class="meta-left">Generated: {{ $generatedAt }}</div>
        <div class="meta-right">Total Records: {{ count($customers) }}</div>
    </div>
</div>

{{-- Active Filters --}}
@if(count($filterLabels) > 0)
<div class="filter-tags">
    <span style="font-size:9px; color:#718096; font-weight:bold; margin-right:4px;">FILTERS:</span>
    @foreach($filterLabels as $label)
        <span class="filter-tag">{{ $label }}</span>
    @endforeach
</div>
@endif

{{-- Stats --}}
<div class="stats-bar">
    Showing <strong>{{ count($customers) }}</strong> customer(s)
    @if(count($filterLabels) > 0)
        &nbsp;&bull;&nbsp; Filtered by: {{ implode(', ', $filterLabels) }}
    @endif
</div>

{{-- Table --}}
<div class="wrap">
    @if(count($customers) === 0)
        <p style="text-align:center; color:#a0aec0; padding:30px; font-style:italic;">No customers found for the selected filters.</p>
    @else
    <table>
        <thead>
            <tr>
                <th class="no-col">#</th>
                <th class="name-col">Customer Name</th>
                <th class="contact-col">Contact Person</th>
                <th class="city-col">City</th>
                <th class="bm-col">Business Model</th>
                <th class="user-col">Linked User</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $i => $cust)
            <tr>
                <td class="no-col">{{ $i + 1 }}</td>
                <td class="name-col">
                    <div class="cust-name">{{ $cust->customer_name }}</div>
                    @if($cust->department)
                        <div class="cust-meta">{{ $cust->department }}</div>
                    @endif
                </td>
                <td class="contact-col">
                    @if($cust->contact_person_name)
                        <div>{{ $cust->contact_person_name }}</div>
                        @if($cust->customer_designation)
                            <div class="cust-meta">{{ $cust->customer_designation }}</div>
                        @endif
                    @else
                        <span class="no-user">—</span>
                    @endif
                </td>
                <td class="city-col">
                    @if($cust->city_name)
                        <span class="city-text">{{ $cust->city_name }}</span>
                    @else
                        <span class="no-user">—</span>
                    @endif
                </td>
                <td class="bm-col">
                    @php
                        $bm = $cust->business_model ?? 'Open';
                        $bmClass = 'bm-open';
                        $bmLabel = $bm;
                        if ($bm === 'Direct Customer') { $bmClass = 'bm-direct'; }
                        elseif ($bm === 'Dealer')      { $bmClass = 'bm-dealer'; $bmLabel = $cust->dealer_business_name ?? 'Dealer'; }
                    @endphp
                    <span class="badge {{ $bmClass }}">{{ $bmLabel }}</span>
                </td>
                <td class="user-col">
                    @if($cust->user_name)
                        <div class="user-name">{{ $cust->user_name }}</div>
                        @if($cust->user_designation)
                            <div class="user-desig">{{ $cust->user_designation }}</div>
                        @endif
                    @else
                        <span class="no-user">Not Assigned</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Footer --}}
<div class="pdf-footer">
    <div class="footer-left">Greenwave &mdash; Confidential</div>
    <div class="footer-right">{{ $generatedAt }}</div>
</div>

</body>
</html>