<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #2F3B2A;
    background: #ffffff;
    line-height: 1.5;
}

/* ═══════════════════════════════════════
   HEADER — same horizontal layout as Move Customer
═══════════════════════════════════════ */
.hdr-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
}
.hdr-left  { vertical-align: top; text-align: left; }
.hdr-right { vertical-align: top; text-align: right; }

.logo-img { width: 150px; height: auto; margin-bottom: 10px; }

.hdr-title-label {
    font-size: 7.5px; color: #7A8A73;
    letter-spacing: 1px; text-transform: uppercase; margin-bottom: 2px;
}
.hdr-doc-type {
    font-size: 13px; font-weight: bold; color: #44543C;
    text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;
}
.hdr-date  { font-size: 8px; color: #7A8A73; }

/* filter tags */
.filter-row { margin-bottom: 14px; }
.ftag {
    display: inline-block; background: #EAF2E6; color: #44543C;
    border: 1px solid #BDD27B; border-radius: 3px;
    padding: 2px 7px; font-size: 7.5px; font-weight: bold;
    margin-right: 4px; text-transform: uppercase; letter-spacing: 0.3px;
}

/* ═══════════════════════════════════════
   KPI SUMMARY STRIP — same boxes as Move Customer
═══════════════════════════════════════ */
.summary-strip {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 22px;
}
.s-box {
    text-align: center; vertical-align: middle;
    padding: 10px 6px;
    border: 1px solid #D6E0D2;
    background-color: #FAFCF9;
}
.s-box + .s-box { border-left: none; }

.s-box-total  { border-top: 3px solid #708A63; width: 14%; }
.s-box-price  { border-top: 3px solid #BDD27B; width: 14%; }
.s-box-np     { border-top: 3px solid #C0392B; width: 14%; }
.s-box-na     { border-top: 3px solid #E67E22; width: 14%; }
.s-box-today  { border-top: 3px solid #A4C497; width: 14%; }
.s-box-spacer { width: 30%; border: none; background: transparent; }

.s-big { font-size: 18px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #1A2416; }
.s-tag { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; display: block; color: #60705A; }

/* ═══════════════════════════════════════
   TABLE
═══════════════════════════════════════ */
.wrap { width: 100%; }

table.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5px;
}

/* thead */
table.data-table thead tr {
    background-color: #44543C;
}
table.data-table thead th {
    padding: 7px 8px;
    color: #fff;
    font-size: 7.5px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid #2F3B2A;
    text-align: left;
    white-space: nowrap;
}
table.data-table thead th.center { text-align: center; }

/* tbody */
table.data-table tbody td {
    padding: 6px 8px;
    border: 1px solid #D6E0D2;
    vertical-align: middle;
    color: #2F3B2A;
    background: #ffffff;
}
table.data-table tbody tr:nth-child(even) td { background: #FAFCF9; }
table.data-table tbody td.center { text-align: center; }

/* product name cell */
.prod-name { font-size: 9px; font-weight: bold; color: #1A2416; display: block; }
.prod-code { font-size: 7.5px; color: #8AA081; display: block; margin-top: 1px; }

/* sr no */
.sr-cell { color: #8AA081; font-size: 8px; font-weight: bold; text-align: center; }

/* moq / dispatch */
.plain-val { color: #2F3B2A; }

/* badges */
.badge {
    display: inline-block; padding: 2px 7px;
    border-radius: 10px; font-size: 7px; font-weight: bold;
    border: 1px solid transparent; white-space: nowrap;
}
.na-yes   { background: #fff5f5; color: #c53030; border-color: #feb2b2; }
.na-no    { background: #f0fff4; color: #276749; border-color: #9ae6b4; }
.pd-today { background: #e6fffa; color: #276749; border-color: #9ae6b4; }
.pd-old   { background: #f7fafc; color: #718096; border-color: #D6E0D2; }
.pd-none  { background: #fff5f5; color: #c53030; border-color: #feb2b2; }

/* price */
.dp-val  { font-weight: bold; color: #2b78ad; }
.dp-none { color: #c53030; font-style: italic; }

/* empty state */
.empty-cell { text-align: center; padding: 28px; color: #8AA081; font-style: italic; }

/* ═══════════════════════════════════════
   FOOTER
═══════════════════════════════════════ */
.footer-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 28px;
    border-top: 1px solid #BDD27B;
    padding-top: 6px;
}
.footer-left  { font-size: 7.5px; font-weight: bold; color: #44543C; }
.footer-mid   { font-size: 7px; color: #7A8A73; text-align: center; }
.footer-right { font-size: 7px; color: #7A8A73; text-align: right; }

</style>
</head>
<body>

@php
    $total    = count($products);
    $hasPrice = $products->filter(fn($p) => !is_null($p->dealer_price))->count();
    $noPrice  = $total - $hasPrice;
    $naCount  = $products->filter(fn($p) => $p->not_available)->count();
    $todayUpd = $products->filter(fn($p) => !is_null($p->dealer_price) && $p->price_date === $today)->count();
@endphp

{{-- ── HEADER ── --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Product Pricing Report</div>
            <div class="hdr-date">Generated on: {{ $generatedAt }}</div>
        </td>
    </tr>
</table>

{{-- ── FILTER TAGS ── --}}
@if(count($filterLabels) > 0)
<div class="filter-row">
    <span style="font-size:7.5px; color:#7A8A73; font-weight:bold; margin-right:4px; text-transform:uppercase; letter-spacing:0.5px;">Filters:</span>
    @foreach($filterLabels as $label)
        <span class="ftag">{{ $label }}</span>
    @endforeach
</div>
@endif

{{-- ── KPI BOXES ── --}}
<table class="summary-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td class="s-box s-box-total">
            <span class="s-big">{{ $total }}</span>
            <span class="s-tag">Total</span>
        </td>
        <td class="s-box s-box-price">
            <span class="s-big">{{ $hasPrice }}</span>
            <span class="s-tag">Has Price</span>
        </td>
        <td class="s-box s-box-np">
            <span class="s-big">{{ $noPrice }}</span>
            <span class="s-tag">No Price</span>
        </td>
        <td class="s-box s-box-na">
            <span class="s-big">{{ $naCount }}</span>
            <span class="s-tag">Not Avail.</span>
        </td>
        <td class="s-box s-box-today">
            <span class="s-big">{{ $todayUpd }}</span>
            <span class="s-tag">Today</span>
        </td>
        <td class="s-box-spacer"></td>
    </tr>
</table>

{{-- ── DATA TABLE ── --}}
<div class="wrap">
@if($total === 0)
    <table class="data-table">
        <tr><td class="empty-cell">No products found for the selected filters.</td></tr>
    </table>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="center" style="width:24px;">#</th>
            <th style="width:22%;">Product Name</th>
            <th style="width:8%;">Code</th>
            <th class="center" style="width:8%;">Not Avail.</th>
            <th style="width:10%;">MOQ</th>
            <th class="center" style="width:9%;">Dispatch</th>
            <th style="width:13%;">Dealer Price (&#8377;)</th>
            <th class="center" style="width:13%;">Price Date</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $i => $p)
    @php
        $hp      = !is_null($p->dealer_price);
        $isToday = $hp && $p->price_date === $today;
        $isNA    = (bool)$p->not_available;
    @endphp
    <tr>
        <td class="sr-cell">{{ $i + 1 }}</td>

        <td>
            <span class="prod-name">{{ $p->product_name }}</span>
        </td>

        <td><span class="prod-code">{{ $p->product_code }}</span></td>

        <td class="center">
            @if($isNA)
                <span class="badge na-yes">&#10007;</span>
            @else
                <span class="badge na-no">&#10003;</span>
            @endif
        </td>

        <td class="plain-val">{{ $p->moq ?? '—' }}</td>

        <td class="center plain-val">{{ $p->average_dispatch_time ?? '—' }}</td>

        <td>
            @if($hp)
                <span class="dp-val">&#8377; {{ number_format((float)$p->dealer_price, 2) }}</span>
            @else
                <span class="dp-none">No Price</span>
            @endif
        </td>

        <td class="center">
            @if($hp)
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

{{-- ── FOOTER ── --}}
<table class="footer-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="footer-left">Greenwave &bull; Product Pricing</td>
        <td class="footer-mid">Confidential &mdash; Internal Use Only</td>
        <td class="footer-right">Total: {{ $total }} records &bull; {{ $generatedAt }}</td>
    </tr>
</table>

</body>
</html>