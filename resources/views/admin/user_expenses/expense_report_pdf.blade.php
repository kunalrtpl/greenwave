<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Expense Report</title>
<style>

* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 13px;
    color: #1e2535;
    background: #ffffff;
    line-height: 1.6;
}

/* ═══════════════════════════════════════════
   HEADER BAND
═══════════════════════════════════════════ */
.hdr-band {
    background: #1b2a4a;
    padding: 0;
    width: 100%;
}
.hdr-inner {
    width: 100%;
    border-collapse: collapse;
}
.hdr-inner td.h-l {
    padding: 28px 34px 24px;
    vertical-align: bottom;
    width: 60%;
}
.hdr-inner td.h-r {
    padding: 28px 34px 24px;
    vertical-align: bottom;
    text-align: right;
    width: 40%;
    border-left: 1px solid rgba(255,255,255,0.06);
}

.hdr-overline {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #7aabee;
    margin-bottom: 10px;
}
.hdr-title {
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.05;
    letter-spacing: -0.5px;
}
.hdr-title span { color: #7aabee; }
.hdr-tagline {
    font-size: 11px;
    color: rgba(255,255,255,0.35);
    margin-top: 8px;
    letter-spacing: 0.3px;
}

.hdr-gen-lbl {
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.3);
    margin-bottom: 6px;
}
.hdr-gen-date {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
}
.hdr-gen-time {
    font-size: 11px;
    color: rgba(255,255,255,0.42);
    margin-top: 3px;
}
.hdr-pill {
    display: inline-block;
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.4);
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 2px;
    margin-top: 14px;
}

.hdr-accent {
    background: #4d8fe8;
    height: 4px;
    width: 100%;
    font-size: 0;
    line-height: 0;
}

/* ═══════════════════════════════════════════
   EMPLOYEE HERO CARD
═══════════════════════════════════════════ */
.emp-hero {
    background: #f2f6fd;
    border-bottom: 1px solid #d4e0f4;
    width: 100%;
}
.emp-hero-tbl {
    width: 100%;
    border-collapse: collapse;
}
.emp-hero-tbl td.el {
    padding: 22px 34px;
    vertical-align: middle;
    width: 55%;
    border-right: 1px solid #d4e0f4;
}
.emp-hero-tbl td.er {
    padding: 22px 34px;
    vertical-align: middle;
    width: 45%;
}

.emp-eyebrow {
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #6a90cc;
    margin-bottom: 8px;
}
.emp-name-xl {
    font-size: 30px;
    font-weight: 700;
    color: #0f1c33;
    line-height: 1.05;
    letter-spacing: -0.5px;
}
.emp-mobile {
    font-size: 13.5px;
    font-weight: 600;
    color: #3d6db0;
    margin-top: 8px;
}

.emp-meta-tbl { width: 100%; border-collapse: collapse; }
.emp-meta-tbl tr td { padding: 6px 0; vertical-align: middle; }
.eml {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #92a8cc;
    width: 75px;
    white-space: nowrap;
}
.emv {
    font-size: 13px;
    font-weight: 700;
    color: #0f1c33;
    padding-left: 10px;
}

