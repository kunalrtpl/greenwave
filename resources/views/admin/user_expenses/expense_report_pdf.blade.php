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
   HEADER — matches move_customers design
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
    color: #7A8A73;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.hdr-user-name {
    font-size: 15px;
    font-weight: bold;
    color: #1A2416;
    letter-spacing: -0.2px;
}
.hdr-user-desig { font-size: 8.5px; color: #60705A; margin-top: 1px; }
.hdr-doc-type {
    font-size: 12px;
    font-weight: bold;
    color: #44543C;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 4px;
}
.hdr-date   { font-size: 8px; color: #7A8A73; }
.hdr-period { font-size: 8px; color: #60705A; margin-top: 3px; }

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
    border: 1px solid #D6E0D2;
    background-color: #FAFCF9;
}
.s-box + .s-box { border-left: none; }
.s-box-total { border-top: 3px solid #708A63; }
.s-box-req   { border-top: 3px solid #BDD27B; }
.s-box-apr   { border-top: 3px solid #A4C497; }
.s-box-pend  { border-top: 3px solid #E8C438; }
.s-box-apprd { border-top: 3px solid #5AAB7A; }
.s-box-part  { border-top: 3px solid #D4A43A; }
.s-box-rej   { border-top: 3px solid #C47070; }

.s-big    { font-size: 18px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #1A2416; }
.s-big-sm { font-size: 12px; font-weight: bold; display: block; line-height: 1.1; margin-bottom: 2px; color: #1A2416; }
.s-tag    { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.7px; display: block; color: #60705A; }

/* ═══════════════════════════════════════
   FILTER TAGS ROW
═══════════════════════════════════════ */
.filter-row {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
    background: #F2F7EF;
    border: 1px solid #D6E0D2;
}
.filter-row td { padding: 7px 12px; vertical-align: middle; }
.f-lbl {
    font-size: 7.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.8px;
    color: #7A8A73; white-space: nowrap; padding-right: 10px;
}
.ftag {
    display: inline-block;
    background: #EAF2E6; color: #44543C;
    border: 1px solid #BDD27B;
    font-size: 8px; font-weight: bold;
    padding: 3px 9px; border-radius: 2px;
    margin-right: 5px; letter-spacing: 0.2px;
}

/* ═══════════════════════════════════════
   SECTION HEADER BAR
═══════════════════════════════════════ */
.sec-bar-table { width: 100%; border-collapse: collapse; }
.sec-bar-td {
    background-color: #EAF2E6;
    padding: 8px 12px;
    font-size: 8.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.8px;
    color: #2F3B2A; vertical-align: middle;
}
.sec-bar-count-td {
    background-color: #EAF2E6;
    font-size: 8.5px; font-weight: bold;
    color: #44543C; text-align: right;
    padding-right: 14px; vertical-align: middle; width: 160px;
}

/* ═══════════════════════════════════════
   EXPENSE TABLE
═══════════════════════════════════════ */
.exp-wrap { border: 1px solid #D6E0D2; margin-bottom: 20px; }

.exp-tbl { width: 100%; border-collapse: collapse; }

.exp-tbl thead tr { background: #2F3B2A; }
.exp-tbl thead th {
    padding: 9px 10px;
    font-size: 8px; font-weight: bold;
    color: #BDD27B;
    text-transform: uppercase; letter-spacing: 0.7px;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,0.08);
    white-space: nowrap;
}
.exp-tbl thead th:last-child { border-right: none; }
.exp-tbl thead th.r { text-align: right; }
.exp-tbl thead th.c { text-align: center; }

.exp-tbl tbody tr { border-bottom: 1px solid #EBF0E9; }
.exp-tbl tbody tr.ro { background: #ffffff; }
.exp-tbl tbody tr.re { background: #FAFCF9; }

.exp-tbl tbody td {
    padding: 9px 10px;
    font-size: 8.5px; color: #2F3B2A;
    border-right: 1px solid #EBF0E9;
    vertical-align: top; line-height: 1.5;
}
.exp-tbl tbody td:last-child { border-right: none; }

.c-id { text-align: center !important; font-size: 8px !important; font-weight: bold; color: #8AA081 !important; width: 28px; }

.d-day { font-size: 18px; font-weight: bold; color: #1A2416; line-height: 1; }
.d-mon { font-size: 8.5px; font-weight: bold; color: #44543C; text-transform: uppercase; margin-top: 1px; }
.d-yr  { font-size: 8px; color: #8AA081; margin-top: 1px; }
.d-wd  { font-size: 7.5px; color: #A0B098; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 2px; }

.emp-name  { font-size: 9.5px; font-weight: bold; color: #1A2416; }
.emp-phone { font-size: 8px; color: #60705A; margin-top: 2px; }

.cat-nm { font-size: 9.5px; font-weight: bold; color: #1A2416; }
.cp-cat {
    display: inline-block; background: #EAF2E6; color: #2F3B2A;
    border: 1px solid #BDD27B; font-size: 7.5px; font-weight: bold;
    padding: 2px 7px; border-radius: 2px; text-transform: uppercase;
    letter-spacing: 0.3px; margin-top: 3px;
}
.cp-miss {
    display: inline-block; background: #FDEAEA; color: #9B2C2C;
    border: 1px solid #F0B8B8; font-size: 7.5px; font-weight: bold;
    padding: 2px 7px; border-radius: 2px; margin-left: 4px; letter-spacing: 0.3px;
}
.cat-rmk   { font-size: 8px; color: #60705A; margin-top: 5px; font-style: italic; line-height: 1.4; }
.cat-msrsn { font-size: 8px; color: #9B2C2C; margin-top: 4px; font-style: italic; line-height: 1.4; }

.amt-lbl  { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; color: #8AA081; margin-bottom: 2px; }
.amt-req  { font-size: 11px; font-weight: bold; color: #1A2416; }
.amt-rule { border: none; border-top: 1px dashed #D6E0D2; margin: 5px 0; }
.amt-apr  { font-size: 10.5px; font-weight: bold; color: #157840; }
.amt-nil  { font-size: 11px; color: #C4D0C0; }

.trv-km   { font-size: 10px; font-weight: bold; color: #44543C; }
.trv-rate { font-size: 8px; color: #60705A; margin-top: 3px; }
.trv-rt   { font-size: 7.5px; color: #8AA081; margin-top: 3px; font-style: italic; }
.trv-nil  { font-size: 11px; color: #D6E0D2; }

.v-yes {
    display: inline-block; background: #E2F5EA; color: #157840;
    border: 1px solid #88D4A8; font-size: 8px; font-weight: bold;
    padding: 3px 9px; border-radius: 2px;
}
.v-no {
    display: inline-block; background: #F4F6F2; color: #8AA081;
    border: 1px solid #D6E0D2; font-size: 8px; font-weight: bold;
    padding: 3px 9px; border-radius: 2px;
}
.v-by { font-size: 7.5px; color: #60705A; margin-top: 4px; font-style: italic; }

.int-note {
    margin-top: 6px; background: #FFFBEE;
    border: 1px solid #E8D070; border-left: 3px solid #D4A800;
    border-radius: 2px; padding: 5px 8px;
}
.int-note-lbl { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6px; color: #9A7800; margin-bottom: 3px; }
.int-note-txt { font-size: 8px; color: #4A3800; font-style: italic; line-height: 1.4; }

.st-ap { display:inline-block; background:#2F3B2A; color:#BDD27B; font-size:8px; font-weight:bold; padding:3px 10px; border-radius:2px; text-transform:uppercase; letter-spacing:0.4px; white-space:nowrap; }
.st-pa { display:inline-block; background:#FFF6E0; color:#8A5C00; border:1px solid #E8C438; font-size:8px; font-weight:bold; padding:3px 10px; border-radius:2px; text-transform:uppercase; letter-spacing:0.4px; white-space:nowrap; }
.st-rj { display:inline-block; background:#FDEAEA; color:#9B2C2C; border:1px solid #F0B8B8; font-size:8px; font-weight:bold; padding:3px 10px; border-radius:2px; text-transform:uppercase; letter-spacing:0.4px; white-space:nowrap; }
.st-pd { display:inline-block; background:#EAF2E6; color:#44543C; border:1px solid #BDD27B; font-size:8px; font-weight:bold; padding:3px 10px; border-radius:2px; text-transform:uppercase; letter-spacing:0.4px; white-space:nowrap; }
.st-df { display:inline-block; background:#F4F6F2; color:#60705A; font-size:8px; font-weight:bold; padding:3px 10px; border-radius:2px; white-space:nowrap; }
.adm-rmk { font-size: 8px; color: #44543C; margin-top: 5px; font-style: italic; line-height: 1.4; }

/* TFOOT */
.exp-tbl tfoot tr { background: #2F3B2A; }
.exp-tbl tfoot td {
    padding: 10px;
    border-top: 2px solid #BDD27B;
    color: #fff;
}
.tf-lbl { font-size: 8px; color: #8AA081; letter-spacing: 0.4px; }
.tf-req { font-size: 12px; font-weight: bold; color: #fff; text-align: right; }
.tf-apr { font-size: 11px; font-weight: bold; color: #BDD27B; text-align: right; margin-top: 4px; }
.tf-sav { font-size: 7.5px; color: rgba(255,255,255,0.35); text-align: right; margin-top: 4px; }

/* FOOTER */
.footer-table {
    width: 100%; border-collapse: collapse;
    margin-top: 24px; border-top: 1px solid #BDD27B; padding-top: 8px;
}
.footer-left  { font-size: 8px; font-weight: bold; color: #44543C; }
.footer-mid   { font-size: 7.5px; color: #7A8A73; text-align: center; }
.footer-right { font-size: 7.5px; color: #7A8A73; text-align: right; }

.empty-row td {
    text-align: center; padding: 24px;
    color: #8AA081; font-style: italic; font-size: 8.5px;
    border-bottom: 1px solid #EBF0E9;
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
            <span class="s-big">{{ $cntPending }}</span>
            <span class="s-tag">Pending</span>
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
            <th class="c" width="24">#</th>
            @if(!$employee)
            <th width="80">Employee</th>
            @endif
            <th width="50">Date</th>
            <th width="140">Category &amp; Details</th>
            <th class="r" width="100">Requested / Approved</th>
            <th width="80">Travel</th>
            <th width="100">Verified &amp; Notes</th>
            <th class="c" width="70">Status</th>
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

        <td>
            <div class="d-day">{{ $expDate->format('d') }}</div>
            <div class="d-mon">{{ $expDate->format('M') }}</div>
            <div class="d-yr">{{ $expDate->format('Y') }}</div>
            <div class="d-wd">{{ $expDate->format('D') }}</div>
        </td>

        <td>
            <div class="cat-nm">{{ $expense->category_name }}</div>
            <div>
                <span class="cp-cat">{{ \Illuminate\Support\Str::limit(strtoupper($expense->category_name), 8, '') }}</span>
                @if($expense->missed_entry)
                    <span class="cp-miss">Missed</span>
                @endif
            </div>
            @if(!empty($expense->remarks))
                <div class="cat-rmk">"{{ \Illuminate\Support\Str::limit($expense->remarks, 55) }}"</div>
            @endif
            @if($expense->missed_entry && !empty($expense->missed_entry_reason))
                <div class="cat-msrsn">Reason: {{ \Illuminate\Support\Str::limit($expense->missed_entry_reason, 50) }}</div>
            @endif
        </td>

        <td style="text-align:right;">
            <div class="amt-lbl">Requested</div>
            <div class="amt-req">&#8377;{{ number_format($expense->requested_amount, 2) }}</div>
            <hr class="amt-rule">
            <div class="amt-lbl">Approved</div>
            @if($expense->approved_amount > 0)
                <div class="amt-apr">&#8377;{{ number_format($expense->approved_amount, 2) }}</div>
            @else
                <div class="amt-nil">&#8212;</div>
            @endif
        </td>

        <td>
            @if($expense->is_travel && !empty($expense->travel_km))
                <div class="trv-km">{{ number_format($expense->travel_km, 0) }} km</div>
                <div class="trv-rate">&#8377;{{ number_format($expense->charge_per_km, 2) }}/km</div>
                @if($expense->is_intercity && !empty($expense->intercity_route))
                    <div class="trv-rt">{{ \Illuminate\Support\Str::limit($expense->intercity_route, 24) }}</div>
                @endif
            @else
                <span class="trv-nil">&#8212;</span>
            @endif
        </td>

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
                    <div class="int-note-txt">{{ \Illuminate\Support\Str::limit($expense->internal_remarks, 55) }}</div>
                </div>
            @endif
        </td>

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
                <div class="adm-rmk">{{ \Illuminate\Support\Str::limit($expense->admin_remarks, 30) }}</div>
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
                <div class="tf-lbl">TOTALS &#8212; {{ $totalCnt }} {{ $totalCnt == 1 ? 'Record' : 'Records' }}</div>
            </td>
            <td style="padding-right:10px;">
                <div class="tf-req">&#8377;{{ number_format($totalReq, 2) }}</div>
                <div class="tf-apr">&#8377;{{ number_format($totalApr, 2) }}</div>
                <div class="tf-sav">Deductions: &#8377;{{ number_format($totalReq - $totalApr, 2) }}</div>
            </td>
            <td colspan="3"></td>
        </tr>
    </tfoot>
    @endif
</table>
</div>

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