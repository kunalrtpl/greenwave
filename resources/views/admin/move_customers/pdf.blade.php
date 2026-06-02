<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Customer List - {{ $rootUser->name }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #2d3748;
        background: #fff;
    }

    /* ── Header ── */
    .pdf-header {
        background: #2980b9;
        color: #fff;
        padding: 12px 16px;
        margin-bottom: 14px;
        border-radius: 4px;
    }
    .pdf-header .title {
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .pdf-header .sub {
        font-size: 9px;
        opacity: 0.85;
        margin-top: 3px;
    }
    .pdf-header .meta {
        font-size: 8.5px;
        opacity: 0.75;
        margin-top: 2px;
    }

    /* ── Filter pill ── */
    .filter-pill {
        display: inline-block;
        background: #ebf5ff;
        border: 1px solid #bee3f8;
        color: #2b6cb0;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 8.5px;
        font-weight: bold;
        margin-bottom: 12px;
    }

    /* ── Employee group ── */
    .emp-group { margin-bottom: 16px; page-break-inside: avoid; }

    .emp-header {
        background: #eef3fb;
        border-left: 4px solid #3598dc;
        padding: 6px 10px;
        margin-bottom: 0;
        border-radius: 2px 2px 0 0;
    }
    .emp-header.is-root {
        background: #2980b9;
        border-left-color: #1a5c8c;
        color: #fff;
    }
    .emp-name {
        font-size: 11px;
        font-weight: bold;
        color: #2d3748;
    }
    .emp-header.is-root .emp-name { color: #fff; }
    .emp-desig {
        font-size: 8.5px;
        color: #718096;
        margin-top: 1px;
    }
    .emp-header.is-root .emp-desig { color: rgba(255,255,255,0.8); }
    .emp-count {
        float: right;
        font-size: 9px;
        font-weight: bold;
        color: #3598dc;
        margin-top: 2px;
    }
    .emp-header.is-root .emp-count { color: rgba(255,255,255,0.9); }

    /* ── Customer Table ── */
    .cust-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e1e5ec;
        border-top: none;
        border-radius: 0 0 2px 2px;
    }
    .cust-table thead th {
        background: #f4f6fa;
        color: #4a5568;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 5px 8px;
        border-bottom: 1px solid #dde3ec;
        border-right: 1px solid #e8ecf2;
        text-align: left;
    }
    .cust-table tbody td {
        padding: 5px 8px;
        border-bottom: 1px solid #f0f4f8;
        border-right: 1px solid #f0f4f8;
        vertical-align: top;
        font-size: 9px;
        color: #2d3748;
    }
    .cust-table tbody tr:nth-child(even) td { background: #fafbfd; }
    .cust-table tbody tr:last-child td { border-bottom: none; }

    .sr-no { color: #a0aec0; font-size: 8px; text-align: center; width: 20px; }
    .cust-name-cell { font-weight: bold; font-size: 9px; }
    .cust-meta { font-size: 8px; color: #718096; margin-top: 1px; }

    /* BM badges */
    .bm-badge {
        display: inline-block;
        font-size: 7.5px;
        font-weight: bold;
        padding: 1px 6px;
        border-radius: 8px;
    }
    .bm-direct { background: #e6fffa; color: #276749; }
    .bm-open   { background: #fffbeb; color: #975a16; }
    .bm-dealer { background: #ebf8ff; color: #2b6cb0; }

    /* ── Footer ── */
    .pdf-footer {
        margin-top: 20px;
        padding-top: 8px;
        border-top: 1px solid #e1e5ec;
        font-size: 8px;
        color: #a0aec0;
        text-align: center;
    }

    /* ── Summary row ── */
    .summary-row {
        margin-bottom: 12px;
        font-size: 9px;
        color: #4a5568;
    }
    .summary-row strong { color: #2d3748; }
</style>
</head>
<body>

{{-- Header --}}
<div class="pdf-header">
    <div class="title">Customer List &mdash; {{ $rootUser->name }}</div>
    <div class="sub">
        {{ $rootUser->designation ?? '' }}
    </div>
    <div class="meta">Generated: {{ $generatedAt }}</div>
</div>

{{-- Active filters --}}
@if($filterLabel)
    <div class="filter-pill">&#9881; Filters: {{ $filterLabel }}</div>
@endif

{{-- Summary --}}
@php
    $totalCustomers = collect($groups)->sum(function($g) { return count($g['customers']); });
    $totalGroups    = count($groups);
@endphp
<div class="summary-row">
    Showing <strong>{{ $totalCustomers }}</strong> customer(s)
    across <strong>{{ $totalGroups }}</strong> employee(s).
</div>

{{-- Groups --}}
@foreach($groups as $group)
@php $custCount = count($group['customers']); @endphp
<div class="emp-group">

    {{-- Employee header --}}
    <div class="emp-header {{ $group['is_root'] ? 'is-root' : '' }}">
        <span class="emp-count">{{ $custCount }} Customer(s)</span>
        <div class="emp-name">{{ $group['user_name'] }}{{ $group['is_root'] ? ' (Selected)' : '' }}</div>
        <div class="emp-desig">{{ $group['designation'] ?? '' }}</div>
    </div>

    {{-- Customer table --}}
    <table class="cust-table">
        <thead>
            <tr>
                <th class="sr-no">#</th>
                <th style="width:22%;">Customer Name</th>
                <th style="width:16%;">Contact Person</th>
                <th style="width:13%;">Department</th>
                <th style="width:13%;">City</th>
                <th style="width:13%;">Business Model</th>
                <th style="width:13%;">Category</th>
            </tr>
        </thead>
        <tbody>
        @foreach($group['customers'] as $i => $c)
        @php
            $bm = $c->business_model ?? 'Open';
            $bmClass = 'bm-open';
            $bmLabel = $bm;
            if ($bm === 'Direct Customer') { $bmClass = 'bm-direct'; }
            elseif ($bm === 'Dealer')      { $bmClass = 'bm-dealer'; $bmLabel = $c->dealer_business_name ?: 'Dealer'; }
        @endphp
        <tr>
            <td class="sr-no">{{ $i + 1 }}</td>
            <td>
                <div class="cust-name-cell">{{ $c->customer_name }}</div>
            </td>
            <td>
                <div>{{ $c->contact_person_name ?? '—' }}</div>
                @if($c->customer_designation)
                    <div class="cust-meta">{{ $c->customer_designation }}</div>
                @endif
            </td>
            <td>{{ $c->department ?? '—' }}</td>
            <td>{{ $c->city_name ?? '—' }}</td>
            <td>
                <span class="bm-badge {{ $bmClass }}">{{ $bmLabel }}</span>
            </td>
            <td>{{ $c->category ?? '—' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endforeach

<div class="pdf-footer">
    Greenwave &bull; Customer Move Report &bull; {{ $generatedAt }}
</div>

</body>
</html>