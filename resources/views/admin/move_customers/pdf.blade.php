<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #1e293b;
    background: #ffffff;
    line-height: 1.5;
}

/* ═══════════════════════════════════════
   CLEAN HORIZONTAL EXECUTIVE HEADER
═══════════════════════════════════════ */
.hdr-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
}

.hdr-left {
    vertical-align: top;
    text-align: left;
}

.hdr-right {
    vertical-align: top;
    text-align: right;
}

.logo-img {
    width: 150px;
    height: auto;
    margin-bottom: 10px;
}

.hdr-title-label {
    font-size: 7.5px;
    color: #64748b;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.hdr-user-name {
    font-size: 15px;
    font-weight: bold;
    color: #0f172a;
    letter-spacing: -0.2px;
}

.hdr-user-desig {
    font-size: 8.5px;
    color: #475569;
    margin-top: 1px;
}

.hdr-doc-type {
    font-size: 13px;
    font-weight: bold;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 4px;
}

.hdr-date {
    font-size: 8px;
    color: #64748b;
}

/* ═══════════════════════════════════════
   PREMIUM STRIP KPI METRICS
═══════════════════════════════════════ */
.summary-strip {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 22px;
}

.s-box {
    text-align: center;
    vertical-align: middle;
    padding: 10px 6px;
    border: 1px solid #cbd5e1;
    background-color: #f8fafc;
}
.s-box + .s-box { border-left: none; }

