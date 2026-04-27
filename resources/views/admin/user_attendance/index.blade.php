@extends('layouts.adminLayout.backendLayout')

@section('content')
{{--
    index.blade.php — Admin Attendance Management
    ──────────────────────────────────────────────
    All status colors, badge CSS, and bar CSS come from:
      • $day['badge_css']  → set by AdminAttendanceController from AttendanceStatus::badgeCss()
      • $day['bar_css']    → set by AdminAttendanceController from AttendanceStatus::barCss()
      • $statusOptions     → AttendanceStatus::ADMIN_OPTIONS passed by controller
    The blade itself never hard-codes any status string for styling decisions.
--}}

<style>
.att-page { padding:0 0 60px; }

/* ── Filter Card ───────────────────────────────────────── */
.att-filter-card {
    background:#fff; border:1px solid #e5e5e5; border-top:3px solid #1e3a5f;
    border-radius:4px; padding:14px 18px 10px; margin-bottom:14px;
}
.att-filter-card label {
    font-size:11px; font-weight:700; color:#999;
    text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:3px;
}
.att-filter-card .form-control {
    height:34px; font-size:13px; border:1px solid #ddd;
    border-radius:3px; color:#333; background:#fafafa;
}
.att-filter-card .form-group { margin-bottom:0; }

/* Date pills */
.date-pills { margin-top:8px; }
.date-pill {
    display:inline-block; padding:3px 11px; border-radius:12px;
    font-size:11px; font-weight:600; background:#f4f6f9; color:#666;
    border:1px solid #dde; cursor:pointer; margin-right:4px;
    text-decoration:none; transition:all .15s;
}
.date-pill:hover, .date-pill.active {
    background:#1e3a5f; color:#fff; border-color:#1e3a5f; text-decoration:none;
}

