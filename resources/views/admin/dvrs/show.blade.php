@extends('layouts.adminLayout.backendLayout')

@section('content')
@php
    $customerName = $dvr->customer
        ? $dvr->customer->name
        : optional($dvr->customer_register_request)->name;

    // Status checklist — same wording and meaning as before; ok = green ✓, bad = red ✕
    $entryStatus  = ($dvr->visit_recorded === 'On Site') ? 'Real Time Entry' : 'Post Visit Entry';
    $siteStatus   = $dvr->site_type ?? '—';
    $metStatus    = $dvr->have_you_met ? 'Met Customer' : 'No Meeting';
    $detailStatus = $dvr->is_submitted ? 'Visit Detail Added' : 'Visit Detail Pending';
    $verifyStatus = $dvr->dvr_verified_date_time ? 'Verified' : 'Not Verified';

    $checks = [
        ['k' => 'Visit Type',   'v' => $dvr->visit_type ?? 'Official', 's' => 'ok'],
        ['k' => 'Entry Mode',   'v' => $entryStatus,  's' => $dvr->visit_recorded === 'On Site' ? 'ok' : 'bad'],
        ['k' => 'Site Type',    'v' => $siteStatus,   's' => $dvr->site_type === 'On Site' ? 'ok' : ($dvr->site_type ? 'bad' : 'neu')],
        ['k' => 'Meeting',      'v' => $metStatus,    's' => $dvr->have_you_met ? 'ok' : 'bad'],
        ['k' => 'Visit Detail', 'v' => $detailStatus, 's' => $dvr->is_submitted ? 'ok' : 'bad'],
        ['k' => 'Verification', 'v' => $verifyStatus, 's' => $dvr->dvr_verified_date_time ? 'ok' : 'bad'],
    ];
    $ico = ['ok' => 'fa-check', 'bad' => 'fa-times', 'neu' => 'fa-minus'];

    // Work hours
    $workHours = '—';
    if ($attendance && $attendance->in_time && $attendance->out_time) {
        $in  = \Carbon\Carbon::parse($attendance->in_time);
        $out = \Carbon\Carbon::parse($attendance->out_time);
        $d   = $in->diff($out);
        $workHours = $d->h . 'h ' . $d->i . 'm';
    }

    $initial = function ($name) { return mb_strtoupper(mb_substr(trim((string) $name), 0, 1)) ?: '?'; };
    $isImage = function ($file) { return (bool) preg_match('/\.(jpe?g|png|gif|webp|bmp)$/i', (string) $file); };
@endphp

