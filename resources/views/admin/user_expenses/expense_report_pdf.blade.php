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
   HEADER
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
.hdr-user-desig { font-size: 8.5px; color: #475569; margin-top: 1px; }
.hdr-doc-type {
    font-size: 13px;
    font-weight: bold;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 4px;
}
.hdr-date   { font-size: 8px; color: #64748b; }
.hdr-period { font-size: 8px; color: #475569; margin-top: 3px; }

/* ═══════════════════════════════════════
   KPI SUMMARY STRIP
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
.s-box-total { border-top: 3px solid #1e293b; }
.s-box-req   { border-top: 3px solid #475569; }
.s-box-apr   { border-top: 3px solid #64748b; }
.s-box-pend  { border-top: 3px solid #e9a8b0; }
.s-box-apprd { border-top: 3px solid #94a3b8; }
.s-box-part  { border-top: 3px solid #cbd5e1; }
.s-box-rej   { border-top: 3px solid #94a3b8; }

.s-big    { font-size: 18px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #0f172a; }
.s-big-sm { font-size: 12px; font-weight: bold; display: block; line-height: 1.1; margin-bottom: 2px; color: #0f172a; }
.s-tag    { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.7px; display: block; color: #475569; }

/* ═══════════════════════════════════════
   FILTER TAGS ROW
═══════════════════════════════════════ */
.filter-row {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.filter-row td { padding: 7px 12px; vertical-align: middle; }
.f-lbl {
    font-size: 7.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.8px;
    color: #64748b; white-space: nowrap; padding-right: 10px;
}
.ftag {
    display: inline-block;
    background: transparent; color: #334155;
    font-size: 8px; font-weight: bold;
    padding: 3px 6px;
    margin-right: 5px; letter-spacing: 0.2px;
    text-transform: uppercase;
}

/* ═══════════════════════════════════════
   SECTION HEADER BAR
═══════════════════════════════════════ */
.sec-bar-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.sec-bar-td {
    background-color: #e9eff6;
    padding: 8px 12px;
    font-size: 8.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.8px;
    color: #334e68; vertical-align: middle;
}
.sec-bar-count-td {
    background-color: #e9eff6;
    font-size: 8.5px; font-weight: bold;
    color: #334e68; text-align: right;
    padding-right: 14px; vertical-align: middle; width: 160px;
}

/* ═══════════════════════════════════════
   EXPENSE TABLE
═══════════════════════════════════════ */
.exp-wrap { border: 1px solid #cbd5e1; margin-bottom: 20px; }

.exp-tbl { width: 100%; border-collapse: collapse; }

.exp-tbl thead tr { background: #1e293b; }
.exp-tbl thead th {
    padding: 9px 10px;
    font-size: 7.5px; font-weight: bold;
    color: #e2e8f0;
    text-transform: uppercase; letter-spacing: 0.7px;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,0.08);
    white-space: nowrap;
}
.exp-tbl thead th:last-child { border-right: none; }
.exp-tbl thead th.r { text-align: right; }
.exp-tbl thead th.c { text-align: center; }

.exp-tbl tbody tr { border-bottom: 1px solid #e2e8f0; }
.exp-tbl tbody tr.ro { background: #ffffff; }
.exp-tbl tbody tr.re { background: #f8fafc; }

.exp-tbl tbody td {
    padding: 9px 10px;
    font-size: 8.5px; color: #334155;
    border-right: 1px solid #e2e8f0;
    vertical-align: top; line-height: 1.5;
}
.exp-tbl tbody td:last-child { border-right: none; }

.c-id { text-align: center !important; font-size: 8px !important; font-weight: bold; color: #94a3b8 !important; width: 24px; }

/* Date cell */
.d-day { font-size: 17px; font-weight: bold; color: #0f172a; line-height: 1; }
.d-mon { font-size: 8px; font-weight: bold; color: #334155; text-transform: uppercase; margin-top: 1px; }
.d-yr  { font-size: 7.5px; color: #94a3b8; margin-top: 1px; }
.d-wd  { font-size: 7px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px; }

/* Employee */
.emp-name  { font-size: 9px; font-weight: bold; color: #0f172a; }
.emp-phone { font-size: 8px; color: #475569; margin-top: 2px; }

/* Category & Details */
.cat-nm { font-size: 9px; font-weight: bold; color: #0f172a; }
.cp-miss {
    display: inline-block; background: #fee2e2; color: #991b1b;
    border: 1px solid #fca5a5; font-size: 7px; font-weight: bold;
    padding: 2px 6px; border-radius: 2px; letter-spacing: 0.3px;
    margin-top: 3px;
}
.cat-rmk   { font-size: 8px; color: #475569; margin-top: 4px; font-style: italic; line-height: 1.4; }
.cat-msrsn { font-size: 8px; color: #991b1b; margin-top: 3px; font-style: italic; line-height: 1.4; }

/* Travel info inside category column */
.trv-block { margin-top: 6px; padding-top: 5px; border-top: 1px dashed #e2e8f0; }
.trv-km    { font-size: 9px; font-weight: bold; color: #334155; }
.trv-rate  { font-size: 7.5px; color: #64748b; margin-top: 2px; }
.trv-rt    { font-size: 7.5px; color: #94a3b8; margin-top: 2px; font-style: italic; }

/* Amount columns */
.amt-lbl  { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; color: #94a3b8; margin-bottom: 2px; }
.amt-req  { font-size: 11px; font-weight: bold; color: #0f172a; }
.amt-apr  { font-size: 11px; font-weight: bold; color: #0f172a; }
.amt-nil  { font-size: 11px; color: #cbd5e1; }

/* Verified */
.v-yes {
    display: inline-block; background: #f1f5f9; color: #0f172a;
    font-size: 7.5px; font-weight: bold;
    padding: 2px 7px; border-radius: 2px;
}
.v-no {
    display: inline-block; background: transparent; color: #94a3b8;
    font-size: 7.5px; font-weight: bold;
    padding: 2px 0px;
}
.v-by  { font-size: 7px; color: #64748b; margin-top: 3px; font-style: italic; }
.ab-by { font-size: 7px; color: #64748b; margin-top: 3px; font-style: italic; }

.int-note {
    margin-top: 6px; background: #fefce8;
    border: 1px solid #fde047; border-left: 3px solid #ca8a04;
    border-radius: 2px; padding: 5px 8px;
}
.int-note-lbl { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6px; color: #854d0e; margin-bottom: 2px; }
.int-note-txt { font-size: 8px; color: #431407; font-style: italic; line-height: 1.4; }

/* Status badges */
.st-ap { font-size:8.5px; font-weight:bold; color:#16a34a; white-space:nowrap; }
.st-pa  { font-size:8.5px; font-weight:bold; color:#ea580c; white-space:nowrap; }
.st-rj  { font-size:8.5px; font-weight:bold; color:#dc2626; white-space:nowrap; }
.st-pd  { font-size:8.5px; font-weight:bold; color:#9d174d; white-space:nowrap; }
.st-req { font-size:8.5px; font-weight:bold; color:#9d174d; white-space:nowrap; }
.st-df  { font-size:8.5px; font-weight:bold; color:#64748b; white-space:nowrap; }
.adm-rmk { font-size: 7.5px; color: #475569; margin-top: 4px; font-style: italic; line-height: 1.4; }

/* TFOOT */
.exp-tbl tfoot tr { background: #1e293b; }
.exp-tbl tfoot td {
    padding: 10px;
    border-top: 2px solid #475569;
    color: #e2e8f0;
}
.tf-lbl { font-size: 7.5px; color: #94a3b8; letter-spacing: 0.4px; }
.tf-req { font-size: 12px; font-weight: bold; color: #ffffff; text-align: right; }
.tf-apr { font-size: 11px; font-weight: bold; color: #cbd5e1; text-align: right; margin-top: 4px; }
.tf-sav { font-size: 7.5px; color: rgba(255,255,255,0.35); text-align: right; margin-top: 4px; }

/* ═══════════════════════════════════════
   CATEGORY SUMMARY SECTION
═══════════════════════════════════════ */
.cat-sec-title-table { width: 100%; border-collapse: collapse; margin-top: 28px; margin-bottom: 0; }
.cat-sec-title-td {
    background-color: #1e293b;
    padding: 9px 12px;
    font-size: 8.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.8px;
    color: #e2e8f0;
}

/* Consolidated KPI row for category section */
.cat-kpi-strip {
    width: 100%;
    border-collapse: collapse;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-top: none;
    margin-bottom: 0;
}
.cat-kpi-strip td {
    padding: 8px 14px;
    vertical-align: middle;
    border-right: 1px solid #e2e8f0;
    font-size: 8px;
}
.cat-kpi-strip td:last-child { border-right: none; }
.ck-lbl { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; display: block; margin-bottom: 2px; }
.ck-val { font-size: 11px; font-weight: bold; color: #0f172a; display: block; }
.ck-val-pink { font-size: 11px; font-weight: bold; color: #9d174d; display: block; }

/* Category breakdown table */
.cat-tbl-wrap { border: 1px solid #cbd5e1; border-top: none; margin-bottom: 20px; }
.cat-tbl { width: 100%; border-collapse: collapse; }

.cat-tbl thead tr { background: #334155; }
.cat-tbl thead th {
    padding: 8px 10px;
    font-size: 7.5px; font-weight: bold;
    color: #e2e8f0;
    text-transform: uppercase; letter-spacing: 0.6px;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,0.08);
    white-space: nowrap;
}
.cat-tbl thead th:last-child { border-right: none; }
.cat-tbl thead th.r { text-align: right; }
.cat-tbl thead th.c { text-align: center; }

.cat-tbl tbody tr { border-bottom: 1px solid #e2e8f0; }
.cat-tbl tbody tr.ro { background: #ffffff; }
.cat-tbl tbody tr.re { background: #f8fafc; }
.cat-tbl tbody td {
    padding: 8px 10px;
    font-size: 8.5px; color: #334155;
    border-right: 1px solid #e2e8f0;
    vertical-align: middle;
}
.cat-tbl tbody td:last-child { border-right: none; }

.cat-name-cell { font-size: 9px; font-weight: bold; color: #0f172a; }
.cat-dist-cell { font-size: 8px; color: #64748b; margin-top: 2px; }
.cat-cnt-badge {
    display: inline-block; background: #f1f5f9; color: #334155;
    font-size: 7.5px; font-weight: bold;
    padding: 2px 7px; border-radius: 2px;
    text-align: center;
}
.cat-apr-val { font-size: 9px; font-weight: bold; color: #0f172a; text-align: right; display: block; }
.cat-req-val { font-size: 8px; color: #475569; text-align: right; display: block; margin-top: 2px; }
.cat-pend-val { font-size: 9px; font-weight: bold; color: #9d174d; text-align: right; display: block; }
.cat-pend-cnt { font-size: 7.5px; color: #be185d; text-align: right; display: block; margin-top: 2px; }
.cat-nil { color: #cbd5e1; font-size: 10px; text-align: right; display: block; }

.cat-tbl tfoot tr { background: #334155; }
.cat-tbl tfoot td {
    padding: 9px 10px;
    border-top: 2px solid #475569;
    color: #e2e8f0;
    font-size: 8.5px; font-weight: bold;
}
.cat-tbl tfoot td.r { text-align: right; }

/* ═══════════════════════════════════════
   FOOTER
═══════════════════════════════════════ */
.footer-table {
    width: 100%; border-collapse: collapse;
    margin-top: 24px; border-top: 1px solid #cbd5e1; padding-top: 8px;
}
.footer-left  { font-size: 8px; font-weight: bold; color: #334155; }
.footer-mid   { font-size: 7.5px; color: #64748b; text-align: center; }
.footer-right { font-size: 7.5px; color: #64748b; text-align: right; }

.empty-row td {
    text-align: center; padding: 24px;
    color: #94a3b8; font-style: italic; font-size: 8.5px;
    border-bottom: 1px solid #e2e8f0;
}
</style>
</head>
<body>

@php
    $totalCnt    = $expenses->count();
    $totalReq    = $expenses->sum('requested_amount');
    $totalApr    = $expenses->sum('approved_amount');
    $cntApproved = $expenses->where('status', 'Approved')->count();
    $cntPartial  = $expenses->where('status', 'Partially Approved')->count();
    $cntPending  = $expenses->whereIn('status', ['Pending Approval', 'Requested'])->count();
    $cntRejected = $expenses->where('status', 'Rejected')->count();

    // Category-wise summary
    $categoryGroups = $expenses->groupBy('category_name');
@endphp

{{-- ══ HEADER ══ --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="{{ public_path('images/greenwave-logo-1-275-sl.jpg') }}" class="logo-img" />
            <div class="hdr-title-label">Expense Report For</div>
            @if($employee)
                <div class="hdr-user-name">{{ $employee->name }}</div>
                @if(!empty($employee->mobile))
                    <div class="hdr-user-desig">{{ $employee->mobile }}</div>
                @endif
            @else
                <div class="hdr-user-name">All Employees</div>
            @endif
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Expense Report</div>
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

{{-- ══ KPI SUMMARY STRIP ══ --}}
<table class="summary-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td class="s-box s-box-total" width="14%">
            <span class="s-big">{{ $totalCnt }}</span>
            <span class="s-tag">Total Claims</span>
        </td>
        <td class="s-box s-box-req" width="18%">
            <span class="s-big-sm">&#8377;{{ number_format($totalReq, 2) }}</span>
            <span class="s-tag">Total Requested</span>
        </td>
        <td class="s-box s-box-apr" width="18%">
            <span class="s-big-sm">&#8377;{{ number_format($totalApr, 2) }}</span>
            <span class="s-tag">Total Approved</span>
        </td>
        @if($cntApproved > 0)
        <td class="s-box s-box-apprd" width="12%">
            <span class="s-big">{{ $cntApproved }}</span>
            <span class="s-tag">Approved</span>
        </td>
        @endif
        @if($cntPartial > 0)
        <td class="s-box s-box-part" width="12%">
            <span class="s-big">{{ $cntPartial }}</span>
            <span class="s-tag">Partial</span>
        </td>
        @endif
        @if($cntPending > 0)
        <td class="s-box s-box-pend" width="12%">
            <span class="s-big" style="color:#9d174d;">{{ $cntPending }}</span>
            <span class="s-tag" style="color:#9d174d;">Pending</span>
        </td>
        @endif
        @if($cntRejected > 0)
        <td class="s-box s-box-rej" width="12%">
            <span class="s-big">{{ $cntRejected }}</span>
            <span class="s-tag">Rejected</span>
        </td>
        @endif
    </tr>
</table>

{{-- ══ FILTER TAGS ══ --}}
<table class="filter-row" cellspacing="0" cellpadding="0">
    <tr>
        <td class="f-lbl">Filters Applied</td>
        <td>
            @if($employee)     <span class="ftag">{{ $employee->name }}</span> @endif
            @if($filterMonth)  <span class="ftag">{{ date('F', mktime(0,0,0,$filterMonth,1)) }}</span> @endif
            @if($filterYear)   <span class="ftag">{{ $filterYear }}</span> @endif
            @if($filterStatus) <span class="ftag">{{ $filterStatus }}</span> @endif
            @if($filterVerified === 'yes')    <span class="ftag">Verified Only</span>
            @elseif($filterVerified === 'no') <span class="ftag">Not Verified</span>
            @endif
            @if(!$employee && !$filterMonth && !$filterYear && !$filterStatus && !$filterVerified)
                <span class="ftag">All Records</span>
            @endif
        </td>
    </tr>
</table>

{{-- ══ SECTION HEADER ══ --}}
<table class="sec-bar-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-bar-td">&#10003;&nbsp; Expense Records</td>
        <td class="sec-bar-count-td">{{ $totalCnt }} record(s)</td>
    </tr>
</table>

{{-- ══ EXPENSE TABLE ══ --}}
<div class="exp-wrap">
<table class="exp-tbl" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th class="c" width="22">#</th>
            @if(!$employee)
            <th width="75">Employee</th>
            @endif
            <th width="44">Date</th>
            <th width="170">Category &amp; Details</th>
            <th class="r" width="75">Requested</th>
            <th class="r" width="75">Approved</th>
            <th width="110">Verification <br> Status</th>
            <th class="c" width="72">Approval <br> Status</th>
        </tr>
    </thead>
    <tbody>
    @forelse($expenses as $idx => $expense)
    @php
        $expDate = \Carbon\Carbon::parse($expense->expense_date);
        $st      = $expense->status;
        $rc      = ($idx % 2 === 0) ? 'ro' : 're';
    @endphp
    <tr class="{{ $rc }}">

        <td class="c-id">{{ $idx + 1 }}</td>

        @if(!$employee)
        <td>
            <div class="emp-name">{{ $expense->employee_name ?? 'N/A' }}</div>
            @if(!empty($expense->employee_mobile))
                <div class="emp-phone">{{ $expense->employee_mobile }}</div>
            @endif
        </td>
        @endif

        {{-- DATE --}}
        <td>
            <div class="d-day">{{ $expDate->format('d') }}</div>
            <div class="d-mon">{{ $expDate->format('M') }}</div>
            <div class="d-yr">{{ $expDate->format('Y') }}</div>
            <div class="d-wd">{{ $expDate->format('D') }}</div>
        </td>

        {{-- CATEGORY & DETAILS (includes travel info) --}}
        <td>
            <div class="cat-nm">{{ $expense->category_name }}</div>
            @if($expense->missed_entry)
                <div><span class="cp-miss">Missed Entry</span></div>
            @endif
            @if(!empty($expense->remarks))
                <div class="cat-rmk">"{{ $expense->remarks }}"</div>
            @endif
            @if($expense->missed_entry && !empty($expense->missed_entry_reason))
                <div class="cat-msrsn">Reason: {{ $expense->missed_entry_reason }}</div>
            @endif
            {{-- TRAVEL INFO moved here --}}
            @if($expense->is_travel && !empty($expense->travel_km))
                <div class="trv-block">
                    <div class="trv-km">&#128663; {{ number_format($expense->travel_km, 0) }} km &nbsp; &times; &nbsp; &#8377;{{ number_format($expense->charge_per_km, 2) }}/km</div>
                    @if($expense->is_intercity && !empty($expense->intercity_route))
                        <div class="trv-rt">{{ $expense->intercity_route }}</div>
                    @endif
                </div>
            @endif
        </td>

        {{-- REQUESTED AMOUNT — separate column --}}
        <td style="text-align:right; vertical-align:top;">
            <div class="amt-req">&#8377;{{ number_format($expense->requested_amount, 2) }}</div>
        </td>

        {{-- APPROVED AMOUNT — separate column --}}
        <td style="text-align:right; vertical-align:top;">
            @if($expense->approved_amount > 0)
                <div class="amt-apr">&#8377;{{ number_format($expense->approved_amount, 2) }}</div>
            @else
                <div class="amt-nil">&#8212;</div>
            @endif
        </td>

        {{-- VERIFIED & APPROVED BY --}}
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
                    <div class="int-note-lbl">&#9998;{{ $expense->internal_remarks }}</div>
                </div>
            @endif
        </td>

        {{-- STATUS --}}
        <td style="text-align:center; vertical-align:top;">
            @if($st === 'Approved')
                <span class="st-ap">Approved</span>
            @elseif($st === 'Partially Approved')
                <span class="st-pa">Partial</span>
            @elseif($st === 'Rejected')
                <span class="st-rj">Rejected</span>
            @elseif($st === 'Pending Approval')
                <span class="st-pd">Pending</span>
            @elseif($st === 'Requested')
                <span class="st-req">Requested</span>
            @else
                <span class="st-df">{{ $st }}</span>
            @endif
            @if(!empty($expense->admin_remarks))
                <div class="adm-rmk">{{ $expense->admin_remarks }}</div>
            @endif
            @if(!empty($expense->approved_by_name))
                <div class="ab-by" style="margin-top:5px; font-size:7px; color:#64748b; font-style:italic;">by {{ $expense->approved_by_name }}</div>
            @endif
        </td>

    </tr>
    @empty
    <tr class="empty-row">
        <td colspan="{{ $employee ? 7 : 8 }}">No expense records found for the selected filters.</td>
    </tr>
    @endforelse
    </tbody>

    @if($expenses->count() > 0)
    <tfoot>
        <tr>
            <td colspan="{{ $employee ? 3 : 4 }}">
                <div class="tf-lbl">TOTALS &mdash; {{ $totalCnt }} {{ $totalCnt == 1 ? 'Record' : 'Records' }}</div>
            </td>
            <td style="text-align:right; padding-right:10px;">
                <div class="tf-req">&#8377;{{ number_format($totalReq, 2) }}</div>
                <div style="font-size:7px;color:#94a3b8;margin-top:2px;">REQUESTED</div>
            </td>
            <td style="text-align:right; padding-right:10px;">
                <div class="tf-apr">&#8377;{{ number_format($totalApr, 2) }}</div>
                <div style="font-size:7px;color:#94a3b8;margin-top:2px;">APPROVED</div>
                <div class="tf-sav">Deductions: &#8377;{{ number_format($totalReq - $totalApr, 2) }}</div>
            </td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>
</div>


{{-- ══ CATEGORY-WISE SUMMARY ══ --}}
@if($expenses->count() > 0)
@php
    // Build category summary
    $catSummary = [];
    foreach ($categoryGroups as $catName => $catExpenses) {
        $catReq        = $catExpenses->sum('requested_amount');
        $catApr        = $catExpenses->sum('approved_amount');
        $catCnt        = $catExpenses->count();
        $catAprCnt     = $catExpenses->where('status', 'Approved')->count();
        $catPendCnt    = $catExpenses->whereIn('status', ['Pending Approval', 'Requested'])->count();
        $catPendAmt    = $catExpenses->whereIn('status', ['Pending Approval', 'Requested'])->sum('requested_amount');
        $catTravelKm   = $catExpenses->where('is_travel', 1)->sum('travel_km');
        $catSummary[]  = [
            'name'      => $catName,
            'total_cnt' => $catCnt,
            'apr_cnt'   => $catAprCnt,
            'pend_cnt'  => $catPendCnt,
            'req_amt'   => $catReq,
            'apr_amt'   => $catApr,
            'pend_amt'  => $catPendAmt,
            'travel_km' => $catTravelKm,
        ];
    }
    $grandPendAmt = collect($catSummary)->sum('pend_amt');
    $grandAprAmt  = collect($catSummary)->sum('apr_amt');
    $grandPendCnt = collect($catSummary)->sum('pend_cnt');
    $grandAprCnt  = collect($catSummary)->sum('apr_cnt');
    $grandCnt     = collect($catSummary)->sum('total_cnt');
@endphp

{{-- Section title bar --}}
<table class="cat-sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="cat-sec-title-td">&#9776;&nbsp; Category-Wise Expense Summary</td>
        <td style="background:#1e293b; text-align:right; padding-right:14px; font-size:8.5px; font-weight:bold; color:#94a3b8; vertical-align:middle; width:160px;">
            {{ count($catSummary) }} {{ count($catSummary) == 1 ? 'Category' : 'Categories' }}
        </td>
    </tr>
</table>

{{-- Consolidated KPI row --}}
<table class="cat-kpi-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td width="16%">
            <span class="ck-lbl">Total Claims</span>
            <span class="ck-val">{{ $grandCnt }}</span>
        </td>
        <td width="16%">
            <span class="ck-lbl">Approved Count</span>
            <span class="ck-val">{{ $grandAprCnt }}</span>
        </td>
        <td width="22%">
            <span class="ck-lbl">Total Approved Amt</span>
            <span class="ck-val">&#8377;{{ number_format($grandAprAmt, 2) }}</span>
        </td>
        <td width="16%">
            <span class="ck-lbl">Pending Count</span>
            <span class="ck-val-pink">{{ $grandPendCnt }}</span>
        </td>
        <td width="22%">
            <span class="ck-lbl">Pending Amount</span>
            <span class="ck-val-pink">&#8377;{{ number_format($grandPendAmt, 2) }}</span>
        </td>
        <td width="8%">
            &nbsp;
        </td>
    </tr>
</table>

{{-- Category breakdown table --}}
<div class="cat-tbl-wrap">
<table class="cat-tbl" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th class="c" width="28">Sr.</th>
            <th width="180">Category Name</th>
            <th class="c" width="50">Claims</th>
            <th class="r" width="100">Requested</th>
            <th class="r" width="110">Approved</th>
            <th class="r" width="110">Pending Approval</th>
        </tr>
    </thead>
    <tbody>
    @foreach($catSummary as $si => $cs)
    @php $rc2 = ($si % 2 === 0) ? 'ro' : 're'; @endphp
    <tr class="{{ $rc2 }}">
        <td style="text-align:center; color:#94a3b8; font-size:8px; font-weight:bold;">{{ $si + 1 }}</td>
        <td>
            <div class="cat-name-cell">{{ $cs['name'] }}</div>
            @if($cs['travel_km'] > 0)
                <div class="cat-dist-cell">&#128663; {{ number_format($cs['travel_km'], 0) }} km traveled</div>
            @endif
        </td>
        <td style="text-align:center;">
            <span class="cat-cnt-badge">{{ $cs['total_cnt'] }}</span>
        </td>
        <td style="text-align:right;">
            <span class="cat-req-val">&#8377;{{ number_format($cs['req_amt'], 2) }}</span>
        </td>
        <td style="text-align:right;">
            @if($cs['apr_amt'] > 0)
                <span class="cat-apr-val">&#8377;{{ number_format($cs['apr_amt'], 2) }}</span>
                @if($cs['apr_cnt'] > 0)
                    <span style="font-size:7px;color:#94a3b8;display:block;margin-top:1px;">{{ $cs['apr_cnt'] }} approved</span>
                @endif
            @else
                <span class="cat-nil">&#8212;</span>
            @endif
        </td>
        <td style="text-align:right;">
            @if($cs['pend_cnt'] > 0)
                <span class="cat-pend-val">&#8377;{{ number_format($cs['pend_amt'], 2) }}</span>
                <span class="cat-pend-cnt">{{ $cs['pend_cnt'] }} pending</span>
            @else
                <span class="cat-nil">&#8212;</span>
            @endif
        </td>
    </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">
                <span style="font-size:7.5px;color:#94a3b8;letter-spacing:0.4px;">TOTALS &mdash; {{ count($catSummary) }} {{ count($catSummary) == 1 ? 'Category' : 'Categories' }}</span>
            </td>
            <td class="c" style="text-align:center;">
                <span style="font-size:10px;font-weight:bold;color:#ffffff;">{{ $grandCnt }}</span>
            </td>
            <td class="r" style="text-align:right; padding-right:10px;">
                <span style="font-size:10px;font-weight:bold;color:#ffffff;">&#8377;{{ number_format($totalReq, 2) }}</span>
            </td>
            <td class="r" style="text-align:right; padding-right:10px;">
                <span style="font-size:10px;font-weight:bold;color:#e2e8f0;">&#8377;{{ number_format($grandAprAmt, 2) }}</span>
            </td>
            <td class="r" style="text-align:right; padding-right:10px;">
                @if($grandPendCnt > 0)
                    <span style="font-size:10px;font-weight:bold;color:#f9a8d4;">&#8377;{{ number_format($grandPendAmt, 2) }}</span>
                    <span style="font-size:7.5px;color:#f9a8d4;display:block;margin-top:2px;">{{ $grandPendCnt }} pending</span>
                @else
                    <span style="font-size:10px;color:rgba(255,255,255,0.25);">&#8212;</span>
                @endif
            </td>
        </tr>
    </tfoot>
</table>
</div>
@endif


{{-- ══ FOOTER ══ --}}
<table class="footer-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="footer-left">Greenwave &bull; Expense Management</td>
        <td class="footer-mid">Confidential Report &bull; Internal Use Only</td>
        <td class="footer-right">Total: {{ $totalCnt }} records &bull; &#8377;{{ number_format($totalReq, 2) }} requested</td>
    </tr>
</table>

</body>
</html>