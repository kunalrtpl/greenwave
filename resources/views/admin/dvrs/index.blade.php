@extends('layouts.adminLayout.backendLayout')


<style>
/* ═══════════════════════════════════════════
   DVR MODULE — PREMIUM STYLES
   Bootstrap 3 + Metronic compatible
═══════════════════════════════════════════ */

:root {
    --dvr-dark:    #1e293b;
    --dvr-accent:  #3b82f6;
    --dvr-green:   #10b981;
    --dvr-yellow:  #f59e0b;
    --dvr-red:     #ef4444;
    --dvr-cyan:    #06b6d4;
    --dvr-gray:    #6b7280;
    --dvr-light:   #f8fafc;
    --dvr-border:  #e2e8f0;
}

/* ─── Page Header ────────────────────────── */
.dvr-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 0 12px;
    border-bottom: 2px solid var(--dvr-border);
    margin-bottom: 20px;
}
.dvr-page-header h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: var(--dvr-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}
.dvr-page-header h2 .icon-wrap {
    width: 38px; height: 38px;
    background: var(--dvr-accent);
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}

/* ─── Filter Panel ───────────────────────── */
.dvr-filter-panel {
    background: #fff;
    border: 1px solid var(--dvr-border);
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.dvr-filter-panel .filter-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--dvr-dark);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.dvr-filter-panel .form-control {
    border-radius: 6px;
    border-color: var(--dvr-border);
    font-size: 13px;
    height: 36px;
}
.dvr-filter-panel label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 5px;
    display: block;
}
.btn-dvr-search {
    background: var(--dvr-accent);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 20px;
    font-size: 13px;
    font-weight: 600;
    height: 36px;
    line-height: 1;
}
.btn-dvr-search:hover { background: #2563eb; color:#fff; }
.btn-dvr-clear {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid var(--dvr-border);
    border-radius: 6px;
    padding: 7px 16px;
    font-size: 13px;
    height: 36px;
    line-height: 1;
}
.btn-dvr-clear:hover { background: #e2e8f0; color:#1e293b; }

/* ─── Summary Stat Cards ─────────────────── */
.dvr-stats-row { margin-bottom: 20px; }
.dvr-stat-card {
    background: #fff;
    border: 1px solid var(--dvr-border);
    border-radius: 10px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.dvr-stat-card .stat-icon {
    width: 46px; height: 46px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #fff; flex-shrink: 0;
}
.dvr-stat-card .stat-num   { font-size: 26px; font-weight: 800; color: var(--dvr-dark); line-height:1; }
.dvr-stat-card .stat-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 600; margin-top:2px; }

/* ─── Empty State ────────────────────────── */
.dvr-empty-state {
    text-align: center;
    padding: 70px 20px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid var(--dvr-border);
}
.dvr-empty-state .empty-icon { font-size: 64px; color: #cbd5e1; margin-bottom: 16px; }
.dvr-empty-state h4 { color: #64748b; font-weight: 600; margin: 0 0 8px; }
.dvr-empty-state p  { color: #94a3b8; font-size: 14px; margin: 0; }

/* ─── DVR Table ──────────────────────────── */
.dvr-table-wrap {
    background: #fff;
    border: 1px solid var(--dvr-border);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.07);
    margin-bottom: 24px;
}
.dvr-table-scroll { overflow-x: auto; }
.dvr-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
    color: var(--dvr-dark);
    min-width: 1200px;
}

/* THEAD */
.dvr-table thead tr th {
    background: var(--dvr-dark);
    color: #94a3b8;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .7px;
    padding: 11px 12px;
    border: none;
    white-space: nowrap;
    vertical-align: middle;
}
.dvr-table thead tr th:first-child { border-radius: 0; }

/* DATE GROUP HEADER */
.dvr-date-header td {
    background: #f1f5f9;
    border-top: 2px solid var(--dvr-border);
    border-bottom: 1px solid var(--dvr-border);
    padding: 0 !important;
}
.dvr-date-header-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 14px;
}
.dvr-date-pill {
    background: var(--dvr-dark);
    color: #fff;
    padding: 5px 13px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}
.dvr-day-badge {
    background: #e2e8f0;
    color: #475569;
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.dvr-date-count {
    margin-left: auto;
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
}
.dvr-date-att-inline {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 11px;
    color: #475569;
}
.dvr-date-att-inline .dot { width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:3px; }

/* DATA ROWS */
.dvr-table tbody tr.dvr-row td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.dvr-table tbody tr.dvr-row:hover td { background: #f8faff; }
.dvr-table tbody tr.dvr-row:last-child td { border-bottom: none; }

/* ─── Cell: S.No ─────────────────────────── */
.dvr-sno-circle {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: var(--dvr-dark);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

/* ─── Cell: Attendance ───────────────────── */
.dvr-att-block { font-size: 11.5px; line-height: 1.8; }
.dvr-att-block .att-row { display:flex; align-items:center; gap:5px; }
.dvr-att-block .dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
.dvr-att-block .att-time { font-weight: 700; color: var(--dvr-dark); }
.dvr-att-status {
    display: inline-block;
    margin-top: 4px;
    padding: 2px 9px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    color: #fff;
}

/* ─── Cell: Customer ─────────────────────── */
.dvr-customer-name {
    font-weight: 700;
    font-size: 13px;
    color: var(--dvr-dark);
    line-height: 1.3;
}
.dvr-customer-type {
    font-size: 10px;
    font-weight: 600;
    margin-top: 3px;
}

/* ─── Cell: Check-in/out ─────────────────── */
.dvr-time-block { font-size: 11.5px; line-height: 1.9; }
.dvr-time-block .dot { width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:4px; }
.dvr-time-block .t-val { font-weight: 700; color: var(--dvr-dark); }
.dvr-time-block .t-dur { font-size: 11px; color: #64748b; }

/* ─── Cell: Met With ─────────────────────── */
.dvr-contact-card {
    background: #f8faff;
    border: 1px solid #e0e9ff;
    border-radius: 7px;
    padding: 7px 10px;
    font-size: 11.5px;
    margin-bottom: 4px;
    line-height: 1.6;
}
.dvr-contact-card:last-child { margin-bottom: 0; }
.dvr-contact-card .c-name { font-weight: 700; color: var(--dvr-dark); }
.dvr-contact-card .c-desig { color: #64748b; font-size: 11px; }
.dvr-contact-card .c-mobile { color: #475569; font-size: 11px; }

/* ─── Cell: Purpose ──────────────────────── */
.purpose-chip {
    display: inline-block;
    background: var(--dvr-accent);
    color: #fff;
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 10.5px;
    font-weight: 600;
    margin: 2px 2px 2px 0;
    white-space: nowrap;
}

/* ─── Cell: Trials ───────────────────────── */
.dvr-trial-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px; height: 34px;
    border-radius: 50%;
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    background: #7c3aed;
    margin-bottom: 5px;
}
.dvr-trial-status {
    display: block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    color: #fff;
    margin-top: 3px;
    text-align: center;
}

/* ─── Cell: Products ─────────────────────── */
.product-pill {
    display: inline-block;
    background: #ede9fe;
    color: #6d28d9;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 10.5px;
    font-weight: 600;
    margin: 2px 2px 2px 0;
}

/* ─── Cell: Status Stack ─────────────────── */
.status-pill {
    display: block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 10.5px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 4px;
    text-align: center;
    white-space: nowrap;
}
.status-pill:last-child { margin-bottom: 0; }

/* ─── Cell: Action ───────────────────────── */
.btn-dvr-view {
    background: var(--dvr-accent);
    color: #fff;
    border: none;
    border-radius: 7px;
    padding: 7px 14px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}
.btn-dvr-view:hover { background: #2563eb; color:#fff; text-decoration:none; }

/* ─── Pagination ─────────────────────────── */
.dvr-pagination-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-top: 1px solid var(--dvr-border);
    background: #f8fafc;
}
.dvr-pagination-wrap .pg-info { font-size: 12px; color: #64748b; }
.dvr-pagination-wrap .pagination { margin: 0; }
.dvr-pagination-wrap .pagination > li > a,
.dvr-pagination-wrap .pagination > li > span {
    border-radius: 6px !important;
    margin: 0 2px;
    border-color: var(--dvr-border);
    color: var(--dvr-accent);
    font-size: 12px;
    padding: 5px 10px;
}
.dvr-pagination-wrap .pagination > .active > a,
.dvr-pagination-wrap .pagination > .active > span {
    background: var(--dvr-accent);
    border-color: var(--dvr-accent);
}

/* ─── Status Filter Tabs ─────────────────── */
.dvr-status-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
    align-items: center;
}
.dvr-status-tabs .stab {
    padding: 5px 13px;
    border-radius: 14px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid transparent;
    background: #f1f5f9;
    color: #475569;
    text-decoration: none;
    display: inline-block;
    transition: all .15s;
}
.dvr-status-tabs .stab:hover,
.dvr-status-tabs .stab.active {
    background: var(--dvr-dark);
    color: #fff;
    text-decoration: none;
}
.dvr-status-tabs .stab-group-label {
    font-size: 10px;
    text-transform: uppercase;
    font-weight: 700;
    color: #94a3b8;
    margin-right: 2px;
    margin-left: 8px;
    letter-spacing: .5px;
}
.dvr-status-tabs .stab-group-label:first-child { margin-left: 0; }

/* ─── Responsive Scrollbar ───────────────── */
.dvr-table-scroll::-webkit-scrollbar { height: 5px; }
.dvr-table-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
.dvr-table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
</style>

@section('content')
<div class="page-content-wrapper">
<div class="page-content">

{{-- ── BREADCRUMB ────────────────────────────────────── --}}
<div class="page-bar" style="margin-bottom:8px;">
    <ul class="page-breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-home"></i> Home</a><i class="fa fa-angle-right"></i></li>
        <li><a href="#">Daily Visit Reports</a></li>
    </ul>
</div>

{{-- ── PAGE HEADER ──────────────────────────────────── --}}
<div class="dvr-page-header">
    <h2>
        <span class="icon-wrap"><i class="fa fa-map-marker"></i></span>
        Daily Visit Reports
        @if(request('user_id') && isset($users))
            @php $selUser = $users->firstWhere('id', request('user_id')); @endphp
            @if($selUser)
            <span style="font-size:14px;font-weight:400;color:#64748b;margin-left:6px;">— {{ $selUser->name }}</span>
            @endif
        @endif
    </h2>
    <div style="font-size:12px;color:#94a3b8;">
        <i class="fa fa-calendar"></i> {{ now()->format('d M Y') }}
    </div>
</div>

{{-- ── FILTER PANEL ─────────────────────────────────── --}}
<div class="dvr-filter-panel">
    <div class="filter-title"><i class="fa fa-sliders"></i> Filters</div>
    <form method="GET" action="{{ url('admin/dvrs') }}" id="dvrForm">
        <div class="row" style="margin-bottom:12px;">
            {{-- Employee --}}
            <div class="col-md-3 col-sm-6">
                <label><i class="fa fa-user"></i> Employee <span style="color:#ef4444;">*</span></label>
                <select name="user_id" class="form-control select2" id="sel_user" required>
                    <option value="">— Select Employee —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Month --}}
            <div class="col-md-2 col-sm-4">
                <label><i class="fa fa-calendar"></i> Month</label>
                <select name="month" class="form-control">
                    <option value="">All Months</option>
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ request('month')==$m?'selected':'' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            {{-- Year --}}
            <div class="col-md-2 col-sm-4">
                <label><i class="fa fa-calendar-o"></i> Year</label>
                <select name="year" class="form-control">
                    <option value="">All Years</option>
                    @for($y=date('Y');$y>=2021;$y--)
                    <option value="{{ $y }}" {{ request('year')==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            {{-- Visit Type --}}
            <div class="col-md-2 col-sm-4">
                <label><i class="fa fa-tag"></i> Visit Type</label>
                <select name="visit_type" class="form-control">
                    <option value="">All Types</option>
                    <option value="Official"   {{ request('visit_type')=='Official'?'selected':'' }}>Official</option>
                    <option value="Unofficial" {{ request('visit_type')=='Unofficial'?'selected':'' }}>Unofficial</option>
                </select>
            </div>
            {{-- Buttons --}}
            <div class="col-md-3 col-sm-6" style="display:flex;align-items:flex-end;gap:8px;padding-bottom:1px;">
                <button type="submit" class="btn-dvr-search">
                    <i class="fa fa-search"></i> Search
                </button>
                @if(request('user_id'))
                <a href="{{ url('admin/dvrs') }}" class="btn-dvr-clear">
                    <i class="fa fa-times"></i> Clear
                </a>
                @endif
            </div>
        </div>

        {{-- Status Filter Tabs --}}
        @if(request('user_id'))
        <div style="border-top:1px solid var(--dvr-border);padding-top:10px;">
            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:8px;">
                <i class="fa fa-filter"></i> Filter by Status
            </div>
            <div class="dvr-status-tabs">
                <a href="{{ request()->fullUrlWithQuery(['status_filter'=>'','page'=>1]) }}"
                   class="stab {{ !request('status_filter') ? 'active' : '' }}">All</a>

                @foreach($statusFilters as $group => $labels)
                    @foreach($labels as $lbl)
                    <a href="{{ request()->fullUrlWithQuery(['status_filter'=>$lbl,'page'=>1]) }}"
                       class="stab {{ request('status_filter')==$lbl ? 'active' : '' }}">
                        {{ $lbl }}
                    </a>
                    @endforeach
                @endforeach
            </div>
            <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
        </div>
        @endif
    </form>
</div>

{{-- ══════════════════════════════════════════════════
     NO EMPLOYEE SELECTED
═══════════════════════════════════════════════════ --}}
@if(!request('user_id'))
<div class="dvr-empty-state">
    <div class="empty-icon"><i class="fa fa-user-circle-o"></i></div>
    <h4>Select an Employee to View DVRs</h4>
    <p>Choose an employee from the filter above to load their Daily Visit Reports.</p>
</div>

@elseif(isset($groupedDvrs) && $groupedDvrs->isEmpty())
<div class="dvr-empty-state">
    <div class="empty-icon"><i class="fa fa-search"></i></div>
    <h4>No DVRs Found</h4>
    <p>No records match your current filters. Try adjusting your search.</p>
</div>

@else

{{-- ── SUMMARY STATS ───────────────────────────────── --}}
@if($summaryStats)
<div class="row dvr-stats-row">
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="dvr-stat-card">
            <div class="stat-icon" style="background:#3b82f6;"><i class="fa fa-calendar-check-o"></i></div>
            <div><div class="stat-num">{{ $summaryStats['total'] }}</div><div class="stat-label">Total DVRs</div></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="dvr-stat-card">
            <div class="stat-icon" style="background:#10b981;"><i class="fa fa-check-circle"></i></div>
            <div><div class="stat-num">{{ $summaryStats['verified'] }}</div><div class="stat-label">Verified</div></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="dvr-stat-card">
            <div class="stat-icon" style="background:#f59e0b;"><i class="fa fa-clock-o"></i></div>
            <div><div class="stat-num">{{ $summaryStats['pending'] }}</div><div class="stat-label">Pending</div></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="dvr-stat-card">
            <div class="stat-icon" style="background:#06b6d4;"><i class="fa fa-handshake-o"></i></div>
            <div><div class="stat-num">{{ $summaryStats['metCount'] }}</div><div class="stat-label">Meetings</div></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="dvr-stat-card">
            <div class="stat-icon" style="background:#7c3aed;"><i class="fa fa-flask"></i></div>
            <div><div class="stat-num">{{ $summaryStats['trialsSum'] }}</div><div class="stat-label">Total Trials</div></div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="dvr-stat-card">
            <div class="stat-icon" style="background:#10b981;"><i class="fa fa-id-card-o"></i></div>
            <div><div class="stat-num">{{ $summaryStats['presentDays'] }}</div><div class="stat-label">Present Days</div></div>
        </div>
    </div>
</div>
@endif

{{-- ── DVR TABLE ──────────────────────────────────── --}}
<div class="dvr-table-wrap">
    <div class="dvr-table-scroll">
    <table class="dvr-table">
        <thead>
            <tr>
                <th style="width:90px;">Date / Day</th>
                <th style="width:155px;">Attendance</th>
                <th style="width:46px;text-align:center;">S.No.</th>
                <th style="width:165px;">Customer</th>
                <th style="width:130px;">Check-In / Check-Out<br><span style="font-size:9px;opacity:.7;">Meeting Time</span></th>
                <th style="width:170px;">Met With</th>
                <th style="min-width:160px;">Purpose of Visit</th>
                <th style="width:110px;text-align:center;">Trials</th>
                <th style="width:150px;">Products</th>
                <th style="width:148px;">Status</th>
                <th style="width:80px;text-align:center;">Attach.</th>
                <th style="width:80px;text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($groupedDvrs as $dateKey => $dateDvrs)
        @php
            $firstRow  = $dateDvrs->first();
            $dateDisp  = $firstRow['dvr_date_display'];
            $dayName   = $firstRow['day_name'];
            $att       = $firstRow['attendance'];
            $dayCount  = $dateDvrs->count();
        @endphp

        {{-- DATE GROUP HEADER ROW --}}
        <tr class="dvr-date-header">
            <td colspan="12">
                <div class="dvr-date-header-inner">
                    <span class="dvr-date-pill">{{ $dateDisp }}</span>
                    <span class="dvr-day-badge">{{ $dayName }}</span>

                    {{-- Attendance summary inline --}}
                    @if($att['exists'])
                    <div class="dvr-date-att-inline">
                        <span><span class="dot" style="background:#10b981;"></span> In: <strong>{{ $att['in_time'] ?? '—' }}</strong></span>
                        <span><span class="dot" style="background:#ef4444;"></span> Out: <strong>{{ $att['out_time'] ?? '—' }}</strong></span>
                        <span><i class="fa fa-clock-o"></i> {{ $att['work_hours'] }}</span>
                        <span style="background:{{ $att['status_color'] }};color:#fff;padding:2px 9px;border-radius:10px;font-size:10px;font-weight:700;">{{ $att['status'] }}</span>
                    </div>
                    @else
                    <span style="background:#e2e8f0;color:#64748b;padding:2px 9px;border-radius:10px;font-size:10px;font-weight:700;">No Attendance Record</span>
                    @endif

                    <span class="dvr-date-count">{{ $dayCount }} visit{{ $dayCount > 1 ? 's' : '' }}</span>
                </div>
            </td>
        </tr>

        {{-- DVR DATA ROWS FOR THIS DATE --}}
        @foreach($dateDvrs as $idx => $row)
        <tr class="dvr-row">

            {{-- DATE (hidden on sub-rows — date is in header) --}}
            <td style="text-align:center; color:#94a3b8; font-size:11px;">
                @if($idx === 0)
                <span style="color:#94a3b8;font-size:11px;">↑ above</span>
                @else
                <span style="color:#cbd5e1;">—</span>
                @endif
            </td>

            {{-- ATTENDANCE --}}
            <td>
                @if($att['exists'])
                <div class="dvr-att-block">
                    <div class="att-row">
                        <span class="dot" style="background:#10b981;"></span>
                        <span>In:</span>
                        <span class="att-time">{{ $att['in_time'] ?? '—' }}</span>
                    </div>
                    <div class="att-row">
                        <span class="dot" style="background:#ef4444;"></span>
                        <span>Out:</span>
                        <span class="att-time">{{ $att['out_time'] ?? '—' }}</span>
                    </div>
                    <div class="att-row" style="color:#64748b;">
                        <i class="fa fa-clock-o" style="font-size:10px;"></i>
                        <span style="font-size:11px;">{{ $att['work_hours'] }}</span>
                    </div>
                    <span class="dvr-att-status" style="background:{{ $att['status_color'] }};">{{ $att['status'] }}</span>
                </div>
                @else
                <span style="background:#e2e8f0;color:#64748b;padding:3px 9px;border-radius:10px;font-size:10px;font-weight:700;display:inline-block;">No Record</span>
                @endif
            </td>

            {{-- S.NO --}}
            <td style="text-align:center;">
                <span class="dvr-sno-circle">{{ $idx + 1 }}</span>
            </td>

            {{-- CUSTOMER --}}
            <td>
                <div class="dvr-customer-name">{{ $row['customer_name'] }}</div>
                @if($row['customer_type'] === 'customer')
                <div class="dvr-customer-type" style="color:#10b981;">
                    <i class="fa fa-building-o"></i> Registered
                </div>
                @elseif($row['customer_type'] === 'request')
                <div class="dvr-customer-type" style="color:#f59e0b;">
                    <i class="fa fa-user-plus"></i> Register Request
                </div>
                @endif
            </td>

            {{-- CHECK-IN / CHECK-OUT --}}
            <td>
                <div class="dvr-time-block">
                    @if($row['check_in'])
                    <div>
                        <span class="dot" style="background:#10b981;"></span>
                        In: <span class="t-val">{{ $row['check_in'] }}</span>
                    </div>
                    @endif
                    @if($row['check_out'])
                    <div>
                        <span class="dot" style="background:#ef4444;"></span>
                        Out: <span class="t-val">{{ $row['check_out'] }}</span>
                    </div>
                    @endif
                    @if($row['meeting_duration'])
                    <div class="t-dur"><i class="fa fa-clock-o"></i> {{ $row['meeting_duration'] }}</div>
                    @endif
                </div>
            </td>

            {{-- MET WITH --}}
            <td>
                @forelse($row['contacts'] as $contact)
                <div class="dvr-contact-card">
                    <div class="c-name">{{ $contact['name'] }}</div>
                    @if($contact['designation'])
                    <div class="c-desig">{{ $contact['designation'] }}</div>
                    @endif
                    @if($contact['mobile'])
                    <div class="c-mobile"><i class="fa fa-phone"></i> {{ $contact['mobile'] }}</div>
                    @endif
                </div>
                @empty
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endforelse
            </td>

            {{-- PURPOSE OF VISIT --}}
            <td>
                @forelse($row['purposes'] as $p)
                <span class="purpose-chip">{{ $p }}</span>
                @empty
                <span style="color:#cbd5e1;">—</span>
                @endforelse
                @if($row['other_purpose'])
                <div style="font-size:10.5px;color:#64748b;margin-top:4px;">
                    <i class="fa fa-comment-o"></i> {{ Str::limit($row['other_purpose'], 55) }}
                </div>
                @endif
            </td>

            {{-- TRIALS --}}
            <td style="text-align:center;">
                @if($row['trials_count'] > 0)
                <div class="dvr-trial-badge">{{ $row['trials_count'] }}</div>
                @foreach($row['trial_rows'] as $tr)
                <span class="dvr-trial-status" style="background:{{ $tr['color'] }};">
                    {{ $tr['label'] }}
                </span>
                @endforeach
                @else
                <span style="background:#e2e8f0;color:#6b7280;padding:4px 10px;border-radius:10px;font-size:10px;font-weight:700;display:inline-block;">No Trials</span>
                @endif
            </td>

            {{-- PRODUCTS --}}
            <td>
                @forelse($row['products'] as $prod)
                <span class="product-pill">{{ $prod }}</span>
                @empty
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endforelse
            </td>

            {{-- STATUS --}}
            <td>
                @foreach($row['statuses'] as $s)
                <span class="status-pill" style="background:{{ $s['color'] }};">{{ $s['label'] }}</span>
                @endforeach
            </td>

            {{-- ATTACHMENTS --}}
            <td style="text-align:center;">
                @if($row['attachments_count'] > 0)
                <span style="background:#7c3aed;color:#fff;padding:4px 10px;border-radius:10px;font-size:11px;font-weight:700;display:inline-block;">
                    {{ $row['attachments_count'] }} <i class="fa fa-paperclip"></i>
                </span>
                @else
                <span style="color:#cbd5e1;">—</span>
                @endif
            </td>

            {{-- ACTION --}}
            <td style="text-align:center;">
                <a href="{{ url('admin/dvrs/'.$row['id']) }}" class="btn-dvr-view">
                    <i class="fa fa-eye"></i> View
                </a>
            </td>
        </tr>
        @endforeach
        @endforeach
        </tbody>
    </table>
    </div>

    {{-- PAGINATION --}}
    @if($paginator && $paginator->hasPages())
    <div class="dvr-pagination-wrap">
        <div class="pg-info">
            Showing {{ $paginator->firstItem() }} – {{ $paginator->lastItem() }} of {{ $paginator->total() }} records
        </div>
        <div>{{ $paginator->links() }}</div>
    </div>
    @endif
</div>

@endif {{-- end groupedDvrs --}}

</div>
</div>
<script>
$(function(){
    if ($.fn.select2) {
        $('#sel_user').select2({ placeholder: '— Select Employee —', allowClear: true });
    }
    // Status tab clicks update hidden input & submit
    $('.stab[href]').on('click', function(e){
        e.preventDefault();
        var val = (new URLSearchParams($(this).attr('href').split('?')[1])).get('status_filter') || '';
        $('input[name="status_filter"]').val(val);
        $('#dvrForm').submit();
    });
});
</script>
@endsection