/* ═══════════════════════════════════════════
   KPI ROW
═══════════════════════════════════════════ */
.kpi-row {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-bottom: 1px solid #d4e0f4;
}
.kpi-row td {
    padding: 18px 14px;
    text-align: center;
    border-right: 1px solid #e8eef8;
    vertical-align: middle;
}
.kpi-row td:last-child { border-right: none; }
.kpi-dot {
    width: 32px;
    height: 4px;
    border-radius: 2px;
    margin: 0 auto 9px;
}
.kpi-l {
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #a0b2cc;
    margin-bottom: 6px;
}
.kpi-v {
    font-size: 19px;
    font-weight: 700;
    color: #1e2535;
    line-height: 1;
}
.kpi-v.blue  { color: #2c6dd4; }
.kpi-v.green { color: #1a8844; }
.kpi-v.amber { color: #aa6e00; }
.kpi-v.red   { color: #be2222; }
.kpi-v.slate { color: #4e6480; }

/* ═══════════════════════════════════════════
   FILTER & SECTION BARS
═══════════════════════════════════════════ */
.filter-bar {
    padding: 9px 28px 10px;
    background: #f8f9fd;
    border-bottom: 1px solid #d8e4f5;
    font-size: 0;
}
.f-lbl {
    display: inline-block;
    font-size: 8.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #b0bece;
    margin-right: 12px;
    vertical-align: middle;
}
.ftag {
    display: inline-block;
    background: #e8f0fc;
    color: #2c5daa;
    border: 1px solid #bdd0f0;
    font-size: 10px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 3px;
    margin-right: 6px;
    vertical-align: middle;
    letter-spacing: 0.2px;
}

.sec-bar {
    padding: 11px 28px 10px;
    background: #ffffff;
    border-bottom: 2px solid #4d8fe8;
}
.sec-bar-txt {
    font-size: 10px;
    font-weight: 700;
    color: #4d8fe8;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

/* ═══════════════════════════════════════════
   MAIN TABLE
═══════════════════════════════════════════ */
.exp-tbl {
    width: 100%;
    border-collapse: collapse;
}

/* HEAD */
.exp-tbl thead tr { background: #1b2a4a; }
.exp-tbl thead th {
    padding: 13px 13px;
    font-size: 9.5px;
    font-weight: 700;
    color: #8ab0e0;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,0.06);
    border-bottom: 3px solid #4d8fe8;
    white-space: nowrap;
}
.exp-tbl thead th:last-child { border-right: none; }
.exp-tbl thead th.r { text-align: right; }
.exp-tbl thead th.c { text-align: center; }

/* BODY ROWS */
.exp-tbl tbody tr { border-bottom: 1px solid #e2eaf6; }
.exp-tbl tbody tr.ro { background: #ffffff; }
.exp-tbl tbody tr.re { background: #f6f9ff; }

.exp-tbl tbody td {
    padding: 15px 13px;
    font-size: 13px;
    color: #1e2535;
    border-right: 1px solid #e8eef8;
    vertical-align: top;
    line-height: 1.5;
}
.exp-tbl tbody td:last-child { border-right: none; }

/* ID */
.c-id {
    text-align: center !important;
    font-size: 11px !important;
    font-weight: 700;
    color: #b0c0d8 !important;
    background: #edf2fa !important;
    border-right: 2px solid #d4e0f4 !important;
}

/* DATE */
.d-day  { font-size: 26px; font-weight: 700; color: #0f1c33; line-height: 1; }
.d-mon  { font-size: 11.5px; font-weight: 700; color: #4d8fe8; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }
.d-yr   { font-size: 10.5px; color: #9aaec8; margin-top: 1px; }
.d-wd   { font-size: 9.5px; color: #c0cedf; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }

/* CATEGORY */
.cat-nm  { font-size: 14px; font-weight: 700; color: #0f1c33; line-height: 1.3; }
.cat-cps { margin-top: 6px; }
.cp-cat {
    display: inline-block;
    background: #e8f0fc; color: #2c5daa;
    border: 1px solid #bdd0f0;
    font-size: 9px; font-weight: 700;
    padding: 3px 9px; border-radius: 3px;
    text-transform: uppercase; letter-spacing: 0.4px;
}
.cp-miss {
    display: inline-block;
    background: #fde8e8; color: #be2222;
    border: 1px solid #f0b8b8;
    font-size: 9px; font-weight: 700;
    padding: 3px 9px; border-radius: 3px;
    margin-left: 5px; letter-spacing: 0.4px;
}
.cat-rmk {
    font-size: 11.5px; color: #6a7a98;
    margin-top: 7px; font-style: italic;
    line-height: 1.5;
}
.cat-msrsn {
    font-size: 11.5px; color: #be3333;
    margin-top: 6px; font-style: italic;
    line-height: 1.45;
}

/* AMOUNTS */
.amt-lbl  { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #aabcda; margin-bottom: 4px; }
.amt-req  { font-size: 16px; font-weight: 700; color: #0f1c33; }
.amt-rule { border: none; border-top: 1px dashed #ccd8ee; margin: 8px 0; }
.amt-apr  { font-size: 15px; font-weight: 700; color: #1a8844; }
.amt-nil  { font-size: 15px; color: #c4d0e4; }

/* TRAVEL */
.trv-km   { font-size: 15px; font-weight: 700; color: #2c6dd4; }
.trv-rate { font-size: 11.5px; color: #6a8ab8; margin-top: 4px; }
.trv-rt   { font-size: 11px; color: #94a8c4; margin-top: 4px; font-style: italic; }
.trv-nil  { font-size: 16px; color: #d4dcea; }

/* VERIFIED + INTERNAL REMARKS */
.v-yes {
    display: inline-block;
    background: #e2f5ea; color: #157840;
    border: 1px solid #88d4a8;
    font-size: 11px; font-weight: 700;
    padding: 5px 13px; border-radius: 3px;
    letter-spacing: 0.3px;
}
.v-no {
    display: inline-block;
    background: #f2f4fa; color: #a8b8d0;
    border: 1px solid #d4dcea;
    font-size: 11px; font-weight: 700;
    padding: 5px 13px; border-radius: 3px;
}
.v-by {
    font-size: 10.5px; color: #6a90bb;
    margin-top: 6px; font-style: italic;
}

/* Internal remarks — amber note box */
.int-note {
    margin-top: 10px;
    background: #fffbee;
    border: 1px solid #e8d070;
    border-left: 4px solid #d4a800;
    border-radius: 3px;
    padding: 7px 10px;
}
.int-note-lbl {
    font-size: 8.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.8px;
    color: #9a7800; margin-bottom: 4px;
}
.int-note-txt {
    font-size: 11px; color: #4a3800;
    font-style: italic; line-height: 1.5;
}

/* STATUS */
.st-ap {
    display:inline-block; background:#157840; color:#fff;
    font-size:10px; font-weight:700; padding:5px 13px;
    border-radius:3px; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap;
}
.st-pa {
    display:inline-block; background:#aa6e00; color:#fff;
    font-size:10px; font-weight:700; padding:5px 13px;
    border-radius:3px; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap;
}
.st-rj {
    display:inline-block; background:#be2222; color:#fff;
    font-size:10px; font-weight:700; padding:5px 13px;
    border-radius:3px; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap;
}
.st-pd {
    display:inline-block; background:#fff6e0; color:#8a5c00;
    border:1.5px solid #e8c438; font-size:10px; font-weight:700;
    padding:4px 13px; border-radius:3px; text-transform:uppercase;
    letter-spacing:0.5px; white-space:nowrap;
}
.st-df {
    display:inline-block; background:#edf0f8; color:#607090;
    font-size:10px; font-weight:700; padding:5px 13px;
    border-radius:3px; white-space:nowrap;
}
.adm-rmk {
    font-size:11px; color:#4470bb;
    margin-top:6px; font-style:italic;
    line-height:1.45;
}

/* ═══════════════════════════════════════════
   TFOOT
═══════════════════════════════════════════ */
.exp-tbl tfoot tr { background: #1b2a4a; }
.exp-tbl tfoot td {
    padding: 15px 13px;
    border-top: 3px solid #4d8fe8;
    font-weight: 700;
    color: #fff;
}
.tf-lbl  { font-size:11px; color:#8ab0e0; letter-spacing:0.5px; }
.tf-req  { font-size:17px; font-weight:700; color:#fff; text-align:right; }
.tf-apr  { font-size:16px; font-weight:700; color:#60d890; text-align:right; margin-top:5px; }
.tf-sav  { font-size:10px; color:rgba(255,255,255,0.22); text-align:right; margin-top:5px; }

/* ═══════════════════════════════════════════
   PAGE FOOTER
═══════════════════════════════════════════ */
.pg-foot {
    margin-top: 18px;
    padding: 12px 34px;
    background: #f2f6fd;
    border-top: 2px solid #d4e0f4;
}
.pg-foot-tbl { width:100%; border-collapse:collapse; }
.pg-foot-tbl td.pfl { font-size:10px; color:#92a8c8; vertical-align:middle; }
.pg-foot-tbl td.pfr {
    font-size:10px; color:#4d8fe8; font-weight:700;
    text-align:right; vertical-align:middle;
    text-transform:uppercase; letter-spacing:0.5px;
}

</style>
</head>
<body>

@php
    $totalReq    = $expenses->sum('requested_amount');
    $totalApr    = $expenses->sum('approved_amount');
    $totalCnt    = $expenses->count();
    $cntApproved = $expenses->where('status','Approved')->count();
    $cntPartial  = $expenses->where('status','Partially Approved')->count();
    $cntPending  = $expenses->whereIn('status',['Pending Approval','Requested'])->count();
    $cntRejected = $expenses->where('status','Rejected')->count();
@endphp

{{-- ═══════ HEADER ═══════ --}}
<div class="hdr-band">
    <table class="hdr-inner" cellspacing="0" cellpadding="0">
        <tr>
            <td class="h-l">
                <div class="hdr-overline">Expense Management System</div>
                <div class="hdr-title">Employee <span>Expense</span> Report</div>
                <div class="hdr-tagline">Detailed claims record &bull; Approval &amp; verification status</div>
            </td>
            <td class="h-r">
                <div class="hdr-gen-lbl">Generated On</div>
                <div class="hdr-gen-date">{{ now()->format('d F Y') }}</div>
                <div class="hdr-gen-time">{{ now()->format('h:i A') }}</div>
                <div><span class="hdr-pill">Confidential</span></div>
            </td>
        </tr>
    </table>
</div>
<div class="hdr-accent">&nbsp;</div>

{{-- ═══════ EMPLOYEE HERO ═══════ --}}
@if($employee)
<div class="emp-hero">
    <table class="emp-hero-tbl" cellspacing="0" cellpadding="0">
        <tr>
            <td class="el">
                <div class="emp-eyebrow">Employee</div>
                <div class="emp-name-xl">{{ $employee->name }}</div>
                @if(!empty($employee->mobile))
                    <div class="emp-mobile">&#9990;&nbsp; {{ $employee->mobile }}</div>
                @endif
            </td>
            <td class="er">
                <table class="emp-meta-tbl" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="eml">Period</td>
                        <td class="emv">
                            @if($filterMonth && $filterYear)
                                {{ date('F', mktime(0,0,0,$filterMonth,1)) }} {{ $filterYear }}
                            @elseif($filterYear)
                                {{ $filterYear }}
                            @elseif($filterMonth)
                                {{ date('F', mktime(0,0,0,$filterMonth,1)) }}
                            @else
                                All Time
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="eml">Status</td>
                        <td class="emv">{{ $filterStatus ?: 'All Statuses' }}</td>
                    </tr>
                    <tr>
                        <td class="eml">Verified</td>
                        <td class="emv">
                            @if($filterVerified === 'yes') Verified Only
                            @elseif($filterVerified === 'no') Not Verified
                            @else All
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
@endif

{{-- ═══════ KPI ROW ═══════ --}}
<table class="kpi-row" cellspacing="0" cellpadding="0">
    <tr>
        <td width="12%">
            <div class="kpi-dot" style="background:#7a9ecc;"></div>
            <div class="kpi-l">Total Claims</div>
            <div class="kpi-v slate">{{ $totalCnt }}</div>
        </td>
        <td width="22%">
            <div class="kpi-dot" style="background:#4d8fe8;"></div>
            <div class="kpi-l">Total Requested</div>
            <div class="kpi-v blue">&#8377;{{ number_format($totalReq,2) }}</div>
        </td>
        <td width="22%">
            <div class="kpi-dot" style="background:#1a8844;"></div>
            <div class="kpi-l">Total Approved</div>
            <div class="kpi-v green">&#8377;{{ number_format($totalApr,2) }}</div>
        </td>
        <td width="11%">
            <div class="kpi-dot" style="background:#1a8844;"></div>
            <div class="kpi-l">Approved</div>
            <div class="kpi-v green">{{ $cntApproved }}</div>
        </td>
        <td width="11%">
            <div class="kpi-dot" style="background:#aa6e00;"></div>
            <div class="kpi-l">Partial</div>
            <div class="kpi-v amber">{{ $cntPartial }}</div>
        </td>
        <td width="11%">
            <div class="kpi-dot" style="background:#aa6e00;"></div>
            <div class="kpi-l">Pending</div>
            <div class="kpi-v amber">{{ $cntPending }}</div>
        </td>
        <td width="11%">
            <div class="kpi-dot" style="background:#be2222;"></div>
            <div class="kpi-l">Rejected</div>
            <div class="kpi-v red">{{ $cntRejected }}</div>
        </td>
    </tr>
</table>

{{-- FILTERS --}}
<div class="filter-bar">
    <span class="f-lbl">Filters</span>
    @if($employee)     <span class="ftag">{{ $employee->name }}</span> @endif
    @if($filterMonth)  <span class="ftag">{{ date('F', mktime(0,0,0,$filterMonth,1)) }}</span> @endif
    @if($filterYear)   <span class="ftag">{{ $filterYear }}</span> @endif
    @if($filterStatus) <span class="ftag">{{ $filterStatus }}</span> @endif
    @if($filterVerified === 'yes')   <span class="ftag">Verified Only</span>
    @elseif($filterVerified === 'no') <span class="ftag">Not Verified</span>
    @endif
    @if(!$employee && !$filterMonth && !$filterYear && !$filterStatus && !$filterVerified)
        <span class="ftag">All Records</span>
    @endif
</div>

{{-- SECTION LABEL --}}
<div class="sec-bar">
    <span class="sec-bar-txt">
        Expense Records &mdash; {{ $totalCnt }} {{ $totalCnt == 1 ? 'Entry' : 'Entries' }}
    </span>
</div>

{{-- ═══════ TABLE ═══════ --}}
<table class="exp-tbl" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th class="c" width="34">#</th>
            @if(!$employee)
            <th width="100">Employee</th>
            @endif
            <th width="62">Date</th>
            <th width="160">Category &amp; Details</th>
            <th class="r" width="124">Requested / Approved</th>
            <th width="88">Travel</th>
            <th width="120">Verified &amp; Notes</th>
            <th class="c" width="84">Status</th>
        </tr>
    </thead>
    <tbody>
    @forelse($expenses as $idx => $expense)
    @php
        $expDate  = \Carbon\Carbon::parse($expense->expense_date);
        $st       = $expense->status;
        $rc       = ($idx % 2 === 0) ? 'ro' : 're';
    @endphp
    <tr class="{{ $rc }}">

        {{-- # --}}
        <td class="c-id">{{ $expense->id }}</td>

        {{-- Employee --}}
        @if(!$employee)
        <td>
            <div style="font-size:13.5px;font-weight:700;color:#0f1c33;">{{ $expense->employee_name ?? 'N/A' }}</div>
            @if(!empty($expense->employee_mobile))
                <div style="font-size:11px;color:#7a90b8;margin-top:4px;">{{ $expense->employee_mobile }}</div>
            @endif
        </td>
        @endif

        {{-- Date --}}
        <td>
            <div class="d-day">{{ $expDate->format('d') }}</div>
            <div class="d-mon">{{ $expDate->format('M') }}</div>
            <div class="d-yr">{{ $expDate->format('Y') }}</div>
            <div class="d-wd">{{ $expDate->format('D') }}</div>
        </td>

        {{-- Category --}}
        <td>
            <div class="cat-nm">{{ $expense->category_name }}</div>
            <div class="cat-cps">
                <span class="cp-cat">
                    {{ strtoupper(substr($expense->category_name,0,6)) }}
                </span>
                @if($expense->missed_entry)
                    <span class="cp-miss">Missed</span>
                @endif
            </div>
            @if(!empty($expense->remarks))
                <div class="cat-rmk">"{{ \Illuminate\Support\Str::limit($expense->remarks,60) }}"</div>
            @endif
            @if($expense->missed_entry && !empty($expense->missed_entry_reason))
                <div class="cat-msrsn">Reason: {{ \Illuminate\Support\Str::limit($expense->missed_entry_reason,55) }}</div>
            @endif
        </td>

        {{-- Amounts --}}
        <td style="text-align:right;">
            <div class="amt-lbl">Requested</div>
            <div class="amt-req">&#8377;{{ number_format($expense->requested_amount,2) }}</div>
            <hr class="amt-rule">
            <div class="amt-lbl">Approved</div>
            @if($expense->approved_amount > 0)
                <div class="amt-apr">&#8377;{{ number_format($expense->approved_amount,2) }}</div>
            @else
                <div class="amt-nil">—</div>
            @endif
        </td>

        {{-- Travel --}}
        <td>
            @if($expense->is_travel && !empty($expense->travel_km))
                <div class="trv-km">{{ number_format($expense->travel_km,0) }} km</div>
                <div class="trv-rate">&#8377;{{ number_format($expense->charge_per_km,2) }}/km</div>
                @if($expense->is_intercity && !empty($expense->intercity_route))
                    <div class="trv-rt">{{ \Illuminate\Support\Str::limit($expense->intercity_route,26) }}</div>
                @endif
            @else
                <span class="trv-nil">—</span>
            @endif
        </td>

        {{-- Verified + Internal Remarks --}}
        <td>
            @if(!empty($expense->verified_by))
                <span class="v-yes">&#10003; Verified</span>
                @if(!empty($expense->verified_by_name))
                    <div class="v-by">by {{ $expense->verified_by_name }}</div>
                @endif
            @else
                <span class="v-no">Not Verified</span>
            @endif

            @if(!empty($expense->internal_remarks))
                <div class="int-note">
                    <div class="int-note-lbl">&#9998; Internal Note</div>
                    <div class="int-note-txt">{{ \Illuminate\Support\Str::limit($expense->internal_remarks,60) }}</div>
                </div>
            @endif
        </td>

        {{-- Status --}}
        <td style="text-align:center;">
            @if($st === 'Approved')
                <span class="st-ap">Approved</span>
            @elseif($st === 'Partially Approved')
                <span class="st-pa">Partial</span>
            @elseif($st === 'Rejected')
                <span class="st-rj">Rejected</span>
            @elseif($st === 'Pending Approval')
                <span class="st-pd">Pending</span>
            @else
                <span class="st-df">{{ $st }}</span>
            @endif
            @if(!empty($expense->admin_remarks))
                <div class="adm-rmk">{{ \Illuminate\Support\Str::limit($expense->admin_remarks,32) }}</div>
            @endif
        </td>

    </tr>
    @empty
    <tr class="ro">
        <td colspan="{{ $employee ? 7 : 8 }}"
            style="text-align:center;padding:38px;color:#a8b8d0;font-style:italic;font-size:13px;">
            No expense records found for the selected filters.
        </td>
    </tr>
    @endforelse
    </tbody>

    @if($expenses->count() > 0)
    <tfoot>
        <tr>
            <td colspan="{{ $employee ? 3 : 4 }}">
                <div class="tf-lbl">TOTALS &mdash; {{ $totalCnt }} {{ $totalCnt == 1 ? 'Record' : 'Records' }}</div>
            </td>
            <td style="padding-right:13px;">
                <div class="tf-req">&#8377;{{ number_format($totalReq,2) }}</div>
                <div class="tf-apr">&#8377;{{ number_format($totalApr,2) }}</div>
                <div class="tf-sav">Savings: &#8377;{{ number_format($totalReq - $totalApr,2) }}</div>
            </td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
    @endif
</table>

{{-- ═══════ PAGE FOOTER ═══════ --}}
<div class="pg-foot">
    <table class="pg-foot-tbl" cellspacing="0" cellpadding="0">
        <tr>
            <td class="pfl">
                Expense Management System &nbsp;&bull;&nbsp;
                Generated: {{ now()->format('d M Y, h:i A') }} &nbsp;&bull;&nbsp;
                {{ $totalCnt }} record(s) &nbsp;&bull;&nbsp;
                Confidential &mdash; for internal use only.
            </td>
            <td class="pfr">Confidential &nbsp;&bull;&nbsp; Internal Use Only</td>
        </tr>
    </table>
</div>

</body>
</html>