.s-box-total { border-top: 3px solid #1e293b; width: 16%; }
.s-box-d     { border-top: 3px solid #475569; width: 16%; }
.s-box-o     { border-top: 3px solid #94a3b8; width: 16%; }
.s-box-dl    { border-top: 3px solid #cbd5e1; width: 16%; }
.s-box-spacer { width: 36%; border: none; background: transparent; }

.s-big    { font-size: 20px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #0f172a; }
.s-tag    { font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; display: block; color: #475569; }

/* ═══════════════════════════════════════
   DATA SECTIONS & ROWS
═══════════════════════════════════════ */
.emp-group {
    margin-bottom: 22px;
    page-break-inside: avoid;
}

.group-wrap {
    border: 1px solid #cbd5e1;
}

.bm-section-table {
    width: 100%;
    border-collapse: collapse;
}

.bm-sec-td {
    background-color: #e9eff6;
    padding: 8px 12px;
    font-size: 8.5px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #1e293b;
    vertical-align: middle;
}

.bm-sec-count-td {
    background-color: #e9eff6;
    font-size: 8.5px;
    font-weight: bold;
    color: #334e68;
    text-align: right;
    padding-right: 14px;
    vertical-align: middle;
    width: 150px;
}

.cust-table {
    width: 100%;
    border-collapse: collapse;
}

.cust-num-td {
    width: 40px;
    text-align: center;
    vertical-align: middle;
    padding: 10px 0;
    color: #64748b;
    font-size: 8.5px;
    font-weight: bold;
    border-bottom: 1px solid #e2e8f0;
}

.cust-info-td {
    vertical-align: middle;
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
}

.cust-city-td {
    vertical-align: middle;
    padding: 10px 16px;
    text-align: right;
    width: 180px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}

.cust-name-text {
    font-size: 9.5px;
    font-weight: bold;
    color: #0f172a;
}

.cust-meta-text {
    font-size: 8px;
    color: #64748b;
    margin-top: 2px;
}

.city-text {
    font-size: 8.5px;
    font-weight: bold;
    color: #334155;
}

.cust-even td {
    background-color: #f8fafc;
}

.section-divider {
    height: 1px;
    background-color: #cbd5e1;
}

/* ═══════════════════════════════════════
   PREMIUM FOOTER
═══════════════════════════════════════ */
.footer-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 28px;
    border-top: 1px solid #cbd5e1;
    padding-top: 6px;
}
.footer-left  { font-size: 7.5px; font-weight: bold; color: #334155; }
.footer-mid   { font-size: 7px; color: #64748b; text-align: center; }
.footer-right { font-size: 7px; color: #64748b; text-align: right; }
</style>
</head>
<body>

@php
    $totalAll = $totalDirect = $totalOpen = $totalDealer = 0;
    foreach ($groups as $g) {
        foreach ($g['customers'] as $c) {
            $totalAll++;
            $bm = $c->business_model ?? 'Open';
            if ($bm === 'Direct Customer') $totalDirect++;
            elseif ($bm === 'Open')        $totalOpen++;
            else                           $totalDealer++;
        }
    }
@endphp

{{-- HORIZONTAL HEADER --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
            <div class="hdr-title-label">Prepared For</div>
            <div class="hdr-user-name">{{ $rootUser->name }}</div>
            @if($rootUser->designation)
                <div class="hdr-user-desig">{{ $rootUser->designation }}</div>
            @endif
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Employee Linked Customers</div>
            <div class="hdr-date">Generated on: {{ $generatedAt }}</div>
        </td>
    </tr>
</table>

{{-- KPI BOXES --}}
<table class="summary-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td class="s-box s-box-total">
            <span class="s-big">{{ $totalAll }}</span>
            <span class="s-tag">Total</span>
        </td>
        @if($totalDirect > 0)
        <td class="s-box s-box-d">
            <span class="s-big">{{ $totalDirect }}</span>
            <span class="s-tag">Direct</span>
        </td>
        @endif
        @if($totalOpen > 0)
        <td class="s-box s-box-o">
            <span class="s-big">{{ $totalOpen }}</span>
            <span class="s-tag">Open</span>
        </td>
        @endif
        @if($totalDealer > 0)
        <td class="s-box s-box-dl">
            <span class="s-big">{{ $totalDealer }}</span>
            <span class="s-tag">Dealer</span>
        </td>
        @endif
        <td class="s-box-spacer"></td>
    </tr>
</table>

{{-- CUSTOMER DATA LOOPS --}}
@foreach($groups as $group)
@php
    $isRoot  = $group['is_root'];
    $cnt     = count($group['customers']);
    $direct  = [];
    $open    = [];
    $dealers = [];
    foreach ($group['customers'] as $c) {
        $bm = $c->business_model ?? 'Open';
        if ($bm === 'Direct Customer') {
            $direct[] = $c;
        } elseif ($bm === 'Open') {
            $open[] = $c;
        } else {
            $dn = trim($c->dealer_business_name ?? '') ?: 'Dealer';
            $dealers[$dn][] = $c;
        }
    }
    ksort($dealers);
    $hasDirect = count($direct) > 0;
    $hasOpen   = count($open)   > 0;
    $hasDealer = count($dealers) > 0;
@endphp

<div class="emp-group">
    <div class="group-wrap">

    {{-- DIRECT SECTION --}}
    @if($hasDirect)
    <table class="bm-section-table" cellspacing="0" cellpadding="0">
        <tr>
            <td class="bm-sec-td">&#10003;&nbsp; Direct Customers</td>
            <td class="bm-sec-count-td">{{ count($direct) }} record(s)</td>
        </tr>
    </table>
    <table class="cust-table" cellspacing="0" cellpadding="0">
    @foreach($direct as $i => $c)
    @php
        $cleanContact = str_replace(['&nbsp;', '·'], ['', ''], $c->contact_person_name ?? '');
        $cleanDesig   = str_replace(['&nbsp;', '·'], ['', ''], $c->customer_designation ?? '');
        $cleanDept    = str_replace(['&nbsp;', '·'], ['', ''], $c->department ?? '');
        $parts = array_filter(array_map('trim', [
            $cleanContact ?: null,
            $cleanDesig ?: null,
            $cleanDept ?: null,
        ]));
        $rowClass = ($i % 2 === 1) ? 'cust-even' : '';
    @endphp
    <tr class="{{ $rowClass }}">
        <td class="cust-num-td">{{ $i + 1 }}</td>
        <td class="cust-info-td">
            <div class="cust-name-text">{{ $c->customer_name }}</div>
            @if(count($parts))
            <div class="cust-meta-text">{{ implode('  ', $parts) }}</div>
            @endif
        </td>
        <td class="cust-city-td">
            @if($c->city_name)
            <span class="city-text">{{ $c->city_name }}</span>
            @endif
        </td>
    </tr>
    @endforeach
    </table>
    @endif

    @if($hasDirect && ($hasOpen || $hasDealer))
        <div class="section-divider"></div>
    @endif

    {{-- OPEN SECTION --}}
    @if($hasOpen)
    <table class="bm-section-table" cellspacing="0" cellpadding="0">
        <tr>
            <td class="bm-sec-td">&#9675;&nbsp; Open Customers</td>
            <td class="bm-sec-count-td">{{ count($open) }} record(s)</td>
        </tr>
    </table>
    <table class="cust-table" cellspacing="0" cellpadding="0">
    @foreach($open as $i => $c)
    @php
        $cleanContact = str_replace(['&nbsp;', '·'], ['', ''], $c->contact_person_name ?? '');
        $cleanDesig   = str_replace(['&nbsp;', '·'], ['', ''], $c->customer_designation ?? '');
        $cleanDept    = str_replace(['&nbsp;', '·'], ['', ''], $c->department ?? '');
        $parts = array_filter(array_map('trim', [
            $cleanContact ?: null,
            $cleanDesig ?: null,
            $cleanDept ?: null,
        ]));
        $rowClass = ($i % 2 === 1) ? 'cust-even' : '';
    @endphp
    <tr class="{{ $rowClass }}">
        <td class="cust-num-td">{{ $i + 1 }}</td>
        <td class="cust-info-td">
            <div class="cust-name-text">{{ $c->customer_name }}</div>
            @if(count($parts))
            <div class="cust-meta-text">{{ implode('  ', $parts) }}</div>
            @endif
        </td>
        <td class="cust-city-td">
            @if($c->city_name)
            <span class="city-text">{{ $c->city_name }}</span>
            @endif
        </td>
    </tr>
    @endforeach
    </table>
    @endif

    @if($hasOpen && $hasDealer)
        <div class="section-divider"></div>
    @endif

    {{-- DEALERS SECTIONS --}}
    @if($hasDealer)
    @php $dIdx = 0; $dTotal = count($dealers); @endphp
    @foreach($dealers as $dealerName => $dRows)
    @php $dIdx++; @endphp
    <table class="bm-section-table" cellspacing="0" cellpadding="0">
        <tr>
            <td class="bm-sec-td">&#9632;&nbsp; {{ $dealerName }}</td>
            <td class="bm-sec-count-td">{{ count($dRows) }} record(s)</td>
        </tr>
    </table>
    <table class="cust-table" cellspacing="0" cellpadding="0">
    @foreach($dRows as $i => $c)
    @php
        $cleanContact = str_replace(['&nbsp;', '·'], ['', ''], $c->contact_person_name ?? '');
        $cleanDesig   = str_replace(['&nbsp;', '·'], ['', ''], $c->customer_designation ?? '');
        $cleanDept    = str_replace(['&nbsp;', '·'], ['', ''], $c->department ?? '');
        $parts = array_filter(array_map('trim', [
            $cleanContact ?: null,
            $cleanDesig ?: null,
            $cleanDept ?: null,
        ]));
        $rowClass = ($i % 2 === 1) ? 'cust-even' : '';
    @endphp
    <tr class="{{ $rowClass }}">
        <td class="cust-num-td">{{ $i + 1 }}</td>
        <td class="cust-info-td">
            <div class="cust-name-text">{{ $c->customer_name }}</div>
            @if(count($parts))
            <div class="cust-meta-text">{{ implode('  ', $parts) }}</div>
            @endif
        </td>
        <td class="cust-city-td">
            @if($c->city_name)
            <span class="city-text">{{ $c->city_name }}</span>
            @endif
        </td>
    </tr>
    @endforeach
    </table>
    @if($dIdx < $dTotal)<div class="section-divider"></div>@endif
    @endforeach
    @endif

    </div>
</div>
@endforeach

{{-- FOOTER --}}
<table class="footer-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="footer-left">Greenwave &bull; Business Intelligence</td>
        <td class="footer-mid">Confidential Report</td>
        <td class="footer-right">Total: {{ $totalAll }} records</td>
    </tr>
</table>

</body>
</html>