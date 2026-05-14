<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>DVR — {{ $selectedUser->name ?? '' }} — {{ $monthName }} {{ $currentYear }}</title>
<style>
/*
 * barryvdh/laravel-dompdf ^0.8.7 compatible
 * Key rules for this version:
 *   - page-break-inside: avoid on <table> tbody WORKS
 *   - Keep each card as ONE <table> with page-break-inside:avoid on the table itself
 *   - No div layout, no flexbox, no CSS vars
 *   - Outer wrapper: <table> with padding cell (not @page margins)
 *   - font-size 12px minimum
 */

* { margin:0; padding:0; }

@page {
    size: A4 portrait;
    margin: 15mm 14mm 18mm 14mm;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color: #1a202c;
    line-height: 1.5;
}

/* ── Header ───────────────────── */
.hdr { width:100%; border-collapse:collapse; background:#0f172a; margin-bottom:12px; }
.hdr td { padding:14px 18px; vertical-align:middle; }
.h-name { font-size:20px; font-weight:bold; color:#fff; }
.h-sub  { font-size:11px; color:#60a5fa; }
.h-meta { font-size:9px; color:#64748b; margin-top:5px; }
.h-num  { font-size:38px; font-weight:bold; color:#fff; line-height:1; text-align:right; }
.h-lbl  { font-size:9px; color:#475569; text-transform:uppercase; letter-spacing:.6px; text-align:right; }

/* ── Stat boxes ───────────────── */
.stats { width:100%; border-collapse:separate; border-spacing:5px; margin-bottom:12px; }
.sb  { text-align:center; padding:9px 4px; border:1px solid #e2e8f0; background:#f8fafc; }
.sbg { text-align:center; padding:9px 4px; border:1px solid #86efac; background:#f0fdf4; }
.sbr { text-align:center; padding:9px 4px; border:1px solid #fca5a5; background:#fff5f5; }
.sbn  { font-size:22px; font-weight:bold; color:#0f172a; }
.sbng { font-size:22px; font-weight:bold; color:#16a34a; }
.sbnr { font-size:22px; font-weight:bold; color:#dc2626; }
.sbl  { font-size:8px; font-weight:bold; text-transform:uppercase; color:#64748b; letter-spacing:.5px; padding-top:4px; }

/* ── Section title ─────────────── */
.sec {
    font-size:9px; font-weight:bold; text-transform:uppercase;
    letter-spacing:.7px; color:#334155;
    background:#f1f5f9; border-left:4px solid #3b82f6;
    padding:5px 0 5px 10px; margin-bottom:8px;
    width:100%; display:block;
}

/* ── Customer summary ─────────── */
.csum { width:100%; border-collapse:collapse; margin-bottom:14px; }
.csum thead th {
    background:#1e293b; color:#94a3b8;
    font-size:9px; font-weight:bold; text-transform:uppercase;
    padding:6px 8px; text-align:left;
}
.csum tbody td { padding:7px 8px; border-bottom:1px solid #e2e8f0; font-size:11px; vertical-align:middle; }
.csum tbody tr:nth-child(even) td { background:#f8fafc; }
.csum tbody tr:last-child td { border-bottom:none; }
.cn { font-weight:bold; font-size:12px; color:#0f172a; }
.bw { width:65px; height:7px; background:#e2e8f0; vertical-align:middle; }
.bg { height:7px; background:#22c55e; display:block; }
.bb { height:7px; background:#3b82f6; display:block; }
.pg { font-size:10px; font-weight:bold; color:#16a34a; padding-left:5px; white-space:nowrap; }
.pb { font-size:10px; font-weight:bold; color:#2563eb; padding-left:5px; white-space:nowrap; }

/* ── Date group header ─────────── */
.dgh { width:100%; border-collapse:collapse; background:#1e293b; margin-top:14px; margin-bottom:5px; }
.dgh td { padding:9px 13px; vertical-align:middle; }
.dgh-dt  { font-size:15px; font-weight:bold; color:#fff; }
.dgh-day { font-size:9px; font-weight:bold; color:#94a3b8; background:#334155; padding:2px 8px; margin-left:7px; }
.dgh-cnt { font-size:9px; font-weight:bold; color:#fff; background:#3b82f6; padding:2px 8px; margin-left:5px; }
.dgh-att { font-size:10px; color:#94a3b8; text-align:right; }
.dgh-att strong { color:#e2e8f0; }
.dp { font-size:9px; font-weight:bold; color:#fff; padding:2px 9px; margin-left:5px; }
.dp-p { background:#16a34a; }
.dp-a { background:#dc2626; }
.dp-n { background:#64748b; }

/* ── DVR CARD ─────────────────────────────────────────────
   page-break-inside:avoid on <table> works in dompdf 0.8.x
   Each card is a single self-contained table.
   The trick: set it on the TABLE element directly.
─────────────────────────────────────────────────────────── */
.card {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #d1d5db;
    margin-bottom: 7px;
    background: #ffffff;
}
/* Keep header row attached to next row — prevents orphan date+header */
.card-head { page-break-inside: avoid; page-break-after: avoid; }
.card-status { page-break-inside: avoid; page-break-before: avoid; }
/* Prevent ONLY the header+status rows from orphaning — allows rest to flow */
.card-head { page-break-inside: avoid; page-break-after: avoid; }
.card-status { page-break-inside: avoid; page-break-before: avoid; }
.card-alt { background: #f9fafb; }
/* Prevent orphan header — keep card-head attached to card-status */
.card-head { page-break-inside: avoid; page-break-after: avoid; }
.card-status { page-break-inside: avoid; page-break-before: avoid; }

/* Row: top bar with S.No + Customer */
.card-head td {
    padding: 10px 14px;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
    background: #f3f4f6;
}
.card-sno {
    width: 44px; text-align:center; border-right:1px solid #e5e7eb;
}
.sno-circle {
    font-size:12px; font-weight:bold; color:#fff;
    background:#1e293b; padding:5px 9px;
    display:inline-block;
}
.c-name { font-size:14px; font-weight:bold; color:#0f172a; }
.c-type { font-size:10px; color:#64748b; margin-top:2px; }
.c-chip {
    font-size:10px; font-weight:bold; color:#1d4ed8;
    background:#dbeafe; padding:2px 8px;
    margin-right:4px; margin-top:4px; display:inline-block;
}

/* Row: status pills — 3 per row, white-space:nowrap prevents splits */
.card-status td {
    padding: 6px 14px;
    border-bottom: 1px solid #f1f5f9;
}
.pill-wrap { border-collapse:collapse; width:100%; }
.pill-wrap td { padding:2px 4px 2px 0; vertical-align:top; white-space:nowrap; }
.sg { font-size:10px; font-weight:bold; color:#fff; background:#16a34a; padding:4px 10px; white-space:nowrap; line-height:14px; display:block; }
.sr { font-size:10px; font-weight:bold; color:#fff; background:#dc2626; padding:4px 10px; white-space:nowrap; line-height:14px; display:block; }

/* Row: timing — fixed widths prevent wrapping */
.card-time td {
    padding: 8px 14px;
    border-bottom: 1px solid #f1f5f9;
}
.time-wrap { border-collapse:collapse; width:100%; }
.time-wrap td { padding:0; vertical-align:middle; width:33%; }
.t-lbl { font-size:9px; font-weight:bold; text-transform:uppercase; color:#94a3b8; letter-spacing:.5px; white-space:nowrap; }
.t-in  { font-size:13px; font-weight:bold; color:#16a34a; white-space:nowrap; }
.t-out { font-size:13px; font-weight:bold; color:#dc2626; white-space:nowrap; }
.t-dur { font-size:13px; font-weight:bold; color:#475569; white-space:nowrap; }

/* Row: data rows (Met With, Visit Details, etc.) */
.card-row td {
    padding: 8px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.card-row-last td { border-bottom: none; }
.d-lbl {
    font-size:9px; font-weight:bold; text-transform:uppercase;
    color:#94a3b8; letter-spacing:.5px; margin-bottom:5px;
}
.d-val  { font-size:12px; color:#1a202c; line-height:1.55; word-wrap:break-word; overflow-wrap:break-word; }
.d-name { font-size:12px; font-weight:bold; color:#0f172a; }
.d-sub  { font-size:10px; color:#64748b; }
.d-mob  { font-size:11px; color:#475569; }
.d-sep  { height:1px; background:#f1f5f9; margin:5px 0; }
.prod-p { font-size:10px; font-weight:bold; color:#6d28d9; background:#ede9fe; padding:3px 9px; margin-right:4px; display:inline-block; }
.t-num  { font-size:15px; font-weight:bold; color:#fff; background:#7c3aed; padding:3px 10px; display:inline-block; }
.t-pill { font-size:9px; font-weight:bold; color:#fff; padding:3px 8px; margin-left:5px; display:inline-block; }
.sch-box { width:100%; border-collapse:collapse; background:#f0fdf4; border:1px solid #86efac; }
.sch-box td { padding:7px 10px; }
.sch-d { font-size:12px; font-weight:bold; color:#15803d; }
.sch-t { font-size:11px; color:#166534; margin-top:3px; }
.em { color:#94a3b8; }

/* ── Footer ───────────────────── */
.ftr { width:100%; border-collapse:collapse; margin-top:16px; border-top:1px solid #e2e8f0; }
.ftr td { padding-top:8px; font-size:9px; color:#94a3b8; text-align:center; }
</style>
</head>
<body>

{{-- ══════════════ HEADER ══════════════ --}}
<table class="hdr"><tr>
    <td style="width:65%;">
        <div class="h-name">{{ $selectedUser->name ?? 'N/A' }}</div>
        <div class="h-sub">Daily Visit Report</div>
        <div class="h-meta">{{ $monthName }} {{ $currentYear }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}</div>
    </td>
    <td style="width:35%; vertical-align:middle;">
        <div class="h-num">{{ $summaryStats['total'] }}</div>
        <div class="h-lbl">Total DVRs</div>
    </td>
</tr></table>

{{-- ══════════════ STAT BOXES ══════════════ --}}
<table class="stats"><tr>
    <td class="sb"><div class="sbn">{{ $summaryStats['total'] }}</div><div class="sbl">Total DVRs</div></td>
    <td class="sbg"><div class="sbng">{{ $summaryStats['verified'] }}</div><div class="sbl">Verified</div></td>
    <td class="sbr"><div class="sbnr">{{ $summaryStats['pending'] }}</div><div class="sbl">Pending</div></td>
    <td class="sb"><div class="sbn">{{ $summaryStats['metCount'] }}</div><div class="sbl">Meetings</div></td>
    <td class="sb"><div class="sbn">{{ $summaryStats['trialsSum'] }}</div><div class="sbl">Trials</div></td>
    <td class="sbg"><div class="sbng">{{ $summaryStats['presentDays'] }}</div><div class="sbl">Present Days</div></td>
</tr></table>

{{-- ══════════════ CUSTOMER SUMMARY ══════════════ --}}
@if($customerStats->count())
@php
$_csvTitle = 'Customer Visit Summary';
if (!empty($filterLabel)) { $_csvTitle .= ' — ' . $filterLabel; }
@endphp
<div class="sec">{{ $_csvTitle }}</div>
<table class="csum">
    <thead><tr>
        <th style="width:24px; text-align:center;">#</th>
        <th>Customer</th>
        <th style="width:60px; text-align:center;">Visits</th>
        <th style="width:120px;">Visit %</th>
        <th style="width:70px; text-align:center;">Meet Time</th>
        <th style="width:120px;">Time %</th>
    </tr></thead>
    <tbody>
    @foreach($customerStats as $i => $cs)
    <tr>
        <td style="text-align:center; font-weight:bold; color:#64748b;">{{ $i+1 }}</td>
        <td><div class="cn">{{ $cs['name'] }}</div></td>
        <td style="text-align:center; font-weight:bold;">{{ $cs['count'] }}<span style="color:#94a3b8; font-weight:normal;">/{{ $cs['total_dvrs'] }}</span></td>
        <td>
            <table style="border-collapse:collapse;"><tr>
                <td class="bw"><span class="bg" style="width:{{ min($cs['visit_pct'],100) }}%;"></span></td>
                <td class="pg">{{ $cs['visit_pct'] }}%</td>
            </tr></table>
        </td>
        <td style="text-align:center; font-weight:bold;">{{ $cs['time_display'] }}</td>
        <td>
            <table style="border-collapse:collapse;"><tr>
                <td class="bw"><span class="bb" style="width:{{ min($cs['time_pct'],100) }}%;"></span></td>
                <td class="pb">{{ $cs['time_pct'] }}%</td>
            </tr></table>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- ══════════════ DVR RECORDS ══════════════ --}}
@php
$_shown = $groupedDvrs->flatten(1)->count();
$_total = isset($totalAllDvrs) ? $totalAllDvrs : $summaryStats['total'];
$_dvrRecordsTitle = $_shown . ' DVR' . ($_shown != 1 ? 's' : '');
if (!empty($filterLabel)) { $_dvrRecordsTitle .= ' — ' . $filterLabel; }
if ($_shown != $_total) { $_dvrRecordsTitle .= ' (of ' . $_total . ' total)'; }
@endphp
<div class="sec">DVR Records &nbsp;&nbsp;{{ $_dvrRecordsTitle }}</div>

@foreach($groupedDvrs as $dateKey => $dateDvrs)
@php $fr = $dateDvrs->first(); $att = $fr['attendance']; @endphp

{{-- Date group header --}}
<table class="dgh"><tr>
    <td style="width:52%; vertical-align:middle;">
        <span class="dgh-dt">{{ $fr['dvr_date_display'] }}</span>
        <span class="dgh-day">{{ $fr['day_name'] }}</span>
        <span class="dgh-cnt">{{ $dateDvrs->count() }} {{ $dateDvrs->count()>1?'visits':'visit' }}</span>
    </td>
    <td class="dgh-att" style="width:48%;">
        @if($att['exists'])
            In: <strong>{{ $att['in_time']??'—' }}</strong>
            &nbsp;&nbsp;Out: <strong>{{ $att['out_time']??'—' }}</strong>
            &nbsp;&nbsp;{{ $att['work_hours'] }}
            <span class="dp {{ $att['status']==='Present'?'dp-p':'dp-a' }}">{{ $att['status'] }}</span>
        @else
            <span class="dp dp-n">No Attendance</span>
        @endif
    </td>
</tr></table>

{{-- Cards for this date --}}
@foreach($dateDvrs as $idx => $row)
<table class="card {{ $idx%2==1?'card-alt':'' }}">

    {{-- ROW 1: S.No + Customer + Type + Purpose --}}
    <tr class="card-head">
        <td class="card-sno"><span class="sno-circle">{{ $idx+1 }}</span></td>
        <td style="padding:10px 14px;">
            <div class="c-name">{{ $row['customer_name'] }}</div>
            <div class="c-type">
                @if($row['customer_type']==='customer')&#9679; Registered Customer
                @elseif($row['customer_type']==='request')&#9675; Register Request
                @endif
            </div>
            @if($row['purposes'])
            <div style="margin-top:5px;">
                @foreach($row['purposes'] as $p)
                <span class="c-chip">{{ $p }}</span>
                @endforeach
            </div>
            @endif
        </td>
    </tr>

    {{-- ROW 2: Status pills — 3 per row layout --}}
    <tr class="card-status">
        <td colspan="2">
                        @php $statusRows = array_chunk($row['statuses'], 3); @endphp
            <table style="border-collapse:collapse; width:100%;">
                @foreach($statusRows as $statusRow)
                <tr>
                    @foreach($statusRow as $s)
                    <td style="padding:2px 4px 2px 0; width:33%; white-space:nowrap; vertical-align:middle;">
                        <span class="{{ $s['color']==='#22c55e'?'sg':'sr' }}">{{ $s['label'] }}</span>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </table>
        </td>
    </tr>

    {{-- ROW 3: Timing --}}
    @if($row['check_in'] || $row['check_out'])
    <tr class="card-time">
        <td colspan="2">
            <table class="time-wrap"><tr>
                @if($row['check_in'])
                <td><div class="t-lbl">Check In</div><div class="t-in">&#9679; {{ $row['check_in'] }}</div></td>
                @endif
                @if($row['check_out'])
                <td><div class="t-lbl">Check Out</div><div class="t-out">&#9679; {{ $row['check_out'] }}</div></td>
                @endif
                @if($row['meeting_duration'])
                <td><div class="t-lbl">Duration</div><div class="t-dur"> {{ $row['meeting_duration'] }}</div></td>
                @endif
            </tr></table>
        </td>
    </tr>
    @endif

    {{-- ROW 4: Met With --}}
    <tr class="card-row">
        <td colspan="2">
            <div class="d-lbl">Met With</div>
            @if($row['contacts'])
                @foreach($row['contacts'] as $ci => $c)
                @if($ci > 0)<div class="d-sep"></div>@endif
                <div class="d-name">{{ $c['name'] }}</div>
                @if($c['designation'])<div class="d-sub">{{ $c['designation'] }}</div>@endif
                @if($c['mobile'])<div class="d-mob">&#9742; {{ $c['mobile'] }}</div>@endif
                @endforeach
            @else
                <span class="em">&mdash;</span>
            @endif
        </td>
    </tr>

    {{-- ROW 5: Visit Details --}}
    <tr class="card-row {{ !$row['products'] && !$row['trials_count'] && !$row['scheduler'] ? 'card-row-last' : '' }}">
        <td colspan="2">
            <div class="d-lbl">Visit Details</div>
            @if($row['visit_detail'])
            <div class="d-val">{{ $row['visit_detail'] }}</div>
            @else
            <span class="em">&mdash;</span>
            @endif
        </td>
    </tr>

    {{-- ROW 6: Products (only if present) --}}
    @if($row['products'])
    <tr class="card-row {{ !$row['trials_count'] && !$row['scheduler'] ? 'card-row-last' : '' }}">
        <td colspan="2">
            <div class="d-lbl">Products</div>
            @foreach($row['products'] as $p)
            <span class="prod-p">{{ $p }}</span>
            @endforeach
        </td>
    </tr>
    @endif

    {{-- ROW 7: Trials (only if present) --}}
    @if($row['trials_count'] > 0)
    <tr class="card-row {{ !$row['scheduler'] ? 'card-row-last' : '' }}">
        <td colspan="2">
            <div class="d-lbl">Trials</div>
            <span class="t-num">{{ $row['trials_count'] }}</span>
            @foreach($row['trial_rows'] as $tr)
            <span class="t-pill" style="background:{{ $tr['color']==='#22c55e'?'#16a34a':'#dc2626' }};">{{ $tr['label'] }}</span>
            @endforeach
        </td>
    </tr>
    @endif

    {{-- ROW 8: Next Plan / Schedule (only if present) --}}
    @if($row['scheduler'])
    <tr class="card-row card-row-last">
        <td colspan="2">
            <div class="d-lbl">Next Plan &amp; Schedule</div>
            <table class="sch-box"><tr><td>
                @if($row['scheduler']['date'])
                @php $_schedTime = $row['scheduler']['date'] . (!empty($row['scheduler']['time']) ? ' @ ' . $row['scheduler']['time'] : ''); @endphp
                <div class="sch-d">&#8594; {{ $_schedTime }}</div>
                @endif
                @if($row['scheduler']['description'])
                <div class="sch-t">{{ $row['scheduler']['description'] }}</div>
                @endif
            </td></tr></table>
        </td>
    </tr>
    @endif

</table>
@endforeach
@endforeach

{{-- ══════════════ FOOTER ══════════════ --}}
<table class="ftr"><tr><td>
    <strong style="color:#475569;">{{ $selectedUser->name ?? '' }}</strong>
    &nbsp;&middot;&nbsp;
    DVR Report &mdash; {{ $monthName }} {{ $currentYear }}
    &nbsp;&middot;&nbsp;
    Generated: {{ now()->format('d M Y, h:i A') }}
</td></tr></table>

</body>
</html>