<style>
/* ═══════════════════════════════════════════
   DVR DETAIL — same visual language as the DVR list
═══════════════════════════════════════════ */
.dd{--dark:#1e293b;--muted:#64748b;--line:#e2e8f0;--soft:#f8fafc;--blue:#3b82f6;padding-bottom:40px;color:var(--dark);}
.dd *{box-sizing:border-box;}

/* breadcrumb */
.dd-bc{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--muted);margin-bottom:12px;}
.dd-bc a{color:var(--blue);text-decoration:none;} .dd-bc a:hover{text-decoration:underline;}
.dd-bc .sep{color:#cbd5e1;font-size:11px;}

/* hero */
.dd-hero{background:#fff;border:1px solid #d6dde8;border-radius:12px;box-shadow:0 2px 10px rgba(15,23,42,.06);overflow:hidden;margin-bottom:16px;}
.dd-hero-top{display:flex;align-items:flex-start;gap:16px;padding:18px 20px;background:linear-gradient(90deg,#eef4ff 0%,#fff 70%);border-bottom:1px solid var(--line);flex-wrap:wrap;}
.dd-av{width:52px;height:52px;border-radius:12px;flex-shrink:0;background:linear-gradient(135deg,#60a5fa,#3b82f6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;box-shadow:0 4px 12px rgba(59,130,246,.3);}
.dd-hd{flex:1;min-width:240px;}
.dd-name{font-size:21px;font-weight:800;margin:0;line-height:1.2;color:var(--dark);}
.dd-meta{display:flex;flex-wrap:wrap;gap:6px 14px;margin-top:6px;font-size:12px;color:var(--muted);}
.dd-meta b{color:var(--dark);font-weight:700;}
.dd-meta i{color:#94a3b8;margin-right:3px;}
.dd-id{display:inline-block;background:var(--dark);color:#fff;font-size:11px;font-weight:800;padding:2px 9px;border-radius:12px;}
.dd-back{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;border:1px solid #bfdbfe;background:#eff6ff;color:#2563eb;font-size:12px;font-weight:700;text-decoration:none;transition:all .15s;}
.dd-back:hover{background:#3b82f6;border-color:#3b82f6;color:#fff;text-decoration:none;}
.dd-chips{display:flex;flex-wrap:wrap;gap:6px;padding:12px 20px;}

/* tinted ✓/✕ chip (same as the list's checklist) */
.st{display:inline-flex;align-items:center;gap:6px;padding:3px 10px 3px 4px;border-radius:14px;border:1px solid;font-size:11px;font-weight:600;line-height:1.3;white-space:nowrap;}
.st-ic{width:16px;height:16px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;font-size:8.5px;color:#fff;}
.st.ok {background:#f0fdf4;border-color:#bbf7d0;color:#166534;} .st.ok  .st-ic{background:#22c55e;}
.st.bad{background:#fef2f2;border-color:#fecaca;color:#b91c1c;} .st.bad .st-ic{background:#ef4444;}
.st.neu{background:#f8fafc;border-color:#e2e8f0;color:#475569;} .st.neu .st-ic{background:#94a3b8;}

/* time tiles */
.dd-tiles{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px;}
.dd-tile{background:#fff;border:1px solid var(--line);border-radius:10px;padding:12px 14px;display:flex;align-items:center;gap:12px;box-shadow:0 1px 3px rgba(15,23,42,.04);}
.dd-tile .ti{width:38px;height:38px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:16px;border:1px solid;}
.dd-tile .tk{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;}
.dd-tile .tv{font-size:16px;font-weight:800;color:var(--dark);line-height:1.25;}
.ti.in {background:#f0fdf4;border-color:#bbf7d0;color:#16a34a;}
.ti.out{background:#fef2f2;border-color:#fecaca;color:#dc2626;}
.ti.car{background:#eff6ff;border-color:#bfdbfe;color:#2563eb;}
.ti.rec{background:#f5f3ff;border-color:#ddd6fe;color:#7c3aed;}

/* layout */
.dd-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:16px;align-items:start;}
.dd-col{display:flex;flex-direction:column;gap:16px;min-width:0;}

/* card */
.dd-card{background:#fff;border:1px solid #d6dde8;border-radius:12px;box-shadow:0 2px 10px rgba(15,23,42,.05);overflow:hidden;}
.dd-ch{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:11px 16px;border-bottom:1px solid var(--line);background:#fbfcfe;}
.dd-ct{display:flex;align-items:center;gap:9px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--dark);margin:0;}
.dd-ct .ci{width:26px;height:26px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;border:1px solid;}
.ci.blue  {background:#eff6ff;border-color:#bfdbfe;color:#2563eb;}
.ci.green {background:#f0fdf4;border-color:#bbf7d0;color:#16a34a;}
.ci.violet{background:#f5f3ff;border-color:#ddd6fe;color:#7c3aed;}
.ci.amber {background:#fffbeb;border-color:#fde68a;color:#d97706;}
.ci.slate {background:#f8fafc;border-color:#e2e8f0;color:#475569;}
.dd-cnt{font-size:10.5px;font-weight:800;background:#eef2ff;color:#4338ca;padding:1px 8px;border-radius:10px;}
.dd-cb{padding:14px 16px;}
.dd-empty{text-align:center;color:#94a3b8;font-size:12px;padding:14px 0;}
.dd-empty i{display:block;font-size:22px;color:#cbd5e1;margin-bottom:6px;}

/* route (start → end location) */
.dd-route{position:relative;padding-left:22px;margin-bottom:14px;}
.dd-route:before{content:'';position:absolute;left:7px;top:10px;bottom:10px;border-left:2px dashed #cbd5e1;}
.dd-stop{position:relative;padding:2px 0 10px;}
.dd-stop:last-child{padding-bottom:0;}
.dd-stop .dot{position:absolute;left:-22px;top:4px;width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 1px var(--line);}
.dd-stop.s .dot{background:#22c55e;} .dd-stop.e .dot{background:#ef4444;}
.dd-stop .sk{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;}
.dd-stop .sv{font-size:12.5px;color:var(--dark);line-height:1.5;}

/* text sections */
.dd-sec{border:1px solid var(--line);border-left:3px solid var(--blue);border-radius:0 8px 8px 0;background:var(--soft);padding:10px 12px;margin-bottom:10px;}
.dd-sec:last-child{margin-bottom:0;}
.dd-sec.green{border-left-color:#22c55e;background:#f7fdf9;}
.dd-sec.amber{border-left-color:#f59e0b;background:#fffdf5;}
.dd-sec.violet{border-left-color:#8b5cf6;background:#fbfaff;}
.dd-sk{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:5px;}
.dd-sk i{margin-right:4px;}
.dd-sv{font-size:13px;line-height:1.6;color:var(--dark);white-space:pre-line;overflow-wrap:anywhere;}
.dd-tags{display:flex;flex-wrap:wrap;gap:5px;}
.tag{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:7px;border:1px solid;font-size:11px;font-weight:600;}
.tag.purp{background:#f5f3ff;border-color:#ddd6fe;color:#6d28d9;}
.tag.prod{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;}
.tag.type{background:#ecfeff;border-color:#a5f3fc;color:#0e7490;}

/* trials */
.dd-trial{border:1px solid var(--line);border-radius:10px;padding:12px 14px;margin-bottom:10px;background:#fff;}
.dd-trial:last-child{margin-bottom:0;}
.dd-trial-h{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:8px;}
.dd-trial-n{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:800;color:var(--dark);}
.dd-trial-n .num{width:24px;height:24px;border-radius:50%;background:#7c3aed;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:11px;}
.dd-trial-row{font-size:12px;color:var(--dark);margin-top:6px;line-height:1.55;}
.dd-trial-row .lbl{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;margin-right:6px;}

/* products */
.dd-prods{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px;}
.dd-prod{display:flex;align-items:center;gap:10px;border:1px solid #dbeafe;border-radius:10px;background:#f8fbff;padding:10px 12px;}
.dd-prod .pi{width:32px;height:32px;border-radius:8px;background:#fff;border:1px solid #bfdbfe;color:#2563eb;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.dd-prod .pn{font-size:12px;font-weight:700;color:var(--dark);line-height:1.3;}
.dd-prod .pq{font-size:11px;color:var(--muted);}

/* attachments */
.dd-atts{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;}
.dd-att{border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#fff;text-decoration:none;display:flex;flex-direction:column;transition:all .15s;}
.dd-att:hover{border-color:#93c5fd;box-shadow:0 4px 14px rgba(59,130,246,.15);text-decoration:none;transform:translateY(-1px);}
.dd-att .thumb{height:100px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.dd-att .thumb img{width:100%;height:100%;object-fit:cover;}
.dd-att .thumb i{font-size:30px;color:#94a3b8;}
.dd-att .an{padding:7px 9px;font-size:11px;font-weight:600;color:var(--dark);display:flex;align-items:center;justify-content:space-between;gap:6px;}
.dd-att .an span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.dd-att .an i{color:#2563eb;}
.dd-afile{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:7px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:600;text-decoration:none;margin:3px 4px 0 0;}
.dd-afile:hover{background:#3b82f6;border-color:#3b82f6;color:#fff;text-decoration:none;}

/* key-value rows */
.kv{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:8px 0;border-bottom:1px dashed var(--line);font-size:12.5px;}
.kv:last-child{border-bottom:none;padding-bottom:0;}
.kv:first-child{padding-top:0;}
.kv .k{color:var(--muted);font-weight:600;white-space:nowrap;}
.kv .k i{width:14px;color:#94a3b8;margin-right:4px;}
.kv .v{color:var(--dark);font-weight:600;text-align:right;overflow-wrap:anywhere;}
.kv .v.small{font-size:11.5px;font-weight:500;}

/* customer */
.dd-cust{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
.dd-cust .ca{width:40px;height:40px;border-radius:10px;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:800;flex-shrink:0;}
.dd-cust .cn{font-size:15px;font-weight:800;color:var(--dark);line-height:1.25;}
.c-tag{display:inline-flex;align-items:center;gap:4px;margin-top:3px;padding:1px 8px;border-radius:7px;border:1px solid;font-size:10.5px;font-weight:700;}
.c-tag.reg{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;}
.c-tag.req{background:#fffbeb;border-color:#fde68a;color:#b45309;}

/* contacts */
.mw{display:flex;align-items:flex-start;gap:10px;}
.mw + .mw{margin-top:10px;padding-top:10px;border-top:1px dashed var(--line);}
.mw-av{width:34px;height:34px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;font-size:13px;font-weight:800;}
.mw-name{font-size:13px;font-weight:700;color:var(--dark);}
.mw-des{font-size:11.5px;color:var(--muted);}
.mw-ph{display:flex;align-items:center;gap:5px;margin-top:4px;font-size:12px;font-weight:600;color:#334155;}
.mw-ph i,.mw-dep i{width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:9px;}
.mw-ph i{background:#dcfce7;color:#16a34a;}
.mw-dep{display:flex;align-items:center;gap:5px;margin-top:3px;font-size:11.5px;color:var(--muted);}
.mw-dep i{background:#f1f5f9;color:#64748b;}

/* attendance */
.att-in,.att-out{display:flex;align-items:center;justify-content:space-between;padding:7px 10px;border-radius:8px;border:1px solid;font-size:12.5px;margin-bottom:6px;}
.att-in {background:#f0fdf4;border-color:#bbf7d0;color:#166534;}
.att-out{background:#fef2f2;border-color:#fecaca;color:#b91c1c;}
.att-in b,.att-out b{font-weight:800;}

/* status summary */
.sum-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px dashed var(--line);}
.sum-row:last-child{border-bottom:none;padding-bottom:0;}
.sum-row:first-child{padding-top:0;}
.sum-row .k{font-size:12px;color:var(--muted);font-weight:600;}


/* ── Verification banner (admin review: Pending Verification -> Verified, one way) ── */
.vb{display:flex;align-items:center;gap:16px;padding:16px 20px;border-radius:12px;border:1px solid;margin-bottom:16px;box-shadow:0 2px 10px rgba(15,23,42,.05);flex-wrap:wrap;}
.vb.pend{background:linear-gradient(90deg,#fffbeb 0%,#fff 75%);border-color:#fde68a;border-left:6px solid #f59e0b;}
.vb.ver {background:linear-gradient(90deg,#f0fdf4 0%,#fff 75%);border-color:#bbf7d0;border-left:6px solid #22c55e;}
.vb-ic{width:52px;height:52px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:24px;color:#fff;}
.vb.pend .vb-ic{background:linear-gradient(135deg,#fbbf24,#f59e0b);box-shadow:0 4px 12px rgba(245,158,11,.35);}
.vb.ver  .vb-ic{background:linear-gradient(135deg,#4ade80,#16a34a);box-shadow:0 4px 12px rgba(22,163,74,.35);}
.vb-tx{flex:1;min-width:220px;}
.vb-k{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:#94a3b8;}
.vb-v{font-size:24px;font-weight:900;line-height:1.15;letter-spacing:-.3px;}
.vb.pend .vb-v{color:#b45309;} .vb.ver .vb-v{color:#15803d;}
.vb-sub{font-size:12px;color:#64748b;margin-top:3px;}
.vb-sub b{color:#1e293b;}
.vb-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:10px;border:none;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;font-size:14px;font-weight:800;box-shadow:0 4px 14px rgba(22,163,74,.3);transition:all .15s;}
.vb-btn:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(22,163,74,.4);}
.vb-lock{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;color:#15803d;background:#dcfce7;border:1px solid #bbf7d0;padding:6px 12px;border-radius:20px;}
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

/* ── Queries (inline thread, same backend as the list's Query popup) ── */
.dq-pill{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:12px;border:1px solid;font-size:10.5px;font-weight:700;}
.dq-pill.qr{background:#fef2f2;border-color:#fecaca;color:#b91c1c;}
.dq-pill.qd{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;}
.dq-pill.nq{background:#f8fafc;border-color:#e2e8f0;color:#64748b;}
.dq-chat{max-height:380px;min-height:120px;overflow-y:auto;padding:14px 16px;background:#f7f9fc;border-bottom:1px solid var(--line);}
.q-bubble{max-width:80%;margin-bottom:12px;}
.q-bubble.admin{margin-left:20%;text-align:right;}
.q-bubble.employee{margin-right:20%;}
.q-binner{display:inline-block;text-align:left;padding:9px 13px;border-radius:10px;font-size:13px;line-height:1.5;word-break:break-word;white-space:pre-wrap;}
.q-bubble.admin .q-binner{background:linear-gradient(135deg,#6366f1,#4338ca);color:#fff;border-radius:10px 10px 2px 10px;}
.q-bubble.employee .q-binner{background:#fff;color:#1e293b;border:1px solid #e2e8f0;border-radius:10px 10px 10px 2px;box-shadow:0 1px 3px rgba(0,0,0,.05);}
.q-meta{font-size:10.5px;color:#94a3b8;margin-top:3px;}
.q-seen{color:#16a34a;font-weight:700;} .q-seen i+i{margin-left:-4px;}
.q-sent{color:#94a3b8;font-weight:600;}
.q-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100px;color:#94a3b8;font-size:12.5px;gap:6px;text-align:center;}
.q-empty i{font-size:26px;color:#cbd5e1;}
.dq-reply{padding:12px 16px 14px;}
.dq-reply label{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:6px;display:block;}
.dq-reply textarea{width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 12px;font-size:13px;resize:vertical;outline:none;background:#fafbfc;min-height:70px;}
.dq-reply textarea:focus{border-color:#6366f1;background:#fff;box-shadow:0 0 0 3px rgba(99,102,241,.12);}
.dq-actions{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:8px;}
.dq-hint{font-size:11px;color:#94a3b8;}
.dq-send{background:linear-gradient(135deg,#6366f1,#4338ca);color:#fff;border:none;border-radius:8px;padding:8px 20px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;}
.dq-send:disabled{opacity:.55;cursor:not-allowed;}
.dvr-toast{position:fixed;right:22px;bottom:22px;z-index:20000;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 6px 18px rgba(0,0,0,.15);display:none;}
.dvr-toast.ok{background:#dcfce7;color:#166534;border:1px solid #86efac;}
.dvr-toast.err{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}

@media (max-width:1199px){ .dd-grid{grid-template-columns:minmax(0,1fr) 300px;} }
@media (max-width:991px){ .dd-grid{grid-template-columns:1fr;} .dd-tiles{grid-template-columns:repeat(2,minmax(0,1fr));} }
@media (max-width:560px){ .dd-tiles{grid-template-columns:1fr;} }
</style>

<div class="page-content-wrapper">
<div class="page-content">
<div class="dd">

{{-- BREADCRUMB --}}
<div class="dd-bc">
    <a href="{{ url('admin/dashboard') }}"><i class="fa fa-home"></i> Home</a>
    <span class="sep"><i class="fa fa-angle-right"></i></span>
    <a href="{{ url('admin/dvrs') }}">Daily Visit Reports</a>
    <span class="sep"><i class="fa fa-angle-right"></i></span>
    <span>DVR #{{ $dvr->id }}</span>
</div>

{{-- HERO --}}
<div class="dd-hero">
    <div class="dd-hero-top">
        <div class="dd-av">{{ $initial($customerName ?? 'C') }}</div>
        <div class="dd-hd">
            <h3 class="dd-name">{{ $customerName ?? 'Customer N/A' }}</h3>
            <div class="dd-meta">
                <span class="dd-id">DVR #{{ $dvr->id }}</span>
                <span><i class="fa fa-calendar"></i><b>{{ \Carbon\Carbon::parse($dvr->dvr_date)->format('l, d M Y') }}</b></span>
                <span><i class="fa fa-user"></i>By <b>{{ optional($dvr->user)->name }}</b></span>
            </div>
        </div>
        <a href="{{ url('admin/dvrs') }}" class="dd-back"><i class="fa fa-arrow-left"></i> Back to List</a>
    </div>
    <div class="dd-chips">
        @foreach($checks as $c)
        <span class="st {{ $c['s'] }}" title="{{ $c['k'] }}"><span class="st-ic"><i class="fa {{ $ico[$c['s']] }}"></i></span>{{ $c['v'] }}</span>
        @endforeach
    </div>
</div>

{{-- VERIFICATION (admin review) --}}
@php
    $isVer      = $dvr->dvr_status === 'Verified';
    $verifiedBy = optional($dvr->verifier)->name;
    $verifiedAt = $dvr->dvr_verified_at ? \Carbon\Carbon::parse($dvr->dvr_verified_at)->format('d M Y, h:i A') : null;
@endphp
<div class="vb {{ $isVer ? 'ver' : 'pend' }}" id="vBanner">
    <span class="vb-ic"><i class="fa {{ $isVer ? 'fa-check' : 'fa-clock-o' }}"></i></span>
    <div class="vb-tx">
        <div class="vb-k">DVR Status</div>
        <div class="vb-v" id="vbVal">{{ $isVer ? 'Verified' : 'Pending Verification' }}</div>
        <div class="vb-sub" id="vbSub">
            @if($isVer)
                @if($verifiedBy)Verified by <b>{{ $verifiedBy }}</b> on <b>{{ $verifiedAt }}</b>@else Verified (visit on or before 31 Oct 2026)@endif
            @else
                Review the visit and the queries below, then mark it as verified.
            @endif
        </div>
    </div>
    <div id="vbAct">
        @if($isVer)
            <span class="vb-lock"><i class="fa fa-lock"></i> Verified — can't be changed back</span>
        @else
            <button type="button" class="vb-btn" id="vbVerify"><i class="fa fa-check-circle"></i> Verify DVR</button>
        @endif
    </div>
</div>

{{-- TIME TILES --}}
<div class="dd-tiles">
    <div class="dd-tile">
        <span class="ti in"><i class="fa fa-sign-in"></i></span>
        <div><div class="tk">Check In</div><div class="tv">{{ $dvr->start_time ? \Carbon\Carbon::parse($dvr->start_time)->format('h:i A') : '—' }}</div></div>
    </div>
    <div class="dd-tile">
        <span class="ti out"><i class="fa fa-sign-out"></i></span>
        <div><div class="tk">Check Out</div><div class="tv">{{ $dvr->end_time ? \Carbon\Carbon::parse($dvr->end_time)->format('h:i A') : '—' }}</div></div>
    </div>
    <div class="dd-tile">
        <span class="ti car"><i class="fa fa-car"></i></span>
        <div><div class="tk">Conveyance</div><div class="tv">{{ $dvr->conveyance_type ?? '—' }}</div></div>
    </div>
    <div class="dd-tile">
        <span class="ti rec"><i class="fa fa-check-square-o"></i></span>
        <div><div class="tk">Visit Recorded</div><div class="tv">{{ $dvr->visit_recorded ?? '—' }}</div></div>
    </div>
</div>

<div class="dd-grid">

{{-- ════════════ LEFT ════════════ --}}
<div class="dd-col">

    {{-- VISIT INFORMATION --}}
    <div class="dd-card">
        <div class="dd-ch"><h4 class="dd-ct"><span class="ci blue"><i class="fa fa-info"></i></span> Visit Information</h4></div>
        <div class="dd-cb">

            <div class="dd-route">
                <div class="dd-stop s"><span class="dot"></span>
                    <div class="sk">Start Location</div>
                    <div class="sv">{{ $dvr->start_location ?? '—' }}</div>
                </div>
                <div class="dd-stop e"><span class="dot"></span>
                    <div class="sk">End Location</div>
                    <div class="sv">{{ $dvr->end_location ?? '—' }}</div>
                </div>
            </div>

            <div class="kv"><span class="k"><i class="fa fa-calendar"></i>DVR Date</span><span class="v">{{ \Carbon\Carbon::parse($dvr->dvr_date)->format('d M Y') }}</span></div>
            <div class="kv"><span class="k"><i class="fa fa-shield"></i>Site Type</span><span class="v">{{ $dvr->site_type ?? '—' }}</span></div>
            <div class="kv" style="margin-bottom:14px;">
                <span class="k"><i class="fa fa-calendar"></i>Verified At</span>
                <span class="v">
                    @if($dvr->dvr_verified_date_time)
                        {{ \Carbon\Carbon::parse($dvr->dvr_verified_date_time)->format('d M Y h:i A') }}
                    @else
                        <span class="st bad"><span class="st-ic"><i class="fa fa-times"></i></span>Pending</span>
                    @endif
                </span>
            </div>

            @if($dvr->purpose_of_visit)
            <div class="dd-sec violet">
                <div class="dd-sk"><i class="fa fa-bullseye"></i>Purpose of Visit</div>
                <div class="dd-tags">
                    @foreach(array_filter(array_map('trim', explode(',,,', $dvr->purpose_of_visit))) as $p)
                        <span class="tag purp">{{ $p }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if($dvr->other_purpose)
            <div class="dd-sec">
                <div class="dd-sk"><i class="fa fa-comment-o"></i>Other Purpose</div>
                <div class="dd-sv">{{ $dvr->other_purpose }}</div>
            </div>
            @endif

            @if($dvr->visit_detail)
            <div class="dd-sec">
                <div class="dd-sk"><i class="fa fa-file-text-o"></i>Visit Details</div>
                <div class="dd-sv">{{ $dvr->visit_detail }}</div>
            </div>
            @endif

            @if($dvr->remarks)
            <div class="dd-sec amber">
                <div class="dd-sk"><i class="fa fa-pencil"></i>Remarks</div>
                <div class="dd-sv">{{ $dvr->remarks }}</div>
            </div>
            @endif

            @if($dvr->next_plan)
            <div class="dd-sec green">
                <div class="dd-sk" style="color:#16a34a;"><i class="fa fa-forward"></i>Next Plan</div>
                <div class="dd-sv">{{ $dvr->next_plan }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- QUERIES — the admin query thread; reply from here or from the list's Query popup --}}
    @php $qs = $dvr->query_status; @endphp
    <div class="dd-card" id="dqCard">
        <div class="dd-ch">
            <h4 class="dd-ct"><span class="ci violet"><i class="fa fa-comments-o"></i></span> Queries</h4>
            <div style="display:flex;align-items:center;gap:6px;">
                <span class="dq-pill {{ $qs === 'Query Raised' ? 'qr' : ($qs ? 'qd' : 'nq') }}" id="dqStatus">
                    <i class="fa {{ $qs === 'Query Raised' ? 'fa-question-circle' : ($qs ? 'fa-reply' : 'fa-minus') }}"></i>
                    <span>{{ $qs ?: 'No query raised' }}</span>
                </span>
                <span class="dd-cnt" id="dqCount" style="display:none;"></span>
            </div>
        </div>
        <div class="dq-chat" id="dqChat"><div class="q-empty"><i class="fa fa-spinner fa-spin"></i> Loading conversation...</div></div>
        <div class="dq-reply">
            <label><i class="fa fa-reply"></i> &nbsp;Raise a Query / Reply to {{ optional($dvr->user)->name ?: 'the employee' }}</label>
            <textarea id="dqMsg" rows="3" maxlength="2000" placeholder="Type your query for the employee..."></textarea>
            <div class="dq-actions">
                <span class="dq-hint"><i class="fa fa-envelope-o"></i> The employee is emailed when you send. Ctrl+Enter to send.</span>
                <button type="button" class="dq-send" id="dqSend"><i class="fa fa-paper-plane"></i> Send</button>
            </div>
        </div>
    </div>

    {{-- TRIALS --}}
    @if($dvr->trials->count())
    <div class="dd-card">
        <div class="dd-ch">
            <h4 class="dd-ct"><span class="ci violet"><i class="fa fa-flask"></i></span> Trials</h4>
            <span class="dd-cnt">{{ $dvr->trials->count() }}</span>
        </div>
        <div class="dd-cb">
            @foreach($dvr->trials as $i => $trial)
            @php
                if (!$trial->trial_done) {
                    $tStatus = 'Trial Not Done'; $tS = 'bad';
                } else {
                    $hasRpt = $trial->attachments->where('type','trial_report')->count() > 0 || !is_null($trial->trial_report_id);
                    $tStatus = $hasRpt ? 'Report Attached' : 'Trial Report Pending';
                    $tS      = $hasRpt ? 'ok' : 'bad';
                }
            @endphp
            <div class="dd-trial">
                <div class="dd-trial-h">
                    <span class="dd-trial-n"><span class="num">{{ $i+1 }}</span> Trial #{{ $i+1 }}</span>
                    <div class="dd-tags">
                        <span class="st {{ $trial->trial_done ? 'ok' : 'neu' }}"><span class="st-ic"><i class="fa {{ $trial->trial_done ? 'fa-check' : 'fa-minus' }}"></i></span>{{ $trial->trial_done ? 'Done' : 'Not Done' }}</span>
                        <span class="st {{ $tS }}"><span class="st-ic"><i class="fa {{ $ico[$tS] }}"></i></span>{{ $tStatus }}</span>
                        @if($trial->trial_type)<span class="tag type">{{ $trial->trial_type }}</span>@endif
                    </div>
                </div>
                @if($trial->objective)
                <div class="dd-trial-row"><span class="lbl">Objective</span>{{ $trial->objective }}</div>
                @endif
                @if($trial->products->count())
                <div class="dd-trial-row"><span class="lbl">Products</span>
                    @foreach($trial->products as $tp)
                        <span class="tag prod">{{ optional($tp->productinfo)->product_name ?? '—' }}</span>
                    @endforeach
                </div>
                @endif
                @if($trial->remarks)
                <div class="dd-trial-row" style="color:#475569;"><i class="fa fa-comment-o" style="color:#94a3b8;"></i> {{ $trial->remarks }}</div>
                @endif
                @if($trial->attachments->count())
                <div class="dd-trial-row"><span class="lbl">Attachments</span><br>
                    @foreach($trial->attachments as $ta)
                        <a href="{{ url('DvrAttachments/'.$dvr->id.'/'.$ta->file) }}" target="_blank" class="dd-afile">
                            <i class="fa {{ $isImage($ta->file) ? 'fa-file-image-o' : 'fa-file-o' }}"></i> {{ $ta->label ?? $ta->type }}
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- PRODUCTS --}}
    @if($dvr->products->count())
    <div class="dd-card">
        <div class="dd-ch">
            <h4 class="dd-ct"><span class="ci green"><i class="fa fa-tags"></i></span> Products Discussed</h4>
            <span class="dd-cnt">{{ $dvr->products->count() }}</span>
        </div>
        <div class="dd-cb">
            <div class="dd-prods">
                @foreach($dvr->products as $p)
                <div class="dd-prod">
                    <span class="pi"><i class="fa fa-tag"></i></span>
                    <div>
                        <div class="pn">{{ optional($p->productinfo)->product_name ?? 'Product' }}</div>
                        @if($p->quantity ?? false)<div class="pq">Qty: {{ $p->quantity }}</div>@endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ATTACHMENTS --}}
    @if($dvr->attachments->count())
    <div class="dd-card">
        <div class="dd-ch">
            <h4 class="dd-ct"><span class="ci slate"><i class="fa fa-paperclip"></i></span> DVR Attachments</h4>
            <span class="dd-cnt">{{ $dvr->attachments->count() }}</span>
        </div>
        <div class="dd-cb">
            <div class="dd-atts">
                @foreach($dvr->attachments as $att)
                <a href="{{ $att->file_url }}" target="_blank" class="dd-att">
                    <div class="thumb">
                        @if($isImage($att->file))
                            {{-- Falls back to a file icon if the image can't be loaded --}}
                            <img src="{{ $att->file_url }}" alt="{{ $att->label ?? $att->type }}" loading="lazy"
                                 onerror="this.outerHTML='<i class=&quot;fa fa-file-image-o&quot;></i>';">
                        @else
                            <i class="fa fa-file-o"></i>
                        @endif
                    </div>
                    <div class="an"><span>{{ $att->label ?? $att->type }}</span><i class="fa fa-eye"></i></div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ════════════ RIGHT ════════════ --}}
<div class="dd-col">

    {{-- CUSTOMER --}}
    <div class="dd-card">
        <div class="dd-ch"><h4 class="dd-ct"><span class="ci blue"><i class="fa fa-building-o"></i></span> Customer</h4></div>
        <div class="dd-cb">
            <div class="dd-cust">
                <span class="ca">{{ $initial($customerName ?? 'C') }}</span>
                <div>
                    <div class="cn">{{ $customerName ?? 'N/A' }}</div>
                    @if($dvr->customer)
                        <span class="c-tag reg"><i class="fa fa-check-circle"></i> Registered Customer</span>
                    @elseif($dvr->customer_register_request)
                        <span class="c-tag req"><i class="fa fa-plus-circle"></i> Register Request</span>
                    @endif
                </div>
            </div>
            @if($dvr->customer)
                <div class="kv"><span class="k">Category</span><span class="v">{{ $dvr->customer->category }}</span></div>
                <div class="kv"><span class="k">Activity</span><span class="v">{{ Str::limit($dvr->customer->activity, 50) }}</span></div>
                <div class="kv"><span class="k">Mobile</span><span class="v">{{ $dvr->customer->mobile }}</span></div>
                <div class="kv"><span class="k">Address</span><span class="v small">{{ $dvr->customer->address }}</span></div>
            @elseif($dvr->customer_register_request)
                <div class="kv"><span class="k">Activity</span><span class="v">{{ $dvr->customer_register_request->activity }}</span></div>
                <div class="kv"><span class="k">Category</span><span class="v">{{ $dvr->customer_register_request->category }}</span></div>
                <div class="kv"><span class="k">Mobile</span><span class="v">{{ $dvr->customer_register_request->mobile }}</span></div>
                <div class="kv"><span class="k">Status</span><span class="v"><span class="tag type">{{ $dvr->customer_register_request->status }}</span></span></div>
            @endif
        </div>
    </div>

    {{-- CONTACTS MET --}}
    <div class="dd-card">
        <div class="dd-ch"><h4 class="dd-ct"><span class="ci blue"><i class="fa fa-users"></i></span> Contacts Met</h4></div>
        <div class="dd-cb">
            @if($dvr->customerContacts->count())
                @foreach($dvr->customerContacts as $cc)
                    @if($cc->customerContact)
                    <div class="mw">
                        <span class="mw-av">{{ $initial($cc->customerContact->name) }}</span>
                        <div>
                            <div class="mw-name">{{ $cc->customerContact->name }}</div>
                            @if($cc->customerContact->designation)<div class="mw-des">{{ $cc->customerContact->designation }}</div>@endif
                            @if($cc->customerContact->department ?? false)<div class="mw-dep"><i class="fa fa-briefcase"></i> {{ $cc->customerContact->department }}</div>@endif
                            @if($cc->customerContact->mobile_number)<div class="mw-ph"><i class="fa fa-phone"></i> {{ $cc->customerContact->mobile_number }}</div>@endif
                        </div>
                    </div>
                    @endif
                @endforeach
            @elseif($dvr->customer_contact_info)
                <div class="mw">
                    <span class="mw-av">{{ $initial($dvr->customer_contact_info->name) }}</span>
                    <div>
                        <div class="mw-name">{{ $dvr->customer_contact_info->name }}</div>
                        @if($dvr->customer_contact_info->designation)<div class="mw-des">{{ $dvr->customer_contact_info->designation }}</div>@endif
                        @if($dvr->customer_contact_info->mobile_number)<div class="mw-ph"><i class="fa fa-phone"></i> {{ $dvr->customer_contact_info->mobile_number }}</div>@endif
                    </div>
                </div>
            @else
                <div class="dd-empty"><i class="fa fa-user"></i> No contacts recorded</div>
            @endif
        </div>
    </div>

    {{-- ATTENDANCE --}}
    <div class="dd-card">
        <div class="dd-ch">
            <h4 class="dd-ct"><span class="ci green"><i class="fa fa-calendar"></i></span> Attendance</h4>
            @if($attendance)
            <span class="st {{ $attendance->status === 'Present' ? 'ok' : 'bad' }}"><span class="st-ic"><i class="fa {{ $attendance->status === 'Present' ? 'fa-check' : 'fa-times' }}"></i></span>{{ $attendance->status ?? '—' }}</span>
            @endif
        </div>
        <div class="dd-cb">
            @if($attendance)
                <div class="att-in"><span><i class="fa fa-sign-in"></i> Punch In</span><b>{{ $attendance->in_time ?? '—' }}</b></div>
                <div class="att-out"><span><i class="fa fa-sign-out"></i> Punch Out</span><b>{{ $attendance->out_time ?? '—' }}</b></div>
                <div class="kv" style="margin-top:6px;"><span class="k"><i class="fa fa-clock-o"></i>Work Hours</span><span class="v">{{ $workHours }}</span></div>
                <div class="kv"><span class="k"><i class="fa fa-map-marker"></i>In Location</span><span class="v small">{{ $attendance->in_place_of_attendance ?? '—' }}</span></div>
                <div class="kv"><span class="k"><i class="fa fa-map-marker"></i>Out Location</span><span class="v small">{{ $attendance->out_place_of_attendance ?? '—' }}</span></div>
            @else
                <div class="dd-empty"><i class="fa fa-calendar"></i> No attendance record for this date</div>
            @endif
        </div>
    </div>

    {{-- STATUS SUMMARY --}}
    <div class="dd-card">
        <div class="dd-ch"><h4 class="dd-ct"><span class="ci amber"><i class="fa fa-list-ul"></i></span> Status Summary</h4></div>
        <div class="dd-cb">
            @foreach($checks as $c)
            <div class="sum-row">
                <span class="k">{{ $c['k'] }}</span>
                <span class="st {{ $c['s'] }}"><span class="st-ic"><i class="fa {{ $ico[$c['s']] }}"></i></span>{{ $c['v'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

</div>{{-- .dd-grid --}}

</div>
</div>
</div>
{{-- VERIFY CONFIRMATION --}}
<div class="modal fade" id="dvrVerifyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document" style="width:400px;max-width:94vw;margin:90px auto;">
        <div class="modal-content">
            <div class="vc-body">
                <div class="vc-ico"><i class="fa fa-shield"></i></div>
                <h4 class="vc-title">Are you sure?</h4>
                <p class="vc-txt">Mark <strong>DVR #{{ $dvr->id }}</strong> for <strong>{{ $customerName ?? 'this customer' }}</strong> ({{ \Carbon\Carbon::parse($dvr->dvr_date)->format('d M Y') }}) as <strong>Verified</strong>?</p>
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
    var DVR_ID = {{ $dvr->id }};
    var BASE   = '{{ url('admin/dvrs') }}/' + DVR_ID;
    var CSRF   = $('meta[name="csrf-token"]').attr('content');

    function toast(type, msg){
        var $t = $('#dvrToast');
        $t.removeClass('ok err').addClass(type).text(msg).fadeIn(150);
        clearTimeout($t.data('t')); $t.data('t', setTimeout(function(){ $t.fadeOut(200); }, 2600));
    }
    function esc(t){ return $('<div>').text(t == null ? '' : t).html(); }
    function bubble(q){
        var side = q.sender_type === 'admin' ? 'admin' : 'employee';
        var receipt = side === 'admin'
            ? (q.user_read ? ' &middot; <span class="q-seen"><i class="fa fa-check"></i><i class="fa fa-check"></i> Seen by employee</span>'
                           : ' &middot; <span class="q-sent"><i class="fa fa-check"></i> Sent</span>')
            : '';
        return '<div class="q-bubble '+side+'"><div class="q-binner">'+esc(q.message)+'</div>'
             + '<div class="q-meta"><strong>'+esc(q.sender_name || (side === 'admin' ? 'Admin' : 'Employee'))+'</strong> &middot; '+esc(q.created_at)+receipt+'</div></div>';
    }
    function setStatus(qs){
        var cls = qs === 'Query Raised' ? 'qr' : (qs ? 'qd' : 'nq');
        var ico = qs === 'Query Raised' ? 'fa-question-circle' : (qs ? 'fa-reply' : 'fa-minus');
        $('#dqStatus').attr('class', 'dq-pill '+cls).html('<i class="fa '+ico+'"></i> <span>'+esc(qs || 'No query raised')+'</span>');
    }
    function setCount(n){ n ? $('#dqCount').text(n + (n === 1 ? ' message' : ' messages')).show() : $('#dqCount').hide(); }
    function scrollBottom(){ var b = document.getElementById('dqChat'); b.scrollTop = b.scrollHeight; }

    // ── Verify: Pending Verification -> Verified (one way), after confirmation ──
    $('#vbVerify').on('click', function(){
        $('#vcYes').prop('disabled', false).html('<i class="fa fa-check"></i> Yes, Verify');
        $('#dvrVerifyModal').modal('show');
    });
    $('#vcYes').on('click', function(){
        var $y = $(this);
        $y.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verifying...');
        $.post(BASE + '/verify', {_token: CSRF})
            .done(function(r){
                if (!r.success) { toast('err', r.message || 'Could not verify.'); return; }
                $('#vBanner').removeClass('pend').addClass('ver').find('.vb-ic i').attr('class', 'fa fa-check');
                $('#vbVal').text('Verified');
                $('#vbSub').html('Verified by <b>'+esc(r.verified_by_name || '')+'</b> on <b>'+esc(r.verified_at)+'</b>');
                $('#vbAct').html('<span class="vb-lock"><i class="fa fa-lock"></i> Verified — can\'t be changed back</span>');
                $('#dvrVerifyModal').modal('hide');
                toast('ok', 'DVR #'+DVR_ID+' marked as Verified.');
            })
            .fail(function(x){
                toast('err', (x.responseJSON && x.responseJSON.message) || 'Could not verify.');
                $y.prop('disabled', false).html('<i class="fa fa-check"></i> Yes, Verify');
            });
    });
    var EMPTY = '<div class="q-empty"><i class="fa fa-comments-o"></i><span>No queries on this DVR yet.<br>Your first message raises a query and emails the employee.</span></div>';

    // Load the thread — this also marks the employee's replies as seen by admin.
    $.get(BASE + '/queries')
        .done(function(r){
            $('#dqChat').html(r.queries.length ? $.map(r.queries, bubble).join('') : EMPTY);
            setStatus(r.query_status); setCount(r.total); scrollBottom();
        })
        .fail(function(){ $('#dqChat').html('<div class="q-empty" style="color:#dc2626;">Failed to load the conversation.</div>'); });

    $('#dqSend').on('click', function(){
        var msg = $.trim($('#dqMsg').val()), $b = $(this);
        if (!msg) { toast('err', 'Please type your query first.'); $('#dqMsg').focus(); return; }
        $b.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        $.post(BASE + '/raise-query', {_token: CSRF, message: msg})
            .done(function(r){
                $('#dqChat .q-empty').remove();
                $('#dqChat').append(bubble(r.query)); scrollBottom();
                $('#dqMsg').val('');
                setStatus(r.query_status); setCount(r.total);
                toast('ok', 'Query sent — the employee has been emailed.');
            })
            .fail(function(x){
                var j = x.responseJSON || {};
                toast('err', j.message || (j.errors && j.errors.message && j.errors.message[0]) || 'Could not send the query.');
            })
            .always(function(){ $b.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send'); });
    });
    $('#dqMsg').on('keydown', function(e){ if (e.ctrlKey && e.key === 'Enter') $('#dqSend').trigger('click'); });
});
</script>
@endsection
