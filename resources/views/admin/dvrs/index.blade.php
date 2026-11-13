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

/* Filter row — all fields + buttons share one baseline */
.fc-row{display:flex;flex-wrap:wrap;align-items:flex-end;}
.fc-row:before,.fc-row:after{display:none;}
/* select2 inside filter — same 36px height as the other controls */
.dvr-fc .select2-container .select2-selection--single{height:36px;border:1.5px solid var(--border);border-radius:7px;}
.dvr-fc .select2-container .select2-selection--single .select2-selection__rendered{line-height:33px;font-size:13px;color:var(--dark);}
.dvr-fc .select2-container .select2-selection--single .select2-selection__arrow{height:33px;}
.dvr-fc .select2-container .select2-selection--multiple{min-height:36px;border:1.5px solid var(--border);border-radius:7px;padding:3px 6px;display:flex;flex-wrap:wrap;align-items:center;}
.dvr-fc .select2-container .select2-selection--multiple .select2-selection__rendered{display:flex;flex-wrap:wrap;align-items:center;padding:0;margin:0;}
.dvr-fc .select2-container .select2-selection--multiple .select2-selection__choice{margin:1px 4px 1px 0;padding:0 7px;line-height:24px;font-size:12px;}
.dvr-fc .select2-container .select2-search--inline{flex:1;min-width:40px;}
.dvr-fc .select2-container .select2-search--inline .select2-search__field{margin:0;height:24px;width:100% !important;}

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

