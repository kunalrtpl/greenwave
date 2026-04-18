<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 12px;
    color: #222;
    margin: 0;
    padding: 0;
}

@page {
    margin: 22px 20px 22px 20px;
}

/* ── HEADER ── */
.header {
    margin-bottom: 14px;
    border-bottom: 2px solid #2b3a4a;
    padding-bottom: 9px;
}
.title { font-size: 20px; font-weight: bold; color: #1a2333; }
.sub   { font-size: 12px; color: #666; margin-top: 2px; }
.meta  { font-size: 11px; margin-top: 5px; color: #555; }

/* ── EMPLOYEE HEADER ── */
.emp-header-block {
    margin-top: 18px; margin-bottom: 0;
    padding: 8px 10px;
    background: #f0f4f9;
    border-left: 4px solid #1e3a5f;
    border-radius: 2px;
}
.emp-name-text { font-size: 14px; font-weight: bold; color: #1a2333; }
.emp-sub-text  { font-size: 11px; color: #666; margin-top: 2px; }

/* ── STATS SUMMARY BAR ── */
.stats-bar {
    margin: 0 0 0 0;
    padding: 7px 10px;
    background: #fafbfd;
    border: 1px solid #e0e5ee;
    border-top: none;
}
.stats-bar table { width: auto; border-collapse: collapse; table-layout: auto; }
.stats-bar td    { border: none; padding: 2px 10px 2px 0; font-size: 11px; vertical-align: middle; }
.stat-pill {
    display: inline-block; padding: 3px 10px; border-radius: 10px;
    font-size: 10px; font-weight: bold; white-space: nowrap;
}
.sp-days    { background: #f4f6f9; color: #444; }
.sp-present { background: #E8F8F1; color: #059669; }
.sp-leave   { background: #FFF4DD; color: #D97706; }
.sp-lwp     { background: #FDECEC; color: #DC2626; }
.sp-weekly  { background: #F2ECFF; color: #7C3AED; }
.sp-compoff { background: #F2ECFF; color: #7C3AED; }
.sp-holiday { background: #F2ECFF; color: #7C3AED; }

/* ── QUOTA SECTION ── */
.quota-section {
    border: 1px solid #e0e5ee;
    border-top: none;
    background: #fefefe;
    padding: 8px 10px 10px;
    margin-bottom: 6px;
}
.quota-title {
    font-size: 10px; font-weight: bold; color: #1e3a5f;
    text-transform: uppercase; letter-spacing: 0.4px;
    margin-bottom: 7px; padding-bottom: 4px;
    border-bottom: 1px solid #e8ecf2;
}
.quota-table { width: 100%; border-collapse: collapse; }
.quota-table th {
    font-size: 9px; font-weight: bold; color: #888;
    text-transform: uppercase; letter-spacing: 0.3px;
    padding: 4px 8px; border-bottom: 1px solid #e5e5e5;
    text-align: left; background: #f7f9fb;
}
.quota-table th.right { text-align: right; }
.quota-table td {
    font-size: 11px; padding: 5px 8px;
    border-bottom: 1px solid #f0f2f5;
    vertical-align: middle;
}
.quota-table td.right { text-align: right; }
.quota-table tr:last-child td { border-bottom: none; }
.quota-table tr:nth-child(even) td { background: #fafbfd; }

/* Code badge */
.q-code {
    display: inline-block; font-size: 9px; font-weight: bold;
    padding: 2px 7px; border-radius: 8px;
}
.q-sl { background: #FDECEC; color: #991B1B; }
.q-cl { background: #E8F8F1; color: #065F46; }
.q-el { background: #FFF4DD; color: #92400E; }
.q-ml { background: #F2ECFF; color: #5B21B6; }
.q-df { background: #f4f6f9; color: #555; }

/* Progress bar */
.pbar-wrap { background: #eef0f3; border-radius: 4px; height: 7px; width: 100px; overflow: hidden; }
.pbar-fill  { height: 7px; border-radius: 4px; }
.pbar-green  { background: #10B981; }
.pbar-amber  { background: #F59E0B; }
.pbar-red    { background: #EF4444; }
.pbar-purple { background: #8B5CF6; }

.q-rem-green  { color: #059669; font-weight: bold; }
.q-rem-amber  { color: #D97706; font-weight: bold; }
.q-rem-red    { color: #DC2626; font-weight: bold; }
.q-unlimited  { color: #7C3AED; font-weight: bold; font-size: 12px; }

/* ── TABLE ── */
table.att-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
table.att-table th {
    background: #2b3a4a; color: #fff;
    font-size: 10px; font-weight: bold;
    padding: 7px 8px; border: 1px solid #3d4e5e;
    text-align: left; letter-spacing: 0.3px;
}
table.att-table td {
    border: 1px solid #dde3ea;
    padding: 7px 8px; vertical-align: top; font-size: 11px;
}
table.att-table tbody tr:nth-child(even) td { background: #f7f9fb; }

.col-bar    { width: 5px;   padding: 0 !important; border-right: none !important; }
.col-date   { width: 68px; }
.col-in     { width: 155px; }
.col-out    { width: 155px; }
.col-dur    { width: 62px; text-align: center; }
.col-status { width: 130px; }
.col-notes  { width: auto; }

/* Side bar */
.s-bar { display: block; width: 5px; min-height: 46px; }
.bar-present    { background: #10B981; }
.bar-half       { background: #F59E0B; }
.bar-halfLwp    { background: #EF4444; }
.bar-leave      { background: #F59E0B; }
.bar-lwp        { background: #EF4444; }
.bar-weekly     { background: #8B5CF6; }
.bar-holiday    { background: #8B5CF6; }
.bar-compoff    { background: #8B5CF6; }
.bar-future     { background: #d1d5db; }
.bar-notpunched { background: #EF4444; }

/* Date cell */
.date-num { font-size: 16px; font-weight: bold; color: #1a2333; line-height: 1; }
.date-sub { font-size: 10px; color: #888; margin-top: 2px; }
.date-tag { display: inline-block; font-size: 8px; font-weight: bold; padding: 1px 5px; border-radius: 6px; margin-top: 3px; }
.tag-today   { background: #e8f4fd; color: #2573b0; }
.tag-sunday  { background: #FDECEC; color: #DC2626; }
.tag-holiday { background: #F2ECFF; color: #7C3AED; }

/* Punch */
.punch-row { margin-bottom: 3px; }
.dir-in  { font-size: 9px; font-weight: bold; padding: 1px 4px; border-radius: 2px; background: #E8F8F1; color: #059669; }
.dir-out { font-size: 9px; font-weight: bold; padding: 1px 4px; border-radius: 2px; background: #FDECEC; color: #DC2626; }
.p-time  { font-size: 12px; font-weight: bold; color: #111; }
.p-small { font-size: 10px; color: #666; }
.p-pend  { font-size: 10px; font-weight: bold; color: #D97706; }
.no-data { color: #ccc; }
.punch-sep { border: none; border-top: 1px dashed #dde; margin: 4px 0; }

/* Duration */
.dur-val  { font-size: 12px; font-weight: bold; color: #222; }
.dur-lbl  { font-size: 9px; color: #aaa; }
.open-lbl { font-size: 10px; font-weight: bold; color: #D97706; }

/* Status badges */
.badge { display: inline-block; padding: 3px 7px; font-size: 10px; font-weight: bold; border-radius: 8px; white-space: nowrap; }
.st-present    { background: #E8F8F1; color: #059669; }
.st-half       { background: #FFF4DD; color: #D97706; }
.st-leave      { background: #FFF4DD; color: #D97706; }
.st-lwp        { background: #FDECEC; color: #DC2626; }
.st-weekly     { background: #F2ECFF; color: #7C3AED; }
.st-holiday    { background: #F2ECFF; color: #7C3AED; }
.st-compoff    { background: #F2ECFF; color: #7C3AED; }
.st-notpunched { background: #FDECEC; color: #DC2626; }
.st-future     { background: #f4f6f9; color: #aaa; }

.note-text  { font-size: 10px; color: #666; font-style: italic; }
.leave-info { font-size: 9px; color: #1a5f9c; margin-top: 3px; }

.page-break { page-break-after: always; }
</style>

</head>
<body>

<!-- ═══ HEADER ═══ -->
<div class="header">
    <div class="title">Attendance Report</div>
    <div class="sub">Employee Attendance Management System</div>
    <div class="meta">
        Period: <strong>{{ $filterLabel }}</strong> &nbsp;|&nbsp;
        Generated: <strong>{{ $generatedAt }}</strong>
    </div>
</div>

@foreach($employeeData as $block)
@php
    $emp          = $block['employee'];
    $dates        = $block['dates'];
    $totalDays    = $block['working_days']   ?? 0;
    $presentCount = $block['present_count']  ?? 0;
    $leaveCount   = $block['leave_count']    ?? 0;
    $lwpCount     = $block['lwp_count']      ?? 0;
    $compOffCount = $block['comp_off_count'] ?? 0;
    $fy           = $block['financial_year'] ?? '—';
    $quotaDetails = $block['quota_details']  ?? [];
@endphp

<!-- ═══ EMPLOYEE HEADER ═══ -->
<div class="emp-header-block">
    <div class="emp-name-text">{{ $emp->name }}</div>
    <div class="emp-sub-text">
        @if($emp->mobile) {{ $emp->mobile }} @endif
        @if($emp->base_city) &nbsp;|&nbsp; {{ $emp->base_city }} @endif
        &nbsp;|&nbsp; FY {{ $fy }}
    </div>
</div>

<!-- ═══ STATS SUMMARY BAR ═══ -->
<div class="stats-bar">
    <table>
        <tr>
            <td><span class="stat-pill sp-days">&#128197; {{ $totalDays }} Working Days</span></td>
            <td><span class="stat-pill sp-present">&#10003; {{ $presentCount }} Present</span></td>
            <td><span class="stat-pill sp-leave">&#128197; {{ $leaveCount }} Leave</span></td>
            <td><span class="stat-pill sp-lwp">&#9888; {{ $lwpCount }} LWP</span></td>
            @if($compOffCount > 0)
            <td><span class="stat-pill sp-compoff">&#8646; {{ $compOffCount }} Comp Off</span></td>
            @endif
        </tr>
    </table>
</div>

<!-- ═══ LEAVE QUOTA SECTION ═══ -->
@if(!empty($quotaDetails))
<div class="quota-section">
    <div class="quota-title">&#9878; Leave Balance — {{ $fy }}</div>
    <table class="quota-table">
        <thead>
            <tr>
                <th style="width:60px;">Type</th>
                <th>Leave Name</th>
                <th class="right" style="width:70px;">Total</th>
                <th class="right" style="width:70px;">Used</th>
                <th class="right" style="width:70px;">Remaining</th>
                <th style="width:120px;">Balance</th>
            </tr>
        </thead>
        <tbody>
        @foreach($quotaDetails as $q)
        @php
            $codeCls  = 'q-df';
            $barCls   = 'pbar-green';
            if ($q['code'] === 'SL') { $codeCls = 'q-sl'; $barCls = 'pbar-red';    }
            elseif ($q['code'] === 'CL') { $codeCls = 'q-cl'; $barCls = 'pbar-green';  }
            elseif ($q['code'] === 'EL') { $codeCls = 'q-el'; $barCls = 'pbar-amber';  }
            elseif ($q['code'] === 'ML') { $codeCls = 'q-ml'; $barCls = 'pbar-purple'; }

            $isUnlimited = $q['unlimited'] ?? false;
            $pct = 0;
            $remCls = 'q-rem-green';
            if (!$isUnlimited && $q['total'] > 0) {
                $pct = min(100, round(($q['used'] / $q['total']) * 100));
                $remRatio = $q['remaining'] / $q['total'];
                if ($remRatio < 0.25)    $remCls = 'q-rem-red';
                elseif ($remRatio < 0.5) $remCls = 'q-rem-amber';
            }
        @endphp
        <tr>
            <td><span class="q-code {{ $codeCls }}">{{ $q['code'] }}</span></td>
            <td>{{ $q['name'] }}</td>
            <td class="right">
                @if($isUnlimited) <span class="q-unlimited">&#8734;</span> @else {{ number_format($q['total'], 1) }} @endif
            </td>
            <td class="right">
                @if($isUnlimited) — @else {{ number_format($q['used'], 1) }} @endif
            </td>
            <td class="right">
                @if($isUnlimited)
                    <span class="q-unlimited">&#8734;</span>
                @else
                    <span class="{{ $remCls }}">{{ number_format($q['remaining'], 1) }}</span>
                @endif
            </td>
            <td>
                @if($isUnlimited)
                    <span class="q-unlimited" style="font-size:10px;">Unlimited</span>
                @else
                    <div class="pbar-wrap">
                        <div class="pbar-fill {{ $barCls }}" style="width:{{ $pct }}%;"></div>
                    </div>
                    <div style="font-size:9px;color:#aaa;margin-top:2px;">{{ $pct }}% used</div>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- ═══ ATTENDANCE TABLE ═══ -->
<table class="att-table">
<thead>
<tr>
    <!-- <th class="col-bar"></th> -->
    <th class="col-date">Date</th>
    <th class="col-in">IN Details</th>
    <th class="col-out">OUT Details</th>
    <th class="col-dur">Duration</th>
    <th class="col-status">Status</th>
    <th class="col-notes">Notes / Leave</th>
</tr>
</thead>
<tbody>
@foreach($dates as $day)
@php
    $st      = $day['computedStatus'] ?? $day['status'] ?? null;
    $recs    = $day['records'] ?? [];
    $mainRec = $day['mainRecord'] ?? $day['main_record'] ?? null;
    $dc      = $day['dc'] ?? $day['carbon'];

    $barCls = 'bar-future';
    if ($st === 'Full Day Present')               $barCls = 'bar-present';
    elseif ($st === 'Not Punched Yet')            $barCls = 'bar-notpunched';
    elseif ($st === '1/2 Present + 1/2 Leave')   $barCls = 'bar-half';
    elseif ($st === '1/2 Present + 1/2 LWP')     $barCls = 'bar-halfLwp';
    elseif (str_contains($st??'','Leave'))        $barCls = 'bar-leave';
    elseif (str_contains($st??'','LWP'))          $barCls = 'bar-lwp';
    elseif ($st === 'Weekly Off')                 $barCls = 'bar-weekly';
    elseif (str_contains($st??'','Holiday'))      $barCls = 'bar-holiday';
    elseif (str_contains($st??'','Compensatory')) $barCls = 'bar-compoff';

    $cls = 'st-future';
    if ($st === 'Full Day Present')               $cls = 'st-present';
    elseif ($st === 'Not Punched Yet')            $cls = 'st-notpunched';
    elseif ($st === '1/2 Present + 1/2 Leave')   $cls = 'st-half';
    elseif ($st === '1/2 Present + 1/2 LWP')     $cls = 'st-lwp';
    elseif (str_contains($st??'','Leave'))        $cls = 'st-leave';
    elseif (str_contains($st??'','LWP'))          $cls = 'st-lwp';
    elseif ($st === 'Weekly Off')                 $cls = 'st-weekly';
    elseif (str_contains($st??'','Holiday'))      $cls = 'st-holiday';
    elseif (str_contains($st??'','Compensatory')) $cls = 'st-compoff';

    $isPresent = in_array($st, ['Full Day Present','1/2 Present + 1/2 Leave','1/2 Present + 1/2 LWP']);
@endphp
<tr>

<!-- BAR -->
<!-- <td class="col-bar"><span class="s-bar {{ $barCls }}"></span></td> -->

<!-- DATE -->
<td class="col-date">
    <div class="date-num">{{ $dc->format('d') }}</div>
    <div class="date-sub">{{ $dc->format('M Y') }}</div>
    <div class="date-sub">{{ $dc->format('D') }}</div>
    @if(isset($day['is_today']) && $day['is_today'])
        <span class="date-tag tag-today">Today</span>
    @elseif(isset($day['isSunday']) && $day['isSunday'])
        <span class="date-tag tag-sunday">Sun</span>
    @elseif(isset($day['isHoliday']) && $day['isHoliday'])
        <span class="date-tag tag-holiday">{{ \Illuminate\Support\Str::limit($day['holiday_name']??'Hol',8) }}</span>
    @elseif(isset($day['is_sunday']) && $day['is_sunday'])
        <span class="date-tag tag-sunday">Sun</span>
    @elseif(isset($day['is_holiday']) && $day['is_holiday'])
        <span class="date-tag tag-holiday">{{ \Illuminate\Support\Str::limit($day['holiday_name']??'Hol',8) }}</span>
    @endif
</td>

<!-- IN -->
<td class="col-in">
    @if(!empty($recs))
        @foreach($recs as $ri => $r)
            @if($ri > 0)<hr class="punch-sep">@endif
            @if($r->in_time)
            <div class="punch-row">
                <span class="dir-in">IN</span>
                <span class="p-time"> {{ \Carbon\Carbon::parse($r->in_time)->format('h:i A') }}</span>
            </div>
            @if($r->in_place_of_attendance)
                <div class="p-small">{{ $r->in_place_of_attendance }}</div>
            @endif
            @if($r->in_latitude_longitude_address ?? null)
                <div class="p-small">{{ \Illuminate\Support\Str::limit($r->in_latitude_longitude_address,45) }}</div>
            @endif
            @if($r->in_customer_name ?? null)
                <div class="p-small" style="color:#3598dc;">{{ $r->in_customer_name }}</div>
            @endif
            @endif
        @endforeach
    @else
        <span class="no-data">&mdash;</span>
    @endif
</td>

<!-- OUT -->
<td class="col-out">
    @if(!empty($recs))
        @foreach($recs as $ri => $r)
            @if($ri > 0)<hr class="punch-sep">@endif
            @if($r->out_time)
            <div class="punch-row">
                <span class="dir-out">OUT</span>
                <span class="p-time"> {{ \Carbon\Carbon::parse($r->out_time)->format('h:i A') }}</span>
            </div>
            @if($r->out_place_of_attendance)
                <div class="p-small">{{ $r->out_place_of_attendance }}</div>
            @endif
            @if($r->out_latitude_longitude_address ?? null)
                <div class="p-small">{{ \Illuminate\Support\Str::limit($r->out_latitude_longitude_address,45) }}</div>
            @endif
            @elseif($isPresent && !($r->missed ?? false))
                <div class="p-pend">&#9200; OUT Pending</div>
            @elseif($r->missed ?? false)
                <div class="p-small" style="color:#DC2626;">&#9888; Missed</div>
            @endif
        @endforeach
    @else
        @if($isPresent)
            <div class="p-pend">&#9200; OUT Pending</div>
        @else
            <span class="no-data">&mdash;</span>
        @endif
    @endif
</td>

<!-- DURATION -->
<td class="col-dur" style="text-align:center;">
    @if(!empty($day['duration']))
        <div class="dur-val">{{ $day['duration'] }}</div>
        <div class="dur-lbl">worked</div>
    @elseif(!empty($day['is_open']) || !empty($day['isOpen']))
        <div class="open-lbl">&#9200; Open</div>
    @elseif($isPresent && empty($recs))
        <div class="open-lbl">&#9200; Open</div>
    @else
        <span class="no-data">&mdash;</span>
    @endif
</td>

<!-- STATUS -->
<td class="col-status">
    @if($st)
        <span class="badge {{ $cls }}">{{ $st }}</span>
    @else
        <span class="no-data">&mdash;</span>
    @endif
</td>

<!-- NOTES / LEAVE INFO -->
<td class="col-notes">
    @if($mainRec && ($mainRec->status_change_note ?? null))
        <div class="note-text">{{ \Illuminate\Support\Str::limit($mainRec->status_change_note, 70) }}</div>
    @endif
    @if(!empty($day['leave_info']))
        @php $lv = $day['leave_info']; @endphp
        <div class="leave-info">
            &#9878; {{ $lv->lt_code }}: {{ $lv->quota_deducted }} day deducted
        </div>
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