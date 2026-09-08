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

/* ── HEADER ── */
.hdr-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.hdr-left  { vertical-align: top; text-align: left; }
.hdr-right { vertical-align: top; text-align: right; }
.logo-img  { width: 150px; height: auto; margin-bottom: 10px; }

.hdr-rule-1 { border-top: 2px solid #1e293b; margin-top: 6px; }
.hdr-rule-2 { border-top: 1px solid #cbd5e1; margin-top: 2px; margin-bottom: 14px; }

.hdr-doc-type {
    font-size: 13px; font-weight: bold; color: #334155;
    text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;
}
.hdr-date { font-size: 8px; color: #64748b; }

/* ── EMPLOYEE DETAILS ── */
.filter-row { margin-bottom: 20px; }
.ftag-lbl {
    font-size: 10px; color: #64748b; font-weight: bold; margin-right: 6px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.ftag {
    display: inline-block; color: #1e293b;
    font-size: 10px; font-weight: bold;
    margin-right: 12px; text-transform: uppercase; letter-spacing: 0.5px;
}

/* ── SUMMARY STRIP — modern spaced KPI cards (gaps via cellspacing, not
   grid borders; every color is set inline in the markup — mPDF has proven
   unreliable applying color through nested/descendant class selectors). ── */
.summary-strip { width: 100%; border-collapse: separate; margin-bottom: 0; }
.s-col { width: 33.33%; vertical-align: top; padding: 16px 18px; border-radius: 10px; }
.s-head  { font-size: 27px; font-weight: bold; display: block; line-height: 1; }
.s-label { font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-top: 4px; }
.s-biz-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.s-biz-table td { font-size: 8.3px; padding: 2.5px 0; color: #334155; }

.month-strip { width: 100%; text-align: right; margin-bottom: 22px; }
.month-strip .m-label { font-size: 13px; font-weight: bold; color: #1e293b; }
.month-strip .m-sub   { font-size: 7.5px; font-weight: bold; letter-spacing: 0.8px; color: #64748b; text-transform: uppercase; }

/* ── SECTION TITLES ── */
.sec-title-table { width: 100%; border-collapse: collapse; margin: 20px 0 0 0; background: #1e293b; }
.sec-title-left {
    color: #ffffff; font-size: 9px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px; padding: 7px 10px;
}
.sec-title-right {
    color: #cbd5e1; font-size: 8px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px; padding: 7px 10px; text-align: right;
}

/* ── DATA TABLES — Date/Customer/Trial Type stay left; numeric columns (via
   .center) are centered — see the "center" class applied per-column below. */
table.data-table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
table.data-table thead tr { background-color: #e9eff6; }
table.data-table thead th {
    padding: 8px; color: #334e68; font-size: 7.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.5px;
    border: 1px solid #cbd5e1; text-align: center; white-space: nowrap;
}
table.data-table tbody td {
    padding: 7px 8px; border: 1px solid #e2e8f0;
    vertical-align: top; color: #334155; background: #ffffff;
    word-wrap: break-word; overflow-wrap: break-word;
}
table.data-table tbody tr:nth-child(even) td { background: #f8fafc; }
.center { text-align: center; }

.col-num { text-align: center; white-space: nowrap; padding-left: 2px !important; padding-right: 2px !important; }

.empty-cell {
    text-align: center; padding: 18px; color: #64748b; font-style: italic;
    border: 1px solid #e2e8f0; border-top: none; background: #ffffff; font-size: 8.5px;
}

/* Breakdown cell — big number + small sub-line */
.brk-num { font-size: 11px; font-weight: bold; color: #0f172a; }
.brk-sub { font-size: 7.3px; color: #64748b; margin-top: 2px; }
.brk-sub .ok  { color: #16a34a; font-weight: bold; }
.brk-sub .bad { color: #dc2626; font-weight: bold; }

/* Totals row — solid navy band. Styled with INLINE styles in the markup below,
   not a CSS class: mPDF does not reliably honor `!important` against the more
   specific `table.data-table tbody td` rule, so a class-based override silently
   lost and left the row background white with invisible white-on-white text. */

/* Trial status */
.status-txt {
    font-size: 8px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
}

</style>
</head>
<body>

@php
    $overall      = $data['overall'];
    $dateWise     = $data['dateWise'];
    $customerWise = $data['customerWise'];
    $trialDetails = $data['trialDetails'];

    /* ── Zero is rendered as a muted dash, never as "0" — a screen full of
       zeros reads as noise; a dash reads as "nothing to report". Applies to
       the Date-Wise and Customer-Wise tables (client request). Every colour
       is inline: mPDF does not reliably resolve nested class selectors. ── */
    $bigNum = function ($n) {
        return $n > 0
            ? '<span class="brk-num">' . $n . '</span>'
            : '<span class="brk-num" style="color:#94a3b8;">&mdash;</span>';
    };

    $totalNum = function ($n) {
        return $n > 0
            ? '<span style="color:#ffffff; font-size:11px; font-weight:bold;">' . $n . '</span>'
            : '<span style="color:#94a3b8; font-size:11px; font-weight:bold;">&mdash;</span>';
    };

    /* Inline sub-line figure inside a TOTAL row, e.g. "Met: 12" / "Met: —". */
    $totalSub = function ($label, $n, $color) {
        return '<span style="color:' . $color . '; font-weight:bold;">' . $label . ': '
             . ($n > 0 ? $n : '&mdash;') . '</span>';
    };

    /* ── "Visit Detail Pending" sub-line — ONE definition, used by both the
       Date-Wise and Customer-Wise tables. It sits next to "Not Met: 2" and
       "Report Pending: 1", so it is styled exactly like them (plain text,
       same size, no box, no icon) and reads as the same kind of note; only
       the colour differs — amber, because unfilled notes are a reminder, not
       a failed visit like the red items. ── */
    $vdpLine = function ($n) {
        return $n > 0
            ? '<div class="brk-sub"><span style="color:#b45309; font-weight:bold;">Visit Detail Pending: ' . $n . '</span></div>'
            : '';
    };

    /* Same line inside the dark navy TOTAL band — amber lightened to stay
       legible on #1e293b. */
    $vdpTotalLine = function ($n) {
        return $n > 0
            ? '<div style="font-size:7.3px; margin-top:2px;"><span style="color:#fbbf24; font-weight:bold;">Visit Detail Pending: ' . $n . '</span></div>'
            : '';
    };
@endphp

{{-- ── HEADER ── --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Monthly Customer Visit Analysis</div>
            <div class="hdr-date">Month: {{ $monthRange }}</div>
            <div class="hdr-date">Generated on: {{ $generatedAt }}</div>
        </td>
    </tr>
</table>
<div class="hdr-rule-1"></div>
<div class="hdr-rule-2"></div>

{{-- ── EMPLOYEE DETAILS ── --}}
<div class="filter-row">
    <span class="ftag-lbl">Employee:</span>
    <span class="ftag">{{ $employee->name }} | </span>
    @if(!empty($employee->designation))
        <span class="ftag">{{ $employee->designation }} | </span>
    @endif
    @if(!empty($employee->base_city))
        <span class="ftag">{{ $employee->base_city }}</span>
    @endif
</div>

{{-- ── MONTH LABEL ── --}}
<div class="month-strip">
    <span class="m-label">{{ $monthLabel }}</span><br>
    <span class="m-sub">Overall Summary</span>
</div>

{{-- ── SUMMARY STRIP — Total Visits | Trials Done | Customers Visited (dealer-wise) ── --}}
<table class="summary-strip" cellspacing="8" cellpadding="0">
    <tr>
        <td class="s-col" style="background-color:#f1f5f9; border-top:4px solid #1e293b;">
            <span class="s-head" style="color:#1e293b;">{{ $overall['total_visits'] }}</span>
            <span class="s-label">Total Visits</span>
            <table class="s-biz-table" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Met</td>
                    <td style="text-align:right; color:#16a34a; font-weight:bold;">{{ $overall['met'] }}</td>
                </tr>
                <tr>
                    <td>Not Met</td>
                    <td style="text-align:right; color:#dc2626; font-weight:bold;">{{ $overall['not_met'] }}</td>
                </tr>
            </table>
        </td>
        <td class="s-col" style="background-color:#f4effc; border-top:4px solid #7c3aed;">
            <span class="s-head" style="color:#6d28d9;">{{ $overall['total_trials'] }}</span>
            <span class="s-label">Trials Done</span>
            <table class="s-biz-table" cellspacing="0" cellpadding="0">
                <tr>
                    <td>Attached</td>
                    <td style="text-align:right; color:#16a34a; font-weight:bold;">{{ $overall['trials_attached'] }}</td>
                </tr>
                <tr>
                    <td>Not Attached</td>
                    <td style="text-align:right; color:#dc2626; font-weight:bold;">{{ $overall['trials_pending'] }}</td>
                </tr>
            </table>
        </td>
        <td class="s-col" style="background-color:#eaf1fd; border-top:4px solid #2563eb;">
            <span class="s-head" style="color:#1d4ed8;">{{ $overall['unique_customers'] }}</span>
            <span class="s-label">Customers Visited</span>
            <table class="s-biz-table" cellspacing="0" cellpadding="0">
                @foreach($overall['dealer_wise'] as $dw)
                <tr>
                    <td>{{ $dw['dealer_name'] }}</td>
                    <td style="text-align:right; color:#1d4ed8; font-weight:bold;">{{ $dw['count'] }}</td>
                </tr>
                @endforeach
                @if($overall['direct_customers'] > 0)
                <tr>
                    <td>Direct Customer</td>
                    <td style="text-align:right; color:#1d4ed8; font-weight:bold;">{{ $overall['direct_customers'] }}</td>
                </tr>
                @endif
                @if($overall['open_customers'] > 0)
                <tr>
                    <td>Open</td>
                    <td style="text-align:right; color:#1d4ed8; font-weight:bold;">{{ $overall['open_customers'] }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>


{{-- ═══════════════ 1. DATE-WISE ANALYSIS ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">1. Date-Wise Analysis &mdash; {{ $monthLabel }}</td>
        <td class="sec-title-right">{{ count($dateWise) }} {{ count($dateWise) === 1 ? 'Active Day' : 'Active Days' }}</td>
    </tr>
</table>
@if(count($dateWise) === 0)
    <div class="empty-cell">No visits were recorded during {{ $monthLabel }}.</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="col-num" style="width:26px;">#</th>
            <th style="width:25%;">Date</th>
            <th class="center" style="width:37%;">Visits</th>
            <th class="center" style="width:38%;">Trials</th>
        </tr>
    </thead>
    <tbody>
    @foreach($dateWise as $i => $dw)
        <tr>
            <td class="col-num" style="width:26px; color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $dw['date'] }}</span><br>
                <span style="font-size:7px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">{{ $dw['day'] }}</span>
            </td>
            <td class="center">
                {!! $bigNum($dw['visits']) !!}
                @if($dw['not_met'] > 0)
                <div class="brk-sub"><span style="color:#dc2626; font-weight:bold;">Not Met: {{ $dw['not_met'] }}</span></div>
                @endif
                {!! $vdpLine($dw['visit_detail_pending']) !!}
            </td>
            <td class="center">
                {!! $bigNum($dw['trials']) !!}
                @if($dw['trials_not_attached'] > 0)
                <div class="brk-sub"><span style="color:#dc2626; font-weight:bold;">Report Pending: {{ $dw['trials_not_attached'] }}</span></div>
                @endif
            </td>
        </tr>
    @endforeach
        @php $tdBg = 'background-color:#1e293b; color:#ffffff; font-weight:bold; border:1px solid #1e293b;'; @endphp
        <tr class="totals-row">
            <td colspan="2" style="{{ $tdBg }} text-align:right;">TOTAL</td>
            <td class="center" style="{{ $tdBg }}">
                {!! $totalNum($overall['total_visits']) !!}
                <div style="font-size:7.3px; margin-top:2px;">{!! $totalSub('Met', $overall['met'], '#4ade80') !!} &nbsp;|&nbsp; {!! $totalSub('Not Met', $overall['not_met'], '#fca5a5') !!}</div>
                {!! $vdpTotalLine($overall['visit_detail_pending']) !!}
            </td>
            <td class="center" style="{{ $tdBg }}">
                {!! $totalNum($overall['total_trials']) !!}
                <div style="font-size:7.3px; margin-top:2px;">{!! $totalSub('Attached', $overall['trials_attached'], '#4ade80') !!} &nbsp;|&nbsp; {!! $totalSub('Pending', $overall['trials_pending'], '#fca5a5') !!}</div>
            </td>
        </tr>
    </tbody>
</table>
@endif

{{-- ── VISIT DETAIL PENDING — action banner, only shown when it needs attention ── --}}
@if($overall['visit_detail_pending'] > 0)
<table style="width:100%; border-collapse:collapse; margin-top:10px;">
    <tr>
        <td style="background-color:#fff7e6; border:1px solid #fcd88f; border-left:4px solid #f59e0b; padding:9px 14px; font-size:9px; font-weight:bold; color:#92400e;">
            {{ $overall['visit_detail_pending'] }} Visit Detail{{ $overall['visit_detail_pending'] == 1 ? '' : 's' }} Pending
            <span style="font-weight:normal; color:#78350f;">&mdash; out of {{ $overall['total_visits'] }} visits this month, {{ $overall['visit_detail_pending'] }} still need{{ $overall['visit_detail_pending'] == 1 ? 's' : '' }} the visit-detail notes filled in.</span>
        </td>
    </tr>
</table>
@endif
{{-- ═══════════════ 2. CUSTOMER-WISE ANALYSIS ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">2. Customer-Wise Analysis &mdash; {{ $monthLabel }}</td>
        <td class="sec-title-right">{{ count($customerWise) }} {{ count($customerWise) === 1 ? 'Customer' : 'Customers' }}</td>
    </tr>
</table>
@if(count($customerWise) === 0)
    <div class="empty-cell">No customer visits were recorded during {{ $monthLabel }}.</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="col-num" style="width:26px;">#</th>
            <th style="width:38%;">Customer</th>
            <th class="center" style="width:31%;">Total Visits</th>
            <th class="center" style="width:31%;">Trials</th>
        </tr>
    </thead>
    <tbody>
    @foreach($customerWise as $i => $cw)
        <tr>
            <td class="col-num" style="width:26px; color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $cw['customer_name'] }}</span><br>
                <span style="font-size:7px; color:#64748b;">Business Linking: {{ !empty($cw['dealer_name']) ? $cw['dealer_name'] : $cw['business_type'] }}</span>
            </td>
            <td class="center">
                {!! $bigNum($cw['total_visits']) !!}
                @if($cw['not_met'] > 0)
                <div class="brk-sub"><span style="color:#dc2626; font-weight:bold;">Not Met: {{ $cw['not_met'] }}</span></div>
                @endif
                {!! $vdpLine($cw['visit_detail_pending']) !!}
            </td>
            <td class="center">
                {!! $bigNum($cw['trials']) !!}
                @if($cw['trials_pending'] > 0)
                <div class="brk-sub"><span style="color:#dc2626; font-weight:bold;">Report Pending: {{ $cw['trials_pending'] }}</span></div>
                @endif
            </td>
        </tr>
    @endforeach
        @php $tdBg2 = 'background-color:#1e293b; color:#ffffff; font-weight:bold; border:1px solid #1e293b;'; @endphp
        <tr class="totals-row">
            <td colspan="2" style="{{ $tdBg2 }} text-align:right;">TOTAL</td>
            <td class="center" style="{{ $tdBg2 }}">
                {!! $totalNum($overall['total_visits']) !!}
                <div style="font-size:7.3px; margin-top:2px;">{!! $totalSub('Met', $overall['met'], '#4ade80') !!} &nbsp;|&nbsp; {!! $totalSub('Not Met', $overall['not_met'], '#fca5a5') !!}</div>
                {!! $vdpTotalLine($overall['visit_detail_pending']) !!}
            </td>
            <td class="center" style="{{ $tdBg2 }}">
                {!! $totalNum($overall['total_trials']) !!}
                <div style="font-size:7.3px; margin-top:2px;">{!! $totalSub('Attached', $overall['trials_attached'], '#4ade80') !!} &nbsp;|&nbsp; {!! $totalSub('Pending', $overall['trials_pending'], '#fca5a5') !!}</div>
            </td>
        </tr>
    </tbody>
</table>
@endif

{{-- ═══════════════ 3. TRIAL DETAILS ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">3. Trial Details &mdash; {{ $monthLabel }}</td>
        <td class="sec-title-right">{{ count($trialDetails) }} {{ count($trialDetails) === 1 ? 'Trial' : 'Trials' }}</td>
    </tr>
</table>
@if(count($trialDetails) === 0)
    <div class="empty-cell">No trials were recorded during {{ $monthLabel }}.</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="col-num" style="width:26px;">#</th>
            <th style="width:13%;">Date</th>
            <th style="width:27%;">Customer</th>
            <th style="width:22%;">Trial Type</th>
            <th style="width:20%;">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($trialDetails as $i => $tr)
        <tr>
            <td class="col-num" style="width:26px; color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>{{ $tr['date'] }}</td>
            <td style="font-weight:bold; color:#0f172a;">{{ $tr['customer_name'] }}</td>
            <td>{{ $tr['trial_type'] }}</td>
            <td>
                <span class="status-txt" style="color: {{ $tr['status_color'] }};">&#9679; {{ $tr['status'] }}</span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

</body>
</html>