/* ═══════════════════════════════════════
   DVR TABLE — compact, bordered, fits the page (no sideways scroll)
═══════════════════════════════════════ */
.dvr-tw{border:1px solid #d6dde8;border-radius:10px;box-shadow:0 2px 10px rgba(15,23,42,.06);}
.dvr-sc{overflow-x:visible;}
.dvr-t{table-layout:fixed;min-width:0;font-size:11.5px;border-collapse:separate;border-spacing:0;}
.dvr-t col.c-no{width:42px;}       .dvr-t col.c-cust{width:13%;}
.dvr-t col.c-time{width:8.5%;}     .dvr-t col.c-met{width:13.5%;}
/* Visit Details has no width: it takes whatever space is left */
.dvr-t col.c-next{width:7%;}       .dvr-t col.c-trial{width:7%;}
.dvr-t col.c-prod{width:7.5%;}     .dvr-t col.c-status{width:22%;}

/* header: column dividers */
.dvr-t thead tr th{font-size:9.5px;letter-spacing:.4px;padding:9px 8px;white-space:normal;line-height:1.35;
    border-right:1px solid rgba(255,255,255,.09);border-bottom:2px solid #3b82f6;}
.dvr-t thead tr th:last-child{border-right:none;}
.dvr-t thead tr th .th-sub{display:block;font-size:8.5px;font-weight:500;opacity:.55;letter-spacing:.3px;margin-top:1px;}

/* body: full cell borders */
.dvr-t tbody tr.dr td{padding:8px;border-bottom:1px solid #e5eaf1;border-right:1px solid #edf1f6;
    word-wrap:break-word;overflow-wrap:anywhere;background:#fff;}
.dvr-t tbody tr.dr td:last-child{border-right:none;}
.dvr-t tbody tr.dr:nth-of-type(even) td{background:#fcfdff;}
.dvr-t tbody tr.dr:hover td{background:#f3f8ff;}

/* date band: left accent + clear separation */
.dgr td{background:linear-gradient(90deg,#eef4ff 0%,#f8fafc 60%);border-top:1px solid #d6dde8;
    border-bottom:1px solid #d6dde8;box-shadow:inset 3px 0 0 #3b82f6;}
.dgi{padding:6px 12px;gap:8px;}
.dgd{font-size:11.5px;padding:3px 11px;}
.dgw,.dgn{font-size:10px;padding:2px 9px;}
.dga{font-size:10.5px;gap:9px;}
.att-pill{font-size:9.5px;padding:1px 8px;}

/* cell contents — smaller type */
.sno{width:24px;height:24px;font-size:10.5px;}
.cn{font-size:12px;font-weight:700;line-height:1.3;}
.dvr-t small{font-size:10px;color:var(--muted);}
.ct{font-size:9.5px;}
.pchip{font-size:9.5px;padding:1px 6px;}
.tb{font-size:11px;line-height:1.75;}
.tdur{font-size:10px;}
.cc{font-size:10.5px;padding:5px 7px;border-radius:6px;line-height:1.5;}
.ccs{font-size:10px;}
.vdt{font-size:11px;}
/* Visit details: Show more / Show less */
.vdt.open{max-height:none;-webkit-line-clamp:unset;display:block;}
.vdt-tg{display:inline-flex;align-items:center;gap:3px;margin-top:4px;font-size:10px;font-weight:700;color:#2563eb;text-decoration:none;}
.vdt-tg:hover,.vdt-tg:focus{color:#1d4ed8;text-decoration:underline;}
.scc{font-size:10.5px;padding:5px 7px;border-radius:6px;line-height:1.55;}
.scd{font-size:11px;}
.scs{font-size:10px;}
.tbadge{width:26px;height:26px;font-size:12px;}
.tpill{font-size:9px;padding:2px 6px;}
.ppill{font-size:9.5px;padding:1px 7px;overflow-wrap:normal;word-break:normal;}
.sp{font-size:9.5px;padding:3px 6px;border-radius:10px;white-space:normal;line-height:1.25;}
.abadge{font-size:10px;padding:2px 7px;white-space:nowrap;display:inline-block;}
.btn-v{font-size:10.5px;padding:5px 9px;border-radius:6px;}

/* narrow screens: let it scroll rather than crush the columns */
@media (max-width:1100px){ .dvr-sc{overflow-x:auto;} .dvr-t{min-width:1000px;} }

/* Status column — checklist style (✓ done / ✕ not done) */
.stl{display:grid;grid-template-columns:1fr 1fr;gap:3px;}
.st{display:flex;align-items:center;gap:4px;padding:3px 5px 3px 3px;border-radius:6px;border:1px solid;font-size:10px;font-weight:600;line-height:1.2;min-width:0;}
.st-ic{width:14px;height:14px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:8px;color:#fff;}
.st-tx{min-width:0;}
.st.ok {background:#f0fdf4;border-color:#bbf7d0;color:#166534;}
.st.ok  .st-ic{background:#22c55e;}
.st.bad{background:#fef2f2;border-color:#fecaca;color:#b91c1c;}
.st.bad .st-ic{background:#ef4444;}
.st.neu{background:#f8fafc;border-color:#e2e8f0;color:#475569;}
.st.neu .st-ic{background:#94a3b8;font-size:5px;}

/* View Details — sits at the bottom of the Customer cell */
.vd-link{display:inline-flex;align-items:center;gap:5px;margin-top:8px;padding:3px 10px 3px 8px;
    border:1px solid #bfdbfe;border-radius:14px;background:#eff6ff;color:#2563eb;
    font-size:10.5px;font-weight:700;text-decoration:none;transition:all .15s;}
.vd-link .vd-arr{font-size:12px;transition:transform .15s;}
.vd-link:hover,.vd-link:focus{background:#3b82f6;border-color:#3b82f6;color:#fff;text-decoration:none;box-shadow:0 2px 8px rgba(59,130,246,.3);}
.vd-link:hover .vd-arr{transform:translateX(2px);}

/* Customer cell — tinted tags like the checklist */
.c-city{font-size:10px;color:#64748b;margin-top:1px;text-transform:uppercase;letter-spacing:.3px;font-weight:600;}
.c-city i{color:#94a3b8;}
.c-tags{display:flex;flex-wrap:wrap;gap:3px;margin-top:6px;}
.c-tag{display:inline-flex;align-items:center;gap:4px;padding:2px 7px;border-radius:6px;border:1px solid;font-size:9.5px;font-weight:600;line-height:1.3;}
.c-tag.reg {background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;}
.c-tag.req {background:#fffbeb;border-color:#fde68a;color:#b45309;}
.c-tag.purp{background:#f5f3ff;border-color:#ddd6fe;color:#6d28d9;}
.c-note{margin-top:6px;padding:4px 7px;border-left:2px solid #cbd5e1;background:#f8fafc;border-radius:0 6px 6px 0;font-size:10px;color:#475569;line-height:1.45;}
.c-note i{font-size:8px;color:#94a3b8;vertical-align:2px;}

/* Check-in / out — stacked tinted rows */
.tm{display:flex;flex-direction:column;gap:3px;}
.tm-row{display:flex;align-items:center;gap:5px;padding:3px 6px 3px 3px;border-radius:6px;border:1px solid;font-size:10px;line-height:1.2;}
.tm-row b{font-weight:700;margin-left:auto;}
.tm-k{font-weight:600;}
.tm-ic{width:14px;height:14px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:8px;color:#fff;}
.tm-row.in {background:#f0fdf4;border-color:#bbf7d0;color:#166534;} .tm-row.in  .tm-ic{background:#22c55e;}
.tm-row.out{background:#fef2f2;border-color:#fecaca;color:#b91c1c;} .tm-row.out .tm-ic{background:#ef4444;}
.tm-row.dur{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;} .tm-row.dur .tm-ic{background:#3b82f6;}
.tm-row.dur b{margin-left:0;}

/* Met with — clean contact rows (no inner box) */
.mw{display:flex;align-items:flex-start;gap:7px;}
.mw + .mw{margin-top:7px;padding-top:7px;border-top:1px dashed #e2e8f0;}
.mw-av{width:26px;height:26px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;
    background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;font-size:11px;font-weight:800;}
.mw-body{min-width:0;overflow-wrap:normal;word-break:normal;padding-top:1px;}
.mw-name{font-size:11px;font-weight:700;color:#1e293b;line-height:1.3;}
.mw-des{font-size:9.5px;color:#64748b;line-height:1.35;}
.mw-ph{white-space:nowrap;display:flex;align-items:center;gap:4px;margin-top:3px;font-size:10px;font-weight:600;color:#334155;}
.mw-ph i{width:15px;height:15px;border-radius:50%;background:#dcfce7;color:#16a34a;display:inline-flex;align-items:center;justify-content:center;font-size:8px;}

/* Attachments block inside the Visit Checklist cell */
.att-sec{margin-top:7px;padding-top:6px;border-top:1px dashed #e2e8f0;}
.att-h{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:4px;}
.att-yes{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;border:1px solid #ddd6fe;background:#f5f3ff;color:#6d28d9;font-size:10px;font-weight:600;}
.att-no{display:inline-flex;align-items:center;padding:2px 8px;border-radius:6px;border:1px dashed #cbd5e1;background:#f8fafc;color:#94a3b8;font-size:10px;font-weight:500;font-style:italic;}

/* DVR review block (top of the Visit Checklist cell) */
.rv-pill{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;border:1px solid;font-size:10px;font-weight:700;line-height:1.3;white-space:nowrap;}
.rv-pill.pend{background:#fffbeb;border-color:#fde68a;color:#b45309;}
.rv-pill.ver {background:#f0fdf4;border-color:#bbf7d0;color:#166534;cursor:default;}
.rv-pill.qr  {background:#fef2f2;border-color:#fecaca;color:#b91c1c;}
.rv-pill.qd  {background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;}
.rv-btn{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;border:1px solid;font-size:10px;font-weight:700;cursor:pointer;background:#fff;transition:all .15s;line-height:1.4;}
.rv-btn.verify{border-color:#86efac;color:#15803d;}
.rv-btn.verify:hover{background:#22c55e;border-color:#22c55e;color:#fff;}
.rv-btn.query{border-color:#c7d2fe;color:#4338ca;}
.rv-btn.query:hover{background:#4f46e5;border-color:#4f46e5;color:#fff;}
.rv-btn.query.unread{border-color:#fca5a5;color:#b91c1c;background:#fef2f2;}
.rv-btn:disabled{opacity:.6;cursor:not-allowed;}
.rv-cnt{background:#4f46e5;color:#fff;border-radius:8px;padding:0 5px;font-size:9px;min-width:15px;text-align:center;}
.rv-btn.query.unread .rv-cnt{background:#ef4444;}
.rv-btn.query:hover .rv-cnt{background:#fff;color:#4f46e5;}
@keyframes rvBlink{0%,100%{opacity:1}50%{opacity:.25}}
.rv-dot{width:6px;height:6px;border-radius:50%;background:#ef4444;animation:rvBlink 1.1s infinite;}

/* Query modal (same chat layout as the expenses screen) */
#dvrQueryModal .modal-content{border-radius:12px;overflow:hidden;border:none;box-shadow:0 20px 50px rgba(15,23,42,.25);}
.dq-hdr{background:linear-gradient(135deg,#4f46e5,#3730a3);color:#fff;padding:14px 18px;}
.dq-hdr .close{color:#fff;opacity:.8;text-shadow:none;margin-top:2px;}
.dq-title{font-size:14px;font-weight:800;margin:0;}
.dq-sub{font-size:11px;opacity:.8;margin-top:2px;}
.dq-status{display:flex;gap:6px;padding:8px 18px;background:#f8fafc;border-bottom:1px solid #e2e8f0;}
.q-chat-box{height:300px;overflow-y:auto;padding:14px 18px;background:#f7f9fc;border-bottom:1px solid #e8edf5;}
.q-bubble{max-width:80%;margin-bottom:10px;}
.q-bubble.admin{margin-left:20%;text-align:right;}
.q-bubble.employee{margin-right:20%;}
.q-binner{display:inline-block;text-align:left;padding:9px 13px;border-radius:10px;font-size:12.5px;line-height:1.5;word-break:break-word;white-space:pre-wrap;}
.q-bubble.admin .q-binner{background:linear-gradient(135deg,#6366f1,#4338ca);color:#fff;border-radius:10px 10px 2px 10px;}
.q-bubble.employee .q-binner{background:#fff;color:#1e293b;border:1px solid #e2e8f0;border-radius:10px 10px 10px 2px;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.q-meta{font-size:10.5px;color:#94a3b8;margin-top:3px;}
.q-seen{color:#16a34a;font-weight:700;} .q-seen i+i{margin-left:-4px;}
.q-sent{color:#94a3b8;font-weight:600;}
.q-empty,.q-loading{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:12.5px;gap:8px;text-align:center;}
.q-reply{padding:12px 18px 6px;background:#fff;}
.q-reply label{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:6px;display:block;}
.q-reply textarea{width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:13px;resize:none;outline:none;background:#fafbfc;}
.q-reply textarea:focus{border-color:#6366f1;background:#fff;box-shadow:0 0 0 3px rgba(99,102,241,.12);}
.dq-foot{padding:10px 18px 14px;background:#fff;display:flex;justify-content:flex-end;gap:8px;}
.dq-close{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:7px;padding:7px 16px;font-size:12.5px;font-weight:600;}
.dq-send{background:linear-gradient(135deg,#6366f1,#4338ca);color:#fff;border:none;border-radius:7px;padding:8px 20px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;}
.dq-send:disabled{opacity:.55;cursor:not-allowed;}
.dvr-toast{position:fixed;right:22px;bottom:22px;z-index:20000;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 6px 18px rgba(0,0,0,.15);display:none;}
.dvr-toast.ok{background:#dcfce7;color:#166534;border:1px solid #86efac;}
.dvr-toast.err{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}

/* Filter row: flexible columns so the two review dropdowns fit on one line */
.fc-flex{margin-left:-6px;margin-right:-6px;}
.fc-flex .fcol{padding:0 6px;margin-bottom:8px;min-width:0;}
.fc-flex .f-emp{flex:1 1 150px;}   .fc-flex .f-month{flex:1.2 1 170px;}
.fc-flex .f-year{flex:0 0 84px;}    .fc-flex .f-type{flex:0 0 108px;}
.fc-flex .f-type .form-control,.fc-flex .f-year .form-control{padding-left:8px;padding-right:4px;}
.fc-flex .f-btns{flex:0 0 auto;display:flex;align-items:flex-end;gap:8px;}
.fc-flex .select2-container{width:100% !important;}

/* Verification + Query — two separate blocks */
.rvb{display:flex;flex-direction:column;gap:5px;margin-bottom:8px;padding-bottom:8px;border-bottom:1px dashed #e2e8f0;}
.rvx{border:1px solid #e2e8f0;border-left:3px solid #cbd5e1;border-radius:7px;background:#fff;padding:5px 7px;}
.rvx.is-pend{border-left-color:#f59e0b;background:#fffdf5;}
.rvx.is-ver {border-left-color:#22c55e;}
.rvx.is-qr  {border-left-color:#ef4444;background:#fffafa;}
.rvx.is-qd  {border-left-color:#3b82f6;}
.rvx-h{font-size:8.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:3px;}
.rvx-b{display:flex;align-items:center;justify-content:space-between;gap:6px;flex-wrap:wrap;}
.rvx-by{font-size:9.5px;color:#64748b;font-weight:600;}
.rvx-none{font-size:10px;color:#94a3b8;font-style:italic;}

/* Verify confirmation */
#dvrVerifyModal .modal-content{border-radius:12px;overflow:hidden;border:none;box-shadow:0 20px 50px rgba(15,23,42,.25);}
.vc-body{padding:22px 22px 8px;text-align:center;}
.vc-ico{width:54px;height:54px;border-radius:50%;margin:0 auto 12px;background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:24px;}
.vc-title{font-size:16px;font-weight:800;color:#1e293b;margin:0 0 6px;}
.vc-txt{font-size:12.5px;color:#475569;line-height:1.6;margin:0;}
.vc-warn{margin:12px 0 0;padding:8px 10px;border-radius:8px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:11.5px;font-weight:600;}
.vc-foot{display:flex;gap:8px;justify-content:center;padding:14px 22px 20px;}
.vc-cancel{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:8px;padding:8px 18px;font-size:13px;font-weight:600;}
.vc-yes{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;}
.vc-yes:disabled{opacity:.6;cursor:not-allowed;}
.stab.rvf.on{background:#4f46e5;color:#fff;}
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

        <div class="row fc-row fc-flex" style="margin-bottom:10px;">
            <div class="fcol f-emp">
                <label><i class="fa fa-user"></i> Employee <span style="color:var(--red);">*</span></label>
                <select name="user_id" class="form-control select2" id="sel_user">
                    <option value="">— Select Employee —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fcol f-month">
                <label><i class="fa fa-calendar"></i> Month</label>
                <select name="month[]" class="form-control select2" id="sel_month" multiple="multiple">
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ in_array($m, $currentMonths)?'selected':'' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="fcol f-year">
                <label><i class="fa fa-calendar-o"></i> Year</label>
                <select name="year" class="form-control">
                    @for($y=date('Y');$y>=2021;$y--)
                    <option value="{{ $y }}" {{ $currentYear==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="fcol f-type">
                <label><i class="fa fa-tag"></i> Visit Type</label>
                <select name="visit_type" class="form-control">
                    <option value="">All Types</option>
                    <option value="Official" {{ request('visit_type')=='Official'?'selected':'' }}>Official</option>
                    <option value="Unofficial" {{ request('visit_type')=='Unofficial'?'selected':'' }}>Unofficial</option>
                </select>
            </div>
            <div class="fcol f-btns">
                <button type="submit" class="btn-s"><i class="fa fa-search"></i> Search</button>
                @if(request('user_id'))
                <a href="{{ url('admin/dvrs') }}" class="btn-c"><i class="fa fa-times"></i> Clear</a>
                <a href="#" id="pdfBtn" class="btn-p"><i class="fa fa-file-pdf-o"></i> PDF</a>
                @endif
            </div>
        </div>

        <input type="hidden" name="status_filter"   value="{{ request('status_filter') }}">
        <input type="hidden" name="customer_filter" value="{{ request('customer_filter') }}">
        <input type="hidden" name="dvr_status"      value="{{ request('dvr_status') }}">
        <input type="hidden" name="query_status"    value="{{ request('query_status') }}">

        @if(request('user_id'))

        {{-- STATUS FILTER TABS --}}
        <div class="fc-div"></div>
        <div class="stab-lbl"><i class="fa fa-filter"></i> Filter by Visit Checklist</div>
        <div class="stabs" style="margin-bottom:12px;">
            <a class="stab {{ !request('status_filter') ? 'on' : '' }}" data-f="" data-t="s">All</a>
            @foreach($statusFilters as $group => $labels)
                @foreach($labels as $lbl)
                <a class="stab {{ request('status_filter')==$lbl ? 'on' : '' }}" data-f="{{ $lbl }}" data-t="s">{{ $lbl }}</a>
                @endforeach
            @endforeach
        </div>

        {{-- DVR STATUS FILTER --}}
        <div class="stab-lbl"><i class="fa fa-shield"></i> Filter by DVR Status</div>
        <div class="stabs" style="margin-bottom:12px;">
            <a class="stab rvf {{ !request('dvr_status') ? 'on' : '' }}" data-f="" data-t="d">All</a>
            @foreach(['Pending Verification', 'Verified'] as $lbl)
            <a class="stab rvf {{ request('dvr_status')==$lbl ? 'on' : '' }}" data-f="{{ $lbl }}" data-t="d">{{ $lbl }}</a>
            @endforeach
        </div>

        {{-- QUERY STATUS FILTER --}}
        <div class="stab-lbl"><i class="fa fa-comments-o"></i> Filter by Query Status</div>
        <div class="stabs" style="margin-bottom:12px;">
            <a class="stab rvf {{ !request('query_status') ? 'on' : '' }}" data-f="" data-t="q">All</a>
            @foreach(['Query Raised' => 'Query Raised', 'Query Responded' => 'Query Responded', 'none' => 'No Query'] as $val => $lbl)
            <a class="stab rvf {{ request('query_status')===(string)$val ? 'on' : '' }}" data-f="{{ $val }}" data-t="q">{{ $lbl }}</a>
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
            {{ $monthLabel }} {{ $currentYear }}
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
    <div class="ei"><i class="fa fa-user"></i></div>
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
            ['n'=>$summaryStats['total'],      'l'=>'Total DVRs',    'i'=>'fa-calendar','bg'=>'#3b82f6'],
            ['n'=>$summaryStats['verified'],    'l'=>'Verified',      'i'=>'fa-check-circle',   'bg'=>'#3b82f6'],
            ['n'=>$summaryStats['pending'],     'l'=>'Pending',       'i'=>'fa-clock-o',        'bg'=>'#ef4444'],
            ['n'=>$summaryStats['metCount'],    'l'=>'Meetings',      'i'=>'fa-users',    'bg'=>'#3b82f6'],
            ['n'=>$summaryStats['trialsSum'],   'l'=>'Total Trials',  'i'=>'fa-flask',          'bg'=>'#7c3aed'],
            ['n'=>$summaryStats['presentDays'],'l'=>'Present Days',   'i'=>'fa-check-square-o',      'bg'=>'#3b82f6'],
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
        <colgroup>
            <col class="c-no"><col class="c-cust"><col class="c-time"><col class="c-met"><col class="c-detail">
            <col class="c-next"><col class="c-trial"><col class="c-prod"><col class="c-status">
        </colgroup>
        <thead>
            <tr>
                <th style="text-align:center;">S.No.</th>
                <th>Customer<span class="th-sub">+ Purpose of Visit</span></th>
                <th>Check-In/Out<span class="th-sub">Meeting Time</span></th>
                <th>Met With</th>
                <th>Visit Details</th>
                <th>Next Plan &amp; Schedule</th>
                <th style="text-align:center;">Trials</th>
                <th>Products</th>
                <th>Visit Checklist</th>
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
            <td colspan="9">
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
                @if($row['customer_city'])
                <div class="c-city"><i class="fa fa-map-marker"></i> {{ $row['customer_city'] }}</div>
                @endif
                <div class="c-tags">
                    @if($row['customer_type']==='customer')
                    <span class="c-tag reg"><i class="fa fa-check-circle"></i> Registered</span>
                    @elseif($row['customer_type']==='request')
                    <span class="c-tag req"><i class="fa fa-plus-circle"></i> Register Request</span>
                    @endif
                    @foreach($row['purposes'] ?? [] as $p)
                    <span class="c-tag purp">{{ $p }}</span>
                    @endforeach
                </div>
                @if($row['other_purpose'])
                <div class="c-note"><i class="fa fa-quote-left"></i> {{ Str::limit($row['other_purpose'],50) }}</div>
                @endif
                <a href="{{ url('admin/dvrs/'.$row['id']) }}" class="vd-link">
                    <i class="fa fa-eye"></i> View Details <i class="fa fa-angle-right vd-arr"></i>
                </a>
            </td>

            {{-- CHECK-IN/OUT --}}
            <td>
                <div class="tm">
                    @if($row['check_in'])
                    <div class="tm-row in"><span class="tm-ic"><i class="fa fa-sign-in"></i></span><span class="tm-k">In</span><b>{{ $row['check_in'] }}</b></div>
                    @endif
                    @if($row['check_out'])
                    <div class="tm-row out"><span class="tm-ic"><i class="fa fa-sign-out"></i></span><span class="tm-k">Out</span><b>{{ $row['check_out'] }}</b></div>
                    @endif
                    @if($row['meeting_duration'])
                    <div class="tm-row dur"><span class="tm-ic"><i class="fa fa-clock-o"></i></span><b>{{ $row['meeting_duration'] }}</b></div>
                    @endif
                    @if(!$row['check_in'] && !$row['check_out'] && !$row['meeting_duration'])
                    <span style="color:#cbd5e1;font-size:12px;">—</span>
                    @endif
                </div>
            </td>

            {{-- MET WITH --}}
            <td>
                @forelse($row['contacts'] as $c)
                <div class="mw">
                    <span class="mw-av">{{ mb_strtoupper(mb_substr(trim($c['name']), 0, 1)) }}</span>
                    <div class="mw-body">
                        <div class="mw-name">{{ $c['name'] }}</div>
                        @if($c['designation'])<div class="mw-des">{{ $c['designation'] }}</div>@endif
                        @if($c['mobile'])<div class="mw-ph"><i class="fa fa-phone"></i> {{ $c['mobile'] }}</div>@endif
                    </div>
                </div>
                @empty
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endforelse
            </td>

            {{-- VISIT DETAILS --}}
            <td>
                @if($row['visit_detail'])
                <div class="vdt">{{ $row['visit_detail'] }}</div>
                <a href="#" class="vdt-tg" style="display:none;">Show more <i class="fa fa-angle-down"></i></a>
                @else
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                @endif
            </td>

            {{-- NEXT PLAN & SCHEDULE --}}
            <td>
                @if($row['scheduler'])
                <div class="scc">
                    @if($row['scheduler']['date'])
                    <div class="scd"><i class="fa fa-calendar"></i> {{ $row['scheduler']['date'] }}
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
                {{-- DVR REVIEW: verification (one way) + admin query thread --}}
                @php
                    $isVer  = $row['dvr_status'] === 'Verified';
                    $qs     = $row['query_status'];
                    $qUnr   = $row['query_unread'] ?? 0;
                @endphp
                <div class="rvb" id="rv-{{ $row['id'] }}">
                    {{-- Verification: Pending Verification -> Verified, one way only --}}
                    <div class="rvx {{ $isVer ? 'is-ver' : 'is-pend' }}" id="rvv-{{ $row['id'] }}">
                        <div class="rvx-h"><i class="fa fa-shield"></i> Verification</div>
                        <div class="rvx-b">
                            <span class="rv-pill {{ $isVer ? 'ver' : 'pend' }}" id="rvst-{{ $row['id'] }}">
                                <i class="fa {{ $isVer ? 'fa-check-circle' : 'fa-clock-o' }}"></i> {{ $row['dvr_status'] }}
                            </span>
                            @if($isVer)
                                @if($row['verified_by_name'])<span class="rvx-by" title="{{ $row['verified_at'] }}">by {{ $row['verified_by_name'] }}</span>@endif
                            @else
                            <button type="button" class="rv-btn verify btn-dvr-verify" data-id="{{ $row['id'] }}"
                                    data-cust="{{ $row['customer_name'] }}" data-date="{{ $row['dvr_date_display'] }}"><i class="fa fa-check"></i> Verify</button>
                            @endif
                        </div>
                    </div>

                    {{-- Query thread with the employee --}}
                    <div class="rvx {{ $qs === 'Query Raised' ? 'is-qr' : ($qs ? 'is-qd' : '') }}" id="rvqx-{{ $row['id'] }}">
                        <div class="rvx-h"><i class="fa fa-comments-o"></i> Query</div>
                        <div class="rvx-b">
                            <span class="rv-pill {{ $qs === 'Query Raised' ? 'qr' : 'qd' }}" id="rvq-{{ $row['id'] }}" style="{{ $qs ? '' : 'display:none;' }}">
                                <i class="fa {{ $qs === 'Query Raised' ? 'fa-question-circle' : 'fa-reply' }}"></i> <span class="rvq-tx">{{ $qs }}</span>
                            </span>
                            <span class="rvx-none" id="rvqn-{{ $row['id'] }}" style="{{ $qs ? 'display:none;' : '' }}">No query raised</span>
                            <button type="button" class="rv-btn query btn-dvr-query {{ $qUnr > 0 ? 'unread' : '' }}"
                                    data-id="{{ $row['id'] }}" data-cust="{{ $row['customer_name'] }}" data-date="{{ $row['dvr_date_display'] }}"
                                    id="rvqb-{{ $row['id'] }}">
                                @if($qUnr > 0)<span class="rv-dot"></span>@endif
                                <i class="fa fa-comments-o"></i> {{ $qs ? 'Open' : 'Raise' }}
                                {{-- Only new replies from the employee that no admin has opened yet --}}
                                <span class="rv-cnt" id="rvqc-{{ $row['id'] }}" title="New replies" style="{{ $qUnr ? '' : 'display:none;' }}">{{ $qUnr > 99 ? '99+' : $qUnr }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="stl">
                @foreach($row['statuses'] as $s)
                    @php $okS = strtolower($s['color']) === '#22c55e'; $badS = strtolower($s['color']) === '#ef4444'; @endphp
                    <div class="st {{ $okS ? 'ok' : ($badS ? 'bad' : 'neu') }}">
                        <span class="st-ic"><i class="fa {{ $okS ? 'fa-check' : ($badS ? 'fa-times' : 'fa-circle') }}"></i></span>
                        <span class="st-tx">{{ $s['label'] }}</span>
                    </div>
                @endforeach
                </div>

                {{-- Attachments (moved here from its own column) --}}
                <div class="att-sec">
                    <div class="att-h"><i class="fa fa-paperclip"></i> Attachments</div>
                    @if($row['attachments_count']>0)
                    <span class="att-yes"><i class="fa fa-paperclip"></i> {{ $row['attachments_count'] }} {{ $row['attachments_count']==1 ? 'attachment' : 'attachments' }} added</span>
                    @else
                    <span class="att-no">No attachments added</span>
                    @endif
                </div>
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
    // Select2 month (multi-select)
    if($.fn.select2){ $('#sel_month').select2({placeholder:'Select Month(s)',width:'100%'}); }

    // Changing employee invalidates any customer/status filter picked for the
    // previous employee — clear them so they don't silently carry over.
    $('#sel_user').on('change', function(){
        $('input[name="customer_filter"]').val('');
        $('input[name="status_filter"]').val('');
        $('input[name="dvr_status"]').val('');
        $('input[name="query_status"]').val('');
    });

    // DVR status / query status chips
    $(document).on('click','.stab[data-t="d"],.stab[data-t="q"]',function(e){
        e.preventDefault();
        var field = $(this).data('t') === 'd' ? 'dvr_status' : 'query_status';
        $('input[name="'+field+'"]').val($(this).data('f'));
        $('#dvrForm').submit();
    });

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

    // Visit details: only offer "Show more" when the text is actually cut off
    $('.vdt').each(function(){
        if(this.scrollHeight > this.clientHeight + 1){ $(this).next('.vdt-tg').css('display','inline-flex'); }
    });
    $(document).on('click','.vdt-tg',function(e){
        e.preventDefault();
        var $t=$(this).prev('.vdt').toggleClass('open');
        $(this).html($t.hasClass('open') ? 'Show less <i class="fa fa-angle-up"></i>' : 'Show more <i class="fa fa-angle-down"></i>');
    });

    // PDF
    $('#pdfBtn').on('click',function(e){
        e.preventDefault();
        window.location.href='{{ url("admin/dvrs/export-pdf") }}'+'?'+$('#dvrForm').serialize();
    });
});
</script>

{{-- ══ DVR QUERY MODAL ══ --}}
<div class="modal fade" id="dvrQueryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:540px;max-width:96vw;margin:50px auto;">
        <div class="modal-content">
            <div class="dq-hdr">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="dq-title"><i class="fa fa-comments-o"></i> DVR Query — <span id="dq_cust"></span></h4>
                <div class="dq-sub">DVR #<span id="dq_id"></span> &middot; <span id="dq_date"></span></div>
            </div>
            <div class="dq-status" id="dq_status"></div>
            <input type="hidden" id="dq_dvr_id">
            <div class="q-chat-box" id="dqChat"><div class="q-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div></div>
            <div class="q-reply">
                <label><i class="fa fa-reply"></i> &nbsp;Raise a Query / Follow up</label>
                <textarea id="dq_msg" rows="3" maxlength="2000" placeholder="Type your query for the employee... (Ctrl+Enter to send)"></textarea>
            </div>
            <div class="dq-foot">
                <button type="button" class="dq-close" data-dismiss="modal">Close</button>
                <button type="button" class="dq-send" id="dqSend"><i class="fa fa-paper-plane"></i> Send</button>
            </div>
        </div>
    </div>
</div>
{{-- ══ VERIFY CONFIRMATION ══ --}}
<div class="modal fade" id="dvrVerifyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document" style="width:400px;max-width:94vw;margin:90px auto;">
        <div class="modal-content">
            <div class="vc-body">
                <div class="vc-ico"><i class="fa fa-shield"></i></div>
                <h4 class="vc-title">Are you sure?</h4>
                <p class="vc-txt">Mark <strong>DVR #<span id="vc_id"></span></strong> for <strong id="vc_cust"></strong> (<span id="vc_date"></span>) as <strong>Verified</strong>?</p>
                <div class="vc-warn"><i class="fa fa-exclamation-triangle"></i> This cannot be undone — a verified DVR can't be set back to Pending Verification.</div>
            </div>
            <div class="vc-foot">
                <button type="button" class="vc-cancel" data-dismiss="modal">Cancel</button>
                <button type="button" class="vc-yes" id="vcYes"><i class="fa fa-check"></i> Yes, Verify</button>
            </div>
        </div>
    </div>
</div>
<div class="dvr-toast" id="dvrToast"></div>

<script>
$(function(){
    var CSRF = $('meta[name="csrf-token"]').attr('content');

    function toast(type, msg){
        var $t = $('#dvrToast');
        $t.removeClass('ok err').addClass(type === 'ok' ? 'ok' : 'err').text(msg).fadeIn(150);
        clearTimeout($t.data('t'));
        $t.data('t', setTimeout(function(){ $t.fadeOut(200); }, 2600));
    }
    function esc(t){ return $('<div>').text(t == null ? '' : t).html(); }
    function bubble(q){
        var side = q.sender_type === 'admin' ? 'admin' : 'employee';
        return '<div class="q-bubble '+side+'"><div class="q-binner">'+esc(q.message)+'</div>'
             + '<div class="q-meta"><strong>'+esc(q.sender_name || (side === 'admin' ? 'Admin' : 'Employee'))+'</strong> &middot; '+esc(q.created_at)
             + (side === 'admin'
                  ? (q.user_read ? ' &middot; <span class="q-seen"><i class="fa fa-check"></i><i class="fa fa-check"></i> Seen by employee</span>'
                                 : ' &middot; <span class="q-sent"><i class="fa fa-check"></i> Sent</span>')
                  : '')
             + '</div></div>';
    }
    function statusPills(dvrStatus, queryStatus){
        var h = '<span class="rv-pill '+(dvrStatus === 'Verified' ? 'ver' : 'pend')+'"><i class="fa '+(dvrStatus === 'Verified' ? 'fa-check-circle' : 'fa-clock-o')+'"></i> '+esc(dvrStatus)+'</span>';
        if (queryStatus) {
            h += '<span class="rv-pill '+(queryStatus === 'Query Raised' ? 'qr' : 'qd')+'"><i class="fa '+(queryStatus === 'Query Raised' ? 'fa-question-circle' : 'fa-reply')+'"></i> '+esc(queryStatus)+'</span>';
        }
        return h;
    }
    function setRowQueryStatus(id, qs){
        var $p = $('#rvq-'+id);
        $('#rvqx-'+id).removeClass('is-qr is-qd').addClass(qs === 'Query Raised' ? 'is-qr' : (qs ? 'is-qd' : ''));
        $('#rvqn-'+id).toggle(!qs);
        if (qs) { $('#rvqb-'+id).contents().filter(function(){ return this.nodeType === 3 && /Raise/.test(this.nodeValue); }).each(function(){ this.nodeValue = this.nodeValue.replace('Raise', 'Open'); }); }
        if (!qs) { $p.hide(); return; }
        $p.removeClass('qr qd').addClass(qs === 'Query Raised' ? 'qr' : 'qd').show();
        $p.find('i').attr('class', 'fa '+(qs === 'Query Raised' ? 'fa-question-circle' : 'fa-reply'));
        $p.find('.rvq-tx').text(qs);
    }
    function scrollBottom(){ var b = document.getElementById('dqChat'); if (b) b.scrollTop = b.scrollHeight; }

    // ── Verify (Pending Verification -> Verified only), after confirmation ──
    $(document).on('click', '.btn-dvr-verify', function(){
        var $b = $(this);
        $('#vc_id').text($b.data('id')); $('#vc_cust').text($b.data('cust')); $('#vc_date').text($b.data('date'));
        $('#vcYes').data('id', $b.data('id')).prop('disabled', false).html('<i class="fa fa-check"></i> Yes, Verify');
        $('#dvrVerifyModal').modal('show');
    });
    $('#vcYes').on('click', function(){
        var $y = $(this), id = $y.data('id');
        $y.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verifying...');
        $.post('{{ url('admin/dvrs') }}/'+id+'/verify', {_token: CSRF})
            .done(function(r){
                if (!r.success) { toast('err', r.message || 'Could not verify.'); return; }
                $('#rvv-'+id).removeClass('is-pend').addClass('is-ver');
                $('#rvst-'+id).removeClass('pend').addClass('ver').html('<i class="fa fa-check-circle"></i> Verified');
                $('#rvv-'+id+' .btn-dvr-verify').replaceWith('<span class="rvx-by" title="'+esc(r.verified_at)+'">by '+esc(r.verified_by_name || '')+'</span>');
                $('#dvrVerifyModal').modal('hide');
                toast('ok', 'DVR #'+id+' marked as Verified.');
            })
            .fail(function(x){
                toast('err', (x.responseJSON && x.responseJSON.message) || 'Could not verify.');
                $y.prop('disabled', false).html('<i class="fa fa-check"></i> Yes, Verify');
            });
    });

    // ── Query thread ──
    $(document).on('click', '.btn-dvr-query', function(){
        var $b = $(this), id = $b.data('id');
        $('#dq_dvr_id').val(id); $('#dq_id').text(id);
        $('#dq_cust').text($b.data('cust')); $('#dq_date').text($b.data('date'));
        $('#dq_msg').val(''); $('#dq_status').html('');
        $('#dqChat').html('<div class="q-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
        $('#dvrQueryModal').modal('show');

        $.get('{{ url('admin/dvrs') }}/'+id+'/queries')
            .done(function(r){
                if (!r.queries.length) {
                    $('#dqChat').html('<div class="q-empty"><i class="fa fa-comments-o" style="font-size:28px;color:#cbd5e1;"></i><span>No queries on this DVR yet.<br>Your first message raises a query and emails the employee.</span></div>');
                } else {
                    $('#dqChat').html($.map(r.queries, bubble).join('')); scrollBottom();
                }
                $('#dq_status').html(statusPills(r.dvr_status, r.query_status));
                // Opening the thread marks the employee's replies as read.
                $b.removeClass('unread').find('.rv-dot').remove();
                $('#rvqc-'+id).text('').hide();
            })
            .fail(function(){ $('#dqChat').html('<div class="q-empty" style="color:#dc2626;">Failed to load the conversation.</div>'); });
    });

    $('#dqSend').on('click', function(){
        var id = $('#dq_dvr_id').val(), msg = $.trim($('#dq_msg').val()), $b = $(this);
        if (!msg) { toast('err', 'Please type your query first.'); $('#dq_msg').focus(); return; }
        $b.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        $.post('{{ url('admin/dvrs') }}/'+id+'/raise-query', {_token: CSRF, message: msg})
            .done(function(r){
                $('#dqChat .q-empty').remove();
                $('#dqChat').append(bubble(r.query)); scrollBottom();
                $('#dq_msg').val('');
                setRowQueryStatus(id, r.query_status);
                $('#dq_status .rv-pill').not(':first').remove();
                $('#dq_status').html(statusPills($('#rvst-'+id).hasClass('ver') ? 'Verified' : 'Pending Verification', r.query_status));
                toast('ok', 'Query sent — the employee has been emailed.');
            })
            .fail(function(x){
                var m = x.responseJSON && (x.responseJSON.message || (x.responseJSON.errors && x.responseJSON.errors.message && x.responseJSON.errors.message[0]));
                toast('err', m || 'Could not send the query.');
            })
            .always(function(){ $b.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send'); });
    });
    $('#dq_msg').on('keydown', function(e){ if (e.ctrlKey && e.key === 'Enter') $('#dqSend').trigger('click'); });
});
</script>

{{-- Placeholder for live stat panel (JS inject) --}}
<div id="liveStatPanel" style="{{ request('customer_filter') ? 'display:none;' : 'display:none;' }}"></div>

@endsection