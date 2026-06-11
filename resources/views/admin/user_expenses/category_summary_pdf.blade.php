<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; background: #ffffff; line-height: 1.5; }

/* HEADER */
.hdr-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
.hdr-left  { vertical-align: top; text-align: left; }
.hdr-right { vertical-align: top; text-align: right; }
.logo-img  { width: 140px; height: auto; margin-bottom: 8px; }
.hdr-title-label { font-size: 7.5px; color: #64748b; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 2px; }
.hdr-user-name   { font-size: 14px; font-weight: bold; color: #0f172a; }
.hdr-doc-type    { font-size: 13px; font-weight: bold; color: #334155; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px; }
.hdr-date        { font-size: 8px; color: #64748b; }
.hdr-period      { font-size: 8px; color: #475569; margin-top: 2px; }

/* ORG KPI STRIP */
.org-strip { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 20px; }
.org-box {
    text-align: center; vertical-align: middle; padding: 10px 6px;
    border: 1px solid #cbd5e1; background-color: #f8fafc;
}
.org-box + .org-box { border-left: none; }
.org-box-total  { border-top: 3px solid #1e293b; }
.org-box-req    { border-top: 3px solid #475569; }
.org-box-apr    { border-top: 3px solid #16a34a; }
.org-box-pend   { border-top: 3px solid #e9a8b0; }
.org-box-emp    { border-top: 3px solid #94a3b8; }
.s-big    { font-size: 18px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #0f172a; }
.s-big-sm { font-size: 11px; font-weight: bold; display: block; line-height: 1.1; margin-bottom: 2px; color: #0f172a; }
.s-tag    { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.7px; display: block; color: #475569; }

/* FILTER ROW */
.filter-row { width: 100%; border-collapse: collapse; margin-bottom: 16px; background: #f8fafc; border: 1px solid #e2e8f0; }
.filter-row td { padding: 7px 12px; vertical-align: middle; }
.f-lbl { font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; white-space: nowrap; padding-right: 10px; }
.ftag  { display: inline-block; background: transparent; color: #334155; font-size: 8px; font-weight: bold; padding: 2px 6px; margin-right: 4px; letter-spacing: 0.2px; text-transform: uppercase; }

/* ORG-WIDE SECTION DIVIDER */
.org-divider-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
.org-divider-td {
    background: #1e293b; padding: 8px 12px;
    font-size: 8px; font-weight: bold; text-transform: uppercase;
    letter-spacing: 0.8px; color: #e2e8f0; vertical-align: middle;
}
.org-cat-kpi { width: 100%; border-collapse: collapse; background: #f8fafc; border: 1px solid #cbd5e1; border-top: none; margin-bottom: 16px; }
.org-cat-kpi td { padding: 7px 12px; vertical-align: middle; border-right: 1px solid #e2e8f0; font-size: 8px; }
.org-cat-kpi td:last-child { border-right: none; }
.ck-lbl { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; display: block; margin-bottom: 2px; }
.ck-val      { font-size: 11px; font-weight: bold; color: #0f172a; display: block; }
.ck-val-pink { font-size: 11px; font-weight: bold; color: #9d174d; display: block; }
.ck-val-green{ font-size: 11px; font-weight: bold; color: #16a34a; display: block; }

/* ORG CAT TABLE */
.org-cat-wrap { border: 1px solid #cbd5e1; border-top: none; margin-bottom: 22px; }
.org-cat-tbl  { width: 100%; border-collapse: collapse; }
.org-cat-tbl thead tr { background: #334155; }
.org-cat-tbl thead th { padding: 7px 10px; font-size: 7.5px; font-weight: bold; color: #e2e8f0; text-transform: uppercase; letter-spacing: 0.6px; text-align: left; border-right: 1px solid rgba(255,255,255,0.08); white-space: nowrap; }
.org-cat-tbl thead th:last-child { border-right: none; }
.org-cat-tbl thead th.r { text-align: right; }
.org-cat-tbl thead th.c { text-align: center; }
.org-cat-tbl tbody tr { border-bottom: 1px solid #e2e8f0; }
.org-cat-tbl tbody tr.ro { background: #ffffff; }
.org-cat-tbl tbody tr.re { background: #f8fafc; }
.org-cat-tbl tbody td { padding: 7px 10px; font-size: 8.5px; color: #334155; border-right: 1px solid #e2e8f0; vertical-align: middle; }
.org-cat-tbl tbody td:last-child { border-right: none; }
.org-cat-tbl tfoot tr { background: #334155; }
.org-cat-tbl tfoot td { padding: 8px 10px; border-top: 2px solid #475569; color: #e2e8f0; font-size: 8.5px; font-weight: bold; }
.cat-name-cell { font-size: 9px; font-weight: bold; color: #0f172a; }
.cat-dist-cell { font-size: 7.5px; color: #64748b; margin-top: 1px; }
.cat-cnt-badge { display: inline-block; background: #f1f5f9; color: #334155; font-size: 7.5px; font-weight: bold; padding: 2px 6px; border-radius: 2px; }
.val-apr  { font-size: 9px; font-weight: bold; color: #16a34a; text-align: right; display: block; }
.val-req  { font-size: 8px; color: #475569; text-align: right; display: block; }
.val-pend { font-size: 9px; font-weight: bold; color: #9d174d; text-align: right; display: block; }
.val-nil  { color: #cbd5e1; font-size: 10px; text-align: right; display: block; }
.pend-cnt { font-size: 7px; color: #be185d; text-align: right; display: block; margin-top: 1px; }
.apr-cnt  { font-size: 7px; color: #15803d; text-align: right; display: block; margin-top: 1px; }

/* EMPLOYEE SECTION */
.emp-sec-bar { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 0; }
.emp-sec-bar-td {
    background: #e9eff6; padding: 8px 12px;
    font-size: 8.5px; font-weight: bold; text-transform: uppercase;
    letter-spacing: 0.7px; color: #334e68; vertical-align: middle;
}
.emp-sec-bar-rt { background: #e9eff6; text-align: right; padding-right: 14px; vertical-align: middle; width: 200px; font-size: 8px; color: #334e68; font-weight: bold; }

/* Employee KPI mini */
.emp-kpi { width: 100%; border-collapse: collapse; background: #ffffff; border: 1px solid #e2e8f0; border-top: none; margin-bottom: 0; }
.emp-kpi td { padding: 6px 12px; vertical-align: middle; border-right: 1px solid #e2e8f0; font-size: 8px; }
.emp-kpi td:last-child { border-right: none; }
.ek-lbl { font-size: 6.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; color: #94a3b8; display: block; margin-bottom: 1px; }
.ek-val       { font-size: 10px; font-weight: bold; color: #0f172a; display: block; }
.ek-val-green { font-size: 10px; font-weight: bold; color: #16a34a; display: block; }
.ek-val-pink  { font-size: 10px; font-weight: bold; color: #9d174d; display: block; }

/* mPDF-safe: keep table rows together, never on div */
.emp-tbl tbody tr { page-break-inside: avoid; }
.emp-tbl tfoot tr { page-break-inside: avoid; }
.emp-kpi tr       { page-break-inside: avoid; }

/* Employee table */
.emp-tbl-wrap { border: 1px solid #e2e8f0; border-top: none; margin-bottom: 18px; }
.emp-tbl { width: 100%; border-collapse: collapse; }
.emp-tbl thead tr { background: #475569; }
.emp-tbl thead th { padding: 6px 9px; font-size: 7px; font-weight: bold; color: #e2e8f0; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; border-right: 1px solid rgba(255,255,255,0.08); white-space: nowrap; }
.emp-tbl thead th:last-child { border-right: none; }
.emp-tbl thead th.r { text-align: right; }
.emp-tbl thead th.c { text-align: center; }
.emp-tbl tbody tr { border-bottom: 1px solid #e2e8f0; }
.emp-tbl tbody tr.ro { background: #ffffff; }
.emp-tbl tbody tr.re { background: #f8fafc; }
.emp-tbl tbody td { padding: 6px 9px; font-size: 8px; color: #334155; border-right: 1px solid #e2e8f0; vertical-align: middle; }
.emp-tbl tbody td:last-child { border-right: none; }
.emp-tbl tfoot tr { background: #475569; }
.emp-tbl tfoot td { padding: 7px 9px; border-top: 1px solid #64748b; color: #e2e8f0; font-size: 8px; font-weight: bold; }

/* FOOTER */
.footer-table { width: 100%; border-collapse: collapse; margin-top: 24px; border-top: 1px solid #cbd5e1; }
.footer-left  { font-size: 8px; font-weight: bold; color: #334155; padding-top: 8px; }
.footer-mid   { font-size: 7.5px; color: #64748b; text-align: center; padding-top: 8px; }
.footer-right { font-size: 7.5px; color: #64748b; text-align: right; padding-top: 8px; }
</style>
</head>
<body>

@php
    // ── ORG-WIDE TOTALS ──
    $orgTotal    = $allExpenses->count();
    $orgReq      = $allExpenses->sum('requested_amount');
    $orgApr      = $allExpenses->sum('approved_amount');
    $orgPend     = $allExpenses->whereIn('status', ['Pending Approval', 'Requested'])->count();
    $orgPendAmt  = $allExpenses->whereIn('status', ['Pending Approval', 'Requested'])->sum('requested_amount');
    $orgAprAmt   = $allExpenses->sum('approved_amount');
    $empCount    = $allExpenses->pluck('employee_name')->unique()->count();

    // ── ORG CATEGORY SUMMARY (all employees combined) ──
    $orgCatGroups = $allExpenses->groupBy('category_name');
    $orgCatSummary = [];
    foreach ($orgCatGroups as $catName => $catExp) {
        $orgCatSummary[] = [
            'name'      => $catName,
            'total_cnt' => $catExp->count(),
            'apr_cnt'   => $catExp->where('status','Approved')->count(),
            'pend_cnt'  => $catExp->whereIn('status',['Pending Approval','Requested'])->count(),
            'req_amt'   => $catExp->sum('requested_amount'),
            'apr_amt'   => $catExp->sum('approved_amount'),
            'pend_amt'  => $catExp->whereIn('status',['Pending Approval','Requested'])->sum('requested_amount'),
            'travel_km' => $catExp->where('is_travel',1)->sum('travel_km'),
        ];
    }
    usort($orgCatSummary, fn($a,$b) => strcmp($a['name'], $b['name']));

    // ── PER-EMPLOYEE grouped, sorted alphabetically ──
    $byEmployee = $allExpenses->groupBy('employee_name')->sortKeys();
@endphp

{{-- ══ HEADER ══ --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="{{ public_path('images/greenwave-logo-1-275-sl.jpg') }}" class="logo-img" />
            <div class="hdr-title-label">Category-Wise Expense Summary</div>
            <div class="hdr-user-name">Greenwave — All Employees</div>
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Expense Summary</div>
            <div class="hdr-date">Generated: {{ now()->format('d/m/Y H:i') }}</div>
            <div class="hdr-period">
                @if($filterMonth && $filterYear)
                    Period: {{ date('F', mktime(0,0,0,$filterMonth,1)) }} {{ $filterYear }}
                @elseif($filterYear)
                    Year: {{ $filterYear }}
                @elseif($filterMonth)
                    Month: {{ date('F', mktime(0,0,0,$filterMonth,1)) }}
                @else
                    Period: All Time
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- ══ ORG KPI ══ --}}
<table class="org-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td class="org-box org-box-emp" width="14%">
            <span class="s-big">{{ $empCount }}</span>
            <span class="s-tag">Employees</span>
        </td>
        <td class="org-box org-box-total" width="14%">
            <span class="s-big">{{ $orgTotal }}</span>
            <span class="s-tag">Total Claims</span>
        </td>
        <td class="org-box org-box-req" width="18%">
            <span class="s-big-sm">&#8377;{{ number_format($orgReq, 2) }}</span>
            <span class="s-tag">Total Requested</span>
        </td>
        <td class="org-box org-box-apr" width="18%">
            <span class="s-big-sm" style="color:#16a34a;">&#8377;{{ number_format($orgApr, 2) }}</span>
            <span class="s-tag">Total Approved</span>
        </td>
        <td class="org-box org-box-pend" width="14%">
            <span class="s-big" style="color:#9d174d;">{{ $orgPend }}</span>
            <span class="s-tag" style="color:#9d174d;">Pending</span>
        </td>
        <td class="org-box org-box-pend" width="22%">
            <span class="s-big-sm" style="color:#9d174d;">&#8377;{{ number_format($orgPendAmt, 2) }}</span>
            <span class="s-tag" style="color:#9d174d;">Pending Amount</span>
        </td>
    </tr>
</table>

{{-- ══ FILTER TAGS ══ --}}
<table class="filter-row" cellspacing="0" cellpadding="0">
    <tr>
        <td class="f-lbl">Filters Applied</td>
        <td>
            @if($filterEmployee) <span class="ftag">{{ $filterEmployee->name }}</span> @endif
            @if($filterMonth)    <span class="ftag">{{ date('F', mktime(0,0,0,$filterMonth,1)) }}</span> @endif
            @if($filterYear)     <span class="ftag">{{ $filterYear }}</span> @endif
            @if(!$filterEmployee && !$filterMonth && !$filterYear)
                <span class="ftag">All Records</span>
            @endif
        </td>
    </tr>
</table>

{{-- ══ ORG-WIDE CATEGORY TABLE ══ --}}
<table class="org-divider-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="org-divider-td">&#9776;&nbsp; Organisation-Wide Category Summary</td>
        <td style="background:#1e293b;text-align:right;padding-right:14px;font-size:8px;font-weight:bold;color:#94a3b8;vertical-align:middle;width:160px;">
            {{ count($orgCatSummary) }} {{ count($orgCatSummary)==1?'Category':'Categories' }}
        </td>
    </tr>
</table>

{{-- Org KPI strip --}}
<table class="org-cat-kpi" cellspacing="0" cellpadding="0">
    <tr>
        <td width="16%"><span class="ck-lbl">Total Claims</span><span class="ck-val">{{ $orgTotal }}</span></td>
        <td width="16%"><span class="ck-lbl">Approved</span><span class="ck-val-green">{{ $allExpenses->where('status','Approved')->count() }}</span></td>
        <td width="22%"><span class="ck-lbl">Total Approved Amt</span><span class="ck-val-green">&#8377;{{ number_format($orgApr, 2) }}</span></td>
        <td width="16%"><span class="ck-lbl">Pending Count</span><span class="ck-val-pink">{{ $orgPend }}</span></td>
        <td width="22%"><span class="ck-lbl">Pending Amount</span><span class="ck-val-pink">&#8377;{{ number_format($orgPendAmt, 2) }}</span></td>
        <td width="8%">&nbsp;</td>
    </tr>
</table>

<div class="org-cat-wrap">
<table class="org-cat-tbl" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th class="c" width="28">Sr.</th>
            <th width="170">Category Name</th>
            <th class="c" width="50">Claims</th>
            <th class="r" width="100">Requested</th>
            <th class="r" width="110">Approved</th>
            <th class="r" width="110">Pending Approval</th>
        </tr>
    </thead>
    <tbody>
    @foreach($orgCatSummary as $si => $cs)
    @php $rc = ($si % 2 === 0) ? 'ro' : 're'; @endphp
    <tr class="{{ $rc }}">
        <td style="text-align:center;color:#94a3b8;font-size:8px;font-weight:bold;">{{ $si+1 }}</td>
        <td>
            <div class="cat-name-cell">{{ $cs['name'] }}</div>
            @if($cs['travel_km'] > 0)
                <div class="cat-dist-cell">&#128663; {{ number_format($cs['travel_km'],0) }} km traveled</div>
            @endif
        </td>
        <td style="text-align:center;"><span class="cat-cnt-badge">{{ $cs['total_cnt'] }}</span></td>
        <td style="text-align:right;"><span class="val-req">&#8377;{{ number_format($cs['req_amt'],2) }}</span></td>
        <td style="text-align:right;">
            @if($cs['apr_amt'] > 0)
                <span class="val-apr">&#8377;{{ number_format($cs['apr_amt'],2) }}</span>
                @if($cs['apr_cnt'] > 0)<span class="apr-cnt">{{ $cs['apr_cnt'] }} approved</span>@endif
            @else
                <span class="val-nil">&#8212;</span>
            @endif
        </td>
        <td style="text-align:right;">
            @if($cs['pend_cnt'] > 0)
                <span class="val-pend">&#8377;{{ number_format($cs['pend_amt'],2) }}</span>
                <span class="pend-cnt">{{ $cs['pend_cnt'] }} pending</span>
            @else
                <span class="val-nil">&#8212;</span>
            @endif
        </td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"><span style="font-size:7.5px;color:#94a3b8;letter-spacing:0.4px;">TOTALS &mdash; {{ count($orgCatSummary) }} {{ count($orgCatSummary)==1?'Category':'Categories' }}</span></td>
            <td style="text-align:center;"><span style="font-size:10px;font-weight:bold;color:#ffffff;">{{ $orgTotal }}</span></td>
            <td style="text-align:right;padding-right:10px;"><span style="font-size:10px;font-weight:bold;color:#ffffff;">&#8377;{{ number_format($orgReq,2) }}</span></td>
            <td style="text-align:right;padding-right:10px;"><span style="font-size:10px;font-weight:bold;color:#86efac;">&#8377;{{ number_format($orgApr,2) }}</span></td>
            <td style="text-align:right;padding-right:10px;">
                @if($orgPend > 0)
                    <span style="font-size:10px;font-weight:bold;color:#f9a8d4;">&#8377;{{ number_format($orgPendAmt,2) }}</span>
                    <span style="font-size:7px;color:#f9a8d4;display:block;margin-top:2px;">{{ $orgPend }} pending</span>
                @else
                    <span style="font-size:10px;color:rgba(255,255,255,0.25);">&#8212;</span>
                @endif
            </td>
        </tr>
    </tfoot>
</table>
</div>


{{-- ══ PER-EMPLOYEE SECTIONS (alphabetical) ══ --}}
@foreach($byEmployee as $empName => $empExpenses)
@php
    $eTotal   = $empExpenses->count();
    $eReq     = $empExpenses->sum('requested_amount');
    $eApr     = $empExpenses->sum('approved_amount');
    $ePendCnt = $empExpenses->whereIn('status',['Pending Approval','Requested'])->count();
    $ePendAmt = $empExpenses->whereIn('status',['Pending Approval','Requested'])->sum('requested_amount');
    $eMobile  = $empExpenses->first()->employee_mobile ?? '';

    // Category breakdown for this employee
    $empCatGroups = $empExpenses->groupBy('category_name');
    $empCatSummary = [];
    foreach ($empCatGroups as $catName => $catExp) {
        $empCatSummary[] = [
            'name'      => $catName,
            'total_cnt' => $catExp->count(),
            'apr_cnt'   => $catExp->where('status','Approved')->count(),
            'pend_cnt'  => $catExp->whereIn('status',['Pending Approval','Requested'])->count(),
            'req_amt'   => $catExp->sum('requested_amount'),
            'apr_amt'   => $catExp->sum('approved_amount'),
            'pend_amt'  => $catExp->whereIn('status',['Pending Approval','Requested'])->sum('requested_amount'),
            'travel_km' => $catExp->where('is_travel',1)->sum('travel_km'),
        ];
    }
    usort($empCatSummary, fn($a,$b) => strcmp($a['name'], $b['name']));
@endphp

{{-- Employee header bar — page break before every employee except the first --}}
@if(!$loop->first)
<table style="width:100%;border-collapse:collapse;page-break-before:always;" cellspacing="0" cellpadding="0"><tr><td style="height:1px;font-size:1px;line-height:1px;">&nbsp;</td></tr></table>
@endif
<table class="emp-sec-bar" cellspacing="0" cellpadding="0">
    <tr>
        <td class="emp-sec-bar-td">
            {{ $empName }}
            @if($eMobile) &nbsp;&bull;&nbsp; <span style="font-weight:normal;font-size:8px;">{{ $eMobile }}</span> @endif
        </td>
        <td class="emp-sec-bar-rt">{{ $eTotal }} claim(s)</td>
    </tr>
</table>

{{-- Employee mini KPI --}}
<table class="emp-kpi" cellspacing="0" cellpadding="0">
    <tr>
        <td width="14%"><span class="ek-lbl">Claims</span><span class="ek-val">{{ $eTotal }}</span></td>
        <td width="20%"><span class="ek-lbl">Requested</span><span class="ek-val">&#8377;{{ number_format($eReq,2) }}</span></td>
        <td width="20%"><span class="ek-lbl">Approved</span><span class="ek-val-green">&#8377;{{ number_format($eApr,2) }}</span></td>
        <td width="14%"><span class="ek-lbl">Pending</span><span class="ek-val-pink">{{ $ePendCnt }}</span></td>
        <td width="20%"><span class="ek-lbl">Pending Amt</span><span class="ek-val-pink">&#8377;{{ number_format($ePendAmt,2) }}</span></td>
        <td width="12%"><span class="ek-lbl">Categories</span><span class="ek-val">{{ count($empCatSummary) }}</span></td>
    </tr>
</table>

{{-- Employee category table --}}
<div class="emp-tbl-wrap">
<table class="emp-tbl" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th class="c" width="24">Sr.</th>
            <th width="160">Category</th>
            <th class="c" width="44">Claims</th>
            <th class="r" width="95">Requested</th>
            <th class="r" width="105">Approved</th>
            <th class="r" width="105">Pending</th>
        </tr>
    </thead>
    <tbody>
    @foreach($empCatSummary as $ei => $ec)
    @php $rc2 = ($ei % 2 === 0) ? 'ro' : 're'; @endphp
    <tr class="{{ $rc2 }}">
        <td style="text-align:center;color:#94a3b8;font-size:7.5px;font-weight:bold;">{{ $ei+1 }}</td>
        <td>
            <div style="font-size:8.5px;font-weight:bold;color:#0f172a;">{{ $ec['name'] }}</div>
            @if($ec['travel_km'] > 0)
                <div style="font-size:7px;color:#64748b;margin-top:1px;">&#128663; {{ number_format($ec['travel_km'],0) }} km</div>
            @endif
        </td>
        <td style="text-align:center;"><span class="cat-cnt-badge">{{ $ec['total_cnt'] }}</span></td>
        <td style="text-align:right;font-size:8.5px;font-weight:bold;color:#334155;">&#8377;{{ number_format($ec['req_amt'],2) }}</td>
        <td style="text-align:right;">
            @if($ec['apr_amt'] > 0)
                <span style="font-size:8.5px;font-weight:bold;color:#16a34a;display:block;">&#8377;{{ number_format($ec['apr_amt'],2) }}</span>
                @if($ec['apr_cnt'] > 0)<span style="font-size:7px;color:#15803d;display:block;">{{ $ec['apr_cnt'] }} approved</span>@endif
            @else
                <span style="color:#cbd5e1;font-size:9px;">&#8212;</span>
            @endif
        </td>
        <td style="text-align:right;">
            @if($ec['pend_cnt'] > 0)
                <span style="font-size:8.5px;font-weight:bold;color:#9d174d;display:block;">&#8377;{{ number_format($ec['pend_amt'],2) }}</span>
                <span style="font-size:7px;color:#be185d;display:block;">{{ $ec['pend_cnt'] }} pending</span>
            @else
                <span style="color:#cbd5e1;font-size:9px;">&#8212;</span>
            @endif
        </td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2" style="color:#94a3b8;font-size:7.5px;letter-spacing:0.3px;">{{ $empName }} &mdash; Totals</td>
            <td style="text-align:center;color:#fff;font-size:9px;font-weight:bold;">{{ $eTotal }}</td>
            <td style="text-align:right;padding-right:9px;color:#fff;font-size:9px;font-weight:bold;">&#8377;{{ number_format($eReq,2) }}</td>
            <td style="text-align:right;padding-right:9px;font-size:9px;font-weight:bold;color:#86efac;">&#8377;{{ number_format($eApr,2) }}</td>
            <td style="text-align:right;padding-right:9px;">
                @if($ePendCnt > 0)
                    <span style="font-size:9px;font-weight:bold;color:#f9a8d4;">&#8377;{{ number_format($ePendAmt,2) }}</span>
                @else
                    <span style="font-size:9px;color:rgba(255,255,255,0.25);">&#8212;</span>
                @endif
            </td>
        </tr>
    </tfoot>
</table>
</div>

@endforeach

{{-- ══ FOOTER ══ --}}
<table class="footer-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="footer-left">Greenwave &bull; Expense Management</td>
        <td class="footer-mid">Confidential Report &bull; Internal Use Only</td>
        <td class="footer-right">{{ $empCount }} employees &bull; {{ $orgTotal }} records &bull; &#8377;{{ number_format($orgReq,2) }} requested</td>
    </tr>
</table>

</body>
</html>