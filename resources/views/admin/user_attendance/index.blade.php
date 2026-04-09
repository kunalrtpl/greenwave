@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
/* ── Reset to Metronic-safe styles ── */
.att-page { padding: 0 0 60px; }

/* Filter card */
.att-filter-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-top: 3px solid #3598dc;
    border-radius: 4px;
    padding: 15px 20px 12px;
    margin-bottom: 15px;
}
.att-filter-card .form-group { margin-bottom: 0; }
.att-filter-card label {
    font-size: 10px;
    font-weight: 700;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 4px;
}
.att-filter-card .form-control {
    height: 34px;
    font-size: 13px;
    border: 1px solid #ddd;
    border-radius: 3px;
    color: #333;
    background: #fafafa;
}
.att-filter-card .form-control:focus {
    border-color: #3598dc;
    outline: none;
    box-shadow: none;
}

/* Date pills */
.date-pills { margin-top: 10px; }
.date-pills .date-pill {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #f4f6f9;
    color: #666;
    border: 1px solid #dde4ee;
    cursor: pointer;
    margin-right: 5px;
    text-decoration: none;
    transition: all 0.15s;
}
.date-pills .date-pill:hover { background: #3598dc; color: #fff; border-color: #3598dc; text-decoration: none; }
.date-pills .date-pill.active { background: #3598dc; color: #fff; border-color: #3598dc; }

/* Stats row */
.att-stat-box {
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    padding: 10px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}
.att-stat-box .stat-icon {
    width: 34px; height: 34px;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 14px; flex-shrink: 0;
}
.att-stat-box .stat-label { font-size: 10px; color: #aaa; font-weight: 600; text-transform: uppercase; }
.att-stat-box .stat-value { font-size: 18px; font-weight: 700; color: #333; line-height: 1.1; }

/* Employee accordion */
.emp-block { margin-bottom: 8px; }

.emp-header {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-left: 4px solid #3598dc;
    border-radius: 4px;
    padding: 12px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    user-select: none;
    transition: background 0.15s;
}
.emp-header:hover { background: #f8f9fb; }
.emp-header.is-open { border-radius: 4px 4px 0 0; border-bottom: 1px solid #eee; }

.emp-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: #3598dc;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    margin-right: 12px;
}
.emp-name { font-size: 14px; font-weight: 700; color: #333; }
.emp-sub  { font-size: 11px; color: #aaa; margin-top: 1px; }

.emp-pills .ep {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 9px;
    border-radius: 10px;
    margin-left: 5px;
}
.ep-p  { background: #dff0d8; color: #3c763d; }
.ep-l  { background: #d9edf7; color: #31708f; }
.ep-a  { background: #f2dede; color: #a94442; }
.ep-lw { background: #e8d5f5; color: #6b21a8; }

.acc-arrow { color: #ccc; font-size: 13px; transition: transform 0.2s; margin-left: 10px; }
.emp-header.is-open .acc-arrow { transform: rotate(180deg); }

/* Attendance table */
.emp-body {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-top: none;
    border-radius: 0 0 4px 4px;
    overflow: hidden;
}

.att-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.att-table col.col-bar   { width: 5px; }
.att-table col.col-date  { width: 95px; }
.att-table col.col-in    { width: 24%; }
.att-table col.col-out   { width: 24%; }
.att-table col.col-dur   { width: 85px; }
.att-table col.col-st    { width: 155px; }
.att-table col.col-act   { width: 90px; }

.att-table thead th {
    background: #2b3a4a;
    color: rgba(255,255,255,0.75);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 9px 10px;
    border: none;
    border-right: 1px solid rgba(255,255,255,0.06);
}
.att-table thead th:first-child { padding: 0; border-right: none; }
.att-table thead th:last-child  { border-right: none; }

.att-table tbody td {
    padding: 10px;
    border-bottom: 1px solid #f0f2f5;
    border-right: 1px solid #f0f2f5;
    vertical-align: top;
    font-size: 12px;
    color: #444;
}
.att-table tbody td:last-child { border-right: none; }
.att-table tbody tr:last-child td { border-bottom: none; }
.att-table tbody tr:hover td { background: #fafbfd; }

/* Status bar column */
.td-bar { padding: 0 !important; width: 5px; border-right: none !important; }
.s-bar  { display: block; width: 5px; min-height: 54px; height: 100%; }
.s-bar.present  { background: #26c281; }
.s-bar.half     { background: #f39c12; }
.s-bar.leave    { background: #3598dc; }
.s-bar.absent   { background: #e74c3c; }
.s-bar.lwp      { background: #9b59b6; }
.s-bar.weekly   { background: #95a5a6; }
.s-bar.holiday  { background: #1abc9c; }
.s-bar.compoff  { background: #e67e22; }
.s-bar.future   { background: #e5e5e5; }
.s-bar.notpunch { background: #f39c12; }

/* Date cell */
.td-date .d-num   { font-size: 22px; font-weight: 800; color: #222; line-height: 1; }
.td-date .d-mon   { font-size: 11px; color: #888; font-weight: 600; margin-top: 2px; }
.td-date .d-dow   { font-size: 10px; color: #bbb; }
.day-tag {
    display: inline-block;
    font-size: 9px; font-weight: 700;
    padding: 2px 6px; border-radius: 10px; margin-top: 3px;
}
.tag-today  { background: #e8f4fd; color: #2573b0; border: 1px solid #c3dff5; }
.tag-sun    { background: #fde8e8; color: #c0392b; }
.tag-hol    { background: #e8fdf5; color: #0f766e; }

/* Punch details */
.punch-wrap { }
.punch-line { display: flex; align-items: flex-start; gap: 5px; margin-bottom: 4px; }
.punch-line:last-child { margin-bottom: 0; }
.punch-dir {
    font-size: 9px; font-weight: 700;
    padding: 2px 5px; border-radius: 3px;
    flex-shrink: 0; letter-spacing: 0.5px; margin-top: 1px;
}
.dir-in  { background: #dff0d8; color: #3c763d; }
.dir-out { background: #f2dede; color: #a94442; }
.p-time  { font-size: 13px; font-weight: 700; color: #222; line-height: 1.2; }
.p-place { font-size: 11px; color: #555; word-break: break-word; }
.p-addr  { font-size: 10px; color: #aaa; word-break: break-word; }
.p-gps   { font-size: 10px; color: #ccc; font-family: monospace; }
.p-ref   { font-size: 10px; color: #3598dc; font-weight: 600; margin-top: 2px; }
.p-other { font-size: 10px; color: #aaa; font-style: italic; }
.punch-sep { border: none; border-top: 1px dashed #e5e5e5; margin: 6px 0; }
.no-punch  { color: #ccc; font-size: 18px; }
.out-pending { font-size: 11px; font-weight: 600; color: #e67e22; }
.missed-tag  { font-size: 10px; color: #c0392b; }

/* Duration */
.dur-val  { font-size: 14px; font-weight: 700; color: #333; }
.dur-lbl  { display: inline-block; font-size: 10px; color: #aaa; background: #f4f6f9; padding: 1px 6px; border-radius: 8px; margin-top: 2px; }
.open-lbl { font-size: 10px; font-weight: 600; color: #e67e22; }

/* Status badge */
.s-badge {
    display: inline-block; font-size: 10px; font-weight: 700;
    padding: 3px 9px; border-radius: 10px; white-space: nowrap;
}
.sb-present  { background: #dff0d8; color: #3c763d; }
.sb-half     { background: #fcf8e3; color: #8a6d3b; }
.sb-leave    { background: #d9edf7; color: #31708f; }
.sb-absent   { background: #f2dede; color: #a94442; }
.sb-notpunch { background: #fcf8e3; color: #c47d00; border: 1px solid #fde0a0; }
.sb-lwp      { background: #e8d5f5; color: #6b21a8; }
.sb-weekly   { background: #f4f6f9; color: #777; }
.sb-holiday  { background: #d4efeb; color: #0f766e; }
.sb-compoff  { background: #fef0e6; color: #c0392b; }
.sb-future   { background: #f4f6f9; color: #ccc; }

/* Update button */
.btn-att-update {
    display: inline-block;
    background: #f4f6f9;
    color: #3598dc;
    border: 1px solid #d5e8f5;
    border-radius: 3px;
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
    white-space: nowrap;
}
.btn-att-update:hover { background: #3598dc; color: #fff; border-color: #3598dc; }

/* Modal */
.modal-content { border-radius: 4px !important; }
.modal-header.att-mod-hdr {
    background: #2b3a4a;
    padding: 14px 20px;
    border-radius: 4px 4px 0 0;
}
.modal-header.att-mod-hdr .modal-title { color: #fff; font-size: 14px; font-weight: 700; }
.modal-header.att-mod-hdr .close { color: rgba(255,255,255,0.7); opacity: 1; text-shadow: none; }
.modal-header.att-mod-hdr .close:hover { color: #fff; }

/* Status options grid */
.st-grid { margin-bottom: 14px; }
.st-row  { margin-bottom: 6px; }
.st-option {
    display: block;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 8px 12px;
    cursor: pointer;
    background: #fafafa;
    transition: all 0.15s;
    position: relative;
}
.st-option:hover { border-color: #3598dc; background: #f0f7fd; }
.st-option.sel   { border-color: #3598dc; background: #e8f4fd; }
.st-option.sel-present  { border-color: #5cb85c; background: #dff0d8; }
.st-option.sel-half     { border-color: #f0ad4e; background: #fcf8e3; }
.st-option.sel-leave    { border-color: #5bc0de; background: #d9edf7; }
.st-option.sel-absent   { border-color: #d9534f; background: #f2dede; }
.st-option.sel-lwp      { border-color: #9b59b6; background: #e8d5f5; }
.st-option.sel-weekly   { border-color: #aaa;    background: #f4f6f9; }
.st-option-name { font-size: 12px; font-weight: 600; color: #333; }
.st-option.sel .st-option-name,
.st-option.sel-present .st-option-name { color: inherit; }

/* Quota picker */
.quota-picker {
    background: #eaf4fd;
    border: 1px solid #bcd8ee;
    border-radius: 4px;
    padding: 12px;
    margin-bottom: 12px;
}
.quota-picker-title {
    font-size: 10px; font-weight: 700; color: #31708f;
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.qi {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 9px 12px;
    margin-bottom: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: border-color 0.15s;
}
.qi:last-child { margin-bottom: 0; }
.qi:hover, .qi.sel { border-color: #3598dc; background: #f0f7fd; }
.qi.low { border-color: #f0ad4e; }
.qi.none{ border-color: #d9534f; opacity: 0.8; }
.qi-left { display: flex; align-items: center; gap: 8px; }
.qi-code { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 10px; }
.qi-name { font-size: 13px; font-weight: 600; color: #333; }
.qi-right { text-align: right; }
.qi-rem  { font-size: 16px; font-weight: 700; }
.qi-lbl  { font-size: 9px; color: #aaa; text-transform: uppercase; }
.qi-warn { font-size: 10px; font-weight: 600; }
.qi-radio{ margin-right: 4px; }

.m-err {
    background: #f2dede;
    border-left: 3px solid #d9534f;
    border-radius: 3px;
    padding: 9px 12px;
    font-size: 12px;
    color: #a94442;
    margin-top: 10px;
}

/* Empty state */
.att-empty {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    padding: 50px 20px;
    text-align: center;
    color: #ccc;
}
.att-empty i { font-size: 36px; display: block; margin-bottom: 10px; }
</style>

<div class="page-content-wrapper">
<div class="page-content att-page">

    <ul class="page-breadcrumb breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
        <li><span>Attendance Management</span></li>
    </ul>

    <div class="row" style="margin-bottom:10px;">
        <div class="col-md-12">
            <h3 style="margin:0 0 3px;font-size:18px;font-weight:700;color:#333;">Attendance Management</h3>
            <p style="margin:0;font-size:12px;color:#999;">View, verify and manage employee daily attendance records</p>
        </div>
    </div>

    {{-- ── FILTER (single row) ── --}}
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
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('M', mktime(0,0,0,$m,1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>Year</label>
                        <select name="year" class="form-control" id="sel-year">
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
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
                            @foreach($statusOptions as $st)
                                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm" style="padding:7px 18px;">
                                <i class="fa fa-search"></i> Apply
                            </button>
                            <a href="{{ url('admin/attendance') }}" class="btn btn-default btn-sm" style="padding:6px 12px;margin-left:5px;">
                                <i class="fa fa-times"></i> Reset
                            </a>
                            <button type="button" id="btn-collapse-all" class="btn btn-default btn-sm" style="padding:6px 10px;margin-left:5px;" title="Collapse All">
                                <i class="fa fa-compress"></i>
                            </button>
                            <button type="button" id="btn-expand-all" class="btn btn-default btn-sm" style="padding:6px 10px;margin-left:3px;" title="Expand All">
                                <i class="fa fa-expand"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Quick date shortcuts --}}
            <div class="date-pills">
                <span style="font-size:11px;color:#aaa;font-weight:600;margin-right:5px;">Jump to:</span>
                @php
                    $todayStr = \Carbon\Carbon::today()->toDateString();
                    $yestStr  = \Carbon\Carbon::yesterday()->toDateString();
                @endphp
                <a href="{{ url('admin/attendance') }}?month={{ $month }}&year={{ $year }}&date={{ $todayStr }}{{ request('employee_id') ? '&employee_id='.request('employee_id') : '' }}"
                   class="date-pill {{ $filterDate === $todayStr ? 'active' : '' }}">Today</a>
                <a href="{{ url('admin/attendance') }}?month={{ $month }}&year={{ $year }}&date={{ $yestStr }}{{ request('employee_id') ? '&employee_id='.request('employee_id') : '' }}"
                   class="date-pill {{ $filterDate === $yestStr ? 'active' : '' }}">Yesterday</a>
                <a href="{{ url('admin/attendance') }}?month={{ $month }}&year={{ $year }}&date={{ request('employee_id') ? '&employee_id='.request('employee_id') : '' }}"
                   class="date-pill {{ is_null($filterDate) ? 'active' : '' }}">Full Month</a>
            </div>
        </form>
    </div>

    {{-- ── STATS ── --}}
    @php
        $totalPresent = $employeeData->sum('present_count');
        $totalLeave   = $employeeData->sum('leave_count');
        $totalAbsent  = $employeeData->sum('absent_count');
        $totalLwp     = $employeeData->sum('lwp_count');
        $totalEmp     = $employeeData->count();
    @endphp
    <div class="row" style="margin-bottom:14px;">
        <div class="col-md-12">
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <div class="att-stat-box" style="min-width:120px;">
                    <div class="stat-icon" style="background:#3598dc;"><i class="fa fa-users"></i></div>
                    <div><div class="stat-label">Employees</div><div class="stat-value">{{ $totalEmp }}</div></div>
                </div>
                <div class="att-stat-box" style="min-width:120px;">
                    <div class="stat-icon" style="background:#26c281;"><i class="fa fa-check"></i></div>
                    <div><div class="stat-label">Present</div><div class="stat-value">{{ $totalPresent }}</div></div>
                </div>
                <div class="att-stat-box" style="min-width:120px;">
                    <div class="stat-icon" style="background:#5bc0de;"><i class="fa fa-calendar"></i></div>
                    <div><div class="stat-label">On Leave</div><div class="stat-value">{{ $totalLeave }}</div></div>
                </div>
                <div class="att-stat-box" style="min-width:120px;">
                    <div class="stat-icon" style="background:#e74c3c;"><i class="fa fa-times-circle"></i></div>
                    <div><div class="stat-label">Absent</div><div class="stat-value">{{ $totalAbsent }}</div></div>
                </div>
                <div class="att-stat-box" style="min-width:120px;">
                    <div class="stat-icon" style="background:#9b59b6;"><i class="fa fa-ban"></i></div>
                    <div><div class="stat-label">LWP</div><div class="stat-value">{{ $totalLwp }}</div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── EMPLOYEE BLOCKS ── --}}
    @forelse($employeeData as $empIdx => $empBlock)
    @php $emp = $empBlock['employee']; $empDates = $empBlock['dates']; @endphp

    <div class="emp-block" id="emp-block-{{ $emp->id }}">

        {{-- Accordion Header — DEFAULT CLOSED --}}
        <div class="emp-header" id="emp-hdr-{{ $emp->id }}"
             onclick="toggleEmp({{ $emp->id }})" data-open="0">
            <div style="display:flex;align-items:center;">
                <div class="emp-avatar">{{ strtoupper(substr($emp->name, 0, 1)) }}</div>
                <div>
                    <div class="emp-name">{{ $emp->name }}</div>
                    <div class="emp-sub">
                        {{ $emp->mobile ?? '' }}
                        @if($emp->base_city)
                            &nbsp;&bull;&nbsp;<i class="fa fa-map-marker"></i> {{ $emp->base_city }}
                        @endif
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;">
                <div class="emp-pills">
                    <span class="ep ep-p"><i class="fa fa-check"></i> {{ $empBlock['present_count'] }} Present</span>
                    <span class="ep ep-l"><i class="fa fa-calendar"></i> {{ $empBlock['leave_count'] }} Leave</span>
                    <span class="ep ep-a"><i class="fa fa-times"></i> {{ $empBlock['absent_count'] }} Absent</span>
                    @if($empBlock['lwp_count'] > 0)
                        <span class="ep ep-lw"><i class="fa fa-ban"></i> {{ $empBlock['lwp_count'] }} LWP</span>
                    @endif
                </div>
                <i class="fa fa-chevron-down acc-arrow" id="chev-{{ $emp->id }}"></i>
            </div>
        </div>

        {{-- Table — hidden by default --}}
        <div class="emp-body" id="emp-body-{{ $emp->id }}" style="display:none;">
            <table class="att-table">
                <colgroup>
                    <col class="col-bar">
                    <col class="col-date">
                    <col class="col-in">
                    <col class="col-out">
                    <col class="col-dur">
                    <col class="col-st">
                    <col class="col-act">
                </colgroup>
                <thead>
                    <tr>
                        <th></th>
                        <th>Date</th>
                        <th>IN Details</th>
                        <th>OUT Details</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($empDates as $day)
                @php
                    $st  = $day['status'];
                    $rec = $day['main_record'];
                    $recs= $day['records'];
                    $car = $day['carbon'];

                    $barCls = 'future';
                    if ($st === 'Full Day Present')          $barCls = 'present';
                    elseif ($st === '1/2 Present + 1/2 Leave')  $barCls = 'half';
                    elseif ($st === 'Allowed Full Day Leave')    $barCls = 'leave';
                    elseif ($st === 'Weekly Off')                $barCls = 'weekly';
                    elseif ($st === 'Holiday')                   $barCls = 'holiday';
                    elseif ($st === 'Comp Off')                  $barCls = 'compoff';
                    elseif ($st === 'Absent')                    $barCls = 'absent';
                    elseif ($st === 'Not Punched Yet')           $barCls = 'notpunch';
                    elseif (str_starts_with($st ?? '', 'LWP'))  $barCls = 'lwp';

                    $bdgCls = 'sb-future';
                    if ($st === 'Full Day Present')          $bdgCls = 'sb-present';
                    elseif ($st === '1/2 Present + 1/2 Leave')  $bdgCls = 'sb-half';
                    elseif ($st === 'Allowed Full Day Leave')    $bdgCls = 'sb-leave';
                    elseif ($st === 'Weekly Off')                $bdgCls = 'sb-weekly';
                    elseif ($st === 'Holiday')                   $bdgCls = 'sb-holiday';
                    elseif ($st === 'Comp Off')                  $bdgCls = 'sb-compoff';
                    elseif ($st === 'Absent')                    $bdgCls = 'sb-absent';
                    elseif ($st === 'Not Punched Yet')           $bdgCls = 'sb-notpunch';
                    elseif (str_starts_with($st ?? '', 'LWP'))  $bdgCls = 'sb-lwp';
                @endphp
                <tr id="row-{{ $emp->id }}-{{ $day['date'] }}">

                    {{-- Bar --}}
                    <td class="td-bar"><span class="s-bar {{ $barCls }}"></span></td>

                    {{-- Date --}}
                    <td class="td-date">
                        <div class="d-num">{{ $car->format('d') }}</div>
                        <div class="d-mon">{{ $car->format('M Y') }}</div>
                        <div class="d-dow">{{ $car->format('D') }}</div>
                        @if($day['is_today'])
                            <span class="day-tag tag-today">Today</span>
                        @elseif($day['is_sunday'])
                            <span class="day-tag tag-sun">Sunday</span>
                        @elseif($day['is_holiday'])
                            <span class="day-tag tag-hol" title="{{ $day['holiday_name'] }}">{{ \Illuminate\Support\Str::limit($day['holiday_name'], 10) }}</span>
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
                                        @if($r->in_place_of_attendance)
                                            <div class="p-place">{{ $r->in_place_of_attendance }}</div>
                                        @endif
                                        @if($r->in_latitude_longitude_address)
                                            <div class="p-addr">{{ \Illuminate\Support\Str::limit($r->in_latitude_longitude_address, 45) }}</div>
                                        @endif
                                        @if($r->in_latitude && $r->in_longitude)
                                            <div class="p-gps">{{ number_format($r->in_latitude,4) }}, {{ number_format($r->in_longitude,4) }}</div>
                                        @endif
                                        @if($r->in_customer_name ?? null)
                                            <div class="p-ref"><i class="fa fa-building-o"></i> {{ $r->in_customer_name }}</div>
                                        @elseif($r->in_crr_name ?? null)
                                            <div class="p-ref"><i class="fa fa-user-plus"></i> {{ $r->in_crr_name }}</div>
                                        @elseif($r->in_dealer_name ?? null)
                                            <div class="p-ref"><i class="fa fa-handshake-o"></i> {{ $r->in_dealer_name }}</div>
                                        @endif
                                        @if($r->in_other)
                                            <div class="p-other">{{ \Illuminate\Support\Str::limit($r->in_other, 35) }}</div>
                                        @endif
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
                                        @if($r->out_place_of_attendance)
                                            <div class="p-place">{{ $r->out_place_of_attendance }}</div>
                                        @endif
                                        @if($r->out_latitude_longitude_address)
                                            <div class="p-addr">{{ \Illuminate\Support\Str::limit($r->out_latitude_longitude_address, 45) }}</div>
                                        @endif
                                        @if($r->out_latitude && $r->out_longitude)
                                            <div class="p-gps">{{ number_format($r->out_latitude,4) }}, {{ number_format($r->out_longitude,4) }}</div>
                                        @endif
                                        @if($r->out_customer_name ?? null)
                                            <div class="p-ref"><i class="fa fa-building-o"></i> {{ $r->out_customer_name }}</div>
                                        @elseif($r->out_crr_name ?? null)
                                            <div class="p-ref"><i class="fa fa-user-plus"></i> {{ $r->out_crr_name }}</div>
                                        @elseif($r->out_dealer_name ?? null)
                                            <div class="p-ref"><i class="fa fa-handshake-o"></i> {{ $r->out_dealer_name }}</div>
                                        @endif
                                        @if($r->out_other)
                                            <div class="p-other">{{ \Illuminate\Support\Str::limit($r->out_other, 35) }}</div>
                                        @endif
                                    </div>
                                </div>
                                @elseif(!$r->missed)
                                    <div class="out-pending"><i class="fa fa-clock-o"></i> OUT Pending</div>
                                @else
                                    <div class="missed-tag"><i class="fa fa-exclamation-triangle"></i> Missed</div>
                                @endif
                            @endforeach
                        @else
                            <span class="no-punch">&mdash;</span>
                        @endif
                    </td>

                    {{-- Duration --}}
                    <td style="text-align:center;">
                        @if($day['duration'])
                            <div class="dur-val">{{ $day['duration'] }}</div>
                            <span class="dur-lbl">worked</span>
                        @elseif($day['is_open'])
                            <div class="open-lbl"><i class="fa fa-clock-o"></i> Open</div>
                        @else
                            <span style="color:#ddd;">&mdash;</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td id="scell-{{ $emp->id }}-{{ $day['date'] }}">
                        @if($st)
                            <span class="s-badge {{ $bdgCls }}" id="sbadge-{{ $emp->id }}-{{ $day['date'] }}">{{ $st }}</span>
                        @else
                            <span style="color:#ddd;">&mdash;</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td style="text-align:center;">
                        @if(!$day['is_future'])
                        <button class="btn-att-update btn-open-modal"
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
        <i class="fa fa-calendar-times-o"></i>
        <p style="font-size:13px;font-weight:500;">No attendance data found for the selected filters.</p>
    </div>
    @endforelse

</div>
</div>


{{-- ════════════════ STATUS MODAL ════════════════ --}}
<div class="modal fade" id="attModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:480px;max-width:95%;margin:60px auto;">
        <div class="modal-content">
            <div class="modal-header att-mod-hdr">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-calendar-check-o"></i>
                    Update Attendance &mdash; <span id="m-emp"></span>
                </h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="m-att-id">
                <input type="hidden" id="m-user-id">
                <input type="hidden" id="m-date">
                <input type="hidden" id="m-has-record">
                <input type="hidden" id="m-new-status">

                {{-- Info --}}
                <div style="background:#f9f9f9;border:1px solid #eee;border-radius:3px;padding:8px 12px;margin-bottom:14px;font-size:12px;">
                    <span style="color:#999;">Date:</span>
                    <strong id="m-date-lbl" style="color:#333;margin-left:4px;"></strong>
                    &nbsp;&nbsp;
                    <span style="color:#999;">Current:</span>
                    <span id="m-curr-st" style="font-weight:600;color:#3598dc;margin-left:4px;"></span>
                </div>

                {{-- Status options --}}
                <div style="font-size:10px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Select New Status</div>
                <div class="row st-grid">
                    @php
                    $stOpts = [
                        ['v'=>'Full Day Present',           'cls'=>'sel-present', 'icon'=>'fa-check-circle',   'label'=>'Full Day Present'],
                        ['v'=>'1/2 Present + 1/2 Leave',    'cls'=>'sel-half',    'icon'=>'fa-adjust',         'label'=>'1/2 Present + 1/2 Leave'],
                        ['v'=>'Allowed Full Day Leave',      'cls'=>'sel-leave',   'icon'=>'fa-calendar-check-o','label'=>'Allowed Full Day Leave'],
                        ['v'=>'Absent',                      'cls'=>'sel-absent',  'icon'=>'fa-times-circle',   'label'=>'Absent'],
                        ['v'=>'Weekly Off',                  'cls'=>'sel-weekly',  'icon'=>'fa-home',           'label'=>'Weekly Off'],
                        ['v'=>'Comp Off',                    'cls'=>'sel-weekly',  'icon'=>'fa-exchange',       'label'=>'Comp Off'],
                        ['v'=>'LWP (Uninformed Absence)',    'cls'=>'sel-lwp',     'icon'=>'fa-ban',            'label'=>'LWP – Uninformed'],
                        ['v'=>'LWP (Unapproved Leave)',      'cls'=>'sel-lwp',     'icon'=>'fa-ban',            'label'=>'LWP – Unapproved'],
                        ['v'=>'LWP (Leave in excess quota)', 'cls'=>'sel-lwp',     'icon'=>'fa-exclamation-circle','label'=>'LWP – Excess Quota'],
                    ];
                    @endphp
                    @foreach($stOpts as $opt)
                    <div class="col-md-4" style="margin-bottom:6px;">
                        <div class="st-option" data-v="{{ $opt['v'] }}" data-cls="{{ $opt['cls'] }}"
                             onclick="pickSt(this)">
                            <i class="fa {{ $opt['icon'] }}" style="margin-right:5px;"></i>
                            <span class="st-option-name">{{ $opt['label'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Quota picker --}}
                <div class="quota-picker" id="quotaBox" style="display:none;">
                    <div class="quota-picker-title">
                        <i class="fa fa-balance-scale"></i>
                        Select Leave Type &mdash; <span id="qp-deduct" style="font-weight:600;color:#31708f;"></span>
                    </div>
                    <div id="quotaList">
                        <div style="text-align:center;padding:16px;color:#ccc;">
                            <i class="fa fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>
                </div>

                {{-- Remarks --}}
                <div class="form-group">
                    <label style="font-size:10px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;">
                        <i class="fa fa-comment-o"></i> Admin Remarks <small style="text-transform:none;font-weight:400;">(optional)</small>
                    </label>
                    <textarea id="m-remarks" rows="2" class="form-control" style="font-size:13px;resize:none;"
                              placeholder="Reason for this change..."></textarea>
                </div>

                <div id="m-err" class="m-err" style="display:none;"></div>
            </div>
            <div class="modal-footer" style="background:#f9f9f9;">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancel
                </button>
                <button type="button" id="btnSaveAtt" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Status
                </button>
            </div>
        </div>
    </div>
</div>


<script>
var LEAVE_STS = ['Allowed Full Day Leave','1/2 Present + 1/2 Leave'];
var quotaCache = null;

/* ── Notify ── */
function attNotify(t, m) {
    if (typeof toastr !== 'undefined') { toastr[t](m); return; }
    var c = {success:'#26c281',error:'#e74c3c',warning:'#f39c12',info:'#3598dc'};
    var el = document.createElement('div');
    el.innerHTML = m;
    el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:'+(c[t]||'#3598dc')+';color:#fff;padding:10px 16px;border-radius:4px;font-size:13px;font-weight:600;box-shadow:0 2px 10px rgba(0,0,0,.2);max-width:300px;opacity:0;transition:opacity .3s;';
    document.body.appendChild(el);
    setTimeout(function(){ el.style.opacity='1'; }, 10);
    setTimeout(function(){ el.style.opacity='0'; setTimeout(function(){ el.parentNode && el.parentNode.removeChild(el); }, 350); }, 3500);
}

/* ── Accordion ── */
function toggleEmp(id) {
    var body = document.getElementById('emp-body-'+id);
    var hdr  = document.getElementById('emp-hdr-'+id);
    var chev = document.getElementById('chev-'+id);
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

document.getElementById('btn-collapse-all').addEventListener('click', function(){
    document.querySelectorAll('.emp-header').forEach(function(hdr){
        var id = hdr.id.replace('emp-hdr-','');
        if (hdr.getAttribute('data-open') === '1') toggleEmp(id);
    });
});
document.getElementById('btn-expand-all').addEventListener('click', function(){
    document.querySelectorAll('.emp-header').forEach(function(hdr){
        var id = hdr.id.replace('emp-hdr-','');
        if (hdr.getAttribute('data-open') === '0') toggleEmp(id);
    });
});

/* ── Status picker ── */
function pickSt(el) {
    document.querySelectorAll('.st-option').forEach(function(o){
        o.className = 'st-option';
    });
    el.classList.add(el.getAttribute('data-cls'));
    var val = el.getAttribute('data-v');
    document.getElementById('m-new-status').value = val;
    document.getElementById('m-err').style.display = 'none';

    var isLeave = LEAVE_STS.indexOf(val) !== -1;
    if (isLeave) {
        var half = (val === '1/2 Present + 1/2 Leave');
        document.getElementById('qp-deduct').textContent = half ? '0.5 day will be deducted' : '1.0 day will be deducted';
        document.getElementById('quotaBox').style.display = 'block';
        loadQuota();
    } else {
        document.getElementById('quotaBox').style.display = 'none';
    }
}

function loadQuota() {
    if (quotaCache) { renderQuota(quotaCache); return; }
    document.getElementById('quotaList').innerHTML = '<div style="text-align:center;padding:14px;color:#ccc;"><i class="fa fa-spinner fa-spin"></i></div>';
    $.ajax({
        url: '{{ url("admin/attendance/quota-info") }}',
        type: 'GET',
        data: {
            user_id:       document.getElementById('m-user-id').value,
            date:          document.getElementById('m-date').value,
            attendance_id: document.getElementById('m-att-id').value
        },
        success: function(r) {
            if (r.success) { quotaCache = r.quota; renderQuota(r.quota, r.existing_leave); }
        },
        error: function() {
            document.getElementById('quotaList').innerHTML = '<div style="color:#a94442;font-size:12px;padding:8px;">Failed to load quota.</div>';
        }
    });
}

function renderQuota(quota, existingLeave) {
    var val    = document.getElementById('m-new-status').value;
    var half   = (val === '1/2 Present + 1/2 Leave');
    var deduct = half ? 0.5 : 1.0;
    var exId   = existingLeave ? existingLeave.leave_type_id : null;
    var cmap   = {SL:'background:#f2dede;color:#a94442',CL:'background:#dff0d8;color:#3c763d',EL:'background:#d9edf7;color:#31708f',LWP:'background:#e8d5f5;color:#6b21a8'};
    var html   = '';

    quota.forEach(function(q) {
        var sel  = exId && exId == q.id;
        var low  = q.remaining > 0 && q.remaining < deduct;
        var none = q.remaining <= 0;
        var cls  = 'qi' + (sel ? ' sel' : '') + (low ? ' low' : '') + (none ? ' none' : '');
        var rc   = none ? '#d9534f' : (low ? '#f0ad4e' : '#26c281');
        var cs   = cmap[q.code] || 'background:#f4f6f9;color:#777';
        html += '<div class="'+cls+'" onclick="selQ(this,'+q.id+')">';
        html += '<div class="qi-left">';
        html += '<input type="radio" class="qi-radio" name="lt_radio" value="'+q.id+'"'+(sel?' checked':'')+' style="cursor:pointer;">';
        html += '<span class="qi-code" style="'+cs+'">'+q.code+'</span>';
        html += '<span class="qi-name">'+q.name+'</span>';
        html += '</div>';
        html += '<div class="qi-right">';
        html += '<div class="qi-rem" style="color:'+rc+';">'+q.remaining.toFixed(1)+'</div>';
        html += '<div class="qi-lbl">days left</div>';
        if (low)  html += '<div class="qi-warn" style="color:#f0ad4e;"><i class="fa fa-exclamation-triangle"></i> Low balance</div>';
        if (none) html += '<div class="qi-warn" style="color:#d9534f;"><i class="fa fa-times-circle"></i> No balance</div>';
        html += '</div></div>';
    });
    document.getElementById('quotaList').innerHTML = html || '<div style="color:#aaa;font-size:12px;padding:8px;">No leave types found.</div>';
}

function selQ(el, id) {
    document.querySelectorAll('.qi').forEach(function(i){ i.classList.remove('sel'); });
    el.classList.add('sel');
    var r = el.querySelector('input[type="radio"]');
    if (r) r.checked = true;
}

/* ── Open modal ── */
$(document).on('click', '.btn-open-modal', function(){
    var attId     = $(this).data('att-id');
    var userId    = $(this).data('user-id');
    var date      = $(this).data('date');
    var status    = $(this).data('status');
    var employee  = $(this).data('employee');
    var hasRecord = $(this).data('has-record');

    quotaCache = null;
    $('#m-att-id').val(attId);
    $('#m-user-id').val(userId);
    $('#m-date').val(date);
    $('#m-has-record').val(hasRecord);
    $('#m-new-status').val('');
    $('#m-remarks').val('');
    $('#m-err').hide();
    $('#quotaBox').hide();
    $('#m-emp').text(employee);
    $('#m-date-lbl').text(date);
    $('#m-curr-st').text(status || '—');

    document.querySelectorAll('.st-option').forEach(function(o){ o.className = 'st-option'; });

    var $cur = $('.st-option[data-v="'+status+'"]');
    if ($cur.length) pickSt($cur[0]);

    $('#attModal').modal('show');
});

/* ── Save ── */
$('#btnSaveAtt').on('click', function(){
    var attId     = $('#m-att-id').val();
    var userId    = $('#m-user-id').val();
    var date      = $('#m-date').val();
    var newStatus = $('#m-new-status').val();
    var remarks   = $('#m-remarks').val().trim();
    var hasRecord = $('#m-has-record').val();
    var $btn      = $(this);

    if (!newStatus) {
        $('#m-err').html('<i class="fa fa-exclamation-circle"></i> Please select a status.').show();
        return;
    }

    var isLeave = LEAVE_STS.indexOf(newStatus) !== -1;
    var leaveTypeId = null;
    if (isLeave) {
        var checked = document.querySelector('input[name="lt_radio"]:checked');
        if (!checked) {
            $('#m-err').html('<i class="fa fa-exclamation-circle"></i> Please select a leave type to deduct quota from.').show();
            return;
        }
        leaveTypeId = checked.value;
    }

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    $('#m-err').hide();

    var url  = hasRecord === '1'
        ? '/admin/attendance/' + attId + '/update-status'
        : '/admin/attendance/create-record';

    var data = { _token: '{{ csrf_token() }}', new_status: newStatus, leave_type_id: leaveTypeId, admin_remarks: remarks };
    if (hasRecord !== '1') { data.user_id = userId; data.date = date; }

    $.ajax({
        url: url, type: 'POST', data: data,
        success: function(r) {
            if (r.success) {
                // Update badge
                var bmap = {
                    'Full Day Present':'sb-present','1/2 Present + 1/2 Leave':'sb-half',
                    'Allowed Full Day Leave':'sb-leave','Weekly Off':'sb-weekly',
                    'Comp Off':'sb-compoff','Holiday':'sb-holiday','Absent':'sb-absent',
                    'Not Punched Yet':'sb-notpunch'
                };
                var bc  = bmap[newStatus] || 'sb-lwp';
                var key = userId + '-' + date;

                var badge = document.getElementById('sbadge-'+key);
                if (badge) { badge.className = 's-badge '+bc; badge.textContent = newStatus; }
                else {
                    var cell = document.getElementById('scell-'+key);
                    if (cell) cell.innerHTML = '<span class="s-badge '+bc+'" id="sbadge-'+key+'">'+newStatus+'</span>';
                }

                // Update bar
                var smap = {
                    'Full Day Present':'present','1/2 Present + 1/2 Leave':'half',
                    'Allowed Full Day Leave':'leave','Weekly Off':'weekly',
                    'Comp Off':'compoff','Holiday':'holiday','Absent':'absent'
                };
                var bar = document.querySelector('#row-'+key+' .s-bar');
                if (bar) bar.className = 's-bar ' + (smap[newStatus] || 'lwp');

                // Update button data
                var btn = document.querySelector('.btn-open-modal[data-user-id="'+userId+'"][data-date="'+date+'"]');
                if (btn) {
                    $(btn).data('status', newStatus).attr('data-status', newStatus);
                    if (hasRecord !== '1' && r.attendance_id) {
                        $(btn).data('att-id', r.attendance_id).attr('data-att-id', r.attendance_id);
                        $(btn).data('has-record', '1').attr('data-has-record', '1');
                    }
                }

                $('#attModal').modal('hide');
                attNotify('success', 'Status updated to "' + newStatus + '"');
            } else {
                $('#m-err').html('<i class="fa fa-exclamation-circle"></i> '+(r.message||'Failed.')).show();
            }
        },
        error: function(x) {
            var m = 'An error occurred.';
            if (x.responseJSON && x.responseJSON.message) m = x.responseJSON.message;
            if (x.responseJSON && x.responseJSON.errors)  m = Object.values(x.responseJSON.errors).flat().join('<br>');
            $('#m-err').html('<i class="fa fa-exclamation-circle"></i> '+m).show();
        },
        complete: function() { $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Status'); }
    });
});

/* ── Date input syncs month/year dropdowns ── */
document.getElementById('sel-date').addEventListener('change', function(){
    if (this.value) {
        var d = new Date(this.value);
        document.getElementById('sel-month').value = d.getMonth() + 1;
        document.getElementById('sel-year').value  = d.getFullYear();
    }
});
</script>
@endsection