/* ── Employee accordion ────────────────────────────────── */
.emp-block { margin-bottom:8px; }
.emp-header {
    background:#fff; border:1px solid #e0e5ee; border-left:4px solid #1e3a5f;
    border-radius:4px; padding:11px 15px; cursor:pointer;
    display:flex; align-items:center; justify-content:space-between;
    user-select:none; transition:background .15s;
}
.emp-header:hover { background:#f8f9fb; }
.emp-header.is-open { border-radius:4px 4px 0 0; }
.emp-avatar {
    width:36px; height:36px; border-radius:50%; background:#1e3a5f;
    color:#fff; font-size:14px; font-weight:700;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; margin-right:10px;
}
.emp-name { font-size:14px; font-weight:700; color:#222; }
.emp-sub  { font-size:12px; color:#aaa; margin-top:1px; }
.stats-note { font-size:10px; color:#aaa; margin-top:3px; font-style:italic; }

/* Stat pills — colors from AttendanceStatus mobile palette */
.emp-stat-pills { display:flex; gap:5px; flex-wrap:wrap; }
.esp { font-size:11px; font-weight:700; padding:3px 9px; border-radius:8px; display:inline-flex; align-items:center; gap:4px; }
.esp-p  { background:#E8F8F1; color:#059669; }   /* Present  — #10B981 */
.esp-l  { background:#FFF4DD; color:#D97706; }   /* Leave    — #F59E0B */
.esp-lw { background:#FDECEC; color:#DC2626; }   /* LWP      — #EF4444 */
.esp-c  { background:#F2ECFF; color:#7C3AED; }   /* Comp Off — #8B5CF6 */
.esp-wd { background:#f4f6f9; color:#555;    }   /* Days */
.acc-arrow { color:#ccc; font-size:12px; transition:transform .2s; margin-left:8px; }
.emp-header.is-open .acc-arrow { transform:rotate(180deg); }

/* PDF button */
.btn-emp-pdf {
    background:#fff5f5; color:#c0392b; border:1px solid #f5c6c6;
    border-radius:3px; padding:4px 10px; font-size:11px; font-weight:600;
    cursor:pointer; transition:all .15s; font-family:inherit; white-space:nowrap;
    text-decoration:none; display:inline-flex; align-items:center; gap:4px;
}
.btn-emp-pdf:hover { background:#c0392b; color:#fff; border-color:#c0392b; text-decoration:none; }

/* ── Attendance table ──────────────────────────────────── */
.emp-body {
    background:#fff; border:1px solid #e0e5ee; border-top:none;
    border-radius:0 0 4px 4px; overflow:hidden;
}
.att-table { width:100%; border-collapse:collapse; table-layout:fixed; }
.att-table col.c0 { width:5px; }
.att-table col.c1 { width:85px; }
.att-table col.c2 { width:22%; }
.att-table col.c3 { width:22%; }
.att-table col.c4 { width:80px; }
.att-table col.c5 { width:175px; }
.att-table col.c6 { width:90px; }
.att-table thead th {
    background:#2b3a4a; color:rgba(255,255,255,.80);
    font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;
    padding:9px 10px; border:none; border-right:1px solid rgba(255,255,255,.06);
}
.att-table thead th:first-child { padding:0; border-right:none; }
.att-table thead th:last-child  { border-right:none; }
.att-table tbody td {
    padding:10px; border-bottom:1px solid #f0f3f7;
    border-right:1px solid #f0f3f7; vertical-align:top;
    font-size:13px; color:#333;
}
.att-table tbody td:last-child  { border-right:none; }
.att-table tbody tr:last-child td { border-bottom:none; }
.att-table tbody tr:hover td { background:#fafbfd; }

/* ── Left color bar (AttendanceStatus::BAR_CSS keys) ────
   One CSS rule per bar class from AttendanceStatus::BAR_CSS.
   Colors match AttendanceStatus::MOBILE_COLORS exactly.
   ─────────────────────────────────────────────────────── */
.td-bar { padding:0 !important; width:5px; border-right:none !important; }
.s-bar  { display:block; width:5px; min-height:52px; }
.s-bar.present    { background:#10B981; }                                                  /* PRESENT */
.s-bar.half       { background:linear-gradient(to bottom,#10B981 50%,#F59E0B 50%); }     /* HALF_LEAVE */
.s-bar.halfLwp    { background:linear-gradient(to bottom,#10B981 50%,#EF4444 50%); }     /* HALF_LWP */
.s-bar.leave      { background:#F59E0B; }                                                  /* FULL_LEAVE / HALF_DAY_LEAVE */
.s-bar.lwp        { background:#EF4444; }                                                  /* All LWP / HALF_LEAVE_LWP */
.s-bar.weekly     { background:#8B5CF6; }                                                  /* WEEKLY_OFF */
.s-bar.holiday    { background:#8B5CF6; }                                                  /* HOLIDAY */
.s-bar.compoff    { background:#8B5CF6; }                                                  /* COMP_OFF */
.s-bar.future     { background:#d1d5db; }                                                  /* null */
.s-bar.notpunched { background:#EF4444; }                                                  /* Not Punched Yet */

/* Date cell */
.d-num  { font-size:22px; font-weight:800; color:#1a2333; line-height:1; }
.d-mon  { font-size:11px; color:#888; font-weight:600; margin-top:2px; }
.d-dow  { font-size:10px; color:#bbb; }
.day-tag { display:inline-block; font-size:9px; font-weight:700; padding:1px 5px; border-radius:8px; margin-top:3px; }
.t-today { background:#e8f4fd; color:#2573b0; border:1px solid #c3dff5; }
.t-sun   { background:#F2ECFF; color:#7C3AED; }
.t-hol   { background:#F2ECFF; color:#7C3AED; }

/* Punch details */
.punch-line { display:flex; align-items:flex-start; gap:4px; margin-bottom:3px; }
.punch-dir  { font-size:9px; font-weight:700; padding:2px 5px; border-radius:2px; flex-shrink:0; margin-top:1px; letter-spacing:.4px; }
.dir-in  { background:#E8F8F1; color:#059669; }
.dir-out { background:#FDECEC; color:#DC2626; }
.p-time  { font-size:13px; font-weight:700; color:#111; line-height:1.2; }
.p-place { font-size:11px; color:#555; word-break:break-word; }
.p-addr  { font-size:10px; color:#aaa; word-break:break-word; }
.p-gps   { font-size:10px; color:#ccc; font-family:monospace; }
.p-ref   { font-size:10px; color:#3598dc; font-weight:600; margin-top:2px; }
.p-other { font-size:10px; color:#aaa; font-style:italic; }
.punch-sep { border:none; border-top:1px dashed #e5e5e5; margin:5px 0; }
.no-punch  { color:#ccc; font-size:18px; }
.out-pend  { font-size:11px; font-weight:700; color:#D97706; background:#FFF4DD; border-radius:3px; padding:2px 7px; display:inline-block; margin-top:2px; }
.missed-t  { font-size:10px; color:#DC2626; }

/* Duration */
.dur-val  { font-size:14px; font-weight:700; color:#222; }
.dur-lbl  { display:inline-block; font-size:10px; color:#aaa; background:#f4f6f9; padding:1px 5px; border-radius:6px; margin-top:2px; }
.open-lbl { font-size:11px; font-weight:700; color:#D97706; background:#FFF4DD; border-radius:3px; padding:2px 7px; display:inline-block; }

/* ── Status badges (AttendanceStatus::BADGE_CSS keys) ────
   Colors match AttendanceStatus::MOBILE_COLORS exactly.
   ─────────────────────────────────────────────────────── */
.s-badge { display:inline-block; font-size:11px; font-weight:700; padding:3px 9px; border-radius:10px; white-space:nowrap; }
.sb-present    { background:#E8F8F1; color:#059669; }
.sb-half       { background:#FFF4DD; color:#D97706; }
.sb-leave      { background:#FFF4DD; color:#D97706; }
.sb-lwp        { background:#FDECEC; color:#DC2626; }
.sb-weekly     { background:#F2ECFF; color:#7C3AED; }
.sb-holiday    { background:#F2ECFF; color:#7C3AED; }
.sb-compoff    { background:#F2ECFF; color:#7C3AED; }
.sb-future     { background:#f4f6f9; color:#aaa; }
.sb-notpunched { background:#FDECEC; color:#DC2626; }

/* Leave info & audit */
.leave-info-tag {
    display:inline-flex; align-items:center; gap:4px;
    font-size:10px; font-weight:700; padding:2px 7px; border-radius:8px;
    background:#eaf3fc; color:#1a5f9c; border:1px solid #bcd8ef; margin-top:4px;
}
.audit-note { font-size:10px; color:#aaa; font-style:italic; margin-top:3px; line-height:1.4; }

/* Update button */
.btn-att-upd {
    background:#f4f6f9; color:#2d6faa; border:1px solid #d5e8f5;
    border-radius:3px; padding:6px 10px; font-size:12px; font-weight:600;
    cursor:pointer; transition:all .15s; font-family:inherit; white-space:nowrap;
}
.btn-att-upd:hover { background:#2d6faa; color:#fff; border-color:#2d6faa; }

/* Empty state */
.att-empty { background:#fff; border:1px solid #e5e5e5; border-radius:4px; padding:50px; text-align:center; color:#ccc; }

/* ── Modal ─────────────────────────────────────────────── */
.modal-content { border-radius:4px !important; }
.att-mod-hdr { background:#1e3a5f; padding:13px 18px; border:none; border-radius:4px 4px 0 0; }
.att-mod-hdr .modal-title { color:#fff; font-size:14px; font-weight:700; }
.att-mod-hdr .close { color:rgba(255,255,255,.7); opacity:1; text-shadow:none; }
.att-mod-hdr .close:hover { color:#fff; }

/* Status option buttons in modal */
.st-opt {
    display:block; border:1px solid #ddd; border-radius:3px;
    padding:7px 11px; cursor:pointer; background:#fafafa;
    transition:all .15s; margin-bottom:5px; font-size:12px; font-weight:600; color:#444;
}
.st-opt:hover { border-color:#1e3a5f; background:#f0f4f9; color:#1e3a5f; }
/* Selected states mirror badge/bar colors from AttendanceStatus */
.st-opt.sel-present { border-color:#10B981 !important; background:#E8F8F1 !important; color:#059669 !important; }
.st-opt.sel-half    { border-color:#F59E0B !important; background:#FFF4DD !important; color:#D97706 !important; }
.st-opt.sel-leave   { border-color:#F59E0B !important; background:#FFF4DD !important; color:#D97706 !important; }
.st-opt.sel-lwp     { border-color:#EF4444 !important; background:#FDECEC !important; color:#DC2626 !important; }
.st-opt.sel-weekly  { border-color:#8B5CF6 !important; background:#F2ECFF !important; color:#7C3AED !important; }
.st-opt.sel-holiday { border-color:#8B5CF6 !important; background:#F2ECFF !important; color:#7C3AED !important; }
.st-opt.sel-compoff { border-color:#8B5CF6 !important; background:#F2ECFF !important; color:#7C3AED !important; }

/* Quota picker */
.quota-picker { background:#eaf4fd; border:1px solid #bcd8ee; border-radius:3px; padding:11px; margin-bottom:11px; }
.qp-title { font-size:10px; font-weight:700; color:#31708f; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; }
.qi {
    background:#fff; border:1px solid #ddd; border-radius:3px;
    padding:8px 11px; margin-bottom:5px; cursor:pointer;
    display:flex; align-items:center; justify-content:space-between; transition:border-color .15s;
}
.qi:last-child { margin-bottom:0; }
.qi:hover, .qi.sel { border-color:#3598dc; background:#f0f7fd; }
.qi.low  { border-color:#F59E0B; }
.qi.none { border-color:#EF4444; opacity:.8; }
.qi-left { display:flex; align-items:center; gap:7px; }
.qi-code { font-size:9px; font-weight:700; padding:2px 6px; border-radius:8px; }
.qi-name { font-size:12px; font-weight:600; color:#333; }
.qi-right { text-align:right; }
.qi-rem  { font-size:14px; font-weight:700; }
.qi-lbl  { font-size:8px; color:#aaa; text-transform:uppercase; }
.qi-warn { font-size:9px; font-weight:600; }
.qi-unlimited { font-size:11px; font-weight:700; color:#8B5CF6; }
.qi-radio { margin-right:3px; }
.m-err { background:#FDECEC; border-left:3px solid #EF4444; border-radius:3px; padding:8px 12px; font-size:12px; color:#DC2626; margin-top:8px; }
</style>

<div class="page-content-wrapper">
<div class="page-content att-page">

    <ul class="page-breadcrumb breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
        <li><span>Attendance Management</span></li>
    </ul>

    <div style="margin-bottom:10px;">
        <h3 style="margin:0 0 2px;font-size:18px;font-weight:700;color:#222;">Attendance Management</h3>
        <p style="margin:0;font-size:12px;color:#999;">View, verify and manage employee daily attendance records</p>
    </div>

    {{-- ── FILTER CARD ── --}}
    <div class="att-filter-card">
        <form method="GET" action="{{ url('admin/attendance') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control select2" style="width:100%;">
                            <option value="">All Employees</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}{{ $emp->mobile ? ' ('.$emp->mobile.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>Month</label>
                        <select name="month" class="form-control" id="sel-month">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ $month==$m ? 'selected' : '' }}>{{ date('M',mktime(0,0,0,$m,1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>Year</label>
                        <select name="year" class="form-control" id="sel-year">
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ $year==$yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Specific Date <small style="text-transform:none;">(optional)</small></label>
                        <input type="date" name="date" id="sel-date" class="form-control"
                               value="{{ $filterDate ?? '' }}"
                               max="{{ \Carbon\Carbon::today()->toDateString() }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            {{-- $statusOptions = AttendanceStatus::ADMIN_OPTIONS passed by controller --}}
                            @foreach($statusOptions as $st)
                                <option value="{{ $st }}" {{ request('status')===$st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm" style="padding:7px 16px;">
                                <i class="fa fa-search"></i> Apply
                            </button>
                            <a href="{{ url('admin/attendance') }}" class="btn btn-default btn-sm" style="padding:6px 11px;margin-left:4px;">
                                <i class="fa fa-times"></i> Reset
                            </a>
                            <button type="button" id="btn-collapse" class="btn btn-default btn-sm" style="padding:6px 9px;margin-left:4px;" title="Collapse All">
                                <i class="fa fa-compress"></i>
                            </button>
                            <button type="button" id="btn-expand" class="btn btn-default btn-sm" style="padding:6px 9px;margin-left:2px;" title="Expand All">
                                <i class="fa fa-expand"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Date quick pills --}}
            <div class="date-pills">
                <span style="font-size:11px;color:#aaa;font-weight:600;margin-right:4px;">Jump to:</span>
                @php
                    $todayStr = \Carbon\Carbon::today()->toDateString();
                    $yestStr  = \Carbon\Carbon::yesterday()->toDateString();
                @endphp
                {{-- Today/Yesterday always use current month/year so the month dropdown resets correctly --}}
                <a href="{{ url('admin/attendance') }}?month={{ now()->month }}&year={{ now()->year }}&date={{ $todayStr }}{{ request('employee_id') ? '&employee_id='.request('employee_id') : '' }}"
                   class="date-pill {{ $filterDate===$todayStr ? 'active' : '' }}">Today</a>
                <a href="{{ url('admin/attendance') }}?month={{ now()->month }}&year={{ now()->year }}&date={{ $yestStr }}{{ request('employee_id') ? '&employee_id='.request('employee_id') : '' }}"
                   class="date-pill {{ $filterDate===$yestStr ? 'active' : '' }}">Yesterday</a>
                {{-- Full Month stays on the browsed month --}}
                <a href="{{ url('admin/attendance') }}?month={{ $month }}&year={{ $year }}&date={{ request('employee_id') ? '&employee_id='.request('employee_id') : '' }}"class="date-pill {{ is_null($filterDate) ? 'active' : '' }}">Full Month</a>
            </div>
        </form>
    </div>

    {{-- ── EMPLOYEE BLOCKS ── --}}
    @forelse($employeeData as $empBlock)
    @php $emp = $empBlock['employee']; $empDates = $empBlock['dates']; @endphp

    <div class="emp-block">
        <div class="emp-header" id="emp-hdr-{{ $emp->id }}" onclick="toggleEmp({{ $emp->id }})" data-open="0">
            <div style="display:flex;align-items:center;">
                <div class="emp-avatar">{{ strtoupper(substr($emp->name,0,1)) }}</div>
                <div>
                    <div class="emp-name">{{ $emp->name }}</div>
                    <div class="emp-sub">
                        {{ $emp->mobile ?? '' }}
                        @if($emp->base_city) &nbsp;&bull;&nbsp;<i class="fa fa-map-marker"></i> {{ $emp->base_city }} @endif
                    </div>
                    <div class="stats-note"><i class="fa fa-info-circle"></i> Stats reflect full month</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;" onclick="event.stopPropagation();">
                <div class="emp-stat-pills">
                    <span class="esp esp-wd"><i class="fa fa-calendar-o"></i> {{ $empBlock['working_days'] }} Days</span>
                    <span class="esp esp-p" ><i class="fa fa-check"></i> {{ $empBlock['present_count'] }} Present</span>
                    <span class="esp esp-l" ><i class="fa fa-calendar-minus-o"></i> {{ $empBlock['leave_count'] }} Leave</span>
                    <span class="esp esp-lw"><i class="fa fa-ban"></i> {{ $empBlock['lwp_count'] }} LWP</span>
                    @if($empBlock['comp_off_count'] > 0)
                        <span class="esp esp-c"><i class="fa fa-exchange"></i> {{ $empBlock['comp_off_count'] }} Comp</span>
                    @endif
                </div>
                <a href="{{ url('admin/attendance/export-pdf/'.$emp->id) }}?month={{ $month }}&year={{ $year }}"
                    class="btn-emp-pdf" title="Download PDF">
                    <i class="fa fa-file-pdf-o"></i> PDF
                </a>
                <a href="{{ url('admin/users/'.$emp->id.'/attendance-settings') }}"
                   target="_blank"
                   class="btn-emp-pdf"
                   style="background:#f0f7ff;color:#1a5f9c;border-color:#b8d4ef;"
                   title="Quota / Attendance Settings">
                    <i class="fa fa-sliders"></i> Quota
                </a>
                <i class="fa fa-chevron-down acc-arrow" id="chev-{{ $emp->id }}"
                   onclick="toggleEmp({{ $emp->id }});event.stopPropagation();"></i>
            </div>
        </div>

        <div class="emp-body" id="emp-body-{{ $emp->id }}" style="display:none;">
            <table class="att-table">
                <colgroup>
                    <col class="c0"><col class="c1"><col class="c2">
                    <col class="c3"><col class="c4"><col class="c5"><col class="c6">
                </colgroup>
                <thead>
                    <tr>
                        <th></th><th>Date</th><th>IN Details</th>
                        <th>OUT Details</th><th>Duration</th><th>Status / Leave</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($empDates as $day)
                @php
                    
                    $st      = $day['status'];
                    $barCls  = $day['bar_css'];
                    $bc      = $day['badge_css'];
                    $rec     = $day['main_record'];
                    $recs    = $day['records'];
                    $car     = $day['carbon'];

                    $isPresent = in_array($st, ['Present','Full Day Present','1/2 Present + 1/2 Leave','1/2 Present + 1/2 LWP']);
                @endphp
                <tr id="row-{{ $emp->id }}-{{ $day['date'] }}">
                    {{-- Color bar --}}
                    <td class="td-bar"><span class="s-bar {{ $barCls }}"></span></td>

                    {{-- Date --}}
                    <td>
                        <div class="d-num">{{ $car->format('d') }}</div>
                        <div class="d-mon">{{ $car->format('M Y') }}</div>
                        <div class="d-dow">{{ $car->format('D') }}</div>
                        @if($day['is_today'])
                            <span class="day-tag t-today">Today</span>
                        @elseif($day['is_sunday'])
                            <span class="day-tag t-sun">Sunday</span>
                        @elseif($day['is_holiday'])
                            <span class="day-tag t-hol" title="{{ $day['holiday_name'] }}">{{ \Illuminate\Support\Str::limit($day['holiday_name'],9) }}</span>
                        @endif
                    </td>

                    {{-- IN --}}
                    <td>
                        @if(!empty($recs))
                            @foreach($recs as $ri => $r)
                                @if($ri > 0)<hr class="punch-sep">@endif
                                @if($r->in_time)
                                <div class="punch-line">
                                    <span class="punch-dir dir-in">IN</span>
                                    <div>
                                        <div class="p-time">{{ \Carbon\Carbon::parse($r->in_time)->format('h:i A') }}</div>
                                        @if($r->in_place_of_attendance)<div class="p-place">{{ $r->in_place_of_attendance }}</div>@endif
                                        @if($r->in_latitude_longitude_address)<div class="p-addr">{{ \Illuminate\Support\Str::limit($r->in_latitude_longitude_address,40) }}</div>@endif
                                        @if($r->in_latitude && $r->in_longitude)<div class="p-gps">{{ number_format($r->in_latitude,4) }}, {{ number_format($r->in_longitude,4) }}</div>@endif
                                        @if($r->in_customer_name ?? null)  <div class="p-ref"><i class="fa fa-building-o"></i> {{ $r->in_customer_name }}</div>
                                        @elseif($r->in_crr_name ?? null)   <div class="p-ref"><i class="fa fa-user-plus"></i> {{ $r->in_crr_name }}</div>
                                        @elseif($r->in_dealer_name ?? null)<div class="p-ref"><i class="fa fa-handshake-o"></i> {{ $r->in_dealer_name }}</div>@endif
                                        @if($r->in_other)<div class="p-other">{{ \Illuminate\Support\Str::limit($r->in_other,30) }}</div>@endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @else
                            <span class="no-punch">&mdash;</span>
                        @endif
                    </td>

                    {{-- OUT --}}
                    <td>
                        @if(!empty($recs))
                            @foreach($recs as $ri => $r)
                                @if($ri > 0)<hr class="punch-sep">@endif
                                @if($r->out_time)
                                <div class="punch-line">
                                    <span class="punch-dir dir-out">OUT</span>
                                    <div>
                                        <div class="p-time">{{ \Carbon\Carbon::parse($r->out_time)->format('h:i A') }}</div>
                                        @if($r->out_place_of_attendance)<div class="p-place">{{ $r->out_place_of_attendance }}</div>@endif
                                        @if($r->out_latitude_longitude_address)<div class="p-addr">{{ \Illuminate\Support\Str::limit($r->out_latitude_longitude_address,40) }}</div>@endif
                                        @if($r->out_latitude && $r->out_longitude)<div class="p-gps">{{ number_format($r->out_latitude,4) }}, {{ number_format($r->out_longitude,4) }}</div>@endif
                                        @if($r->out_customer_name ?? null)  <div class="p-ref"><i class="fa fa-building-o"></i> {{ $r->out_customer_name }}</div>
                                        @elseif($r->out_crr_name ?? null)   <div class="p-ref"><i class="fa fa-user-plus"></i> {{ $r->out_crr_name }}</div>
                                        @elseif($r->out_dealer_name ?? null)<div class="p-ref"><i class="fa fa-handshake-o"></i> {{ $r->out_dealer_name }}</div>@endif
                                    </div>
                                </div>
                                @elseif($isPresent && !$r->missed)
                                    <div class="out-pend"><i class="fa fa-clock-o"></i> OUT Pending</div>
                                @elseif($r->missed)
                                    <div class="missed-t"><i class="fa fa-exclamation-triangle"></i> Missed</div>
                                @endif
                            @endforeach
                        @else
                            @if($isPresent)
                                <div class="out-pend"><i class="fa fa-clock-o"></i> OUT Pending</div>
                            @else
                                <span class="no-punch">&mdash;</span>
                            @endif
                        @endif
                    </td>

                    {{-- Duration --}}
                    <td style="text-align:center;">
                        @if($day['duration'])
                            <div class="dur-val">{{ $day['duration'] }}</div>
                            <span class="dur-lbl">worked</span>
                        @elseif($day['is_open'])
                            <div class="open-lbl"><i class="fa fa-clock-o"></i> Open</div>
                        @elseif($isPresent && empty($recs))
                            <div class="open-lbl"><i class="fa fa-clock-o"></i> Open</div>
                        @else
                            <span style="color:#ddd;">&mdash;</span>
                        @endif
                    </td>

                    {{-- Status + Leave Info + Audit --}}
                    <td id="scell-{{ $emp->id }}-{{ $day['date'] }}">
                        @if($st)
                            {{-- badge_css pre-computed from AttendanceStatus::badgeCss() --}}
                            <span class="s-badge {{ $bc }}" id="sbadge-{{ $emp->id }}-{{ $day['date'] }}">{{ $st }}</span>
                            @if($day['leave_info'])
                                @php $lv = $day['leave_info']; @endphp
                                <div style="margin-top:4px;">
                                    <small><i class="fa fa-balance-scale"></i>
                                        {{ $lv->lt_code }}: {{ $lv->quota_deducted }} day deducted
                                    </small>
                                </div>
                            @endif
                            @if($rec && $rec->status_change_note)
                                <div class="audit-note" id="anote-{{ $emp->id }}-{{ $day['date'] }}">
                                    <i class="fa fa-history"></i> {{ \Illuminate\Support\Str::limit($rec->status_change_note,55) }}
                                </div>
                            @endif
                        @else
                            <span style="color:#ddd;">&mdash;</span>
                        @endif
                    </td>

                    {{-- Action --}}
                    <td style="text-align:center;">
                        @if(!$day['is_future'])
                        <button class="btn-att-upd btn-open-modal"
                            data-att-id="{{ $rec ? $rec->id : '' }}"
                            data-user-id="{{ $emp->id }}"
                            data-date="{{ $day['date'] }}"
                            data-status="{{ $st }}"
                            data-employee="{{ $emp->name }}"
                            data-has-record="{{ $day['has_record'] ? '1' : '0' }}">
                            <i class="fa fa-pencil"></i> Update
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="att-empty">
        <i class="fa fa-calendar-times-o" style="font-size:36px;display:block;margin-bottom:10px;"></i>
        <p style="font-size:13px;font-weight:500;">No attendance data found.</p>
    </div>
    @endforelse

</div>
</div>

{{-- ════ STATUS UPDATE MODAL ════ --}}
<div class="modal fade" id="attModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:500px;max-width:95%;margin:55px auto;">
        <div class="modal-content">
            <div class="modal-header att-mod-hdr">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-calendar-check-o"></i>&nbsp; Update Attendance &mdash; <span id="m-emp"></span></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="m-att-id">
                <input type="hidden" id="m-user-id">
                <input type="hidden" id="m-date">
                <input type="hidden" id="m-has-record">
                <input type="hidden" id="m-new-status">

                <div style="background:#f9f9f9;border:1px solid #eee;border-radius:3px;padding:7px 11px;margin-bottom:12px;font-size:12px;">
                    <span style="color:#999;">Date:</span> <strong id="m-date-lbl" style="color:#222;"></strong>
                    &nbsp;&nbsp;<span style="color:#999;">Current:</span>
                    <span id="m-curr-st" style="font-weight:600;color:#1e3a5f;"></span>
                </div>

                <div style="font-size:9px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px;">Select New Status</div>

                {{--
                    Status options loop — driven by $statusOptions = AttendanceStatus::ADMIN_OPTIONS.
                    The icon and sel-class are the only things defined here; color comes from CSS.
                --}}
                @php
                $stMeta = [
                    'Present'                          => ['icon'=>'fa-check-circle',       'cls'=>'sel-present'],
                    '1/2 Present + 1/2 LWP'           => ['icon'=>'fa-adjust',             'cls'=>'sel-lwp'],
                    '1/2 Present + 1/2 Leave'         => ['icon'=>'fa-adjust',             'cls'=>'sel-half'],
                    '1/2 Day Leave'                    => ['icon'=>'fa-calendar-o',   'cls'=>'sel-leave'],
                    '1/2 Leave + 1/2 LWP'             => ['icon'=>'fa-adjust',             'cls'=>'sel-lwp'],
                    'Allowed Full Day Leave'           => ['icon'=>'fa-calendar-o',         'cls'=>'sel-leave'],
                    'LWP (Uninformed Absence)'         => ['icon'=>'fa-ban',                'cls'=>'sel-lwp'],
                    'LWP (Unapproved Leave)'           => ['icon'=>'fa-ban',                'cls'=>'sel-lwp'],
                    'LWP (Exceeds Quota)'   => ['icon'=>'fa-exclamation-circle', 'cls'=>'sel-lwp'],
                    'Holiday'                  => ['icon'=>'fa-sun-o',              'cls'=>'sel-holiday'],
                    'Compensatory Weekly Off'          => ['icon'=>'fa-exchange',           'cls'=>'sel-compoff'],
                    'Weekly Off'                       => ['icon'=>'fa-home',               'cls'=>'sel-weekly'],
                ];
                @endphp

                <div class="row">
                    @foreach($statusOptions as $stVal)
                    @php
                        $meta = $stMeta[$stVal] ?? ['icon'=>'fa-circle', 'cls'=>''];
                    @endphp
                    @if($loop->index > 0 && $loop->index % 2 === 0)
                        <div class="clearfix"></div>
                    @endif
                    <div class="col-md-6" style="margin-bottom:4px;">
                        <div class="st-opt" data-v="{{ $stVal }}" data-cls="{{ $meta['cls'] }}" onclick="pickSt(this)">
                            <i class="fa {{ $meta['icon'] }}" style="margin-right:5px;width:14px;text-align:center;"></i>{{ $stVal }}
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Quota picker (shown when leave status selected) --}}
                <div class="quota-picker" id="quotaBox" style="display:none;">
                    <div class="qp-title"><i class="fa fa-balance-scale"></i>&nbsp; Select Leave Type &mdash; <span id="qp-deduct" style="font-weight:600;color:#31708f;"></span></div>
                    <div id="quotaList"><div style="text-align:center;padding:14px;color:#ccc;"><i class="fa fa-spinner fa-spin"></i></div></div>
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label style="font-size:10px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="fa fa-comment-o"></i> Admin Remarks <small style="text-transform:none;font-weight:400;">(optional)</small>
                    </label>
                    <textarea id="m-remarks" rows="2" class="form-control" style="font-size:13px;resize:none;" placeholder="Reason for this status change..."></textarea>
                </div>

                <div id="m-err" class="m-err" style="display:none;"></div>
            </div>
            <div class="modal-footer" style="background:#f9f9f9;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                <button type="button" id="btnSaveAtt" class="btn btn-primary"><i class="fa fa-save"></i> Save Status</button>
            </div>
        </div>
    </div>
</div>

<script>
{{--
    LEAVE_STS: statuses that require quota picker.
    Driven by AttendanceStatus::QUOTA_DEDUCT_STATUSES output from PHP.
--}}
var LEAVE_STS = @json(\App\AttendanceStatus::QUOTA_DEDUCT_STATUSES);
var quotaCache = null;

/* ── Toast notification ── */
function attNotify(t, m) {
    if (typeof toastr !== 'undefined') { toastr[t](m); return; }
    var c = { success:'#10B981', error:'#EF4444', warning:'#F59E0B', info:'#1e3a5f' };
    var el = document.createElement('div');
    el.innerHTML = m;
    el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:'+(c[t]||'#1e3a5f')+';color:#fff;padding:10px 16px;border-radius:4px;font-size:13px;font-weight:600;box-shadow:0 2px 10px rgba(0,0,0,.2);max-width:300px;opacity:0;transition:opacity .3s;';
    document.body.appendChild(el);
    setTimeout(function(){ el.style.opacity = '1'; }, 10);
    setTimeout(function(){ el.style.opacity = '0'; setTimeout(function(){ el.parentNode && el.parentNode.removeChild(el); }, 350); }, 3500);
}

/* ── Accordion ── */
function toggleEmp(id) {
    var body = document.getElementById('emp-body-' + id);
    var hdr  = document.getElementById('emp-hdr-'  + id);
    var chev = document.getElementById('chev-'     + id);
    var open = hdr.getAttribute('data-open') === '1';
    if (open) {
        body.style.display = 'none';
        hdr.classList.remove('is-open');
        chev.className = 'fa fa-chevron-down acc-arrow';
        hdr.setAttribute('data-open', '0');
    } else {
        body.style.display = '';
        hdr.classList.add('is-open');
        chev.className = 'fa fa-chevron-up acc-arrow';
        hdr.setAttribute('data-open', '1');
    }
}
document.getElementById('btn-collapse').onclick = function() {
    document.querySelectorAll('.emp-header').forEach(function(h) {
        if (h.getAttribute('data-open') === '1') toggleEmp(h.id.replace('emp-hdr-', ''));
    });
};
document.getElementById('btn-expand').onclick = function() {
    document.querySelectorAll('.emp-header').forEach(function(h) {
        if (h.getAttribute('data-open') === '0') toggleEmp(h.id.replace('emp-hdr-', ''));
    });
};

/* ── Status picker ── */
function pickSt(el) {
    document.querySelectorAll('.st-opt').forEach(function(o) { o.className = 'st-opt'; });
    el.classList.add(el.getAttribute('data-cls'));
    var val = el.getAttribute('data-v');
    document.getElementById('m-new-status').value = val;
    document.getElementById('m-err').style.display = 'none';

    var isLeave = LEAVE_STS.indexOf(val) !== -1;
    if (isLeave) {
        var half = (val === '1/2 Present + 1/2 Leave' || val === '1/2 Day Leave' || val === '1/2 Leave + 1/2 LWP');
        document.getElementById('qp-deduct').textContent = half ? '0.5 day will be deducted' : '1.0 day will be deducted';
        document.getElementById('quotaBox').style.display = 'block';
        loadQuota();
    } else {
        document.getElementById('quotaBox').style.display = 'none';
    }
}

/* ── Quota loader ── */
function loadQuota() {
    if (quotaCache) { renderQuota(quotaCache, null); return; }
    document.getElementById('quotaList').innerHTML = '<div style="text-align:center;padding:12px;color:#ccc;"><i class="fa fa-spinner fa-spin"></i></div>';
    $.ajax({
        url: '{{ url("admin/attendance/quota-info") }}', type: 'GET',
        data: { user_id: $('#m-user-id').val(), date: $('#m-date').val(), attendance_id: $('#m-att-id').val() },
        success: function(r) {
            if (r.success) { quotaCache = r.quota; renderQuota(r.quota, r.existing_leave); }
        },
        error: function() {
            document.getElementById('quotaList').innerHTML = '<div style="color:#DC2626;font-size:12px;padding:8px;">Failed to load quota.</div>';
        }
    });
}

function renderQuota(quota, existingLeave) {
    var val    = $('#m-new-status').val();
    var half   = (val === '1/2 Present + 1/2 Leave' || val === '1/2 Day Leave' || val === '1/2 Leave + 1/2 LWP');
    var deduct = half ? 0.5 : 1.0;
    var exId   = existingLeave ? existingLeave.leave_type_id : null;
    var cmap   = { SL:'background:#FDECEC;color:#DC2626', CL:'background:#E8F8F1;color:#059669', EL:'background:#FFF4DD;color:#D97706', ML:'background:#F2ECFF;color:#7C3AED' };
    var html = '';
    quota.forEach(function(q) {
        var sel         = exId && exId == q.id;
        var isUnlimited = (q.remaining === null || q.remaining === undefined);
        var low         = !isUnlimited && q.remaining > 0 && q.remaining < deduct;
        var none        = !isUnlimited && q.remaining <= 0;
        var cls         = 'qi' + (sel ? ' sel' : '') + (low ? ' low' : '') + (none ? ' none' : '');
        var rc          = none ? '#EF4444' : (low ? '#F59E0B' : '#10B981');
        var cs          = cmap[q.code] || 'background:#f4f6f9;color:#555';
        html += '<div class="' + cls + '" onclick="selQ(this,' + q.id + ')">';
        html += '<div class="qi-left"><input type="radio" class="qi-radio" name="lt_radio" value="' + q.id + '"' + (sel ? ' checked' : '') + ' style="cursor:pointer;">';
        html += '<span class="qi-code" style="' + cs + '">' + q.code + '</span><span class="qi-name">' + q.name + '</span></div>';
        html += '<div class="qi-right">';
        if (isUnlimited) {
            html += '<div class="qi-unlimited">&#8734;</div><div class="qi-lbl">Unlimited</div>';
        } else {
            html += '<div class="qi-rem" style="color:' + rc + ';">' + q.remaining.toFixed(1) + '</div><div class="qi-lbl">days left</div>';
        }
        if (low)  html += '<div class="qi-warn" style="color:#F59E0B;"><i class="fa fa-exclamation-triangle"></i> Low</div>';
        if (none) html += '<div class="qi-warn" style="color:#EF4444;"><i class="fa fa-times-circle"></i> None</div>';
        html += '</div></div>';
    });
    document.getElementById('quotaList').innerHTML = html || '<div style="color:#aaa;font-size:12px;padding:8px;">No leave types found.</div>';
}

function selQ(el, id) {
    document.querySelectorAll('.qi').forEach(function(i) { i.classList.remove('sel'); });
    el.classList.add('sel');
    var r = el.querySelector('input[type="radio"]');
    if (r) r.checked = true;
}

/* ── Open modal ── */
$(document).on('click', '.btn-open-modal', function() {
    var attId     = $(this).data('att-id');
    var userId    = $(this).data('user-id');
    var date      = $(this).data('date');
    var status    = $(this).data('status');
    var employee  = $(this).data('employee');
    var hasRecord = $(this).data('has-record');
    quotaCache = null;
    $('#m-att-id').val(attId); $('#m-user-id').val(userId); $('#m-date').val(date);
    $('#m-has-record').val(hasRecord); $('#m-new-status').val(''); $('#m-remarks').val('');
    $('#m-err').hide(); $('#quotaBox').hide();
    $('#m-emp').text(employee); $('#m-date-lbl').text(date); $('#m-curr-st').text(status || '—');
    document.querySelectorAll('.st-opt').forEach(function(o) { o.className = 'st-opt'; });
    var $cur = $('.st-opt[data-v="' + status + '"]');
    if ($cur.length) pickSt($cur[0]);
    $('#attModal').modal('show');
});

/* ── Save status ── */
$('#btnSaveAtt').on('click', function() {
    var attId     = $('#m-att-id').val();
    var userId    = $('#m-user-id').val();
    var date      = $('#m-date').val();
    var newStatus = $('#m-new-status').val();
    var remarks   = $('#m-remarks').val().trim();
    var hasRecord = $('#m-has-record').val();
    var $btn      = $(this);

    if (!newStatus) { $('#m-err').html('<i class="fa fa-exclamation-circle"></i> Please select a status.').show(); return; }

    var isLeave = LEAVE_STS.indexOf(newStatus) !== -1;
    var leaveTypeId = null;
    if (isLeave) {
        var checked = document.querySelector('input[name="lt_radio"]:checked');
        if (!checked) { $('#m-err').html('<i class="fa fa-exclamation-circle"></i> Please select a leave type.').show(); return; }
        leaveTypeId = checked.value;
    }

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    $('#m-err').hide();

    var url  = hasRecord === '1' ? '/admin/attendance/' + attId + '/update-status' : '/admin/attendance/create-record';
    var data = { _token: '{{ csrf_token() }}', new_status: newStatus, leave_type_id: leaveTypeId, admin_remarks: remarks };
    if (hasRecord !== '1') { data.user_id = userId; data.date = date; }

    $.ajax({
        url: url, type: 'POST', data: data,
        success: function(r) {
            if (r.success) {
                var key    = userId + '-' + date;
                var badge  = document.getElementById('sbadge-' + key);
                var bcCss  = r.badge_css || 'sb-future';
                var barCss = r.bar_css   || 'future';

                if (badge) {
                    badge.className   = 's-badge ' + bcCss;
                    badge.textContent = newStatus;
                } else {
                    var cell = document.getElementById('scell-' + key);
                    if (cell) cell.innerHTML = '<span class="s-badge ' + bcCss + '" id="sbadge-' + key + '">' + newStatus + '</span>';
                }

                var bar = document.querySelector('#row-' + key + ' .s-bar');
                if (bar) bar.className = 's-bar ' + barCss;

                if (r.change_note) {
                    var noteEl = document.getElementById('anote-' + key);
                    if (noteEl) noteEl.innerHTML = '<i class="fa fa-history"></i> ' + r.change_note.substring(0, 55) + (r.change_note.length > 55 ? '…' : '');
                }

                var btn = document.querySelector('.btn-open-modal[data-user-id="' + userId + '"][data-date="' + date + '"]');
                if (btn) {
                    $(btn).data('status', newStatus).attr('data-status', newStatus);
                    if (hasRecord !== '1' && r.attendance_id) {
                        $(btn).data('att-id', r.attendance_id).attr('data-att-id', r.attendance_id)
                              .data('has-record', '1').attr('data-has-record', '1');
                    }
                }

                $('#attModal').modal('hide');
                attNotify('success', 'Status updated to "' + newStatus + '"');
            } else {
                $('#m-err').html('<i class="fa fa-exclamation-circle"></i> ' + (r.message || 'Failed.')).show();
            }
        },
        error: function(x) {
            var m = 'An error occurred.';
            if (x.responseJSON && x.responseJSON.message) m = x.responseJSON.message;
            if (x.responseJSON && x.responseJSON.errors)  m = Object.values(x.responseJSON.errors).flat().join('<br>');
            $('#m-err').html('<i class="fa fa-exclamation-circle"></i> ' + m).show();
        },
        complete: function() { $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Status'); }
    });
});

/* ── Filter sync ── */
// Picking a specific date → sync month/year dropdowns
document.getElementById('sel-date').addEventListener('change', function() {
    if (this.value) {
        var d = new Date(this.value);
        document.getElementById('sel-month').value = d.getMonth() + 1;
        document.getElementById('sel-year').value  = d.getFullYear();
    }
});
// Changing month/year → clear date input (submits as Full Month)
document.getElementById('sel-month').addEventListener('change', function() {
    document.getElementById('sel-date').value = '';
});
document.getElementById('sel-year').addEventListener('change', function() {
    document.getElementById('sel-date').value = '';
});
</script>
@endsection