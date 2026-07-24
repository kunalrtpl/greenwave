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

/* ── KPI STRIP ── */
.summary-strip { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 22px; }
.s-box {
    text-align: center; vertical-align: middle;
    padding: 10px 6px; border: 1px solid #cbd5e1; background-color: #f8fafc;
}
.s-box + .s-box { border-left: none; }
.s-box-1 { border-top: 3px solid #1e293b; width: 17%; }
.s-box-2 { border-top: 3px solid #475569; width: 17%; }
.s-box-3 { border-top: 3px solid #64748b; width: 17%; }
.s-box-4 { border-top: 3px solid #94a3b8; width: 17%; }

.s-box-date-clean {
    text-align: right;
    vertical-align: middle;
    padding: 10px 0;
    border: none;
    background: transparent;
    width: 32%;
}

.s-big { font-size: 18px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #0f172a; }
.s-tag { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; display: block; color: #475569; }

.s-date-large { font-size: 14px; font-weight: bold; color: #000000; display: block; line-height: 1.2; padding-right: 4px; }
.s-date-day   { font-size: 7.5px; font-weight: bold; letter-spacing: 0.8px; display: block; color: #475569; padding-right: 4px; }

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

/* ── DAY BANDS — one per weekday ── */
.day-band-table { width: 100%; border-collapse: collapse; margin: 16px 0 0 0; background: #334e68; }
.day-band-left {
    color: #ffffff; font-size: 9.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1.5px; padding: 6px 10px;
}
.day-band-date { color: #b9cbdd; font-weight: bold; letter-spacing: 0.5px; }
.day-band-right {
    color: #b9cbdd; font-size: 7.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.8px; padding: 6px 10px; text-align: right;
}
.day-band-sunday { background: #64748b; }

.day-sub-head {
    font-size: 7.5px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 1px;
    padding: 8px 0 3px 0;
}

.day-empty {
    text-align: center; padding: 10px; color: #94a3b8; font-style: italic;
    border: 1px solid #e2e8f0; border-top: none; background: #fbfcfe; font-size: 8px;
}

/* ── DATA TABLES ── */
table.data-table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
table.data-table thead tr { background-color: #e9eff6; }
table.data-table thead th {
    padding: 8px; color: #334e68; font-size: 7.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.5px;
    border: 1px solid #cbd5e1; text-align: left; white-space: nowrap;
}
table.data-table tbody td {
    padding: 6px 8px; border: 1px solid #e2e8f0;
    vertical-align: top; color: #334155; background: #ffffff;
    word-wrap: break-word; overflow-wrap: break-word;
}
table.data-table tbody tr:nth-child(even) td { background: #f8fafc; }
.center { text-align: center; }

/* Serial-number column — fixed width + minimal padding so 2-digit numbers never wrap */
.col-num { text-align: center; white-space: nowrap; padding-left: 2px !important; padding-right: 2px !important; }

/* "No visits" message for empty days in the Customer Visits section */
.day-band-empty { background: #94a3b8; }
.no-visit-note {
    padding: 9px 12px; font-size: 8.5px; font-style: italic; color: #64748b;
    background: #f8fafc; border: 1px solid #e2e8f0; border-top: none;
}
.no-visit-note .no-visit-icon { color: #cbd5e1; font-style: normal; }

.empty-cell {
    text-align: center; padding: 18px; color: #64748b; font-style: italic;
    border: 1px solid #e2e8f0; border-top: none; background: #ffffff; font-size: 8.5px;
}

/* ── STATUS ── */
.status-cell { border-collapse: collapse; }
.status-cell td { padding: 0; vertical-align: top; border: none; background: transparent; }
.status-dot {
    width: 12px; font-size: 8px; line-height: 1.4;
    padding-right: 4px !important; text-align: left;
}
.status-txt {
    font-size: 8px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.6px;
    white-space: nowrap; line-height: 1.4;
}
.status-sub {
    margin-top: 1px;
    font-size: 7px; font-weight: bold; color: #94a3b8;
    text-transform: none; letter-spacing: 0.3px;
    white-space: nowrap; line-height: 1.3;
}
.mode-txt {
    font-size: 7.5px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.8px;
    white-space: nowrap;
}
.inline-list { font-weight: bold; color: #334155; }
.inline-list .sep { color: #cbd5e1; font-weight: normal; }

/* ═══════════════ DVR VISIT SHEET ═══════════════ */
.visit-card {
    width: 100%; border-collapse: collapse;
    border: 1px solid #cbd5e1;
    margin-top: 10px;
    page-break-inside: avoid;
}

.vc-head-num {
    width: 58px; background: #1e293b; color: #ffffff;
    text-align: center; vertical-align: middle;
    font-size: 7px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px;
    padding: 9px 4px;
    border-bottom: 1px solid #cbd5e1;
}
.vc-head-num .num { display: block; font-size: 15px; letter-spacing: 0; margin-top: 1px; }
.vc-head {
    background: #e9eff6; padding: 8px 12px;
    border-bottom: 1px solid #cbd5e1; vertical-align: middle;
}
.vc-cust      { font-size: 11px; font-weight: bold; color: #0f172a; }
.vc-cust-city {
    font-size: 8.5px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.6px;
}

.vc-head-meta {
    background: #e9eff6; border-bottom: 1px solid #cbd5e1;
    vertical-align: middle; text-align: right;
    padding: 6px 12px; width: 42%;
}
.vc-meta-mini { border-collapse: collapse; width: 100%; }
.vc-meta-mini td { text-align: center; padding: 0 10px; border-right: 1px solid #cbd5e1; }
.vc-meta-mini td.vcm-last { border-right: none; padding-right: 0; }
.vcm-lbl {
    display: block; font-size: 6px; font-weight: bold; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 1px;
    white-space: nowrap;
}
.vcm-val { font-size: 9px; font-weight: bold; color: #0f172a; white-space: nowrap; }

/* status strip */
.vc-status { width: 100%; border-collapse: collapse; }
.vc-status td {
    width: 20%; text-align: center; padding: 7px 4px;
    border-right: 1px solid #e2e8f0; border-bottom: 1px solid #cbd5e1;
    background: #f8fafc;
}
.vc-status td.last { border-right: none; }
.vcs-lbl {
    display: block; font-size: 6.5px; font-weight: bold; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 2px;
}
.vcs-val    { font-size: 8.5px; font-weight: bold; }
.vcs-ok     { color: #16a34a; }
.vcs-not    { color: #dc2626; }
.icon-tick  { font-size: 9px; font-weight: bold; color: #16a34a; }
.icon-cross { font-size: 9px; font-weight: bold; color: #dc2626; }

/* detail rows */
.vc-detail { width: 100%; border-collapse: collapse; }
.vc-detail td { border-bottom: 1px solid #e2e8f0; vertical-align: top; }
.vc-detail tr.vc-last td { border-bottom: none; }
.vcd-lbl {
    width: 20%; background: #f8fafc; padding: 7px 12px;
    font-size: 7px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.6px;
    border-right: 1px solid #e2e8f0;
}
.vcd-val { padding: 7px 12px; font-size: 8.5px; color: #334155; line-height: 1.55; }

.vcd-lbl-last {
    color: #b45309 !important;
    background: #fff7ed !important;
    border-left: 3px solid #f59e0b;
}

.contact-name { font-weight: bold; color: #0f172a; font-size: 9px; }
.contact-sub  { font-size: 7.5px; color: #64748b; }
.next-plan-val { font-weight: bold; color: #0f172a; }
.next-action-tag {
    display: inline-block; margin-left: 8px;
    font-size: 7.5px; font-weight: bold; color: #b45309;
    background: #fef3e0; padding: 1px 7px; border-radius: 3px;
    letter-spacing: 0.3px; vertical-align: 1px;
}

.trial-line  { margin-top: 2px; font-size: 8.5px; color: #334155; line-height: 1.5; }
.trial-line b { color: #475569; }

/* mini trial sheets */
.trial-tbl { width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; margin: 2px 0 8px 0; }
.trial-tbl-last { margin-bottom: 2px; }
.tt-head {
    background: #e9eff6; padding: 5px 9px;
    font-size: 7.5px; font-weight: bold; color: #334e68;
    text-transform: uppercase; letter-spacing: 0.8px;
    border-bottom: 1px solid #cbd5e1;
}
.tt-head-status { text-align: right; }
.tt-lbl {
    width: 17%; background: #f8fafc; padding: 5px 9px;
    font-size: 6.5px; font-weight: bold; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.6px;
    border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
}
.tt-val {
    padding: 5px 9px; font-size: 8.5px; color: #334155;
    border-bottom: 1px solid #e2e8f0; line-height: 1.5;
}
.trial-tbl tr.tt-last td { border-bottom: none; }

/* ═══════════ LAST VISIT BOX ═══════════ */
.last-visit-box {
    width: 100%; border-collapse: collapse;
    border: 1px solid #fcd88f; background: #fffdf7;
}
.last-visit-box td { vertical-align: top; }

.lv-head {
    background: #fef3e0; border-bottom: 1px solid #fcd88f;
    padding: 6px 10px; vertical-align: middle;
}
.lv-date {
    font-size: 10.5px; font-weight: bold; color: #92400e;
    letter-spacing: 0.3px;
}
.lv-day {
    font-size: 7.5px; font-weight: bold; color: #b45309;
    text-transform: uppercase; letter-spacing: 0.8px; margin-left: 8px;
}
.lv-ago {
    display: inline-block; margin-left: 8px;
    font-size: 6.5px; font-weight: bold; color: #ffffff; background: #ea9315;
    padding: 1px 7px; border-radius: 3px; letter-spacing: 0.6px; vertical-align: 1px;
}

.lv-lbl {
    width: 22%; padding: 5px 10px;
    font-size: 6.5px; font-weight: bold; color: #b45309;
    text-transform: uppercase; letter-spacing: 0.6px;
    border-bottom: 1px solid #fbe6bf; border-right: 1px solid #fbe6bf;
    background: #fffaf0; white-space: nowrap;
}
.lv-cell {
    padding: 5px 10px; font-size: 8.5px; color: #334155; line-height: 1.55;
    border-bottom: 1px solid #fbe6bf;
}
.last-visit-box tr.lv-last .lv-lbl,
.last-visit-box tr.lv-last .lv-cell { border-bottom: none; }

.lv-met-yes  { color: #16a34a; font-weight: bold; }
.lv-met-no   { color: #dc2626; font-weight: bold; }
.lv-met-name { color: #0f172a; font-weight: bold; }

.lv-lbl-plan { color: #1e293b; background: #fef3e0; }
.lv-plan-val { font-weight: bold; color: #0f172a; }

/* ── Divider between Last Visit and Current Visit ── */
.cv-divider {
    text-align: left;
    background: #f1f5f9;
    color: #1e293b;
    font-size: 8.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 3px;
    padding: 7px 10px;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}

/* ── UPCOMING WEEK section (accent #A5C552, like daily "Today") ── */
.sec-title-up { background: #A5C552; }
.sec-title-up .sec-title-left { color: #0f172a; }
.sec-title-up .sec-title-right { color: #3f4e1c; }
.sec-up-tag {
    font-size: 6.5px; font-weight: bold; color: #ffffff;
    background: #0f172a; padding: 1px 6px; border-radius: 3px;
    letter-spacing: 1px; margin-right: 8px; vertical-align: middle;
}
table.data-table-up thead tr { background-color: #f7fee7; }
table.data-table-up thead th { color: #4d6215; border-color: #e2f3b6; }
table.data-table-up tbody tr:nth-child(even) td { background: #f8fafc; }
.empty-cell-up { border-color: #e2f3b6; color: #4d6215; }
.up-day {
    font-weight: bold; color: #3f4e1c; font-size: 8px;
    text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
}
.up-date { font-size: 7.5px; color: #64748b; white-space: nowrap; }

/* ── Upcoming-week segregation strip (KPI-style, green accent) ── */
.up-strip { width: 100%; border-collapse: separate; border-spacing: 0; margin: 26px 0 0 0; }
.up-strip-box {
    text-align: center; vertical-align: middle;
    padding: 10px 6px; border: 1px solid #cbd5e1; background-color: #f7fee7;
    border-top: 3px solid #A5C552; width: 20%;
}
.up-strip-big { font-size: 18px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #3f4e1c; }
.up-strip-tag { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; display: block; color: #4d6215; }
.up-strip-date-clean {
    text-align: right; vertical-align: middle;
    padding: 10px 0; border: none; background: transparent; width: 60%;
}
.up-strip-date-large { font-size: 14px; font-weight: bold; color: #3f4e1c; display: block; line-height: 1.2; padding-right: 4px; }
.up-strip-date-day   { font-size: 7.5px; font-weight: bold; letter-spacing: 0.8px; display: block; color: #4d6215; padding-right: 4px; }
</style>
</head>
<body>

@php
    $weekTasks = $data['weekTasks'];
    $visitDays = $data['visitDays'];
    $weekNotes = $data['weekNotes'];
    $upcoming  = $data['upcoming'];
    $counts    = $data['counts'];
@endphp

{{-- ── HEADER ── --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Weekly Work Report</div>
            <div class="hdr-date">Week: {{ $weekRange }}</div>
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

{{-- ── WEEKLY KPI BOXES ── --}}
<table class="summary-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td class="s-box s-box-1">
            <span class="s-big">{{ $counts['tasks'] }}</span>
            <span class="s-tag"><br>Scheduled Tasks</span>
        </td>
        <td class="s-box s-box-2">
            <span class="s-big">{{ $counts['visits'] }}</span>
            <span class="s-tag"><br>Customer Visits</span>
        </td>
        <td class="s-box s-box-3">
            <span class="s-big">{{ $counts['work_notes'] }}</span>
            <span class="s-tag"><br>Other Developments</span>
        </td>
        <td class="s-box s-box-4">
            <span class="s-big">{{ $counts['active_days'] }}<span style="font-size:10px; color:#64748b;">/7</span></span>
            <span class="s-tag"><br>Active Days</span>
        </td>
        <td class="s-box-date-clean">
            <span class="s-date-large">{{ $weekRange }}</span>
            <span class="s-date-day"><br>Monday to Sunday</span>
        </td>
    </tr>
</table>

{{-- ═══════════════ 1. SCHEDULED TASKS — WHOLE WEEK (flat list) ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">1. Scheduled Tasks &mdash; {{ $weekRange }}</td>
        <td class="sec-title-right">{{ $counts['tasks'] }} {{ $counts['tasks'] === 1 ? 'Task' : 'Tasks' }}</td>
    </tr>
</table>
@if(count($weekTasks) === 0)
    <div class="empty-cell">No tasks were scheduled during this week.</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="col-num" style="width:30px;">#</th>
            <th style="width:11%;">Date</th>
            <th style="width:8%;">Time</th>
            <th style="width:18%;">Related To</th>
            <th style="width:19%;">Subject</th>
            <th style="width:26%;">Description</th>
            <th style="width:14%;">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($weekTasks as $i => $t)
        <tr>
            <td class="col-num" style="width:30px; color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $t['date'] }}</span><br>
                <span style="font-size:7px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">{{ $t['day'] }}</span>
            </td>
            <td>{{ $t['time'] }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $t['name'] }}</span><br>
                <span style="font-size:7.5px; color:#64748b;">{{ $t['related_to'] }}</span>
            </td>
            <td>{{ $t['subject'] }}</td>
            <td>{{ $t['description'] ?: '—' }}</td>
            <td>
                <table class="status-cell" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="status-dot" style="color: {{ $t['status_color'] }};">&#9679;</td>
                        <td class="status-body">
                            <span class="status-txt" style="color: {{ $t['status_color'] }};">{{ $t['status'] }}</span>
                            @if(!empty($t['rescheduled_to']))
                                <div class="status-sub" style="color:#d97706;">&#8594; {{ $t['rescheduled_to'] }}</div>
                            @endif
                            @if($t['sub_label'])
                                <div class="status-sub">{{ $t['sub_label'] }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- ═══════════════ 2. CUSTOMER VISITS — DAY-WISE ═══════════════ --}}
@if(count($visitDays) === 0)
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">2. Customer Visits &mdash; {{ $weekRange }}</td>
        <td class="sec-title-right">{{ $counts['visits'] }} {{ $counts['visits'] === 1 ? 'Visit' : 'Visits' }}</td>
    </tr>
</table>
    <div class="empty-cell">No customer visits were recorded during this week.</div>
@else
    @foreach($visitDays as $vd)
        {{-- Wrap band + its first content so mPDF won't orphan the band at a page bottom.
             On the FIRST day we also pull the section title into this same wrapper so the
             "2. Customer Visits" header never sits alone at the bottom of a page. --}}
        <div style="page-break-inside: avoid;">
        @if($loop->first)
        <table class="sec-title-table" cellspacing="0" cellpadding="0">
            <tr>
                <td class="sec-title-left">2. Customer Visits &mdash; {{ $weekRange }}</td>
                <td class="sec-title-right">{{ $counts['visits'] }} {{ $counts['visits'] === 1 ? 'Visit' : 'Visits' }}</td>
            </tr>
        </table>
        @endif
        {{-- Day band — shown for every day of the week --}}
        <table class="day-band-table {{ $vd['is_sunday'] ? 'day-band-sunday' : '' }} {{ !$vd['has_visits'] ? 'day-band-empty' : '' }}" cellspacing="0" cellpadding="0">
            <tr>
                <td class="day-band-left">
                    {{ strtoupper($vd['day']) }} &nbsp;<span class="day-band-date">&mdash; {{ $vd['date'] }}</span>
                </td>
                <td class="day-band-right">
                    @if($vd['has_visits'])
                        {{ count($vd['visits']) }} {{ count($vd['visits']) === 1 ? 'Visit' : 'Visits' }}
                    @else
                        No Visits
                    @endif
                </td>
            </tr>
        </table>

        @if(!$vd['has_visits'])
            <div class="no-visit-note">
                <span class="no-visit-icon">&#9679;</span> No customer visits were recorded on this day.
            </div>
        @else
        @foreach($vd['visits'] as $i => $v)
        <table class="visit-card" cellspacing="0" cellpadding="0">

            {{-- Header band --}}
            <tr>
                <td class="vc-head-num">
                    Visit
                    <span class="num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                </td>
                <td class="vc-head">
                    <span class="vc-cust">{{ $v['customer_name'] }}</span>@if(!empty($v['customer_city']))<span class="vc-cust-city"> &nbsp;|&nbsp; {{ $v['customer_city'] }}</span>@endif
                </td>
                <td class="vc-head-meta">
                    <table class="vc-meta-mini" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <span class="vcm-lbl">Check In</span><br>
                                <span class="vcm-val vct-in">{{ $v['check_in'] ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="vcm-lbl">Check Out</span><br>
                                <span class="vcm-val vct-in">{{ $v['check_out'] ?: '—' }}</span>
                            </td>
                            <td class="vcm-last">
                                <span class="vcm-lbl">Duration</span><br>
                                <span class="vcm-val">{!! $v['duration'] ? str_replace(' ', '&nbsp;', e($v['duration'])) : '—' !!}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- Tick/cross status strip --}}
            <tr>
                <td colspan="3" style="padding:0;">
                    <table class="vc-status" cellspacing="0" cellpadding="0">
                        <tr>
                            @foreach($v['statuses'] as $k => $st)
                            <td class="{{ $k === count($v['statuses']) - 1 ? 'last' : '' }}">
                                <span class="vcs-lbl">{{ $st['label'] }}</span>
                                <span class="vcs-val {{ $st['ok'] ? 'vcs-ok' : 'vcs-not' }}">
                                    @if($st['ok'])
                                        <span class="icon-tick">&#10003;</span>
                                    @else
                                        <span class="icon-cross">&#10007;</span>
                                    @endif
                                    {{ $st['value'] }}
                                </span>
                            </td>
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- Detail rows --}}
            <tr>
                <td colspan="3" style="padding:0;">
                    <table class="vc-detail" cellspacing="0" cellpadding="0">

                        {{-- ── LAST VISIT ── --}}
                        @if(!empty($v['last_visit']))
                            @php $lv = $v['last_visit']; @endphp
                            <tr>
                                <td class="vcd-lbl vcd-lbl-last">Last Visit</td>
                                <td class="vcd-val" style="padding:8px 10px;">
                                    <table class="last-visit-box" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td class="lv-head" colspan="2">
                                                <span class="lv-date">{{ $lv['date'] }}</span>
                                                @if($lv['day'])<span class="lv-day">{{ strtoupper($lv['day']) }}</span>@endif
                                                @if(!is_null($lv['days_ago']))
                                                    <span class="lv-ago">{{ $lv['days_ago'] }} {{ $lv['days_ago'] == 1 ? 'DAY' : 'DAYS' }} AGO</span>
                                                @endif
                                            </td>
                                        </tr>

                                        @if(count($lv['purposes']) > 0)
                                        <tr>
                                            <td class="lv-lbl">Purpose</td>
                                            <td class="lv-cell">
                                                <span class="inline-list">@foreach($lv['purposes'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach</span>
                                            </td>
                                        </tr>
                                        @endif

                                        <tr>
                                            <td class="lv-lbl">Met</td>
                                            <td class="lv-cell">
                                                @if($lv['met'])
                                                    <span class="lv-met-yes">&#10003; Yes</span>@if($lv['met_name'])<span class="lv-met-name"> &mdash; {{ $lv['met_name'] }}</span>@endif
                                                @else
                                                    <span class="lv-met-no">&#10007; Not Met</span>
                                                @endif
                                            </td>
                                        </tr>

                                        @if($lv['summary'])
                                        <tr>
                                            <td class="lv-lbl">Summary</td>
                                            <td class="lv-cell">{{ $lv['summary'] }}</td>
                                        </tr>
                                        @endif

                                        @if($lv['next_plan'])
                                        <tr class="lv-last">
                                            <td class="lv-lbl lv-lbl-plan">Then Planned</td>
                                            <td class="lv-cell lv-plan-val">{{ $lv['next_plan'] }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="cv-divider">Current Visit</td>
                            </tr>
                        @endif

                        @if(count($v['purposes']) > 0)
                        <tr>
                            <td class="vcd-lbl">Purpose of Visit</td>
                            <td class="vcd-val inline-list">
                                @foreach($v['purposes'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach
                            </td>
                        </tr>
                        @endif
                        @if(!empty($v['other_purpose']))
                        <tr>
                            <td class="vcd-lbl">Other Purpose</td>
                            <td class="vcd-val">{{ $v['other_purpose'] }}</td>
                        </tr>
                        @endif
                        @if(count($v['contacts']) > 0)
                        <tr>
                            <td class="vcd-lbl">Met With</td>
                            <td class="vcd-val">
                                @foreach($v['contacts'] as $c)
                                    <div>
                                        <span class="contact-name">{{ $c['name'] }}</span>
                                        @if(!empty($c['designation']))
                                            <span class="contact-sub"> &mdash; {{ $c['designation'] }}</span>
                                        @endif
                                        @if(!empty($c['mobile']))
                                            <span class="contact-sub">&nbsp;&nbsp;&#9742; {{ $c['mobile'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                        @endif

                        @if(count($v['products']) > 0)
                        <tr>
                            <td class="vcd-lbl">Products Discussed</td>
                            <td class="vcd-val inline-list">
                                @foreach($v['products'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach
                            </td>
                        </tr>
                        @endif

                        @if(count($v['trials']) > 0)
                        <tr>
                            <td class="vcd-lbl">Trials</td>
                            <td class="vcd-val">
                            @if(count($v['trials']) === 1)
                                @php $tr = $v['trials'][0]; @endphp
                                <span class="status-txt" style="color: {{ $tr['status_color'] }};">&#9679; {{ $tr['status'] }}</span>
                                @if($tr['type'])
                                    &nbsp;&nbsp;<span class="mode-txt">{{ $tr['type'] }}</span>
                                @endif
                                @if($tr['objective'])
                                    <div class="trial-line"><b>Objective:</b> {{ $tr['objective'] }}</div>
                                @endif
                                @if(count($tr['products']) > 0)
                                    <div class="trial-line"><b>Products:</b>
                                        <span class="inline-list">@foreach($tr['products'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach</span>
                                    </div>
                                @endif
                                @if($tr['jointly'] && $tr['team_member'])
                                    <div class="trial-line"><b>Jointly with:</b> {{ $tr['team_member'] }}</div>
                                @endif
                                @if($tr['remarks'])
                                    <div class="trial-line" style="color:#64748b;">{{ $tr['remarks'] }}</div>
                                @endif
                            @else
                                @foreach($v['trials'] as $ti => $tr)
                                <table class="trial-tbl {{ $loop->last ? 'trial-tbl-last' : '' }}" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="tt-head">
                                            Trial {{ str_pad($ti + 1, 2, '0', STR_PAD_LEFT) }}
                                            @if($tr['type'])
                                                &nbsp;&nbsp;<span style="color:#64748b; letter-spacing:0.5px;">{{ $tr['type'] }}</span>
                                            @endif
                                        </td>
                                        <td class="tt-head tt-head-status">
                                            <span class="status-txt" style="color: {{ $tr['status_color'] }};">&#9679; {{ $tr['status'] }}</span>
                                        </td>
                                    </tr>
                                    @if($tr['objective'])
                                    <tr>
                                        <td class="tt-lbl">Objective</td>
                                        <td class="tt-val">{{ $tr['objective'] }}</td>
                                    </tr>
                                    @endif
                                    @if(count($tr['products']) > 0)
                                    <tr>
                                        <td class="tt-lbl">Products</td>
                                        <td class="tt-val inline-list">@foreach($tr['products'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach</td>
                                    </tr>
                                    @endif
                                    @if($tr['jointly'] && $tr['team_member'])
                                    <tr>
                                        <td class="tt-lbl">Jointly With</td>
                                        <td class="tt-val">{{ $tr['team_member'] }}</td>
                                    </tr>
                                    @endif
                                    @if($tr['remarks'])
                                    <tr class="tt-last">
                                        <td class="tt-lbl">Remarks</td>
                                        <td class="tt-val" style="color:#64748b;">{{ $tr['remarks'] }}</td>
                                    </tr>
                                    @endif
                                </table>
                                @endforeach
                            @endif
                            </td>
                        </tr>
                        @endif

                        @if(!empty($v['visit_detail']))
                        <tr>
                            <td class="vcd-lbl">Visit Details</td>
                            <td class="vcd-val">{{ $v['visit_detail'] }}</td>
                        </tr>
                        @endif
                        @if(!empty($v['remarks']))
                        <tr>
                            <td class="vcd-lbl">Remarks</td>
                            <td class="vcd-val">{{ $v['remarks'] }}</td>
                        </tr>
                        @endif

                        @if(!empty($v['next_plan']) || !empty($v['next_action']))
                        <tr class="vc-last">
                            <td class="vcd-lbl" style="color:#1e293b;">Next Plan</td>
                            <td class="vcd-val next-plan-val">
                                @if(!empty($v['next_plan'])){{ $v['next_plan'] }}@endif
                                @if(!empty($v['next_action']))
                                    <span class="next-action-tag">Follow-up: {{ $v['next_action'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @endif

                    </table>
                </td>
            </tr>
        </table>
        @endforeach
        @endif
        </div>
    @endforeach
@endif

{{-- ═══════════════ 3. OTHER DEVELOPMENTS — WHOLE WEEK (flat list) ═══════════════ --}}
@if(count($weekNotes) === 0)
<div style="page-break-inside: avoid;">
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">3. Other Developments &mdash; {{ $weekRange }}</td>
        <td class="sec-title-right">{{ $counts['work_notes'] }} {{ $counts['work_notes'] === 1 ? 'Record' : 'Records' }}</td>
    </tr>
</table>
    <div class="empty-cell">No other developments were recorded during this week.</div>
</div>
@else
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">3. Other Developments &mdash; {{ $weekRange }}</td>
        <td class="sec-title-right">{{ $counts['work_notes'] }} {{ $counts['work_notes'] === 1 ? 'Record' : 'Records' }}</td>
    </tr>
</table>
<table class="data-table">
    <thead>
        <tr>
            <th class="col-num" style="width:30px;">#</th>
            <th style="width:11%;">Date</th>
            <th style="width:15%;">Related To</th>
            <th style="width:15%;">Subject</th>
            <th class="center" style="width:10%;">Mode</th>
            <th style="width:22%;">Description</th>
            <th style="width:23%;">Key Takeaway / Action</th>
        </tr>
    </thead>
    <tbody>
    @foreach($weekNotes as $i => $n)
        <tr>
            <td class="col-num" style="width:30px; color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $n['date'] }}</span><br>
                <span style="font-size:7px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">{{ $n['day'] }}</span>
            </td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $n['name'] }}</span><br>
                <span style="font-size:7.5px; color:#64748b;">{{ $n['related_to'] }}</span>
            </td>
            <td>{{ $n['subject'] }}</td>
            <td class="center">
                @if($n['activity_mode'])
                    <span class="mode-txt">{{ $n['activity_mode'] }}</span>
                @else
                    —
                @endif
            </td>
            <td>{{ $n['description'] ?: '—' }}</td>
            <td>
                {{ $n['key_take_away'] ?: '—' }}
                @if($n['action'])
                    <div style="margin-top:3px; font-size:7.5px; color:#b45309; font-weight:bold;">
                        &#9888; Action: {{ $n['action'] }}
                    </div>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- ═══════════════ 4. THIS WEEK'S UPCOMING TASKS ═══════════════ --}}

{{-- Segregation strip — sets the upcoming week apart with its own date range + count --}}
<table class="up-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td class="up-strip-box">
            <span class="up-strip-big">{{ count($upcoming) }}</span>
            <span class="up-strip-tag"><br>Upcoming Tasks</span>
        </td>
        <td class="up-strip-date-clean">
            <span class="up-strip-date-large">{{ $upWeekRange }}</span>
            <span class="up-strip-date-day"><br>Next Week &mdash; Monday to Sunday</span>
        </td>
    </tr>
</table>
@if(count($upcoming) === 0)
<div style="page-break-inside: avoid;">
<table class="sec-title-table sec-title-up" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left"><span class="sec-up-tag">&#9654; UPCOMING</span> 4. Scheduled Tasks &mdash; This Week</td>
        <td class="sec-title-right">{{ count($upcoming) }} {{ count($upcoming) === 1 ? 'Task' : 'Tasks' }}</td>
    </tr>
</table>
    <div class="empty-cell empty-cell-up">No tasks are scheduled for this week.</div>
</div>
@else
<table class="sec-title-table sec-title-up" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left"><span class="sec-up-tag">&#9654; UPCOMING</span> 4. Scheduled Tasks &mdash; This Week</td>
        <td class="sec-title-right">{{ count($upcoming) }} {{ count($upcoming) === 1 ? 'Task' : 'Tasks' }}</td>
    </tr>
</table>
<table class="data-table data-table-up">
    <thead>
        <tr>
            <th class="col-num" style="width:30px;">#</th>
            <th style="width:11%;">Day</th>
            <th style="width:8%;">Time</th>
            <th style="width:18%;">Related To</th>
            <th style="width:19%;">Subject</th>
            <th style="width:26%;">Description</th>
            <th style="width:14%;">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($upcoming as $i => $t)
        <tr>
            <td class="col-num" style="width:30px; color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>
                <span class="up-day">{{ $t['day'] }}</span><br>
                <span class="up-date">{{ $t['date'] }}</span>
            </td>
            <td>{{ $t['time'] }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $t['name'] }}</span><br>
                <span style="font-size:7.5px; color:#64748b;">{{ $t['related_to'] }}</span>
            </td>
            <td>{{ $t['subject'] }}</td>
            <td>{{ $t['description'] ?: '—' }}</td>
            <td>
                <table class="status-cell" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="status-dot" style="color: {{ $t['status_color'] }};">&#9679;</td>
                        <td class="status-body">
                            <span class="status-txt" style="color: {{ $t['status_color'] }};">{{ $t['status'] }}</span>
                            @if(!empty($t['rescheduled_to']))
                                <div class="status-sub" style="color:#d97706;">&#8594; {{ $t['rescheduled_to'] }}</div>
                            @endif
                            @if($t['sub_label'])
                                <div class="status-sub">{{ $t['sub_label'] }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

</body>
</html>