<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 12px; /* 🔥 increased */
    color: #222;
}

/* PAGE */
@page {
    margin: 20px;
}

/* HEADER */
.header {
    margin-bottom: 15px;
    border-bottom: 2px solid #2b3a4a;
    padding-bottom: 8px;
}

.title {
    font-size: 18px; /* 🔥 bigger */
    font-weight: bold;
}

.sub {
    font-size: 12px;
    color: #666;
}

.meta {
    font-size: 11px;
    margin-top: 5px;
}

/* EMPLOYEE */
.emp {
    margin-top: 18px;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: bold;
    padding: 6px 8px;
    background: #eef2f7;
    border-left: 4px solid #2b3a4a;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th {
    background: #2b3a4a;
    color: #fff;
    font-size: 11px;
    padding: 8px;
    border: 1px solid #444;
}

td {
    border: 1px solid #ddd;
    padding: 8px;
    vertical-align: top;
    font-size: 11px;
}

/* ZEBRA */
tr:nth-child(even) {
    background: #f9fbfd;
}

/* COLUMN WIDTH */
.col-date { width: 80px; }
.col-in { width: 170px; }
.col-out { width: 170px; }
.col-dur { width: 70px; text-align:center; }
.col-status { width: 140px; }

/* DATE STYLE */
.date-num {
    font-size: 14px;
    font-weight: bold;
}
.date-day {
    font-size: 10px;
    color: #888;
}

/* TEXT */
.small { font-size: 10px; color:#666; }
.center { text-align: center; }
.no-data { color: #bbb; }

/* STATUS BADGES */
.badge {
    display: inline-block;
    padding: 4px 8px;
    font-size: 10px;
    border-radius: 5px;
    font-weight: bold;
}

/* COLORS */
.st-present { background: #dff0d8; color: #2e7d32; }
.st-leave   { background: #d9edf7; color: #1565c0; }
.st-lwp     { background: #fdecea; color: #c62828; }
.st-weekly  { background: #eeeeee; color: #555; }
.st-holiday { background: #e0f2f1; color: #00695c; }
.st-half    { background: #fff8e1; color: #ef6c00; }
.st-pending { background: #fff3cd; color: #856404; }

/* PAGE BREAK */
.page-break {
    page-break-after: always;
}
</style>

</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="title">Attendance Report</div>
    <div class="sub">Employee Attendance Management System</div>

    <div class="meta">
        Period: {{ $filterLabel }} |
        Generated: {{ $generatedAt }} 
    </div>
</div>

@foreach($employeeData as $block)

<div class="emp">
    {{ $block['employee']->name }} |
    {{ $block['employee']->mobile }} |
    {{ $block['employee']->base_city }}
</div>

<table>
<thead>
<tr>
    <th class="col-date">Date</th>
    <th class="col-in">IN Details</th>
    <th class="col-out">OUT Details</th>
    <th class="col-dur">Duration</th>
    <th class="col-status">Status</th>
    <th>Notes</th>
</tr>
</thead>

<tbody>
@foreach($block['dates'] as $day)

@php
$st = $day['computedStatus'];

$cls = '';
if($st == 'Full Day Present') $cls = 'st-present';
elseif(str_contains($st,'Leave')) $cls = 'st-leave';
elseif(str_contains($st,'LWP')) $cls = 'st-lwp';
elseif($st == 'Weekly Off') $cls = 'st-weekly';
elseif(str_contains($st,'Holiday')) $cls = 'st-holiday';
elseif(str_contains($st,'1/2')) $cls = 'st-half';
elseif($st == 'Not Punched Yet') $cls = 'st-pending';
@endphp

<tr>

<td>
<div class="date-num">{{ $day['dc']->format('d') }}</div>
<div class="date-day">{{ $day['dc']->format('M D') }}</div>
</td>

<td>
@if(!empty($day['records']))
@foreach($day['records'] as $r)
@if($r->in_time)
<b>IN:</b> {{ \Carbon\Carbon::parse($r->in_time)->format('h:i A') }}<br>
<span class="small">{{ $r->in_place_of_attendance }}</span><br>
@endif
@endforeach
@else
<span class="no-data">—</span>
@endif
</td>

<td>
@if(!empty($day['records']))
@foreach($day['records'] as $r)
@if($r->out_time)
<b>OUT:</b> {{ \Carbon\Carbon::parse($r->out_time)->format('h:i A') }}<br>
<span class="small">{{ $r->out_place_of_attendance }}</span><br>
@endif
@endforeach
@else
<span class="no-data">—</span>
@endif
</td>

<td class="center">
{{ $day['duration'] ?? '—' }}
</td>

<td>
@if($st)
<span class="badge {{ $cls }}">{{ $st }}</span>
@else
—
@endif
</td>

<td>
@if($day['mainRecord'] && $day['mainRecord']->status_change_note)
<span class="small">{{ $day['mainRecord']->status_change_note }}</span>
@endif
</td>

</tr>

@endforeach
</tbody>
</table>

@if(!$loop->last)
<div class="page-break"></div>
@endif

@endforeach

</body>
</html>