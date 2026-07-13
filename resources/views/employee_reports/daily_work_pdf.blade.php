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

/* ── HEADER (same as product-pricing) ── */
.hdr-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.hdr-left  { vertical-align: top; text-align: left; }
.hdr-right { vertical-align: top; text-align: right; }
.logo-img  { width: 150px; height: auto; margin-bottom: 10px; }

/* double rule under header — engraved corporate look */
.hdr-rule-1 { border-top: 2px solid #1e293b; margin-top: 6px; }
.hdr-rule-2 { border-top: 1px solid #cbd5e1; margin-top: 2px; margin-bottom: 14px; }

.hdr-doc-type {
    font-size: 13px; font-weight: bold; color: #334155;
    text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;
}
.hdr-date { font-size: 8px; color: #64748b; }

/* ── EMPLOYEE TAGS (ftag = product-pricing chip) ── */
.filter-row { margin-bottom: 16px; }
.ftag-lbl {
    font-size: 7.5px; color: #64748b; font-weight: bold; margin-right: 4px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.ftag {
    display: inline-block; background: #f1f5f9; color: #334155;
    border: 1px solid #cbd5e1; border-radius: 3px;
    padding: 2px 7px; font-size: 7.5px; font-weight: bold;
    margin-right: 4px; text-transform: uppercase; letter-spacing: 0.3px;
}

/* ── KPI STRIP ── */
.summary-strip { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 22px; }
.s-box {
    text-align: center; vertical-align: middle;
    padding: 10px 6px; border: 1px solid #cbd5e1; background-color: #f8fafc;
}
.s-box + .s-box { border-left: none; }
.s-box-1 { border-top: 3px solid #1e293b; width: 16%; }
.s-box-2 { border-top: 3px solid #475569; width: 16%; }
.s-box-3 { border-top: 3px solid #64748b; width: 16%; }
.s-box-4 { border-top: 3px solid #94a3b8; width: 16%; }
.s-box-spacer { width: 36%; border: none; background: transparent; }

.s-big { font-size: 18px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #0f172a; }
.s-tag { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; display: block; color: #475569; }

/* ── SECTION TITLES ── */
.sec-title {
    background: #1e293b; color: #ffffff;
    font-size: 9px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px;
    padding: 7px 10px; margin: 20px 0 0 0;
}
.sec-sub { font-size: 7.5px; color: #cbd5e1; font-weight: normal; letter-spacing: 0.3px; }

/* ── DATA TABLES (identical to product-pricing) ── */
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
}
table.data-table tbody tr:nth-child(even) td { background: #f8fafc; }
.center { text-align: center; }

.empty-cell {
    text-align: center; padding: 18px; color: #64748b; font-style: italic;
    border: 1px solid #e2e8f0; border-top: none; background: #ffffff; font-size: 8.5px;
}

/* ── STATUS (typography-based — mPDF renders boxes badly) ── */
.status-txt {
    font-size: 8px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.6px;
    white-space: nowrap;
}
.status-sub {
    font-size: 6.5px; font-weight: bold; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 1px;
    white-space: nowrap;
}
.mode-txt {
    font-size: 7.5px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.8px;
    white-space: nowrap;
}
.inline-list { font-weight: bold; color: #334155; }
.inline-list .sep { color: #cbd5e1; font-weight: normal; }

/* ═══════════════ DVR VISIT SHEET (corporate card) ═══════════════ */
.visit-card {
    width: 100%; border-collapse: collapse;
    border: 1px solid #cbd5e1;
    margin-top: 12px;
    page-break-inside: avoid;
}

/* header band — same tone as data-table thead */
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
.vc-cust-type {
    font-size: 7px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.6px; margin-top: 2px;
}
/* compact times at the right of the visit header band */
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

/* section title with right-aligned count */
.sec-title-table { width: 100%; border-collapse: collapse; margin: 20px 0 0 0; background: #1e293b; }
.sec-title-left {
    color: #ffffff; font-size: 9px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px; padding: 7px 10px;
}
.sec-title-right {
    color: #cbd5e1; font-size: 8px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px; padding: 7px 10px; text-align: right;
}

/* ── TODAY section — blue theme so upcoming stands apart from yesterday ── */
.sec-title-today { background: #1d4ed8; }
.sec-title-today .sec-title-right { color: #bfdbfe; }
.sec-today-tag {
    font-size: 6.5px; font-weight: bold; color: #1d4ed8;
    background: #ffffff; padding: 1px 6px; border-radius: 3px;
    letter-spacing: 1px; margin-right: 8px; vertical-align: middle;
}
table.data-table-today thead tr { background-color: #eff6ff; }
table.data-table-today thead th { color: #1e40af; border-color: #bfdbfe; }
table.data-table-today tbody tr:nth-child(even) td { background: #f8fafc; }
.empty-cell-today { border-color: #bfdbfe; color: #1e40af; }

/* tick/cross status strip — product-pricing style */
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

/* check in / out / duration strip */
.vc-time { width: 100%; border-collapse: collapse; }
.vc-time td {
    width: 33.33%; padding: 9px 12px;
    border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}
.vc-time td.last { border-right: none; }
.vct-lbl {
    display: block; font-size: 6.5px; font-weight: bold; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px;
}
.vct-val   { font-size: 10.5px; font-weight: bold; color: #0f172a; }
.vct-in    { color: #16a34a; }
.vct-out   { color: #dc2626; }

/* detail rows — label column + value column, invoice style */
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

.contact-name { font-weight: bold; color: #0f172a; font-size: 9px; }
.contact-sub  { font-size: 7.5px; color: #64748b; }

.next-plan-val { font-weight: bold; color: #0f172a; }

/* trials inside the visit sheet */
.trial-line  { margin-top: 2px; font-size: 8.5px; color: #334155; line-height: 1.5; }
.trial-line b { color: #475569; }

/* multiple trials → mini trial sheets */
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

/* ── FOOTER ── */
.footer-table {
    width: 100%; border-collapse: collapse; margin-top: 28px;
    border-top: 1px solid #cbd5e1; padding-top: 6px;
}
.footer-left  { font-size: 7.5px; font-weight: bold; color: #334155; }
.footer-mid   { font-size: 7px; color: #64748b; text-align: center; }
.footer-right { font-size: 7px; color: #64748b; text-align: right; }
</style>
</head>
<body>

@php
    $yTasks = $data['yesterdayTasks'];
    $visits = $data['visits'];
    $notes  = $data['workNotes'];
    $tTasks = $data['todayTasks'];
@endphp

{{-- ── HEADER ── --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Daily Work Report</div>
            <div class="hdr-date">Report Date: {{ $reportDate }} ({{ $reportDay }})</div>
            <div class="hdr-date">Generated on: {{ $generatedAt }}</div>
        </td>
    </tr>
</table>
<div class="hdr-rule-1"></div>
<div class="hdr-rule-2"></div>

{{-- ── EMPLOYEE TAGS ── --}}
<div class="filter-row">
    <span class="ftag-lbl">Employee:</span>
    <span class="ftag">{{ $employee->name }}</span>
    @if(!empty($employee->designation))
        <span class="ftag">{{ $employee->designation }}</span>
    @endif
    @if(!empty($employee->base_city))
        <span class="ftag">{{ $employee->base_city }}</span>
    @endif
</div>

{{-- ── KPI BOXES ── --}}
<table class="summary-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td class="s-box s-box-1">
            <span class="s-big">{{ count($yTasks) }}</span>
            <span class="s-tag"><br>Scheduled Tasks</span>
        </td>
        <td class="s-box s-box-2">
            <span class="s-big">{{ count($visits) }}</span>
            <span class="s-tag"><br>Customer Visits</span>
        </td>
        <td class="s-box s-box-3">
            <span class="s-big">{{ count($notes) }}</span>
            <span class="s-tag"><br>Other Developments</span>
        </td>
        <!-- <td class="s-box s-box-4">
            <span class="s-big" style="color:#1d4ed8;">{{ count($tTasks) }}</span>
            <span class="s-tag"><br>Today's Tasks</span>
        </td> -->
        <td class="s-box-spacer"></td>
    </tr>
</table>

{{-- ═══════════════ 1. YESTERDAY'S SCHEDULED TASKS ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">1. Scheduled Tasks &mdash; {{ $reportDate }} (Yesterday)</td>
        <td class="sec-title-right">{{ count($yTasks) }} {{ count($yTasks) === 1 ? 'Task' : 'Tasks' }}</td>
    </tr>
</table>
@if(count($yTasks) === 0)
    <div class="empty-cell">No tasks were scheduled for this day.</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="center" style="width:22px;">#</th>
            <th style="width:9%;">Time</th>
            <th style="width:20%;">Related To</th>
            <th style="width:22%;">Subject</th>
            <th style="width:34%;">Description</th>
            <th style="width:15%;">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($yTasks as $i => $t)
        <tr>
            <td class="center" style="color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>{{ $t['time'] }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $t['name'] }}</span><br>
                <span style="font-size:7.5px; color:#64748b;">{{ $t['related_to'] }}</span>
            </td>
            <td>{{ $t['subject'] }}</td>
            <td>{{ $t['description'] ?: '—' }}</td>
            <td>
                <span class="status-txt" style="color: {{ $t['status_color'] }};">&#9679; {{ $t['status'] }}</span>
                @if(!empty($t['rescheduled_to']))
                    <br><span class="status-sub" style="color:#d97706;">&#8594; {{ $t['rescheduled_to'] }}</span>
                @endif
                @if($t['sub_label'])
                    <br><span class="status-sub">{{ $t['sub_label'] }}</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- ═══════════════ 2. CUSTOMER VISITS — VISIT SHEETS ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">2. Customer Visits</td>
        <td class="sec-title-right">{{ count($visits) }} {{ count($visits) === 1 ? 'Visit' : 'Visits' }}</td>
    </tr>
</table>
@if(count($visits) === 0)
    <div class="empty-cell">No customer visits were recorded on this day.</div>
@else
    @foreach($visits as $i => $v)
    <table class="visit-card" cellspacing="0" cellpadding="0">

        {{-- Header band --}}
        <tr>
            <td class="vc-head-num">
                Visit
                <span class="num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
            </td>
            <td class="vc-head">
                <div class="vc-cust">{{ $v['customer_name'] }}</div>
                <div class="vc-cust-type">{{ $v['customer_type'] }}</div>
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
                            <span class="vcm-val vct-out">{{ $v['check_out'] ?: '—' }}</span>
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

        {{-- Detail rows: label + value --}}
        <tr>
            <td colspan="3" style="padding:0;">
                <table class="vc-detail" cellspacing="0" cellpadding="0">

                    @if(count($v['purposes']) > 0)
                    <tr>
                        <td class="vcd-lbl">Purpose of Visit</td>
                        <td class="vcd-val inline-list">
                            @foreach($v['purposes'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach
                        </td>
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
                            {{-- single trial → clean inline block, no table --}}
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
                            {{-- multiple trials → one mini trial sheet each --}}
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

                    @if(!empty($v['other_purpose']))
                    <tr>
                        <td class="vcd-lbl">Other Purpose</td>
                        <td class="vcd-val">{{ $v['other_purpose'] }}</td>
                    </tr>
                    @endif

                    @if(!empty($v['remarks']))
                    <tr>
                        <td class="vcd-lbl">Remarks</td>
                        <td class="vcd-val">{{ $v['remarks'] }}</td>
                    </tr>
                    @endif

                    @if(!empty($v['next_plan']))
                    <tr class="vc-last">
                        <td class="vcd-lbl" style="color:#1e293b;">Next Plan</td>
                        <td class="vcd-val next-plan-val">{{ $v['next_plan'] }}</td>
                    </tr>
                    @endif

                </table>
            </td>
        </tr>
    </table>
    @endforeach
@endif

{{-- ═══════════════ 3. OTHER DEVELOPMENTS (WORK NOTES) ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">3. Other Developments</td>
        <td class="sec-title-right">{{ count($notes) }} {{ count($notes) === 1 ? 'Record' : 'Records' }}</td>
    </tr>
</table>
@if(count($notes) === 0)
    <div class="empty-cell">No other developments were recorded on this day.</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="center" style="width:22px;">#</th>
            <th style="width:17%;">Related To</th>
            <th style="width:16%;">Subject</th>
            <th class="center" style="width:12%;">Mode</th>
            <th style="width:26%;">Description</th>
            <th style="width:29%;">Key Takeaway / Action</th>
        </tr>
    </thead>
    <tbody>
    @foreach($notes as $i => $n)
        <tr>
            <td class="center" style="color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
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

{{-- ═══════════════ 4. TODAY'S UPCOMING TASKS ═══════════════ --}}
<table class="sec-title-table sec-title-today" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left"><span class="sec-today-tag">&#9654; UPCOMING</span> 4. Scheduled Tasks &mdash; {{ $todayDate }} (Today)</td>
        <td class="sec-title-right">{{ count($tTasks) }} {{ count($tTasks) === 1 ? 'Task' : 'Tasks' }}</td>
    </tr>
</table>
@if(count($tTasks) === 0)
    <div class="empty-cell empty-cell-today">No tasks are scheduled for today.</div>
@else
<table class="data-table data-table-today">
    <thead>
        <tr>
            <th class="center" style="width:22px;">#</th>
            <th style="width:9%;">Time</th>
            <th style="width:20%;">Related To</th>
            <th style="width:22%;">Subject</th>
            <th style="width:34%;">Description</th>
            <th style="width:15%;">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($tTasks as $i => $t)
        <tr>
            <td class="center" style="color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>{{ $t['time'] }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $t['name'] }}</span><br>
                <span style="font-size:7.5px; color:#64748b;">{{ $t['related_to'] }}</span>
            </td>
            <td>{{ $t['subject'] }}</td>
            <td>{{ $t['description'] ?: '—' }}</td>
            <td>
                <span class="status-txt" style="color: {{ $t['status_color'] }};">&#9679; {{ $t['status'] }}</span>
                @if(!empty($t['rescheduled_to']))
                    <br><span class="status-sub" style="color:#d97706;">&#8594; {{ $t['rescheduled_to'] }}</span>
                @endif
                @if($t['sub_label'])
                    <br><span class="status-sub">{{ $t['sub_label'] }}</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

</body>
</html>