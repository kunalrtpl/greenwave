<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>DVR — {{ $selectedUser->name ?? '' }} — {{ $monthName }} {{ $currentYear }}</title>
<style>
/*
   DVR PDF v5 — Card layout, portrait A4
   DomPDF: no flexbox, no grid, no CSS vars
   Base font 11px — readable at 100%
*/
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #1e293b;
    background: #fff;
    line-height: 1.5;
}

@page {
    size: A4 portrait;
    margin: 14mm 12mm 16mm 12mm;
}

/* ─── Header Banner ─────────────────────── */
.hdr {
    background: #0f172a;
    width: 100%;
    padding: 16px 20px;
    margin-bottom: 14px;
}
.hdr-tbl { width: 100%; border-collapse: collapse; }
.hdr-tbl td { vertical-align: middle; }
.hdr-name  { font-size: 20px; font-weight: bold; color: #fff; }
.hdr-title { font-size: 11px; color: #60a5fa; margin-top: 2px; }
.hdr-meta  { font-size: 9px;  color: #64748b; margin-top: 5px; }
.hdr-right { text-align: right; }
.hdr-big   { font-size: 36px; font-weight: bold; color: #fff; line-height: 1; }
.hdr-big-l { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }

/* ─── Stat Row ──────────────────────────── */
.stat-tbl { width: 100%; border-collapse: separate; border-spacing: 5px; margin-bottom: 14px; }
.sc {
    text-align: center; vertical-align: middle;
    border: 1.5px solid #e2e8f0; padding: 8px 6px;
    background: #f8fafc;
}
.sc-g { border-color: #86efac; background: #f0fdf4; }
.sc-r { border-color: #fca5a5; background: #fff5f5; }
.sc .n { font-size: 22px; font-weight: bold; color: #0f172a; line-height: 1; }
.sc-g .n { color: #16a34a; }
.sc-r .n { color: #dc2626; }
.sc .l { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #64748b; letter-spacing: 0.4px; margin-top: 3px; }

/* ─── Section heading ───────────────────── */
.sh {
    font-size: 10px; font-weight: bold; text-transform: uppercase;
    letter-spacing: 0.6px; color: #334155;
    border-left: 3px solid #3b82f6;
    padding: 4px 0 4px 8px;
    background: #f1f5f9;
    margin-bottom: 8px;
}

/* ─── Customer Summary ──────────────────── */
.cs-tbl { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10.5px; }
.cs-tbl thead th {
    background: #1e293b; color: #94a3b8;
    font-size: 9px; font-weight: bold; text-transform: uppercase;
    padding: 7px 8px; text-align: left; letter-spacing: 0.4px;
}
.cs-tbl tbody td { padding: 7px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
.cs-tbl tbody tr:last-child td { border-bottom: none; }
.cs-tbl tbody tr:nth-child(even) td { background: #f8fafc; }
.cs-tbl .vname { font-weight: bold; font-size: 11px; }
.cs-tbl .vsub  { font-size: 9px; color: #64748b; }

.bar-w { display: inline-block; width: 60px; height: 7px; background: #e2e8f0; vertical-align: middle; margin-right: 4px; overflow: hidden; }
.bar-i { display: block; height: 7px; }
.b-g { background: #22c55e; }
.b-b { background: #3b82f6; }
.pv  { font-size: 10px; font-weight: bold; vertical-align: middle; }
.pv-g { color: #16a34a; }
.pv-b { color: #2563eb; }

/* ─── Date Group Header ─────────────────── */
.dgh {
    background: #1e293b;
    width: 100%;
    padding: 9px 12px;
    margin-top: 14px;
    margin-bottom: 0;
}
.dgh-tbl { width: 100%; border-collapse: collapse; }
.dgh-date { font-size: 14px; font-weight: bold; color: #fff; }
.dgh-day  {
    display: inline-block; background: #334155; color: #94a3b8;
    font-size: 9px; font-weight: bold; padding: 1px 8px; margin-left: 6px;
}
.dgh-cnt  {
    display: inline-block; background: #3b82f6; color: #fff;
    font-size: 9px; font-weight: bold; padding: 1px 8px; margin-left: 5px;
}
.dgh-att  { font-size: 9.5px; color: #94a3b8; text-align: right; }
.dgh-att strong { color: #e2e8f0; }
.att-badge {
    display: inline-block; padding: 2px 8px; font-size: 9px; font-weight: bold; color: #fff;
    margin-left: 6px;
}
.att-p { background: #16a34a; }
.att-a { background: #dc2626; }
.att-n { background: #64748b; }

/* ─── DVR Card ──────────────────────────── */
.dvr-card {
    width: 100%;
    border: 1px solid #e2e8f0;
    margin-top: 6px;
    background: #fff;
    page-break-inside: avoid;
}
.dvr-card-even { background: #fafcff; }

/* Card top bar: S.No + Customer + Status */
.card-top { width: 100%; border-collapse: collapse; border-bottom: 1px solid #e2e8f0; }
.card-top td { vertical-align: top; padding: 9px 10px; }
.card-top .ct-sno {
    width: 28px; text-align: center; vertical-align: middle;
    border-right: 1px solid #e2e8f0;
    background: #f1f5f9;
}
.sno-c {
    display: inline-block; width: 22px; height: 22px;
    background: #1e293b; color: #fff;
    text-align: center; line-height: 22px;
    font-size: 10px; font-weight: bold;
}
.ct-customer { width: 38%; border-right: 1px solid #e2e8f0; }
.ct-status   { width: 28%; border-right: 1px solid #e2e8f0; }
.ct-timing   { width: 22%; }

.cust-name { font-size: 13px; font-weight: bold; color: #0f172a; }
.cust-type { font-size: 9px; color: #64748b; margin-top: 1px; }
.p-chip {
    display: inline-block; background: #dbeafe; color: #1d4ed8;
    padding: 1px 6px; font-size: 9px; font-weight: bold; margin: 2px 2px 0 0;
}

/* Status pills */
.sp {
    display: block; padding: 3px 8px; font-size: 10px; font-weight: bold; color: #fff;
    margin-bottom: 3px; text-align: center;
}
.sp:last-child { margin-bottom: 0; }
.sp-g { background: #16a34a; }
.sp-r { background: #dc2626; }

/* Timing */
.t-in  { font-size: 11px; font-weight: bold; color: #16a34a; }
.t-out { font-size: 11px; font-weight: bold; color: #dc2626; }
.t-dur { font-size: 10px; color: #64748b; margin-top: 2px; }

/* Card body: Met With + Details + Products + Schedule */
.card-body { width: 100%; border-collapse: collapse; }
.card-body td { vertical-align: top; padding: 8px 10px; border-right: 1px solid #f1f5f9; }
.card-body td:last-child { border-right: none; }
.card-body .cb-met     { width: 25%; }
.card-body .cb-detail  { width: 40%; }
.card-body .cb-prod    { width: 15%; }
.card-body .cb-sched   { width: 20%; }

.cb-lbl {
    font-size: 8px; font-weight: bold; text-transform: uppercase;
    color: #94a3b8; letter-spacing: 0.4px; margin-bottom: 4px;
}

.ct-name  { font-size: 11px; font-weight: bold; color: #0f172a; }
.ct-desig { font-size: 9px; color: #64748b; }
.ct-mob   { font-size: 9.5px; color: #475569; }
.ct-sep   { border-top: 1px solid #f1f5f9; margin: 4px 0; }

.vd-txt { font-size: 10.5px; color: #374151; line-height: 1.5; }

.prod-pill {
    display: inline-block; background: #ede9fe; color: #6d28d9;
    padding: 2px 7px; font-size: 9.5px; font-weight: bold; margin: 2px 1px 0 0;
}

.trial-circle {
    display: inline-block; width: 24px; height: 24px;
    background: #7c3aed; color: #fff;
    text-align: center; line-height: 24px;
    font-size: 12px; font-weight: bold; margin-bottom: 3px;
}
.t-pill {
    display: block; padding: 2px 5px; font-size: 8px; font-weight: bold; color: #fff;
    margin-top: 2px; text-align: center;
}

.sched-box {
    background: #f0fdf4; border: 1px solid #86efac;
    padding: 6px 8px;
}
.sched-d { font-size: 10px; font-weight: bold; color: #15803d; }
.sched-t { font-size: 9.5px; color: #166534; margin-top: 2px; line-height: 1.4; }

.em { color: #cbd5e1; }

/* ─── Footer ─────────────────────────────── */
.ftr {
    border-top: 1px solid #e2e8f0;
    margin-top: 14px; padding-top: 8px;
    text-align: center; font-size: 9px; color: #94a3b8;
}
.ftr strong { color: #475569; }
</style>
</head>
<body>

{{-- ═══════════════════════════════
     HEADER
═══════════════════════════════ --}}
<div class="hdr">
    <table class="hdr-tbl">
        <tr>
            <td style="width:70%;">
                <div class="hdr-name">{{ $selectedUser->name ?? 'N/A' }}</div>
                <div class="hdr-title">Daily Visit Report</div>
                <div class="hdr-meta">{{ $monthName }} {{ $currentYear }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}</div>
            </td>
            <td style="width:30%;" class="hdr-right">
                <div class="hdr-big">{{ $summaryStats['total'] }}</div>
                <div class="hdr-big-l">Total DVRs</div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════
     STAT BOXES
═══════════════════════════════ --}}
<table class="stat-tbl">
    <tr>
        <td class="sc">
            <div class="n">{{ $summaryStats['total'] }}</div>
            <div class="l">Total DVRs</div>
        </td>
        <td class="sc sc-g">
            <div class="n">{{ $summaryStats['verified'] }}</div>
            <div class="l">Verified</div>
        </td>
        <td class="sc sc-r">
            <div class="n">{{ $summaryStats['pending'] }}</div>
            <div class="l">Pending</div>
        </td>
        <td class="sc">
            <div class="n">{{ $summaryStats['metCount'] }}</div>
            <div class="l">Meetings</div>
        </td>
        <td class="sc">
            <div class="n">{{ $summaryStats['trialsSum'] }}</div>
            <div class="l">Trials</div>
        </td>
        <td class="sc sc-g">
            <div class="n">{{ $summaryStats['presentDays'] }}</div>
            <div class="l">Present Days</div>
        </td>
    </tr>
</table>

{{-- ═══════════════════════════════
     CUSTOMER SUMMARY
═══════════════════════════════ --}}
@if($customerStats->count())
<div class="sh">Customer Visit Summary</div>
<table class="cs-tbl">
    <thead>
        <tr>
            <th style="width:22px;">#</th>
            <th>Customer</th>
            <th style="width:55px; text-align:center;">Visits</th>
            <th style="width:120px;">Visit %</th>
            <th style="width:65px; text-align:center;">Meet Time</th>
            <th style="width:120px;">Time %</th>
        </tr>
    </thead>
    <tbody>
    @foreach($customerStats as $i => $cs)
    <tr>
        <td style="text-align:center; color:#64748b; font-weight:bold;">{{ $i+1 }}</td>
        <td>
            <div class="vname">{{ $cs['name'] }}</div>
        </td>
        <td style="text-align:center; font-weight:bold;">
            {{ $cs['count'] }}<span style="color:#94a3b8; font-weight:normal;">/{{ $cs['total_dvrs'] }}</span>
        </td>
        <td>
            <div class="bar-w"><div class="bar-i b-g" style="width:{{ min($cs['visit_pct'],100) }}%;"></div></div>
            <span class="pv pv-g">{{ $cs['visit_pct'] }}%</span>
        </td>
        <td style="text-align:center; font-weight:bold;">{{ $cs['time_display'] }}</td>
        <td>
            <div class="bar-w"><div class="bar-i b-b" style="width:{{ min($cs['time_pct'],100) }}%;"></div></div>
            <span class="pv pv-b">{{ $cs['time_pct'] }}%</span>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- ═══════════════════════════════
     DVR RECORDS — CARD LAYOUT
═══════════════════════════════ --}}
<div class="sh">DVR Records</div>

@foreach($groupedDvrs as $dateKey => $dateDvrs)
@php
    $fr  = $dateDvrs->first();
    $att = $fr['attendance'];
@endphp

{{-- DATE GROUP HEADER --}}
<div class="dgh">
    <table class="dgh-tbl">
        <tr>
            <td style="width:55%;">
                <span class="dgh-date">{{ $fr['dvr_date_display'] }}</span>
                <span class="dgh-day">{{ $fr['day_name'] }}</span>
                <span class="dgh-cnt">{{ $dateDvrs->count() }} {{ $dateDvrs->count()>1?'visits':'visit' }}</span>
            </td>
            <td style="width:45%;" class="dgh-att">
                @if($att['exists'])
                    In: <strong>{{ $att['in_time'] ?? '—' }}</strong>
                    &nbsp;&nbsp;
                    Out: <strong>{{ $att['out_time'] ?? '—' }}</strong>
                    &nbsp;&nbsp;
                    {{ $att['work_hours'] }}
                    <span class="att-badge {{ $att['status']==='Present' ? 'att-p' : 'att-a' }}">{{ $att['status'] }}</span>
                @else
                    <span class="att-badge att-n">No Attendance</span>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- DVR CARDS FOR THIS DATE --}}
@foreach($dateDvrs as $idx => $row)
<div class="dvr-card {{ $idx%2==1 ? 'dvr-card-even' : '' }}">

    {{-- TOP SECTION: No. | Customer | Status | Timing --}}
    <table class="card-top">
        <tr>
            {{-- S.No --}}
            <td class="ct-sno">
                <span class="sno-c">{{ $idx+1 }}</span>
            </td>

            {{-- Customer & Purpose --}}
            <td class="ct-customer">
                <div class="cust-name">{{ $row['customer_name'] }}</div>
                <div class="cust-type">
                    @if($row['customer_type']==='customer')Registered Customer
                    @elseif($row['customer_type']==='request')Register Request
                    @endif
                </div>
                @if($row['purposes'])
                <div style="margin-top:4px;">
                    @foreach($row['purposes'] as $p)
                    <span class="p-chip">{{ $p }}</span>
                    @endforeach
                </div>
                @endif
            </td>

            {{-- Status --}}
            <td class="ct-status">
                @foreach($row['statuses'] as $s)
                <span class="sp {{ $s['color']==='#22c55e' ? 'sp-g' : 'sp-r' }}">{{ $s['label'] }}</span>
                @endforeach
            </td>

            {{-- Timing --}}
            <td class="ct-timing">
                @if($row['check_in'])
                <div class="t-in">&#9679; In: {{ $row['check_in'] }}</div>
                @endif
                @if($row['check_out'])
                <div class="t-out">&#9679; Out: {{ $row['check_out'] }}</div>
                @endif
                @if($row['meeting_duration'])
                <div class="t-dur">&#9201; {{ $row['meeting_duration'] }}</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- BOTTOM SECTION: Met With | Visit Details | Products & Trials | Schedule --}}
    <table class="card-body">
        <tr>
            {{-- Met With --}}
            <td class="cb-met">
                <div class="cb-lbl">Met With</div>
                @if($row['contacts'])
                    @foreach($row['contacts'] as $ci => $c)
                    @if($ci > 0)<div class="ct-sep"></div>@endif
                    <div class="ct-name">{{ $c['name'] }}</div>
                    @if($c['designation'])<div class="ct-desig">{{ $c['designation'] }}</div>@endif
                    @if($c['mobile'])<div class="ct-mob">&#9742; {{ $c['mobile'] }}</div>@endif
                    @endforeach
                @else
                    <span class="em">&mdash;</span>
                @endif
            </td>

            {{-- Visit Details --}}
            <td class="cb-detail">
                <div class="cb-lbl">Visit Details</div>
                @if($row['visit_detail'])
                <div class="vd-txt">{{ Str::limit($row['visit_detail'], 200) }}</div>
                @else
                <span class="em">&mdash;</span>
                @endif
            </td>

            {{-- Products & Trials --}}
            <td class="cb-prod">
                <div class="cb-lbl">Products</div>
                @if($row['products'])
                    @foreach($row['products'] as $p)
                    <span class="prod-pill">{{ $p }}</span>
                    @endforeach
                @else
                    <span class="em">&mdash;</span>
                @endif

                @if($row['trials_count'] > 0)
                <div style="margin-top:6px;">
                    <div class="cb-lbl">Trials</div>
                    <div><span class="trial-circle">{{ $row['trials_count'] }}</span></div>
                    @foreach($row['trial_rows'] as $tr)
                    <span class="t-pill" style="background:{{ $tr['color']==='#22c55e'?'#16a34a':'#dc2626' }};">{{ $tr['label'] }}</span>
                    @endforeach
                </div>
                @endif
            </td>

            {{-- Next Plan / Schedule --}}
            <td class="cb-sched">
                <div class="cb-lbl">Next Plan</div>
                @if($row['scheduler'])
                <div class="sched-box">
                    @if($row['scheduler']['date'])
                    <div class="sched-d">{{ $row['scheduler']['date'] }}
                        @if($row['scheduler']['time']) &nbsp;{{ $row['scheduler']['time'] }}@endif
                    </div>
                    @endif
                    @if($row['scheduler']['description'])
                    <div class="sched-t">{{ Str::limit($row['scheduler']['description'], 80) }}</div>
                    @endif
                </div>
                @else
                <span class="em">&mdash;</span>
                @endif
            </td>
        </tr>
    </table>

</div>{{-- .dvr-card --}}
@endforeach
@endforeach

{{-- FOOTER --}}
<div class="ftr">
    <strong>{{ $selectedUser->name ?? '' }}</strong>
    &nbsp;&middot;&nbsp;
    DVR Report &mdash; {{ $monthName }} {{ $currentYear }}
    &nbsp;&middot;&nbsp;
    Generated {{ now()->format('d M Y, h:i A') }}
</div>

</body>
</html>