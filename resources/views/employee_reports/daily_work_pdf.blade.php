<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #1e293b;
    background: #ffffff;
    line-height: 1.5;
}

/* ── HEADER ── */
.hdr-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.hdr-left  { vertical-align: top; text-align: left; }
.hdr-right { vertical-align: top; text-align: right; }
.logo-img  { width: 150px; height: auto; margin-bottom: 10px; }

.hdr-rule-1 { border-top: 2px solid #1e293b; margin-top: 6px; }
.hdr-rule-2 { border-top: 1px solid #cbd5e1; margin-top: 2px; margin-bottom: 14px; }

.hdr-doc-type {
    font-size: 13px; font-weight: bold; color: #334155;
    text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;
}
.hdr-date { font-size: 8px; color: #64748b; }

/* ── EMPLOYEE DETAILS ── */
.filter-row { margin-bottom: 20px; }
.ftag-lbl {
    font-size: 10px; color: #64748b; font-weight: bold; margin-right: 6px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.ftag {
    display: inline-block; color: #1e293b;
    font-size: 10px; font-weight: bold;
    margin-right: 12px; text-transform: uppercase; letter-spacing: 0.5px;
}

/* ── KPI STRIP ── */
.summary-strip { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 22px; }
.s-box {
    text-align: center; vertical-align: middle;
    padding: 10px 6px; border: 1px solid #cbd5e1; background-color: #f8fafc;
}
.s-box + .s-box { border-left: none; }
.s-box-1 { border-top: 3px solid #1e293b; width: 22%; }
.s-box-2 { border-top: 3px solid #475569; width: 22%; }
.s-box-3 { border-top: 3px solid #64748b; width: 22%; }

.s-box-date-clean {
    text-align: right;
    vertical-align: middle;
    padding: 10px 0;
    border: none;
    background: transparent;
    width: 34%;
}

.s-big { font-size: 18px; font-weight: bold; display: block; line-height: 1; margin-bottom: 2px; color: #0f172a; }
.s-tag { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; display: block; color: #475569; }

.s-date-large { font-size: 18px; font-weight: bold; color: #000000; display: block; line-height: 1; padding-right: 4px; }
.s-date-day   { font-size: 7.5px; font-weight: bold; text-transform: capitalize; letter-spacing: 0.8px; display: block; color: #475569; padding-right: 24px; }

/* ── SECTION TITLES ── */
.sec-title {
    background: #1e293b; color: #ffffff;
    font-size: 9px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px;
    padding: 7px 10px; margin: 20px 0 0 0;
}
.sec-sub { font-size: 7.5px; color: #cbd5e1; font-weight: normal; letter-spacing: 0.3px; }

/* ── DATA TABLES ── */
table.data-table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
table.data-table thead tr { background-color: #e9eff6; }
table.data-table thead th {
    padding: 8px; color: #334e68; font-size: 7.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.5px;
    border: 1px solid #cbd5e1; text-align: left; white-space: nowrap;
}
table.data-table tbody td {
    padding: 6px 8px; border: 1px solid #e2e8f0;
    vertical-align: top; color: #334155; background: #ffffff;
}
table.data-table tbody tr:nth-child(even) td { background: #f8fafc; }
.center { text-align: center; }

.empty-cell {
    text-align: center; padding: 18px; color: #64748b; font-style: italic;
    border: 1px solid #e2e8f0; border-top: none; background: #ffffff; font-size: 8.5px;
}

/* ── STATUS ── */
.status-cell { border-collapse: collapse; }
.status-cell td { padding: 0; vertical-align: top; border: none; background: transparent; }
.status-dot {
    width: 12px; font-size: 8px; line-height: 1.4;
    padding-right: 4px !important; text-align: left;
}
.status-txt {
    font-size: 8px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.6px;
    white-space: nowrap; line-height: 1.4;
}
.status-sub {
    margin-top: 1px;
    font-size: 7px; font-weight: bold; color: #94a3b8;
    text-transform: none; letter-spacing: 0.3px;
    white-space: nowrap; line-height: 1.3;
}
.mode-txt {
    font-size: 7.5px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.8px;
    white-space: nowrap;
}
.inline-list { font-weight: bold; color: #334155; }
.inline-list .sep { color: #cbd5e1; font-weight: normal; }

/* ══════════════════════════════════════════════════════
   TASK STATS STRIP — sits between section header & table
   ══════════════════════════════════════════════════════ */
.task-stats-wrap {
    border: 1px solid #e2e8f0;
    border-top: none;
    background: #f8fafc;
    padding: 0;
    margin-bottom: 12px;
}
.task-stats-strip {
    width: 100%;
    border-collapse: collapse;
}
/* Each stat cell */
.ts-cell {
    text-align: center;
    vertical-align: middle;
    padding: 8px 4px 7px 4px;
    border-right: 1px solid #e2e8f0;
}
.ts-cell.ts-last { border-right: none; }

/* Total tasks — slightly darker bg to anchor left */
.ts-cell-total {
    background: #f1f5f9;
    width: 14%;
    border-right: 2px solid #cbd5e1;
}
.ts-total-num {
    font-size: 17px; font-weight: bold; color: #0f172a;
    display: block; line-height: 1;
}
.ts-total-lbl {
    font-size: 6.5px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.8px;
    display: block; margin-top: 2px;
}

/* Individual status cells */
.ts-num {
    font-size: 15px; font-weight: bold;
    display: block; line-height: 1; margin-bottom: 2px;
}
.ts-lbl {
    font-size: 6.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.7px;
    display: block;
}
/* Sub-counts (V: x  OD: x) */
.ts-sub {
    margin-top: 3px;
}
.ts-sub-item {
    display: inline-block;
    font-size: 6px; font-weight: bold;
    background: #e2e8f0; color: #334155;
    border-radius: 3px; padding: 1px 5px;
    margin: 1px 1px 0 1px;
    letter-spacing: 0.4px;
}

/* Status-specific colours */
.ts-done        { color: #16a34a; }
.ts-rescheduled { color: #d97706; }
.ts-closed      { color: #2563eb; }
.ts-cancelled   { color: #dc2626; }
.ts-pending     { color: #dc2626; }
.ts-open        { color: #2563eb; }

/* Sub-badge tints per status */
.ts-sub-done        { background: #dcfce7; color: #15803d; }
.ts-sub-pending     { background: #fee2e2; color: #b91c1c; }
.ts-sub-rescheduled { background: #fef3c7; color: #b45309; }
.ts-sub-closed      { background: #dbeafe; color: #1d4ed8; }
.ts-sub-cancelled   { background: #fee2e2; color: #b91c1c; }

/* ═══════════════ DVR VISIT SHEET ═══════════════ */
.visit-card {
    width: 100%; border-collapse: collapse;
    border: 1px solid #cbd5e1;
    margin-top: 12px;
    page-break-inside: avoid;
}
/* Thin white gap between inner bands (Jindba → Last Visit, Last Visit → Current Visit) */
.vc-spacer td { height: 8px; line-height: 8px; font-size: 1px; background: #ffffff; padding: 0; }

.vc-head-num {
    width: 58px; background: #1e293b; color: #ffffff;
    text-align: center; vertical-align: middle;
    font-size: 7px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px;
    padding: 9px 4px;
    border-bottom: 1px solid #cbd5e1;
}
.vc-head-num .num { display: block; font-size: 15px; letter-spacing: 0; margin-top: 1px; }
.vc-head {
    background: #e9eff6; padding: 8px 12px;
    border-bottom: 1px solid #cbd5e1; vertical-align: middle;
}
.vc-cust      { font-size: 11px; font-weight: bold; color: #0f172a; }
.vc-cust-city {
    font-size: 8.5px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.6px;
}

/* Check In / Out / Duration strip — now rendered INSIDE the Current Visit block */
.vc-cvtime { width: 100%; border-collapse: collapse; }
.vc-cvtime td {
    width: 33.33%; text-align: center; padding: 8px 10px;
    border-right: 1px solid #f2d9a8; border-bottom: 1px solid #f2d9a8;
    background: #fffaf0;
}
.vc-cvtime td.last { border-right: none; }
.vctm-lbl {
    display: block; font-size: 6.5px; font-weight: bold; color: #b45309;
    text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px;
    white-space: nowrap;
}
.vctm-val   { font-size: 10.5px; font-weight: bold; color: #0f172a; white-space: nowrap; }
.vctm-in    { color: #16a34a; }
.vctm-out   { color: #dc2626; }

/* section title table */
.sec-title-table { width: 100%; border-collapse: collapse; margin: 20px 0 0 0; background: #1e293b; }
.sec-title-left {
    color: #ffffff; font-size: 9px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px; padding: 7px 10px;
}
.sec-title-right {
    color: #cbd5e1; font-size: 8px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 1px; padding: 7px 10px; text-align: right;
}

/* ── TODAY section ── */
.sec-title-today { background: #A5C552; }
.sec-title-today .sec-title-left { color: #0f172a; }
.sec-title-today .sec-title-right { color: #3f4e1c; }
.sec-today-tag {
    font-size: 6.5px; font-weight: bold; color: #ffffff;
    background: #0f172a; padding: 1px 6px; border-radius: 3px;
    letter-spacing: 1px; margin-right: 8px; vertical-align: middle;
}
table.data-table-today thead tr { background-color: #f7fee7; }
table.data-table-today thead th { color: #4d6215; border-color: #e2f3b6; }
table.data-table-today tbody tr:nth-child(even) td { background: #f8fafc; }
.empty-cell-today { border-color: #e2f3b6; color: #4d6215; }

/* status strip (base) */
.vc-status { width: 100%; border-collapse: collapse; }
.vc-status td {
    width: 20%; text-align: center; padding: 7px 4px;
    border-right: 1px solid #e2e8f0; border-bottom: 1px solid #cbd5e1;
    background: #f8fafc;
}
.vc-status td.last { border-right: none; }
.vcs-lbl {
    display: block; font-size: 6.5px; font-weight: bold; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 2px;
}
.vcs-val    { font-size: 8.5px; font-weight: bold; }
.vcs-ok     { color: #16a34a; }
.vcs-not    { color: #dc2626; }
.icon-tick  { font-size: 9px; font-weight: bold; color: #16a34a; }
.icon-cross { font-size: 9px; font-weight: bold; color: #dc2626; }

/* ── CURRENT VISIT theme = AMBER (swapped from last-visit) ── */
.vc-status.cv-status-theme td {
    border-right: 1px solid #fbe6bf; border-bottom: 1px solid #fbe6bf;
    background: #fffaf0;
}
.vc-status.cv-status-theme .vcs-lbl { color: #b45309; }

/* time strip */
.vc-time { width: 100%; border-collapse: collapse; }
.vc-time td {
    width: 33.33%; padding: 9px 12px;
    border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}
.vc-time td.last { border-right: none; }
.vct-lbl {
    display: block; font-size: 6.5px; font-weight: bold; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px;
}
.vct-val   { font-size: 10.5px; font-weight: bold; color: #0f172a; }
.vct-in    { color: #16a34a; }
.vct-out   { color: #dc2626; }

/* detail rows (base) */
.vc-detail { width: 100%; border-collapse: collapse; }
.vc-detail td { border-bottom: 1px solid #e2e8f0; vertical-align: top; }
.vc-detail tr.vc-last td { border-bottom: none; }
.vcd-lbl {
    width: 20%; background: #f8fafc; padding: 7px 12px;
    font-size: 7px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.6px;
    border-right: 1px solid #e2e8f0;
}
.vcd-val { padding: 7px 12px; font-size: 8.5px; color: #334155; line-height: 1.55; }

/* ── CURRENT VISIT detail rows theme = AMBER (swapped) ── */
.vc-detail.cv-detail-theme td { border-bottom: 1px solid #fbe6bf; }
.vc-detail.cv-detail-theme .vcd-lbl {
    background: #fffaf0; color: #b45309; border-right: 1px solid #fbe6bf;
}

.contact-name { font-weight: bold; color: #0f172a; font-size: 9px; }
.contact-sub  { font-size: 7.5px; color: #64748b; }
.next-plan-val { font-weight: bold; color: #0f172a; }

/* Eye-catching "no next plan" empty state */
.no-plan {
    display: inline-block; font-weight: bold; font-size: 8.5px;
    color: #b91c1c; background: #fef2f2;
    border: 1px solid #fecaca; border-radius: 3px;
    padding: 3px 9px; letter-spacing: 0.3px;
}
.no-plan .np-icon { color: #dc2626; font-weight: bold; margin-right: 4px; }
.next-action-tag {
    display: inline-block; margin-left: 8px;
    font-size: 7.5px; font-weight: bold; color: #b45309;
    background: #fef3e0; padding: 1px 7px; border-radius: 3px;
    letter-spacing: 0.3px; vertical-align: 1px;
}

.trial-line  { margin-top: 2px; font-size: 8.5px; color: #334155; line-height: 1.5; }
.trial-line b { color: #475569; }

.trial-tbl { width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; margin: 2px 0 8px 0; }
.trial-tbl-last { margin-bottom: 2px; }
.tt-head {
    background: #e9eff6; padding: 5px 9px;
    font-size: 7.5px; font-weight: bold; color: #334e68;
    text-transform: uppercase; letter-spacing: 0.8px;
    border-bottom: 1px solid #cbd5e1;
}
.tt-head-status { text-align: right; }
.tt-lbl {
    width: 17%; background: #f8fafc; padding: 5px 9px;
    font-size: 6.5px; font-weight: bold; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.6px;
    border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
}
.tt-val {
    padding: 5px 9px; font-size: 8.5px; color: #334155;
    border-bottom: 1px solid #e2e8f0; line-height: 1.5;
}
.trial-tbl tr.tt-last td { border-bottom: none; }

/* ═══════════ LAST VISIT BOX — GRAY / NEUTRAL theme ═══════════ */
.last-visit-box {
    width: 100%; border-collapse: collapse;
    border: 1px solid #d4d4d8; background: #ffffff;
}
.last-visit-box td { vertical-align: top; }

/* Merged header bar: "LAST VISIT" label (left) + date/day/ago (right) in ONE row */
.lv-titlebar {
    width: 100%; border-collapse: collapse;
    background: #e9eff6; border-bottom: 1px solid #cbd5e1;
}
.lv-titlebar td { vertical-align: middle; padding: 7px 10px; }
.lv-title-lbl {
    color: #1e293b; font-size: 8.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 2px; white-space: nowrap;
}
.lv-title-meta { text-align: right; white-space: nowrap; }
.lv-date {
    font-size: 10px; font-weight: bold; color: #0f172a;
    letter-spacing: 0.3px;
}
.lv-day {
    font-size: 7px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.8px; margin-left: 8px;
}
.lv-ago {
    font-size: 7px; font-weight: bold; color: #475569;
    text-transform: uppercase; letter-spacing: 0.6px; margin-left: 6px;
}
.lv-ago-sep { color: #94a3b8; margin: 0 2px; }

/* Last-visit tick strip (mirrors current tick strip, gray theme, SAME font size) */
.lv-status { width: 100%; border-collapse: collapse; }
.lv-status td {
    width: 20%; text-align: center; padding: 7px 4px;
    border-right: 1px solid #e4e4e7; border-bottom: 1px solid #e4e4e7;
    background: #fafafa;
}
.lv-status td.last { border-right: none; }
.lvs-lbl {
    display: block; font-size: 6.5px; font-weight: bold; color: #71717a;
    text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 2px;
}
.lvs-val { font-size: 8.5px; font-weight: bold; }

/* Last-visit detail rows — same labels/format/SIZE as current, gray theme */
.lv-lbl {
    width: 20%; padding: 7px 12px;
    font-size: 7px; font-weight: bold; color: #52525b;
    text-transform: uppercase; letter-spacing: 0.6px;
    border-bottom: 1px solid #e4e4e7; border-right: 1px solid #e4e4e7;
    background: #fafafa; white-space: nowrap;
}
.lv-cell {
    padding: 7px 12px; font-size: 8.5px; color: #334155; line-height: 1.55;
    border-bottom: 1px solid #e4e4e7;
}
.last-visit-box tr.lv-last .lv-lbl,
.last-visit-box tr.lv-last .lv-cell { border-bottom: none; }

.lv-met-yes  { color: #16a34a; font-weight: bold; }
.lv-met-no   { color: #dc2626; font-weight: bold; }
.lv-met-name { color: #0f172a; font-weight: bold; }

.lv-lbl-plan { color: #27272a; background: #f4f4f5; }
.lv-plan-val { font-weight: bold; color: #0f172a; }

/* Current Visit title bar: "CURRENT VISIT" (left) + Check In/Out/Duration (right) */
.cv-titlebar {
    width: 100%; border-collapse: collapse;
    background: #fdecd0; border-top: 1px solid #eec694; border-bottom: 1px solid #eec694;
}
.cv-titlebar td { vertical-align: middle; }
.cv-title-lbl {
    color: #b45309; font-size: 8.5px; font-weight: bold;
    text-transform: uppercase; letter-spacing: 3px; white-space: nowrap;
    padding: 7px 10px;
}
.cv-title-time { padding: 0; text-align: right; width: 55%; }
.cv-time-mini { border-collapse: collapse; width: 100%; }
.cv-time-mini td {
    text-align: center; padding: 4px 10px;
    border-left: 1px solid #eec694; width: 33.33%;
}
.cvtm-lbl {
    font-size: 6.5px; font-weight: bold; color: #b45309;
    text-transform: uppercase; letter-spacing: 0.8px; white-space: nowrap;
}
.cvtm-val   { font-size: 9.5px; font-weight: bold; color: #7c2d12; white-space: nowrap; }

/* ── FOOTER ── */
.footer-table {
    width: 100%; border-collapse: collapse; margin-top: 28px;
    border-top: 1px solid #cbd5e1; padding-top: 6px;
}
.footer-left  { font-size: 7.5px; font-weight: bold; color: #334155; }
.footer-mid   { font-size: 7px; color: #64748b; text-align: center; }
.footer-right { font-size: 7px; color: #64748b; text-align: right; }
</style>
</head>
<body>

@php
    $yTasks = $data['yesterdayTasks'];
    $visits = $data['visits'];
    $notes  = $data['workNotes'];
    $tTasks = $data['todayTasks'];

    /* ── Task Stats Calculation ── */
    $statsDone        = 0; $statsDoneV = 0;  $statsDoneOD = 0;
    $statsRescheduled = 0;
    $statsClosed      = 0;
    $statsCancelled   = 0;
    $statsPending     = 0; $statsPendingV = 0; $statsPendingOD = 0;
    $statsOpen        = 0; $statsOpenV = 0;    $statsOpenOD = 0;

    foreach ($yTasks as $t) {
        $s = $t['status'] ?? 'Open';

        // Is this task a Visit (has user_dvr_id) or OD (work_note_id where sub_label != 'Visit')?
        // sub_label == 'Visit' or 'Visit (No Meeting)' → Visit bucket
        // sub_label set but not Visit → OD bucket
        $isVisit = isset($t['sub_label']) && $t['sub_label'] !== null
                   && (str_starts_with((string)$t['sub_label'], 'Visit'));
        $isOD    = isset($t['sub_label']) && $t['sub_label'] !== null && !$isVisit;

        if (in_array($s, ['Done', 'Completed'])) {
            $statsDone++;
            if ($isVisit) $statsDoneV++;
            elseif ($isOD) $statsDoneOD++;
        } elseif ($s === 'Rescheduled') {
            $statsRescheduled++;
        } elseif ($s === 'Closed') {
            $statsClosed++;
        } elseif ($s === 'Cancelled') {
            $statsCancelled++;
        } else {
            // Pending / Open
            $statsPending++;
            if ($isVisit) $statsPendingV++;
            elseif ($isOD) $statsPendingOD++;
        }
    }
    $statsTotal = count($yTasks);
@endphp

{{-- ── HEADER ── --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Daily Work Report</div>
            <div class="hdr-date">Report Date: {{ $reportDate }} ({{ $reportDay }})</div>
            <div class="hdr-date">Generated on: {{ $generatedAt }}</div>
        </td>
    </tr>
</table>
<div class="hdr-rule-1"></div>
<div class="hdr-rule-2"></div>

{{-- ── EMPLOYEE DETAILS ── --}}
<div class="filter-row">
    <span class="ftag-lbl">Employee:</span>
    <span class="ftag">{{ $employee->name }} | </span>
    @if(!empty($employee->designation))
        <span class="ftag">{{ $employee->designation }} | </span>
    @endif
    @if(!empty($employee->base_city))
        <span class="ftag">{{ $employee->base_city }}</span>
    @endif
</div>

{{-- ── KPI BOXES & ALIGNED OPEN DATE ── --}}
<table class="summary-strip" cellspacing="0" cellpadding="0">
    <tr>
        <td class="s-box s-box-1">
            <span class="s-big">{{ count($yTasks) }}</span>
            <span class="s-tag"><br>Scheduled Tasks</span>
        </td>
        <td class="s-box s-box-2">
            <span class="s-big">{{ count($visits) }}</span>
            <span class="s-tag"><br>Customer Visits</span>
        </td>
        <td class="s-box s-box-3">
            <span class="s-big">{{ count($notes) }}</span>
            <span class="s-tag"><br>Other Developments</span>
        </td>
        <td class="s-box-date-clean">
            <span class="s-date-large">{{ $reportDate }}</span>
            <span class="s-date-day"><br>{{ ucwords(strtolower($reportDay)) }}</span>
        </td>
    </tr>
</table>

{{-- ═══════════════ 1. YESTERDAY'S SCHEDULED TASKS ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left"> Scheduled Tasks &mdash; {{ $reportDate }} (Yesterday)</td>
        <td class="sec-title-right">{{ count($yTasks) }} {{ count($yTasks) === 1 ? 'Task' : 'Tasks' }}</td>
    </tr>
</table>

{{-- ── TASK STATUS STATS STRIP (always shown, even when 0 tasks) ── --}}
<div class="task-stats-wrap">
    <table class="task-stats-strip" cellspacing="0" cellpadding="0">
        <tr>
            {{-- Total --}}
            <td class="ts-cell ts-cell-total">
                <span class="ts-total-num">{{ $statsTotal }}</span>
                <span class="ts-total-lbl">Tasks</span>
            </td>

            {{-- Done --}}
            <td class="ts-cell" style="width:21%;">
                <span class="ts-num ts-done">{{ $statsDone }}</span>
                <span class="ts-lbl ts-done">Done</span>
                @if($statsDone > 0)
                <div class="ts-sub">
                    <span class="ts-sub-item ts-sub-done">CV: {{ $statsDoneV }}</span>
                    <span class="ts-sub-item ts-sub-done">OD: {{ $statsDoneOD }}</span>
                </div>
                @endif
            </td>

            

            {{-- Rescheduled --}}
            <td class="ts-cell" style="width:15%;">
                <span class="ts-num ts-rescheduled">{{ $statsRescheduled }}</span>
                <span class="ts-lbl ts-rescheduled">Rescheduled</span>
            </td>

            {{-- Closed --}}
            <td class="ts-cell" style="width:15%;">
                <span class="ts-num ts-closed">{{ $statsClosed }}</span>
                <span class="ts-lbl ts-closed">Closed</span>
            </td>

            {{-- Cancelled --}}
            <!-- <td class="ts-cell ts-last" style="width:14%;">
                <span class="ts-num ts-cancelled">{{ $statsCancelled }}</span>
                <span class="ts-lbl ts-cancelled">Cancelled</span>
            </td> -->
            {{-- Pending / Open --}}
            <td class="ts-cell" style="width:21%;">
                <span class="ts-num ts-pending">{{ $statsPending }}</span>
                <span class="ts-lbl ts-pending">Pending</span>
                @if($statsPending > 0)
                <div class="ts-sub">
                    <span class="ts-sub-item ts-sub-pending">CV: {{ $statsPendingV }}</span>
                    <span class="ts-sub-item ts-sub-pending">OD: {{ $statsPendingOD }}</span>
                </div>
                @endif
            </td>
        </tr>
    </table>
</div>

@if(count($yTasks) === 0)
    <div class="empty-cell">No tasks were scheduled for this day.</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="center" style="width:22px;">#</th>
            <th style="width:9%;">Time</th>
            <th style="width:34%;">Related To / Subject</th>
            <th style="width:42%;">Description</th>
            <th style="width:15%;">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($yTasks as $i => $t)
        <tr>
            <td class="center" style="color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>{{ $t['time'] }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $t['name'] }}</span><br>
                <span style="font-size:7.5px; color:#64748b;">{{ $t['related_to'] }}</span>
                @if(!empty($t['subject']) && $t['subject'] !== '—')
                    <div style="margin-top:3px; font-size:8px; color:#334155;"><span style="font-weight:bold; color:#475569;">Subject:</span> {{ $t['subject'] }}</div>
                @endif
            </td>
            <td>{{ $t['description'] ?: '—' }}</td>
            <td>
                <table class="status-cell" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="status-dot" style="color: {{ $t['status_color'] }};">&#9679;</td>
                        <td class="status-body">
                            <span class="status-txt" style="color: {{ $t['status_color'] }};">{{ $t['status'] }}</span>
                            @if(!empty($t['rescheduled_to']))
                                <div class="status-sub" style="color:#d97706;">&#8594; {{ $t['rescheduled_to'] }}</div>
                            @endif
                            @if($t['sub_label'])
                                <div class="status-sub">{{ $t['sub_label'] }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- ═══════════════ 2. CUSTOMER VISITS — VISIT SHEETS ═══════════════ --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">Customer Visits</td>
        <td class="sec-title-right">{{ count($visits) }} {{ count($visits) === 1 ? 'Visit' : 'Visits' }}</td>
    </tr>
</table>
@if(count($visits) === 0)
    <div class="empty-cell">No customer visits were recorded on this day.</div>
@else
    @foreach($visits as $i => $v)
    <table class="visit-card" cellspacing="0" cellpadding="0">

        {{-- Header band --}}
        <tr>
            <td class="vc-head-num">
                Visit
                <span class="num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
            </td>
            <td class="vc-head" colspan="2">
                <span class="vc-cust">{{ $v['customer_name'] }}</span>@if(!empty($v['customer_city']))<span class="vc-cust-city"> &nbsp;|&nbsp; {{ $v['customer_city'] }}</span>@endif
            </td>
        </tr>

        {{-- gap between customer band and Last Visit band --}}
        <tr class="vc-spacer"><td colspan="3"></td></tr>

        {{-- ══════════ LAST VISIT BLOCK (gray theme, merged title+date bar) ══════════ --}}
        @if(!empty($v['last_visit']))
            @php $lv = $v['last_visit']; @endphp
            <tr>
                <td colspan="3" style="padding:0;">
                    <table class="last-visit-box" cellspacing="0" cellpadding="0">

                        {{-- Merged header: "LAST VISIT" (left) + date / day / ago (right) --}}
                        <tr>
                            <td colspan="2" style="padding:0;">
                                <table class="lv-titlebar" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="lv-title-lbl">Last Visit</td>
                                        <td class="lv-title-meta">
                                            <span class="lv-date">{{ $lv['date'] }}</span>
                                            @if($lv['day'])
                                                <span class="lv-day">{{ strtoupper($lv['day']) }}</span>
                                            @endif
                                            @if(!is_null($lv['days_ago']))
                                                <span class="lv-ago-sep">&bull;</span>
                                                <span class="lv-ago">
                                                    @if($lv['days_ago'] === 0)
                                                        Earlier Today
                                                    @elseif($lv['days_ago'] == 1)
                                                        1 Day Ago
                                                    @else
                                                        {{ $lv['days_ago'] }} Days Ago
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        {{-- Last-visit's OWN tick strip (Visit Type / Entry / Site / Customer Met / Visit Detail) --}}
                        @if(!empty($lv['statuses']))
                        <tr>
                            <td colspan="2" style="padding:0;">
                                <table class="lv-status" cellspacing="0" cellpadding="0">
                                    <tr>
                                        @foreach($lv['statuses'] as $k => $st)
                                        <td class="{{ $k === count($lv['statuses']) - 1 ? 'last' : '' }}">
                                            <span class="lvs-lbl">{{ $st['label'] }}</span>
                                            <span class="lvs-val {{ $st['ok'] ? 'vcs-ok' : 'vcs-not' }}">
                                                @if($st['ok'])<span class="icon-tick">&#10003;</span>@else<span class="icon-cross">&#10007;</span>@endif
                                                {{ $st['value'] }}
                                            </span>
                                        </td>
                                        @endforeach
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        @endif

                        {{-- Detail rows — SAME LABELS as Current Visit (mapped from existing last-visit data) --}}
                        @if(count($lv['purposes']) > 0)
                        <tr>
                            <td class="lv-lbl">Purpose of Visit</td>
                            <td class="lv-cell">
                                <span class="inline-list">@foreach($lv['purposes'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach</span>
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td class="lv-lbl">Met With</td>
                            <td class="lv-cell">
                                @if($lv['met'])
                                    <span class="lv-met-yes">&#10003; Yes</span>@if($lv['met_name'])<span class="lv-met-name"> &mdash; {{ $lv['met_name'] }}</span>@endif
                                @else
                                    <span class="lv-met-no">&#10007; Not Met</span>
                                @endif
                            </td>
                        </tr>

                        @if($lv['summary'])
                        <tr>
                            <td class="lv-lbl">Visit Details</td>
                            <td class="lv-cell">{{ $lv['summary'] }}</td>
                        </tr>
                        @endif

                        <tr class="lv-last">
                            <td class="lv-lbl lv-lbl-plan">Next Plan</td>
                            @if(!empty($lv['next_plan']))
                            <td class="lv-cell lv-plan-val">{{ $lv['next_plan'] }}</td>
                            @else
                            <td class="lv-cell"><span class="no-plan"><span class="np-icon">&#9888;</span>No next plan added</span></td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- gap between Last Visit band and Current Visit band --}}
            <tr class="vc-spacer"><td colspan="3"></td></tr>
        @endif

        {{-- ══════════ CURRENT VISIT title bar (label left, Check In/Out/Duration right) ══════════ --}}
        <tr>
            <td colspan="3" style="padding:0;">
                <table class="cv-titlebar" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="cv-title-lbl">Current Visit</td>
                        <td class="cv-title-time">
                            <table class="cv-time-mini" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <span class="cvtm-lbl">Check In</span>&nbsp;
                                        <span class="cvtm-val">{{ $v['check_in'] ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="cvtm-lbl">Check Out</span>&nbsp;
                                        <span class="cvtm-val">{{ $v['check_out'] ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="cvtm-lbl">Duration</span>&nbsp;
                                        <span class="cvtm-val">{!! $v['duration'] ? str_replace(' ', '&nbsp;', e($v['duration'])) : '—' !!}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Current Visit's OWN tick strip (amber theme) --}}
        <tr>
            <td colspan="3" style="padding:0;">
                <table class="vc-status cv-status-theme" cellspacing="0" cellpadding="0">
                    <tr>
                        @foreach($v['statuses'] as $k => $st)
                        <td class="{{ $k === count($v['statuses']) - 1 ? 'last' : '' }}">
                            <span class="vcs-lbl">{{ $st['label'] }}</span>
                            <span class="vcs-val {{ $st['ok'] ? 'vcs-ok' : 'vcs-not' }}">
                                @if($st['ok'])
                                    <span class="icon-tick">&#10003;</span>
                                @else
                                    <span class="icon-cross">&#10007;</span>
                                @endif
                                {{ $st['value'] }}
                            </span>
                        </td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Current Visit detail rows (amber theme) --}}
        <tr>
            <td colspan="3" style="padding:0;">
                <table class="vc-detail cv-detail-theme" cellspacing="0" cellpadding="0">

                    @if(count($v['purposes']) > 0)
                    <tr>
                        <td class="vcd-lbl">Purpose of Visit</td>
                        <td class="vcd-val inline-list">
                            @foreach($v['purposes'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach
                        </td>
                    </tr>
                    @endif
                    @if(!empty($v['other_purpose']))
                    <tr>
                        <td class="vcd-lbl">Other Purpose</td>
                        <td class="vcd-val">{{ $v['other_purpose'] }}</td>
                    </tr>
                    @endif
                    @if(count($v['contacts']) > 0)
                    <tr>
                        <td class="vcd-lbl">Met With</td>
                        <td class="vcd-val">
                            @foreach($v['contacts'] as $c)
                                <div>
                                    <span class="contact-name">{{ $c['name'] }}</span>
                                    @if(!empty($c['designation']))
                                        <span class="contact-sub"> &mdash; {{ $c['designation'] }}</span>
                                    @endif
                                    @if(!empty($c['mobile']))
                                        <span class="contact-sub">&nbsp;&nbsp;&#9742; {{ $c['mobile'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    @if(count($v['products']) > 0)
                    <tr>
                        <td class="vcd-lbl">Products Discussed</td>
                        <td class="vcd-val inline-list">
                            @foreach($v['products'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach
                        </td>
                    </tr>
                    @endif

                    @if(count($v['trials']) > 0)
                    <tr>
                        <td class="vcd-lbl">Trials</td>
                        <td class="vcd-val">
                        @if(count($v['trials']) === 1)
                            @php $tr = $v['trials'][0]; @endphp
                            <span class="status-txt" style="color: {{ $tr['status_color'] }};">&#9679; {{ $tr['status'] }}</span>
                            @if($tr['type'])
                                &nbsp;&nbsp;<span class="mode-txt">{{ $tr['type'] }}</span>
                            @endif
                            @if($tr['objective'])
                                <div class="trial-line"><b>Objective:</b> {{ $tr['objective'] }}</div>
                            @endif
                            @if(count($tr['products']) > 0)
                                <div class="trial-line"><b>Products:</b>
                                    <span class="inline-list">@foreach($tr['products'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach</span>
                                </div>
                            @endif
                            @if($tr['jointly'] && $tr['team_member'])
                                <div class="trial-line"><b>Jointly with:</b> {{ $tr['team_member'] }}</div>
                            @endif
                            @if($tr['remarks'])
                                <div class="trial-line" style="color:#64748b;">{{ $tr['remarks'] }}</div>
                            @endif
                        @else
                            @foreach($v['trials'] as $ti => $tr)
                            <table class="trial-tbl {{ $loop->last ? 'trial-tbl-last' : '' }}" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="tt-head">
                                        Trial {{ str_pad($ti + 1, 2, '0', STR_PAD_LEFT) }}
                                        @if($tr['type'])
                                            &nbsp;&nbsp;<span style="color:#64748b; letter-spacing:0.5px;">{{ $tr['type'] }}</span>
                                        @endif
                                    </td>
                                    <td class="tt-head tt-head-status">
                                        <span class="status-txt" style="color: {{ $tr['status_color'] }};">&#9679; {{ $tr['status'] }}</span>
                                    </td>
                                </tr>
                                @if($tr['objective'])
                                <tr>
                                    <td class="tt-lbl">Objective</td>
                                    <td class="tt-val">{{ $tr['objective'] }}</td>
                                </tr>
                                @endif
                                @if(count($tr['products']) > 0)
                                <tr>
                                    <td class="tt-lbl">Products</td>
                                    <td class="tt-val inline-list">@foreach($tr['products'] as $p){{ $p }}@if(!$loop->last) <span class="sep">&nbsp;&bull;&nbsp;</span> @endif @endforeach</td>
                                </tr>
                                @endif
                                @if($tr['jointly'] && $tr['team_member'])
                                <tr>
                                    <td class="tt-lbl">Jointly With</td>
                                    <td class="tt-val">{{ $tr['team_member'] }}</td>
                                </tr>
                                @endif
                                @if($tr['remarks'])
                                <tr class="tt-last">
                                    <td class="tt-lbl">Remarks</td>
                                    <td class="tt-val" style="color:#64748b;">{{ $tr['remarks'] }}</td>
                                </tr>
                                @endif
                            </table>
                            @endforeach
                        @endif
                        </td>
                    </tr>
                    @endif

                    @if(!empty($v['visit_detail']))
                    <tr>
                        <td class="vcd-lbl">Visit Details</td>
                        <td class="vcd-val">{{ $v['visit_detail'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($v['remarks']))
                    <tr>
                        <td class="vcd-lbl">Remarks</td>
                        <td class="vcd-val">{{ $v['remarks'] }}</td>
                    </tr>
                    @endif

                    <tr class="vc-last">
                        <td class="vcd-lbl" style="color:#1e293b;">Next Plan</td>
                        @if(!empty($v['next_plan']) || !empty($v['next_action']))
                        <td class="vcd-val next-plan-val">
                            @if(!empty($v['next_plan'])){{ $v['next_plan'] }}@endif
                            @if(!empty($v['next_action']))
                                <span class="next-action-tag">Follow-up: {{ $v['next_action'] }}</span>
                            @endif
                        </td>
                        @else
                        <td class="vcd-val"><span class="no-plan"><span class="np-icon">&#9888;</span>No next plan added</span></td>
                        @endif
                    </tr>

                </table>
            </td>
        </tr>
    </table>
    @endforeach
@endif

{{-- ── 3. OTHER DEVELOPMENTS (WORK NOTES) ── --}}
<table class="sec-title-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left">Other Developments</td>
        <td class="sec-title-right">{{ count($notes) }} {{ count($notes) === 1 ? 'Record' : 'Records' }}</td>
    </tr>
</table>
@if(count($notes) === 0)
    <div class="empty-cell">No other developments were recorded on this day.</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th class="center" style="width:22px;">#</th>
            <th style="width:17%;">Related To</th>
            <th style="width:16%;">Subject</th>
            <th class="center" style="width:12%;">Mode</th>
            <th style="width:26%;">Description</th>
            <th style="width:29%;">Key Takeaway / Action</th>
        </tr>
    </thead>
    <tbody>
    @foreach($notes as $i => $n)
        <tr>
            <td class="center" style="color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $n['name'] }}</span><br>
                <span style="font-size:7.5px; color:#64748b;">{{ $n['related_to'] }}</span>
            </td>
            <td>{{ $n['subject'] }}</td>
            <td class="center">
                @if($n['activity_mode'])
                    <span class="mode-txt">{{ $n['activity_mode'] }}</span>
                @else
                    —
                @endif
            </td>
            <td>{{ $n['description'] ?: '—' }}</td>
            <td>
                {{ $n['key_take_away'] ?: '—' }}
                @if($n['action'])
                    <div style="margin-top:3px; font-size:7.5px; color:#b45309; font-weight:bold;">
                        &#9888; Action: {{ $n['action'] }}
                    </div>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

{{-- ── 4. TODAY'S UPCOMING TASKS ── --}}
<table class="sec-title-table sec-title-today" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sec-title-left"><span class="sec-today-tag">&#9654; UPCOMING</span> Scheduled Tasks &mdash; {{ $todayDate }} (Today)</td>
        <td class="sec-title-right">{{ count($tTasks) }} {{ count($tTasks) === 1 ? 'Task' : 'Tasks' }}</td>
    </tr>
</table>
@if(count($tTasks) === 0)
    <div class="empty-cell empty-cell-today">No tasks are scheduled for today.</div>
@else
<table class="data-table data-table-today">
    <thead>
        <tr>
            <th class="center" style="width:22px;">#</th>
            <th style="width:9%;">Time</th>
            <th style="width:34%;">Related To / Subject</th>
            <th style="width:42%;">Description</th>
            <th style="width:15%;">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($tTasks as $i => $t)
        <tr>
            <td class="center" style="color:#64748b; font-weight:bold;">{{ $i + 1 }}</td>
            <td>{{ $t['time'] }}</td>
            <td>
                <span style="font-weight:bold; color:#0f172a;">{{ $t['name'] }}</span><br>
                <span style="font-size:7.5px; color:#64748b;">{{ $t['related_to'] }}</span>
                @if(!empty($t['subject']) && $t['subject'] !== '—')
                    <div style="margin-top:3px; font-size:8px; color:#334155;"><span style="font-weight:bold; color:#475569;">Subject:</span> {{ $t['subject'] }}</div>
                @endif
            </td>
            <td>{{ $t['description'] ?: '—' }}</td>
            <td>
                <table class="status-cell" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="status-dot" style="color: {{ $t['status_color'] }};">&#9679;</td>
                        <td class="status-body">
                            <span class="status-txt" style="color: {{ $t['status_color'] }};">{{ $t['status'] }}</span>
                            @if(!empty($t['rescheduled_to']))
                                <div class="status-sub" style="color:#d97706;">&#8594; {{ $t['rescheduled_to'] }}</div>
                            @endif
                            @if($t['sub_label'])
                                <div class="status-sub">{{ $t['sub_label'] }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

</body>
</html>