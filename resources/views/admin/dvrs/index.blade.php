@extends('layouts.adminLayout.backendLayout')

@section('content')

<style>
/* ═══════════════════════════════════════
   DVR MODULE v4 — inline styles
   Status: Blue + Red only
═══════════════════════════════════════ */
:root{
    --blue:#3b82f6;--red:#ef4444;--dark:#1e293b;
    --border:#e2e8f0;--white:#fff;--muted:#64748b;--bg:#f8fafc;
}
*{box-sizing:border-box;}
.dvr-wrap{padding:0 0 40px;}

/* Breadcrumb */
.dvr-bc{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);margin-bottom:12px;}
.dvr-bc a{color:var(--blue);text-decoration:none;}
.dvr-bc a:hover{text-decoration:underline;}
.dvr-bc .sep{color:#cbd5e1;}

/* Title row */
.dvr-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;}
.dvr-title h2{margin:0;font-size:20px;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:10px;}
.dvr-title h2 .ti{width:36px;height:36px;border-radius:9px;background:var(--blue);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:15px;}
.dvr-title h2 .ts{font-size:14px;font-weight:400;color:var(--muted);}
.dvr-title .td{font-size:12px;color:var(--muted);}

/* Filter card */
.dvr-fc{background:var(--white);border:1px solid var(--border);border-radius:10px;padding:16px 18px;margin-bottom:16px;box-shadow:0 1px 4px rgba(0,0,0,.05);}
.dvr-fc .fc-head{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:12px;display:flex;align-items:center;gap:6px;}
.dvr-fc label{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);margin-bottom:4px;display:block;}
.dvr-fc .form-control{border-radius:7px;border:1.5px solid var(--border);font-size:13px;height:36px;color:var(--dark);}
.dvr-fc .form-control:focus{border-color:var(--blue);outline:none;box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.btn-s{background:var(--blue);color:#fff;border:none;border-radius:7px;padding:0 20px;font-size:13px;font-weight:700;height:36px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;}
.btn-s:hover{background:#2563eb;color:#fff;}
.btn-c{background:#f1f5f9;color:var(--muted);border:1.5px solid var(--border);border-radius:7px;padding:0 14px;font-size:13px;height:36px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;}
.btn-c:hover{background:#e2e8f0;color:var(--dark);text-decoration:none;}
.btn-p{background:#ef4444;color:#fff;border:none;border-radius:7px;padding:0 16px;font-size:13px;font-weight:700;height:36px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.btn-p:hover{background:#dc2626;color:#fff;text-decoration:none;}

/* Divider inside filter */
.fc-div{border-top:1px solid var(--border);margin:12px 0;}

/* Status tabs */
.stab-lbl{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:6px;}
.stabs{display:flex;flex-wrap:wrap;gap:5px;}
.stab{padding:4px 12px;border-radius:14px;font-size:11px;font-weight:600;background:#f1f5f9;color:var(--muted);cursor:pointer;border:1.5px solid transparent;transition:all .15s;display:inline-block;text-decoration:none;}
.stab:hover,.stab.on{background:var(--dark);color:#fff;text-decoration:none;}

/* ── Customer selector row ──────────────── */
.cust-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.cust-all-btn{height:40px;padding:0 16px;border-radius:9px;font-size:12px;font-weight:700;background:#f1f5f9;color:var(--muted);border:1.5px solid var(--border);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all .15s;white-space:nowrap;}
.cust-all-btn.on{background:var(--dark);color:#fff;border-color:var(--dark);}
.cust-all-btn:hover{background:var(--dark);color:#fff;text-decoration:none;}
/* Custom dropdown */
.cust-dd-wrap{position:relative;display:inline-block;}
.cust-dd-trigger{height:40px;padding:0 40px 0 14px;border-radius:9px;border:1.5px solid var(--border);background:var(--white);font-size:13px;font-weight:600;color:var(--dark);cursor:pointer;display:inline-flex;align-items:center;gap:8px;min-width:240px;transition:all .15s;position:relative;white-space:nowrap;overflow:hidden;user-select:none;}
.cust-dd-trigger:hover{border-color:#94a3b8;}
.cust-dd-trigger.open{border-color:var(--blue);box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.cust-dd-trigger.selected{border-color:#22c55e;background:#f0fdf4;color:#166534;}
.cust-dd-trigger .dd-ico{width:26px;height:26px;border-radius:7px;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;font-size:12px;color:var(--muted);flex-shrink:0;}
.cust-dd-trigger.selected .dd-ico{background:#dcfce7;color:#16a34a;}
.cust-dd-trigger .dd-lbl{flex:1;overflow:hidden;text-overflow:ellipsis;}
.cust-dd-trigger .dd-arr{position:absolute;right:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:10px;transition:transform .2s;pointer-events:none;}
.cust-dd-trigger.open .dd-arr{transform:translateY(-50%) rotate(180deg);}
.cust-dd-trigger .dd-x{position:absolute;right:32px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:11px;cursor:pointer;display:none;width:18px;height:18px;border-radius:50%;background:#e2e8f0;align-items:center;justify-content:center;}
.cust-dd-trigger.selected .dd-x{display:inline-flex;}
.cust-dd-trigger.selected .dd-arr{right:12px;}
.cust-dd-panel{position:absolute;top:calc(100% + 6px);left:0;background:var(--white);border:1.5px solid var(--border);border-radius:10px;box-shadow:0 10px 40px rgba(0,0,0,.13);z-index:9999;min-width:340px;max-width:460px;display:none;overflow:hidden;}
.cust-dd-panel.show{display:block;}
.cust-dd-search{padding:10px 12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;background:#f8fafc;}
.cust-dd-search input{flex:1;border:none;outline:none;font-size:13px;color:var(--dark);background:transparent;}
.cust-dd-list{max-height:240px;overflow-y:auto;}
.cust-dd-list::-webkit-scrollbar{width:3px;}
.cust-dd-list::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:2px;}
.cust-dd-item{display:flex;align-items:center;justify-content:space-between;padding:9px 14px;cursor:pointer;transition:background .1s;border-bottom:1px solid #f8fafc;}
.cust-dd-item:last-child{border-bottom:none;}
.cust-dd-item:hover{background:#f0f9ff;}
.cust-dd-item.active{background:#f0fdf4;}
.di-name{font-size:13px;font-weight:600;color:var(--dark);flex:1;}
.cust-dd-item.active .di-name{color:#166534;}
.di-cnt{font-size:11px;font-weight:700;background:#f1f5f9;color:var(--muted);padding:2px 8px;border-radius:8px;flex-shrink:0;}
.cust-dd-item.active .di-cnt{background:#dcfce7;color:#166534;}
.cust-dd-empty{padding:20px;text-align:center;color:#94a3b8;font-size:13px;}

/* ── Customer stat panel ────────────────── */
.cust-stat-panel{
    background:linear-gradient(135deg,#1e293b 0%,#334155 100%);
    border-radius:10px;padding:18px 22px;margin-bottom:16px;
    display:flex;align-items:center;gap:0;flex-wrap:wrap;
    box-shadow:0 4px 16px rgba(30,41,59,.18);
}
.csp-left{flex:1;min-width:200px;padding-right:24px;}
.csp-left .csp-name{font-size:18px;font-weight:800;color:#fff;margin-bottom:4px;line-height:1.2;}
.csp-left .csp-sub{font-size:12px;color:#94a3b8;}
.csp-divider{width:1px;background:rgba(255,255,255,.12);margin:0 24px;align-self:stretch;flex-shrink:0;}
.csp-block{text-align:center;padding:0 20px;flex-shrink:0;}
.csp-block .big-num{font-size:42px;font-weight:900;line-height:1;letter-spacing:-1px;}
.csp-block .big-frac{font-size:14px;font-weight:600;color:rgba(255,255,255,.55);margin-top:2px;}
.csp-block .big-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:rgba(255,255,255,.45);margin-top:6px;}
.csp-block.visits .big-num{color:#4ade80;}
.csp-block.time .big-num{color:#34d399;}
.csp-bar-wrap{margin-top:8px;background:rgba(255,255,255,.12);border-radius:4px;height:6px;width:100px;margin-left:auto;margin-right:auto;}
.csp-bar{height:6px;border-radius:4px;}
.csp-block.visits .csp-bar{background:#4ade80;}
.csp-block.time .csp-bar{background:#34d399;}

/* Summary stat cards */
.dvr-stats{margin-bottom:16px;}
.stat-card{background:var(--white);border:1px solid var(--border);border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:12px;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.stat-card .si{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0;}
.stat-card .sn{font-size:24px;font-weight:800;color:var(--dark);line-height:1;}
.stat-card .sl{font-size:10px;font-weight:700;text-transform:uppercase;color:var(--muted);margin-top:2px;letter-spacing:.4px;}

/* DVR table wrap */
.dvr-tw{background:var(--white);border:1px solid var(--border);border-radius:10px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.06);margin-bottom:20px;}
.dvr-sc{overflow-x:auto;}
.dvr-sc::-webkit-scrollbar{height:4px;}
.dvr-sc::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:2px;}
.dvr-t{width:100%;border-collapse:collapse;font-size:12.5px;color:var(--dark);min-width:1350px;}
.dvr-t thead tr th{background:var(--dark);color:#94a3b8;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;padding:11px 11px;border:none;white-space:nowrap;vertical-align:middle;}

/* Date group header */
.dgr td{background:#f8fafc;border-top:2px solid var(--border);border-bottom:1px solid var(--border);padding:0!important;}
.dgi{display:flex;align-items:center;gap:10px;padding:8px 14px;flex-wrap:wrap;}
.dgd{background:var(--dark);color:#fff;padding:5px 14px;border-radius:20px;font-size:13px;font-weight:800;white-space:nowrap;}
.dgw{background:#e2e8f0;color:#475569;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;}
.dgn{background:var(--blue);color:#fff;padding:3px 12px;border-radius:10px;font-size:12px;font-weight:800;}
.dga{display:flex;align-items:center;gap:10px;font-size:11px;color:var(--muted);}
.dot8{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:2px;}
.att-pill{padding:2px 9px;border-radius:10px;font-size:10px;font-weight:800;color:#fff;}

/* Data rows */
.dvr-t tbody tr.dr td{padding:10px 11px;border-bottom:1px solid #f1f5f9;vertical-align:top;}
.dvr-t tbody tr.dr:hover td{background:#f8fbff;}
.dvr-t tbody tr.dr:last-child td{border-bottom:none;}

/* S.No */
.sno{width:30px;height:30px;border-radius:50%;background:var(--dark);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;}

/* Customer cell */
.cn{font-weight:800;font-size:13px;color:var(--dark);}
.ct{font-size:10px;font-weight:700;margin-top:2px;}
.pchip{display:inline-block;background:#eff6ff;color:var(--blue);padding:2px 7px;border-radius:8px;font-size:10px;font-weight:600;margin:2px 2px 0 0;}

/* Time block */
.tb{font-size:11.5px;line-height:1.9;}
.tv{font-weight:700;}
.tdur{font-size:11px;color:var(--muted);}

/* Contact card */
.cc{background:#f8faff;border:1px solid #dbeafe;border-radius:7px;padding:7px 9px;margin-bottom:4px;font-size:11.5px;line-height:1.6;}
.cc:last-child{margin-bottom:0;}
.ccn{font-weight:700;color:var(--dark);}
.ccs{color:var(--muted);font-size:11px;}

/* Visit detail */
.vdt{font-size:11.5px;line-height:1.5;max-height:80px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;}

/* Scheduler */
.scc{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:7px 9px;font-size:11.5px;line-height:1.7;}
.scd{font-weight:800;color:#166534;font-size:12px;}
.scs{color:#15803d;font-size:11px;margin-top:2px;}

/* Trial */
.tbadge{width:32px;height:32px;border-radius:50%;background:#7c3aed;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;margin-bottom:4px;}
.tpill{display:block;padding:3px 8px;border-radius:10px;font-size:10px;font-weight:700;color:#fff;margin-top:2px;text-align:center;}

/* Product */
.ppill{display:inline-block;background:#ede9fe;color:#6d28d9;padding:2px 9px;border-radius:10px;font-size:10.5px;font-weight:600;margin:2px 2px 0 0;}

/* Status pill */
.sp{display:block;padding:4px 10px;border-radius:12px;font-size:10.5px;font-weight:700;color:#fff;margin-bottom:3px;text-align:center;white-space:nowrap;}
.sp:last-child{margin-bottom:0;}

/* Attach */
.abadge{background:#7c3aed;color:#fff;padding:3px 9px;border-radius:10px;font-size:11px;font-weight:700;}

/* Action */
.btn-v{background:var(--blue);color:#fff;border:none;border-radius:7px;padding:6px 13px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;text-decoration:none;white-space:nowrap;}
.btn-v:hover{background:#2563eb;color:#fff;text-decoration:none;}

/* Pagination */
.dvr-pg{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid var(--border);background:var(--bg);}
.dvr-pg .pgi{font-size:12px;color:var(--muted);}
.dvr-pg .pagination{margin:0;}
.dvr-pg .pagination>li>a,.dvr-pg .pagination>li>span{border-radius:6px!important;margin:0 2px;border-color:var(--border);color:var(--blue);font-size:12px;padding:5px 10px;}
.dvr-pg .pagination>.active>a,.dvr-pg .pagination>.active>span{background:var(--blue);border-color:var(--blue);color:#fff;}

/* Empty */
.dvr-empty{text-align:center;padding:70px 20px;background:var(--white);border:1px solid var(--border);border-radius:10px;}
.dvr-empty .ei{font-size:60px;color:#cbd5e1;margin-bottom:14px;}
.dvr-empty h4{color:var(--muted);font-weight:600;margin:0 0 6px;}
.dvr-empty p{color:#94a3b8;font-size:13px;margin:0;}
</style>

<div class="page-content-wrapper">
<div class="page-content">
<div class="dvr-wrap">

{{-- BREADCRUMB --}}
<div class="dvr-bc">
    <a href="{{ url('admin/dashboard') }}"><i class="fa fa-home"></i> Home</a>
    <span class="sep"><i class="fa fa-angle-right"></i></span>
    <span>Daily Visit Reports</span>
</div>

{{-- TITLE --}}
<div class="dvr-title">
    <h2>
        <span class="ti"><i class="fa fa-map-marker"></i></span>
        Daily Visit Reports
        @if(request('user_id') && isset($users))
            @php $selUser = $users->firstWhere('id', request('user_id')); @endphp
            @if($selUser)<span class="ts">— {{ $selUser->name }}</span>@endif
        @endif
    </h2>
    <div class="td"><i class="fa fa-calendar"></i> {{ now()->format('d M Y') }}</div>
</div>

{{-- FILTER PANEL --}}
<div class="dvr-fc">
    <div class="fc-head"><i class="fa fa-sliders"></i> Filters</div>
    <form method="GET" action="{{ url('admin/dvrs') }}" id="dvrForm">

        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-3 col-sm-6" style="margin-bottom:8px;">
                <label><i class="fa fa-user"></i> Employee <span style="color:var(--red);">*</span></label>
                <select name="user_id" class="form-control select2" id="sel_user">
                    <option value="">— Select Employee —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-4" style="margin-bottom:8px;">
                <label><i class="fa fa-calendar"></i> Month</label>
                <select name="month" class="form-control">
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ $currentMonth==$m?'selected':'' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 col-sm-4" style="margin-bottom:8px;">
                <label><i class="fa fa-calendar-o"></i> Year</label>
                <select name="year" class="form-control">
                    @for($y=date('Y');$y>=2021;$y--)
                    <option value="{{ $y }}" {{ $currentYear==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 col-sm-4" style="margin-bottom:8px;">
                <label><i class="fa fa-tag"></i> Visit Type</label>
                <select name="visit_type" class="form-control">
                    <option value="">All Types</option>
                    <option value="Official" {{ request('visit_type')=='Official'?'selected':'' }}>Official</option>
                    <option value="Unofficial" {{ request('visit_type')=='Unofficial'?'selected':'' }}>Unofficial</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6" style="display:flex;align-items:flex-end;gap:8px;padding-bottom:2px;margin-bottom:8px;">
                <button type="submit" class="btn-s"><i class="fa fa-search"></i> Search</button>
                @if(request('user_id'))
                <a href="{{ url('admin/dvrs') }}" class="btn-c"><i class="fa fa-times"></i> Clear</a>
                <a href="#" id="pdfBtn" class="btn-p"><i class="fa fa-file-pdf-o"></i> PDF</a>
                @endif
            </div>
        </div>

        <input type="hidden" name="status_filter"   value="{{ request('status_filter') }}">
        <input type="hidden" name="customer_filter" value="{{ request('customer_filter') }}">

        @if(request('user_id'))

        {{-- STATUS FILTER TABS --}}
        <div class="fc-div"></div>
        <div class="stab-lbl"><i class="fa fa-filter"></i> Filter by Status</div>
        <div class="stabs" style="margin-bottom:12px;">
            <a class="stab {{ !request('status_filter') ? 'on' : '' }}" data-f="" data-t="s">All Statuses</a>
            @foreach($statusFilters as $group => $labels)
                @foreach($labels as $lbl)
                <a class="stab {{ request('status_filter')==$lbl ? 'on' : '' }}" data-f="{{ $lbl }}" data-t="s">{{ $lbl }}</a>
                @endforeach
            @endforeach
        </div>

        {{-- CUSTOMER FILTER --}}
        @if(isset($customerStats) && $customerStats->count())
        <div class="fc-div"></div>
        <div class="stab-lbl" style="margin-bottom:8px;"><i class="fa fa-building-o"></i> Filter by Customer</div>
        <div class="cust-row">
            <a class="cust-all-btn {{ !request('customer_filter') ? 'on' : '' }}" id="custAllBtn">
                <i class="fa fa-th-list"></i> All Customers
            </a>
            <div class="cust-dd-wrap" id="custDdWrap">
                <div class="cust-dd-trigger {{ request('customer_filter') ? 'selected' : '' }}" id="custDdTrigger">
                    <span class="dd-ico"><i class="fa fa-building-o"></i></span>
                    <span class="dd-lbl" id="custDdLabel">
                        {{ request('customer_filter') ?: 'Select Customer' }}
                    </span>
                    <span class="dd-x" id="custDdClear" title="Clear filter"><i class="fa fa-times"></i></span>
                    <span class="dd-arr"><i class="fa fa-chevron-down"></i></span>
                </div>
                <div class="cust-dd-panel" id="custDdPanel">
                    <div class="cust-dd-search">
                        <i class="fa fa-search" style="color:#94a3b8;font-size:12px;flex-shrink:0;"></i>
                        <input type="text" id="custDdSearch" placeholder="Search customer...">
                    </div>
                    <div class="cust-dd-list" id="custDdList">
                        @foreach($customerStats as $cs)
                        <div class="cust-dd-item {{ request('customer_filter')==$cs['name'] ? 'active' : '' }}"
                             data-name="{{ $cs['name'] }}"
                             data-count="{{ $cs['count'] }}"
                             data-total="{{ $cs['total_dvrs'] }}"
                             data-vpct="{{ $cs['visit_pct'] }}"
                             data-tdisp="{{ $cs['time_display'] }}"
                             data-ttotal="{{ $cs['total_minutes'] }}"
                             data-tpct="{{ $cs['time_pct'] }}">
                            <span class="di-name">{{ $cs['name'] }}</span>
                            <span class="di-cnt">{{ $cs['count'] }}/{{ $cs['total_dvrs'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        @endif
    </form>
</div>

{{-- ═══════════════════════════════════════════
     CUSTOMER STAT PANEL (shown when a customer is selected)
═══════════════════════════════════════════ --}}
@if(request('customer_filter') && isset($customerStats))
@php
    $selCs = $customerStats->firstWhere('name', request('customer_filter'));
@endphp
@if($selCs)
<div class="cust-stat-panel" id="custStatPanel">
    <div class="csp-left">
        <div class="csp-name">{{ $selCs['name'] }}</div>
        <div class="csp-sub">
            Customer performance for
            {{ date('F', mktime(0,0,0,$currentMonth,1)) }} {{ $currentYear }}
        </div>
    </div>
    <div class="csp-divider"></div>
    <div class="csp-block visits">
        <div class="big-num">{{ $selCs['visit_pct'] }}%</div>
        <div class="big-frac">{{ $selCs['count'] }} / {{ $selCs['total_dvrs'] }} visits</div>
        <div class="big-lbl">DVR Share</div>
        <div class="csp-bar-wrap"><div class="csp-bar" style="width:{{ min($selCs['visit_pct'],100) }}%;"></div></div>
    </div>
    <div class="csp-divider"></div>
    <div class="csp-block time">
        <div class="big-num">{{ $selCs['time_pct'] }}%</div>
        <div class="big-frac">{{ $selCs['time_display'] }} / {{ $selCs['total_time_display'] ?? floor($selCs['total_minutes']/60).'h '.($selCs['total_minutes']%60).'m' }}</div>
        <div class="big-lbl">Meeting Time Share</div>
        <div class="csp-bar-wrap"><div class="csp-bar" style="width:{{ min($selCs['time_pct'],100) }}%;"></div></div>
    </div>
</div>
@endif
@endif

{{-- NO EMPLOYEE --}}
@if(!request('user_id'))
<div class="dvr-empty">
    <div class="ei"><i class="fa fa-user-circle-o"></i></div>
    <h4>Select an Employee to View DVRs</h4>
    <p>Choose an employee from the filter above to load their Daily Visit Reports.</p>
</div>

@elseif(isset($groupedDvrs) && $groupedDvrs->isEmpty())
<div class="dvr-empty">
    <div class="ei"><i class="fa fa-search"></i></div>
    <h4>No DVRs Found</h4>
    <p>No records match your current filters.</p>
</div>

@else

{{-- SUMMARY STATS --}}
@if($summaryStats)
<div class="row dvr-stats">
    @php
        $cards=[
            ['n'=>$summaryStats['total'],      'l'=>'Total DVRs',    'i'=>'fa-calendar-check-o','bg'=>'#3b82f6'],
            ['n'=>$summaryStats['verified'],    'l'=>'Verified',      'i'=>'fa-check-circle',   'bg'=>'#3b82f6'],
            ['n'=>$summaryStats['pending'],     'l'=>'Pending',       'i'=>'fa-clock-o',        'bg'=>'#ef4444'],
            ['n'=>$summaryStats['metCount'],    'l'=>'Meetings',      'i'=>'fa-handshake-o',    'bg'=>'#3b82f6'],
            ['n'=>$summaryStats['trialsSum'],   'l'=>'Total Trials',  'i'=>'fa-flask',          'bg'=>'#7c3aed'],
            ['n'=>$summaryStats['presentDays'],'l'=>'Present Days',   'i'=>'fa-id-card-o',      'bg'=>'#3b82f6'],
        ];
    @endphp
    @foreach($cards as $card)
    <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom:12px;">
        <div class="stat-card">
            <div class="si" style="background:{{ $card['bg'] }};"><i class="fa {{ $card['i'] }}"></i></div>
            <div><div class="sn">{{ $card['n'] }}</div><div class="sl">{{ $card['l'] }}</div></div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- DVR TABLE --}}
<div class="dvr-tw">
    <div class="dvr-sc">
    <table class="dvr-t">
        <thead>
            <tr>
                <th style="width:56px;text-align:center;">S.No.</th>
                <th style="width:175px;">Customer<br><span style="font-size:9px;opacity:.5;font-weight:400;">+ Purpose of Visit</span></th>
                <th style="width:120px;">Check-In/Out<br><span style="font-size:9px;opacity:.5;font-weight:400;">Meeting Time</span></th>
                <th style="width:155px;">Met With</th>
                <th style="min-width:160px;">Visit Details</th>
                <th style="width:130px;">Next Plan &amp; Schedule</th>
                <th style="width:95px;text-align:center;">Trials</th>
                <th style="width:120px;">Products</th>
                <th style="width:140px;">Status</th>
                <th style="width:68px;text-align:center;">Attach.</th>
                <th style="width:74px;text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($groupedDvrs as $dateKey => $dateDvrs)
        @php
            $fr=$dateDvrs->first();
            $att=$fr['attendance'];
            $dc=$dateDvrs->count();
        @endphp

        {{-- DATE GROUP HEADER --}}
        <tr class="dgr">
            <td colspan="11">
                <div class="dgi">
                    <span class="dgd">{{ $fr['dvr_date_display'] }}</span>
                    <span class="dgw">{{ $fr['day_name'] }}</span>
                    <span class="dgn">{{ $dc }} visit{{ $dc>1?'s':'' }}</span>
                    @if($att['exists'])
                    <div class="dga">
                        <span><span class="dot8" style="background:#22c55e;"></span> In: <strong>{{ $att['in_time']??'—' }}</strong></span>
                        <span><span class="dot8" style="background:#ef4444;"></span> Out: <strong>{{ $att['out_time']??'—' }}</strong></span>
                        <span><i class="fa fa-clock-o"></i> {{ $att['work_hours'] }}</span>
                        <span class="att-pill" style="background:{{ $att['status_color'] }};">{{ $att['status'] }}</span>
                    </div>
                    @else
                    <span style="background:#e2e8f0;color:#64748b;padding:2px 9px;border-radius:10px;font-size:10px;font-weight:800;">No Attendance</span>
                    @endif
                </div>
            </td>
        </tr>

        {{-- DATA ROWS --}}
        @foreach($dateDvrs as $idx => $row)
        <tr class="dr">
            <td style="text-align:center;vertical-align:middle;"><span class="sno">{{ $idx+1 }}</span></td>

            {{-- CUSTOMER + PURPOSE --}}
            <td>
                <div class="cn">{{ $row['customer_name'] }}</div>
                <small><i>{{ $row['customer_city'] }}</i></small>
                @if($row['customer_type']==='customer')
                <div class="ct" style="color:#3b82f6;"><i class="fa fa-building-o"></i> Registered</div>
                @elseif($row['customer_type']==='request')
                <div class="ct" style="color:#f59e0b;"><i class="fa fa-user-plus"></i> Register Request</div>
                @endif
                @if($row['purposes'])
                <div style="margin-top:5px;">
                    @foreach($row['purposes'] as $p)
                    <span class="pchip">{{ $p }}</span>
                    @endforeach
                </div>
                @endif
                @if($row['other_purpose'])
                <div style="font-size:10px;color:var(--muted);margin-top:3px;"><i class="fa fa-comment-o"></i> {{ Str::limit($row['other_purpose'],50) }}</div>
                @endif
            </td>

            {{-- CHECK-IN/OUT --}}
            <td>
                <div class="tb">
                    @if($row['check_in'])
                    <div><span class="dot8" style="background:#22c55e;"></span> In: <span class="tv">{{ $row['check_in'] }}</span></div>
                    @endif
                    @if($row['check_out'])
                    <div><span class="dot8" style="background:#ef4444;"></span> Out: <span class="tv">{{ $row['check_out'] }}</span></div>
                    @endif
                    @if($row['meeting_duration'])
                    <div class="tdur"><i class="fa fa-clock-o"></i> {{ $row['meeting_duration'] }}</div>
                    @endif
                </div>
            </td>

            {{-- MET WITH --}}
            <td>
                @forelse($row['contacts'] as $c)
                <div class="cc">
                    <div class="ccn">{{ $c['name'] }}</div>
                    @if($c['designation'])<div class="ccs">{{ $c['designation'] }}</div>@endif
                    @if($c['mobile'])<div class="ccs"><i class="fa fa-phone"></i> {{ $c['mobile'] }}</div>@endif
                </div>
                @empty
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endforelse
            </td>

            {{-- VISIT DETAILS --}}
            <td>
                @if($row['visit_detail'])
                <div class="vdt">{{ $row['visit_detail'] }}</div>
                @else
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endif
            </td>

            {{-- NEXT PLAN & SCHEDULE --}}
            <td>
                @if($row['scheduler'])
                <div class="scc">
                    @if($row['scheduler']['date'])
                    <div class="scd"><i class="fa fa-calendar-check-o"></i> {{ $row['scheduler']['date'] }}
                        @if($row['scheduler']['time']) @ {{ $row['scheduler']['time'] }} @endif
                    </div>
                    @endif
                    @if($row['scheduler']['description'])
                    <div class="scs">{{ Str::limit($row['scheduler']['description'],80) }}</div>
                    @endif
                    @if($row['scheduler']['status'])
                    <span style="background:#dcfce7;color:#166534;padding:1px 7px;border-radius:8px;font-size:10px;font-weight:700;display:inline-block;margin-top:3px;">{{ $row['scheduler']['status'] }}</span>
                    @endif
                </div>
                @else
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endif
            </td>

            {{-- TRIALS --}}
            <td style="text-align:center;vertical-align:top;">
                @if($row['trials_count']>0)
                <div class="tbadge">{{ $row['trials_count'] }}</div>
                @foreach($row['trial_rows'] as $tr)
                <span class="tpill" style="background:{{ $tr['color'] }};">{{ $tr['label'] }}</span>
                @endforeach
                @else
                <span style="background:#e2e8f0;color:#64748b;padding:3px 9px;border-radius:10px;font-size:10px;font-weight:700;display:inline-block;">No Trials</span>
                @endif
            </td>

            {{-- PRODUCTS --}}
            <td>
                @forelse($row['products'] as $prod)
                <span class="ppill">{{ $prod }}</span>
                @empty
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endforelse
            </td>

            {{-- STATUS --}}
            <td>
                @foreach($row['statuses'] as $s)
                <span class="sp" style="background:{{ $s['color'] }};">{{ $s['label'] }}</span>
                @endforeach
            </td>

            {{-- ATTACHMENTS --}}
            <td style="text-align:center;vertical-align:middle;">
                @if($row['attachments_count']>0)
                <span class="abadge">{{ $row['attachments_count'] }} <i class="fa fa-paperclip"></i></span>
                @else
                <span style="color:#cbd5e1;">—</span>
                @endif
            </td>

            {{-- ACTION --}}
            <td style="text-align:center;vertical-align:middle;">
                <a href="{{ url('admin/dvrs/'.$row['id']) }}" class="btn-v"><i class="fa fa-eye"></i> View</a>
            </td>
        </tr>
        @endforeach
        @endforeach
        </tbody>
    </table>
    </div>

    @if($paginator && $paginator->hasPages())
    <div class="dvr-pg">
        <div class="pgi">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</div>
        <div>{{ $paginator->links() }}</div>
    </div>
    @endif
</div>

@endif

</div>{{-- .dvr-wrap --}}
</div>
</div>

<script>
$(function(){
    // Select2 employee
    if($.fn.select2){ $('#sel_user').select2({placeholder:'— Select Employee —',allowClear:true}); }

    // Status tabs
    $(document).on('click','.stab[data-t="s"]',function(e){
        e.preventDefault();
        $('input[name="status_filter"]').val($(this).data('f'));
        $('#dvrForm').submit();
    });

    // All Customers btn
    $('#custAllBtn').on('click',function(e){
        e.preventDefault();
        $('input[name="customer_filter"]').val('');
        $('#dvrForm').submit();
    });

    // ── Custom customer dropdown ──────────────────────
    var $wrap   = $('#custDdWrap');
    var $trigger= $('#custDdTrigger');
    var $panel  = $('#custDdPanel');
    var $search = $('#custDdSearch');
    var $list   = $('#custDdList');
    var $label  = $('#custDdLabel');
    var $clear  = $('#custDdClear');

    // Open / close
    $trigger.on('click',function(e){
        if($(e.target).closest('.dd-x').length) return; // handled by clear
        $trigger.toggleClass('open');
        $panel.toggleClass('show');
        if($panel.hasClass('show')){ $search.focus(); }
    });

    // Close on outside click
    $(document).on('click',function(e){
        if(!$wrap.is(e.target) && $wrap.has(e.target).length===0){
            $trigger.removeClass('open');
            $panel.removeClass('show');
        }
    });

    // Search filter
    $search.on('input',function(){
        var q=$(this).val().toLowerCase();
        $list.find('.cust-dd-item').each(function(){
            var name=$(this).data('name').toLowerCase();
            $(this).toggle(name.includes(q));
        });
        var visible=$list.find('.cust-dd-item:visible').length;
        $list.find('.cust-dd-empty').remove();
        if(visible===0){ $list.append('<div class="cust-dd-empty">No customers found</div>'); }
    });

    // Item click — select and show stat panel, then submit
    $list.on('click','.cust-dd-item',function(){
        var name=$(this).data('name');
        $('input[name="customer_filter"]').val(name);
        $label.text(name);
        $trigger.addClass('selected').removeClass('open');
        $panel.removeClass('show');
        // Live stat panel
        buildStatPanel(
            name, $(this).data('count'), $(this).data('total'),
            $(this).data('vpct'), $(this).data('tdisp'),
            $(this).data('ttotal'), $(this).data('tpct')
        );
        $('#dvrForm').submit();
    });

    // Clear
    $clear.on('click',function(e){
        e.stopPropagation();
        $('input[name="customer_filter"]').val('');
        $label.text('Select Customer');
        $trigger.removeClass('selected open');
        $panel.removeClass('show');
        $('#liveStatPanel').hide();
        $('#dvrForm').submit();
    });

    // ── Stat panel builder ────────────────────────────
    function buildStatPanel(name,count,total,vpct,tdisp,ttotalMin,tpct){
        var tH=Math.floor(ttotalMin/60), tM=ttotalMin%60;
        var totalDisp=tH+'h '+tM+'m';
        $('#liveStatPanel').html(
            '<div class="cust-stat-panel">'
            +'<div class="csp-left"><div class="csp-name">'+name+'</div><div class="csp-sub">Customer performance</div></div>'
            +'<div class="csp-divider"></div>'
            +'<div class="csp-block visits">'
            +'<div class="big-num">'+vpct+'%</div>'
            +'<div class="big-frac">'+count+' / '+total+' visits</div>'
            +'<div class="big-lbl">DVR Share</div>'
            +'<div class="csp-bar-wrap"><div class="csp-bar" style="width:'+Math.min(vpct,100)+'%;"></div></div>'
            +'</div>'
            +'<div class="csp-divider"></div>'
            +'<div class="csp-block time">'
            +'<div class="big-num">'+tpct+'%</div>'
            +'<div class="big-frac">'+tdisp+' / '+totalDisp+'</div>'
            +'<div class="big-lbl">Meeting Time Share</div>'
            +'<div class="csp-bar-wrap"><div class="csp-bar" style="width:'+Math.min(tpct,100)+'%;"></div></div>'
            +'</div>'
            +'</div>'
        ).show();
    }

    // PDF
    $('#pdfBtn').on('click',function(e){
        e.preventDefault();
        window.location.href='{{ url("admin/dvrs/export-pdf") }}'+'?'+$('#dvrForm').serialize();
    });
});
</script>

{{-- Placeholder for live stat panel (JS inject) --}}
<div id="liveStatPanel" style="{{ request('customer_filter') ? 'display:none;' : 'display:none;' }}"></div>

@endsection