@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    .page-content { padding-bottom: 40px !important; }

    .portlet.light.bordered {
        border-radius: 6px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.08);
        border: 1px solid #dde3ec;
    }

    /* ── Filter strip — single row, labels above controls ── */
    .filter-strip {
        display: flex; align-items: flex-end; gap: 10px; flex-wrap: nowrap;
        background: #f4f6fa; border: 1px solid #dde3ec;
        border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;
        overflow-x: auto;
    }

    /* labelled group: small label on top, control below */
    .filter-strip .fg {
        display: flex; flex-direction: column; gap: 3px; flex-shrink: 0;
    }
    .filter-strip .fg > label {
        font-size: 10px; font-weight: 700; color: #5a6a85;
        text-transform: uppercase; letter-spacing: 0.5px;
        margin: 0; white-space: nowrap;
    }

    .filter-strip select,
    .filter-strip input[type="text"] {
        height: 30px; border: 1px solid #c8d0dc; border-radius: 4px !important;
        font-size: 12px; color: #2d3748; background: #fff; padding: 0 8px;
    }
    .filter-strip select:focus, .filter-strip input:focus {
        outline: none; border-color: #3598dc;
        box-shadow: 0 0 0 2px rgba(53,152,220,0.15);
    }
    #filter-product      { width: 155px; }
    #filter-price-status { width: 125px; }
    #filter-search       { width: 140px; }

    /* vertical divider */
    .f-sep {
        width: 1px; height: 30px; background: #d5dbe8;
        flex-shrink: 0; margin: 0 2px;
    }

    /* checkbox group: label on top, checkbox+text below */
    .fg-check-wrap {
        display: flex; flex-direction: column; gap: 3px; flex-shrink: 0;
    }
    .fg-check-wrap > .fg-lbl {
        font-size: 10px; font-weight: 700; color: #5a6a85;
        text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
    }
    .fg-check {
        display: inline-flex; align-items: center; gap: 5px;
        white-space: nowrap; height: 30px; cursor: pointer;
    }
    .fg-check input[type="checkbox"] { transform: scale(1.15); cursor: pointer; flex-shrink: 0; }
    .fg-check span { font-size: 11px; font-weight: 600; color: #5a6a85; white-space: nowrap; }
    .fg-check input[type="checkbox"].chk-na    { accent-color: #e53e3e; }
    .fg-check input[type="checkbox"].chk-disc  { accent-color: #805ad5; }
    .fg-check input[type="checkbox"].chk-focus { accent-color: #38a169; }

    /* "Showing X of Y" pushed to far right, aligned to bottom */
    .filter-result {
        font-size: 11px; color: #718096; white-space: nowrap;
        margin-left: auto; flex-shrink: 0; padding-left: 8px;
        padding-bottom: 5px;
    }
    .filter-result strong { color: #3598dc; }

    .btn-clear-filters {
        height: 30px; padding: 0 10px; font-size: 11px; font-weight: 700;
        border-radius: 4px !important; border: 1px solid #c8d0dc;
        background: #fff; color: #718096; cursor: pointer;
        text-transform: uppercase; letter-spacing: 0.4px;
        display: inline-flex; align-items: center; gap: 4px;
        transition: all 0.15s; white-space: nowrap; flex-shrink: 0;
    }
    .btn-clear-filters:hover { background: #fed7d7; border-color: #fc8181; color: #c53030; }
    .btn-clear-filters.hidden { display: none; }

    .btn-export-pdf {
        display: inline-flex; align-items: center; gap: 5px;
        height: 30px; padding: 0 12px; font-size: 11px; font-weight: 600;
        border-radius: 4px !important; border: 1px solid #e53e3e;
        background: #fff5f5; color: #c53030; cursor: pointer;
        text-decoration: none; white-space: nowrap; transition: all 0.15s;
        flex-shrink: 0;
    }
    .btn-export-pdf:hover { background: #e53e3e; color: #fff; text-decoration: none; }

    /* ── Table ── */
    .pricing-wrap { width: 100%; overflow-x: auto; }
    .pricing-table { width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed; }
    .pricing-table col.c-no       { width: 28px; }
    .pricing-table col.c-name     { width: 14%; }
    .pricing-table col.c-moq      { width: 10%; }
    .pricing-table col.c-dispatch { width: 10%; }
    .pricing-table col.c-na       { width: 8%; }
    .pricing-table col.c-disc     { width: 9%; }
    .pricing-table col.c-focus    { width: 8%; }
    .pricing-table col.c-dp       { width: 11%; }
    .pricing-table col.c-date     { width: 11%; }
    .pricing-table col.c-action   { width: 9%; }

    .pricing-table thead tr th {
        background: #eef1f7; color: #4a5568; font-weight: 700; font-size: 11px;
        text-transform: uppercase; letter-spacing: 0.55px; padding: 10px 12px;
        border: 1px solid #d5dbe8; white-space: nowrap; text-align: left;
    }
    .pricing-table thead tr th.center { text-align: center; }
    .pricing-table tbody td {
        padding: 8px 12px; border: 1px solid #e4e9f2; vertical-align: middle;
        color: #2d3748; background: #fff; overflow: hidden; text-overflow: ellipsis;
    }
    .pricing-table tbody tr:hover td { background: #f8faff; }
    .pricing-table tbody tr.is-dirty td { background: #fffde7; }

    @keyframes rowFlash {
        0%   { background: #c6f6d5; }
        100% { background: #fff; }
    }
    .row-saved td { animation: rowFlash 1.6s ease forwards; }
    .pricing-table tbody tr:nth-child(even) td { background: #fafbfd; }
    .pricing-table tbody tr:nth-child(even):hover td { background: #f0f5ff; }
    .pricing-table tbody tr.is-dirty td,
    .pricing-table tbody tr.is-dirty:nth-child(even) td { background: #fffde7 !important; }

    .prod-name { font-weight: 600; color: #2d3748; font-size: 13px; display: block; line-height: 1.3; word-wrap: break-word; }
    .prod-code { font-size: 11px; color: #a0aec0; display: block; margin-top: 1px; }

    .inline-input {
        border: 1px solid #e2e8f0; border-radius: 4px !important;
        padding: 4px 7px; font-size: 13px; color: #2d3748;
        background: #fff; width: 100%; height: 30px;
        transition: border-color 0.18s, box-shadow 0.18s; box-sizing: border-box;
    }
    .inline-input:focus { outline: none; border-color: #3598dc; box-shadow: 0 0 0 2px rgba(53,152,220,0.12); }
    .inline-input.changed { border-color: #f6ad55 !important; background: #fffdf5; }

    .na-toggle   { text-align: center; }
    .disc-toggle { text-align: center; }
    .focus-toggle { text-align: center; }

    .na-toggle    input[type="checkbox"] { transform: scale(1.3); accent-color: #e53e3e;  cursor: pointer; }
    .disc-toggle  input[type="checkbox"] { transform: scale(1.3); accent-color: #805ad5;  cursor: pointer; }
    .disc-toggle  input[type="checkbox"]:disabled { cursor: not-allowed; opacity: .45; }
    .na-toggle    input[type="checkbox"]:disabled { cursor: not-allowed; opacity: .45; }
    .focus-toggle input[type="checkbox"] { transform: scale(1.3); accent-color: #38a169;  cursor: pointer; }

    .dp-wrap { display: flex; align-items: center; gap: 4px; width: 100%; }
    .dp-wrap .cur { font-size: 12px; color: #888; flex-shrink: 0; }
    .dp-input { flex-grow: 1; min-width: 0; }

    .price-date-td { text-align: center; }
    .pd-badge {
        display: inline-block; font-size: 10px; padding: 2px 8px;
        border-radius: 10px !important; font-weight: 700; white-space: nowrap; border: 1px solid transparent;
    }
    .pd-today { background: #e6fffa; color: #276749; border-color: #9ae6b4; }
    .pd-old   { background: #f7fafc; color: #718096; border-color: #e2e8f0; }
    .pd-none  { background: #fff5f5; color: #c53030; border-color: #feb2b2; }

    .btn-update {
        width: 100%; max-width: 84px; padding: 4px 0; font-size: 11px; font-weight: 700;
        border-radius: 4px !important; text-transform: uppercase; letter-spacing: 0.4px;
        transition: all 0.18s; display: block; margin: 0 auto; text-align: center;
    }
    .btn-update:disabled { opacity: 0.3; cursor: not-allowed; pointer-events: none; }
    .btn-update:not(:disabled):hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(53,152,220,0.30); }
    .btn-update.saving { opacity: 0.6; pointer-events: none; }

    .sr-no-cell { color: #a0aec0; font-size: 11px; text-align: center; }
    .pricing-table thead tr th:first-child,
    .sr-no-cell {
        padding: 8px 4px !important;
        max-width: 28px !important;
    }

    #empty-row td {
        text-align: center; padding: 30px; color: #a0aec0;
        font-style: italic; border: 1px solid #e4e9f2;
    }

    /* ── Toast ── */
    #toast-wrap {
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        display: flex; flex-direction: column; gap: 8px; pointer-events: none;
    }
    .toast-item {
        padding: 10px 18px; border-radius: 6px; font-size: 13px; font-weight: 600;
        box-shadow: 0 4px 14px rgba(0,0,0,0.12); pointer-events: auto;
        opacity: 0; transition: opacity 0.28s; border: 1px solid transparent;
    }
    .toast-success { background:#c6f6d5; color:#276749; border-color:#9ae6b4; }
    .toast-danger  { background:#fed7d7; color:#9b2c2c; border-color:#fc8181; }

    /* ── Summary bar ── */
    .summary-bar { display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
    .sum-card {
        background: #f4f6fa; border: 1px solid #dde3ec; border-radius: 6px;
        padding: 8px 16px; font-size: 12px; color: #5a6a85;
        display: flex; align-items: center; gap: 6px;
    }
    .sum-card strong { font-size: 15px; color: #2d3748; }
    .sum-card .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .dot-total    { background: #3598dc; }
    .dot-noprice  { background: #e53e3e; }
    .dot-na       { background: #ed8936; }
    .dot-disc     { background: #805ad5; }
    .dot-focus    { background: #38a169; }

    /* ══════════════ Notify popup (Discont. / N.A. / price) ══════════════ */
    @keyframes dcSpin   { to { transform: rotate(360deg); } }
    @keyframes dcFadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes dcRise   { from { opacity: 0; transform: translateY(18px) scale(.98); } to { opacity: 1; transform: none; } }
    @keyframes dcStripe { from { background-position: 0 0; } to { background-position: 28px 0; } }
    @keyframes dcPulse  { 0%,100% { box-shadow: 0 0 0 0 rgba(128,90,213,.35); } 50% { box-shadow: 0 0 0 6px rgba(128,90,213,0); } }

    .dc-overlay {
        position: fixed; inset: 0; z-index: 10050; display: none;
        align-items: center; justify-content: center; padding: 16px;
        background: rgba(26,32,44,.55); backdrop-filter: blur(3px);
        animation: dcFadeIn .18s ease;
    }
    .dc-overlay.open { display: flex; }
    body.dc-lock { overflow: hidden; }

    .dc-modal {
        width: 100%; max-width: 860px; max-height: calc(100vh - 32px);
        background: #fff; border-radius: 14px !important; overflow: hidden;
        display: flex; flex-direction: column;
        box-shadow: 0 24px 70px rgba(0,0,0,.28); animation: dcRise .25s ease;
    }

    /* header */
    .dc-head {
        position: relative; padding: 20px 24px 18px; color: #fff;
        background: linear-gradient(135deg, #553c9a 0%, #805ad5 60%, #9f7aea 100%);
        transition: background .4s;
    }
    .dc-modal.is-done   .dc-head { background: linear-gradient(135deg, #22543d 0%, #2f855a 60%, #48bb78 100%); }
    .dc-modal.is-failed .dc-head { background: linear-gradient(135deg, #7b341e 0%, #c05621 60%, #ed8936 100%); }
    .dc-head-row { display: flex; align-items: center; gap: 14px; }
    .dc-head-icon {
        width: 46px; height: 46px; border-radius: 12px !important; flex-shrink: 0;
        background: rgba(255,255,255,.18); display: flex; align-items: center; justify-content: center; font-size: 21px;
    }
    .dc-head-kicker { font-size: 10.5px; text-transform: uppercase; letter-spacing: 1.2px; opacity: .8; font-weight: 700; }
    .dc-head-title  { font-size: 19px; font-weight: 700; line-height: 1.25; margin-top: 2px; word-break: break-word; }
    .dc-head-code   {
        display: inline-block; margin-top: 6px; font-size: 11px; font-weight: 600; padding: 2px 10px;
        border-radius: 20px !important; background: rgba(255,255,255,.18);
    }
    .dc-close {
        position: absolute; top: 14px; right: 14px; width: 32px; height: 32px; border-radius: 50% !important;
        border: 0; background: rgba(255,255,255,.15); color: #fff; font-size: 16px; cursor: pointer; transition: background .15s;
    }
    .dc-close:hover { background: rgba(255,255,255,.3); }
    .dc-modal.is-busy .dc-close { display: none; }

    /* body */
    .dc-body { padding: 20px 24px; overflow-y: auto; flex: 1; background: #fafbfd; }

    .dc-loading { text-align: center; padding: 50px 10px; color: #718096; font-size: 13px; }
    .dc-loading .dc-spinner {
        width: 34px; height: 34px; margin: 0 auto 12px; border-radius: 50%;
        border: 3px solid #e9d8fd; border-top-color: #805ad5; animation: dcSpin .8s linear infinite;
    }

    .dc-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
    .dc-stat {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 10px !important; padding: 12px 14px;
        display: flex; align-items: center; gap: 12px;
    }
    .dc-stat-ico {
        width: 38px; height: 38px; border-radius: 10px !important; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .dc-stat-ico.emp  { background: #ebf8ff; color: #3182ce; }
    .dc-stat-ico.dlr  { background: #faf5ff; color: #805ad5; }
    .dc-stat-ico.mail { background: #f0fff4; color: #38a169; }
    .dc-stat-num { font-size: 20px; font-weight: 800; color: #2d3748; line-height: 1; }
    .dc-stat-lbl { font-size: 11px; color: #718096; font-weight: 600; margin-top: 3px; }

    .dc-lists { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
    .dc-list { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px !important; overflow: hidden; display: flex; flex-direction: column; }
    .dc-list-head {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        padding: 10px 14px; background: #f7f8fc; border-bottom: 1px solid #e2e8f0;
    }
    .dc-list-title { font-size: 11px; font-weight: 800; color: #4a5568; text-transform: uppercase; letter-spacing: .6px; }
    .dc-list-title .cnt {
        display: inline-block; min-width: 20px; text-align: center; margin-left: 4px; padding: 1px 7px;
        font-size: 10.5px; border-radius: 10px !important; background: #e2e8f0; color: #4a5568;
    }
    .dc-selall { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; color: #718096; font-weight: 600; cursor: pointer; margin: 0; }
    .dc-selall input { accent-color: #805ad5; cursor: pointer; margin: 0; }
    .dc-items { max-height: 260px; overflow-y: auto; }
    .dc-empty { padding: 26px 14px; text-align: center; color: #a0aec0; font-size: 12px; font-style: italic; }
    .dc-empty i { display: block; font-size: 22px; margin-bottom: 6px; font-style: normal; }

    .dc-item {
        display: flex; align-items: center; gap: 10px; padding: 9px 14px;
        border-bottom: 1px solid #f1f3f8; transition: background .2s; margin: 0; cursor: pointer; font-weight: normal;
    }
    .dc-item:last-child { border-bottom: 0; }
    .dc-item:hover { background: #fbf9ff; }
    .dc-item.no-email { cursor: default; opacity: .7; }
    .dc-item.no-email:hover { background: transparent; }
    .dc-item input[type="checkbox"] { accent-color: #805ad5; transform: scale(1.1); flex-shrink: 0; margin: 0; cursor: pointer; }
    .dc-avatar {
        width: 32px; height: 32px; border-radius: 50% !important; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 11.5px; font-weight: 800; color: #fff;
    }
    .dc-avatar.emp { background: linear-gradient(135deg, #4299e1, #3182ce); }
    .dc-avatar.dlr { background: linear-gradient(135deg, #9f7aea, #805ad5); }
    .dc-who { flex: 1; min-width: 0; }
    .dc-who-name, .dc-who-sub { display: block; }
    .dc-who-name { font-size: 13px; font-weight: 700; color: #2d3748; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dc-who-sub  { font-size: 11px; color: #a0aec0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dc-who-sub .warn { color: #dd6b20; font-weight: 700; }

    .dc-pill {
        flex-shrink: 0; font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 12px !important;
        text-transform: uppercase; letter-spacing: .4px; display: none; white-space: nowrap;
    }
    .dc-modal.is-sending .dc-pill, .dc-modal.is-finished .dc-pill { display: inline-block; }
    .dc-pill.queued  { background: #edf2f7; color: #718096; }
    .dc-pill.sending { background: #faf5ff; color: #805ad5; animation: dcPulse 1.2s infinite; }
    .dc-pill.sent    { background: #f0fff4; color: #2f855a; }
    .dc-pill.failed  { background: #fff5f5; color: #c53030; }
    .dc-pill.skipped { background: #fffaf0; color: #c05621; }
    .dc-pill.excluded{ background: transparent; color: #cbd5e0; }
    .dc-item.st-sent    { background: #f8fffa; }
    .dc-item.st-failed  { background: #fffafa; }

    .dc-fields { margin-bottom: 16px; }
    .dc-field label {
        display: block; font-size: 10.5px; font-weight: 800; color: #5a6a85; text-transform: uppercase;
        letter-spacing: .5px; margin-bottom: 5px;
    }
    .dc-field label .opt { font-weight: 600; color: #a0aec0; text-transform: none; letter-spacing: 0; }
    .dc-field textarea {
        width: 100%; border: 1px solid #cbd5e0; border-radius: 8px !important; background: #fff;
        font-size: 13px; color: #2d3748; padding: 7px 10px; transition: border-color .15s, box-shadow .15s;
    }
    .dc-field textarea { min-height: 36px; height: 70px; resize: vertical; }
    .dc-field textarea:focus { outline: none; border-color: #805ad5; box-shadow: 0 0 0 3px rgba(128,90,213,.15); }
    .dc-modal.is-busy .dc-field textarea { background: #f7fafc; }

    .dc-notice {
        margin-bottom: 16px;
        display: none; gap: 12px; padding: 12px 14px; border-radius: 10px !important;
        background: #fffbeb; border: 1px solid #fbd38d; border-left: 4px solid #dd6b20;
        font-size: 12.5px; color: #7b341e; line-height: 1.6;
    }
    /* Shown only once the main button is pressed and the work is running. */
    .dc-modal.is-sending .dc-notice { display: flex; }
    .dc-notice > i { font-size: 18px; color: #dd6b20; margin-top: 2px; flex-shrink: 0; }
    .dc-notice strong { color: #7b341e; }

    /* progress */
    .dc-progress { display: none; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px !important; padding: 16px 18px; margin-bottom: 16px; }
    .dc-modal.is-sending .dc-progress, .dc-modal.is-finished .dc-progress { display: block; }
    .dc-steps { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .dc-step { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 700; color: #a0aec0; }
    .dc-step .num {
        width: 22px; height: 22px; border-radius: 50% !important; display: flex; align-items: center; justify-content: center;
        background: #edf2f7; color: #a0aec0; font-size: 11px;
    }
    .dc-step.active { color: #553c9a; }
    .dc-step.active .num { background: #805ad5; color: #fff; animation: dcPulse 1.2s infinite; }
    .dc-step.done { color: #2f855a; }
    .dc-step.done .num { background: #48bb78; color: #fff; }
    .dc-step-line { flex: 1; min-width: 16px; height: 2px; background: #e2e8f0; }
    .dc-step-line.done { background: #48bb78; }

    .dc-bar-top { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 7px; }
    .dc-bar-label { font-size: 13px; color: #4a5568; font-weight: 600; }
    .dc-bar-pct { font-size: 22px; font-weight: 800; color: #553c9a; }
    .dc-modal.is-finished .dc-bar-pct { color: #2f855a; }
    .dc-bar { height: 14px; border-radius: 10px !important; background: #edf2f7; overflow: hidden; }
    .dc-bar-fill {
        height: 100%; width: 0; border-radius: 10px !important; transition: width .45s ease;
        background-color: #805ad5;
        background-image: linear-gradient(45deg, rgba(255,255,255,.22) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.22) 50%, rgba(255,255,255,.22) 75%, transparent 75%, transparent);
        background-size: 28px 28px; animation: dcStripe .8s linear infinite;
    }
    .dc-modal.is-finished .dc-bar-fill { animation: none; background-color: #48bb78; background-image: none; }
    .dc-modal.is-finished.has-failures .dc-bar-fill { background-color: #ed8936; }
    .dc-counts { display: flex; gap: 16px; margin-top: 10px; flex-wrap: wrap; font-size: 12px; color: #718096; }
    .dc-counts b { font-size: 13px; }
    .dc-counts .c-sent b { color: #2f855a; }
    .dc-counts .c-fail b { color: #c53030; }
    .dc-counts .c-skip b { color: #c05621; }
    .dc-result-msg { margin-top: 12px; font-size: 13px; font-weight: 600; display: none; }
    .dc-modal.is-finished .dc-result-msg { display: block; }

    .dc-error {
        display: none; margin-bottom: 14px; padding: 10px 14px; border-radius: 8px !important;
        background: #fff5f5; border: 1px solid #feb2b2; color: #9b2c2c; font-size: 12.5px; font-weight: 600;
    }

    /* footer */
    .dc-foot {
        display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
        padding: 14px 24px; border-top: 1px solid #e2e8f0; background: #fff;
    }
    .dc-foot-right { display: flex; gap: 8px; margin-left: auto; flex-wrap: wrap; }
    .dc-btn {
        height: 38px; padding: 0 18px; border-radius: 8px !important; font-size: 13px; font-weight: 700;
        border: 1px solid transparent; cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
        transition: all .15s; white-space: nowrap;
    }
    .dc-btn:disabled { opacity: .5; cursor: not-allowed; }
    .dc-btn-ghost   { background: #fff; border-color: #cbd5e0; color: #4a5568; }
    .dc-btn-ghost:hover:not(:disabled) { background: #f7fafc; }
    .dc-btn-primary { background: linear-gradient(135deg, #6b46c1, #805ad5); color: #fff; box-shadow: 0 4px 12px rgba(128,90,213,.3); }
    .dc-btn-primary:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(128,90,213,.38); }
    .dc-btn-success { background: linear-gradient(135deg, #2f855a, #48bb78); color: #fff; box-shadow: 0 4px 12px rgba(72,187,120,.3); }
    .dc-btn-warn    { background: #fff; border-color: #ed8936; color: #c05621; }
    .dc-btn-warn:hover { background: #fffaf0; }
    .dc-link {
        background: none; border: 0; padding: 0; font-size: 12px; font-weight: 600; color: #718096;
        text-decoration: underline; cursor: pointer;
    }
    .dc-link:hover { color: #553c9a; }
    .dc-busy-hint { font-size: 12px; color: #805ad5; font-weight: 700; display: none; align-items: center; gap: 7px; }
    .dc-modal.is-busy .dc-busy-hint { display: inline-flex; }
    .dc-modal.is-busy .dc-link { display: none; }
    .dc-hidden { display: none !important; }

    /* themes — one per kind of change (done / failed headers still win) */
    .dc-modal.t-green:not(.is-done):not(.is-failed) .dc-head { background: linear-gradient(135deg, #22543d 0%, #2f855a 60%, #48bb78 100%); }
    .dc-modal.t-red:not(.is-done):not(.is-failed)   .dc-head { background: linear-gradient(135deg, #822727 0%, #c53030 60%, #f56565 100%); }
    .dc-modal.t-blue:not(.is-done):not(.is-failed)  .dc-head { background: linear-gradient(135deg, #2a4365 0%, #2b6cb0 60%, #4299e1 100%); }
    .dc-modal.t-green .dc-btn-primary { background: linear-gradient(135deg, #2f855a, #48bb78); box-shadow: 0 4px 12px rgba(72,187,120,.3); }
    .dc-modal.t-red   .dc-btn-primary { background: linear-gradient(135deg, #c53030, #e53e3e); box-shadow: 0 4px 12px rgba(229,62,62,.3); }
    .dc-modal.t-blue  .dc-btn-primary { background: linear-gradient(135deg, #2b6cb0, #3182ce); box-shadow: 0 4px 12px rgba(49,130,206,.3); }

    /* "what is changing" card */
    .dc-change {
        display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px; padding: 14px 16px;
        background: #fff; border: 1px solid #e2e8f0; border-left: 4px solid #805ad5; border-radius: 10px !important;
    }
    .dc-change-ico {
        width: 36px; height: 36px; border-radius: 10px !important; flex-shrink: 0; font-size: 16px;
        display: flex; align-items: center; justify-content: center; background: #faf5ff; color: #805ad5;
    }
    .dc-modal.t-green .dc-change      { border-left-color: #38a169; }
    .dc-modal.t-green .dc-change-ico  { background: #f0fff4; color: #2f855a; }
    .dc-modal.t-red   .dc-change      { border-left-color: #e53e3e; }
    .dc-modal.t-red   .dc-change-ico  { background: #fff5f5; color: #c53030; }
    .dc-modal.t-blue  .dc-change      { border-left-color: #3182ce; }
    .dc-modal.t-blue  .dc-change-ico  { background: #ebf8ff; color: #2b6cb0; }
    .dc-change-body { flex: 1; min-width: 0; font-size: 13px; color: #4a5568; line-height: 1.6; padding-top: 6px; }
    .dc-change-body b { color: #2d3748; }
    .dc-change-sub { font-size: 12px; color: #718096; margin-top: 10px; }

    .dc-price { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; padding-top: 0; }
    .dc-price-col .cap { font-size: 10px; font-weight: 800; color: #a0aec0; text-transform: uppercase; letter-spacing: .6px; }
    .dc-price-col .old { font-size: 18px; font-weight: 700; color: #a0aec0; text-decoration: line-through; }
    .dc-price-col .new { font-size: 24px; font-weight: 800; color: #2d3748; }
    .dc-price-arrow { color: #cbd5e0; font-size: 20px; padding-top: 12px; }
    .dc-price-delta {
        margin-left: auto; padding: 8px 14px; border-radius: 10px !important; font-size: 15px; font-weight: 800;
        display: flex; flex-direction: column; align-items: flex-end; line-height: 1.3;
    }
    .dc-price-delta span { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; }
    .dc-price-delta.up   { background: #fff5f5; color: #c53030; }
    .dc-price-delta.down { background: #f0fff4; color: #2f855a; }
    .dc-price-delta.same { background: #ebf8ff; color: #2b6cb0; font-size: 13px; }
    @media (max-width: 720px) { .dc-price-delta { margin-left: 0; align-items: flex-start; } }

    @media (max-width: 720px) {
        .dc-lists { grid-template-columns: 1fr; }
        .dc-stats { grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
        .dc-stat { flex-direction: column; align-items: flex-start; gap: 6px; padding: 10px; }
        .dc-body, .dc-head, .dc-foot { padding-left: 16px; padding-right: 16px; }
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">

            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-tag font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">{{ $title }}</span>
                </div>
            </div>

            <div class="portlet-body">

                @if(isset($flash_success))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-check-circle"></i> {{ $flash_success }}
                    </div>
                @endif

                {{-- Summary Cards --}}
                <div class="summary-bar">
                    <div class="sum-card">
                        <span class="dot dot-total"></span>
                        Total Active &nbsp;<strong id="sum-total">{{ $products->count() }}</strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-noprice"></span>
                        No Price Set &nbsp;
                        <strong id="sum-noprice" style="color:#e53e3e;">
                            {{ $products->filter(fn($p) => is_null($p->dealer_price))->count() }}
                        </strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-na"></span>
                        Not Available &nbsp;
                        <strong id="sum-na" style="color:#ed8936;">
                            {{ $products->filter(fn($p) => $p->not_available)->count() }}
                        </strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-disc"></span>
                        Discontinued &nbsp;
                        <strong id="sum-disc" style="color:#805ad5;">
                            {{ $products->filter(fn($p) => $p->discontinued)->count() }}
                        </strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-focus"></span>
                        Focus Products &nbsp;
                        <strong id="sum-focus" style="color:#38a169;">
                            {{ $products->filter(fn($p) => $p->focus_product)->count() }}
                        </strong>
                    </div>
                </div>

                {{-- Filters — single row with labels --}}
                <div class="filter-strip">

                    <div class="fg">
                        <label><i class="fa fa-cube"></i> Product</label>
                        <select id="filter-product">
                            <option value="">— All Products —</option>
                            @foreach($products->sortBy('product_name') as $p)
                                <option value="{{ $p->id }}">{{ $p->product_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fg">
                        <label><i class="fa fa-inr"></i> Price Status</label>
                        <select id="filter-price-status">
                            <option value="">— All —</option>
                            <option value="has_price">Has Price</option>
                            <option value="no_price">No Price</option>
                            <option value="today">Updated Today</option>
                        </select>
                    </div>

                    <div class="fg">
                        <label><i class="fa fa-search"></i> Search</label>
                        <input type="text" id="filter-search" placeholder="Name or code...">
                    </div>

                    <span class="f-sep"></span>

                    <div class="fg-check-wrap">
                        <span class="fg-lbl">Flags</span>
                        <div style="display:flex;gap:12px;align-items:center;height:30px;">
                            <label class="fg-check">
                                <input type="checkbox" id="filter-na" class="chk-na">
                                <span>Not Available</span>
                            </label>
                            <label class="fg-check">
                                <input type="checkbox" id="filter-disc" class="chk-disc">
                                <span>Discontinued</span>
                            </label>
                            <label class="fg-check">
                                <input type="checkbox" id="filter-focus" class="chk-focus">
                                <span>Focus Only</span>
                            </label>
                        </div>
                    </div>

                    <span class="f-sep"></span>

                    <div class="fg">
                        <label>&nbsp;</label>
                        <a href="#" id="btn-export-pdf" class="btn-export-pdf">
                            <i class="fa fa-file-pdf-o"></i> Export PDF
                        </a>
                    </div>

                    <div class="fg">
                        <label>&nbsp;</label>
                        <button type="button" id="btn-clear-filters" class="btn-clear-filters hidden">
                            <i class="fa fa-times"></i> Clear
                        </button>
                    </div>

                    <span class="filter-result">
                        Showing <strong id="visible-count">{{ $products->count() }}</strong> of {{ $products->count() }}
                    </span>

                </div>

                {{-- Table --}}
                <div class="pricing-wrap">
                    <table class="pricing-table" id="pricing-table">
                        <colgroup>
                            <col class="c-no">
                            <col class="c-name">
                            <col class="c-moq">
                            <col class="c-dispatch">
                            <col class="c-na">
                            <col class="c-disc">
                            <col class="c-focus">
                            <col class="c-dp">
                            <col class="c-date">
                            <col class="c-action">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="center">#</th>
                                <th>Product Name</th>
                                <th>MOQ</th>
                                <th>Disp. Time</th>
                                <th class="center">N.A.</th>
                                <th class="center">Discont.</th>
                                <th class="center" style="color:#276749;">Focus</th>
                                <th>Std. DP (₹)</th>
                                <th class="center">Date</th>
                                <th class="center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="pricing-tbody">

                        @foreach($products as $index => $product)
                        @php
                            $hasPrice = !is_null($product->dealer_price);
                            $isToday  = $hasPrice && $product->price_date === $today;
                        @endphp
                        <tr class="product-row"
                            id="row-{{ $product->id }}"
                            data-product-id="{{ $product->id }}"
                            data-orig-moq="{{ $product->moq }}"
                            data-orig-dispatch="{{ $product->average_dispatch_time }}"
                            data-orig-na="{{ $product->not_available ? 1 : 0 }}"
                            data-orig-disc="{{ $product->discontinued ? 1 : 0 }}"
                            data-orig-focus="{{ $product->focus_product ? 1 : 0 }}"
                            data-orig-dp="{{ $hasPrice ? number_format((float)$product->dealer_price,2,'.','') : '' }}"
                            data-pricing-id="{{ $product->pricing_id }}"
                            data-has-price="{{ $hasPrice ? 1 : 0 }}"
                            data-is-today="{{ $isToday ? 1 : 0 }}"
                            data-name="{{ strtolower($product->product_name) }}"
                            data-code="{{ strtolower($product->product_code) }}"
                            data-focus="{{ $product->focus_product ? 1 : 0 }}">

                            <td class="sr-no-cell">{{ $index + 1 }}</td>

                            <td>
                                <span class="prod-name">{{ $product->product_name }}</span>
                                <span class="prod-code">{{ $product->product_code }}</span>
                            </td>

                            <td>
                                <div class="dp-wrap">
                                    <input type="text" class="inline-input dp-input field-moq"
                                           value="{{ $product->moq }}" maxlength="191">
                                    <span class="cur">kg</span>
                                </div>
                            </td>

                            <td>
                                <div class="dp-wrap">
                                    <input type="number" class="inline-input dp-input field-dispatch"
                                           value="{{ $product->average_dispatch_time }}"
                                           min="0" step="0.5">
                                    <span class="cur">days</span>
                                </div>
                            </td>

                            <td>
                                <div class="na-toggle">
                                    <input type="checkbox" class="field-na"
                                           {{ $product->not_available ? 'checked' : '' }}
                                           {{ $product->discontinued ? 'disabled' : '' }}>
                                </div>
                            </td>

                            <td>
                                <div class="disc-toggle">
                                    <input type="checkbox" class="field-disc"
                                           {{ $product->discontinued ? 'checked' : '' }}
                                           {{ $product->not_available ? 'disabled' : '' }}>
                                </div>
                            </td>

                            {{-- ── FOCUS PRODUCT (independent) ── --}}
                            <td>
                                <div class="focus-toggle">
                                    <input type="checkbox" class="field-focus"
                                           {{ $product->focus_product ? 'checked' : '' }}>
                                </div>
                            </td>

                            <td>
                                <div class="dp-wrap">
                                    <span class="cur">₹</span>
                                    <input type="number"
                                           class="inline-input dp-input field-dp"
                                           value="{{ $hasPrice ? number_format((float)$product->dealer_price,2,'.','') : '' }}"
                                           placeholder="{{ $hasPrice ? '' : '—' }}"
                                           min="0" step="0.01">
                                </div>
                            </td>

                            <td class="price-date-td">
                                @if($hasPrice)
                                    <span class="pd-badge {{ $isToday ? 'pd-today' : 'pd-old' }} price-date-label">
                                        {{ $isToday ? 'Today' : \Carbon\Carbon::parse($product->price_date)->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="pd-badge pd-none price-date-label">No Price</span>
                                @endif
                            </td>

                            <td>
                                <button type="button" class="btn btn-primary btn-xs btn-update" disabled>
                                    <i class="fa fa-save"></i> Update
                                </button>
                            </td>
                        </tr>
                        @endforeach

                        <tr id="empty-row" style="display:none;">
                            <td colspan="10">
                                <i class="fa fa-inbox" style="font-size:22px; display:block; margin-bottom:6px;"></i>
                                No products match the current filters.
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ══ Notify popup: Discont., N.A. and dealer price changes ══ --}}
<div class="dc-overlay" id="dc-overlay" aria-hidden="true">
    <div class="dc-modal" id="dc-modal" role="dialog" aria-modal="true" aria-labelledby="dc-title">

        <div class="dc-head">
            <button type="button" class="dc-close" id="dc-close" title="Close"><i class="fa fa-times"></i></button>
            <div class="dc-head-row">
                <div class="dc-head-icon"><i class="fa fa-ban" id="dc-head-icon"></i></div>
                <div style="min-width:0;">
                    <div class="dc-head-kicker" id="dc-kicker">Discontinue Product</div>
                    <div class="dc-head-title" id="dc-title">—</div>
                    <span class="dc-head-code" id="dc-code"></span>
                </div>
            </div>
        </div>

        <div class="dc-body">
            <div class="dc-loading" id="dc-loading">
                <div class="dc-spinner"></div>
                Finding employees and dealers linked to this product…
            </div>

            <div id="dc-content" class="dc-hidden">
                <div class="dc-error" id="dc-error"></div>

                {{-- what is changing; filled per change by the script --}}
                <div class="dc-change">
                    <span class="dc-change-ico"><i class="fa fa-ban" id="dc-change-icon"></i></span>
                    <div class="dc-change-body" id="dc-change-body"></div>
                </div>

                <div class="dc-notice">
                    <i class="fa fa-exclamation-triangle"></i>
                    <div>
                        <strong>Important:</strong> keep this window open until every email has been sent.
                        Emails go out one by one from this page — closing, refreshing or leaving it
                        stops the ones that are left.
                    </div>
                </div>

                {{-- progress (shown once sending starts) --}}
                <div class="dc-progress">
                    <div class="dc-steps">
                        <span class="dc-step" id="dc-step-1"><span class="num">1</span> <span id="dc-step-1-label">Save change</span></span>
                        <span class="dc-step-line" id="dc-line-1"></span>
                        <span class="dc-step" id="dc-step-2"><span class="num">2</span> Send emails</span>
                        <span class="dc-step-line" id="dc-line-2"></span>
                        <span class="dc-step" id="dc-step-3"><span class="num">3</span> Done</span>
                    </div>
                    <div class="dc-bar-top">
                        <span class="dc-bar-label" id="dc-bar-label">Preparing…</span>
                        <span class="dc-bar-pct" id="dc-bar-pct">0%</span>
                    </div>
                    <div class="dc-bar"><div class="dc-bar-fill" id="dc-bar-fill"></div></div>
                    <div class="dc-counts">
                        <span class="c-sent"><i class="fa fa-check-circle"></i> Sent <b id="dc-c-sent">0</b></span>
                        <span class="c-fail"><i class="fa fa-times-circle"></i> Failed <b id="dc-c-fail">0</b></span>
                        <span class="c-skip"><i class="fa fa-minus-circle"></i> Skipped <b id="dc-c-skip">0</b></span>
                        <span class="c-left"><i class="fa fa-clock-o"></i> Remaining <b id="dc-c-left">0</b></span>
                    </div>
                    <div class="dc-result-msg" id="dc-result-msg"></div>
                </div>

                <div class="dc-stats">
                    <div class="dc-stat">
                        <span class="dc-stat-ico emp"><i class="fa fa-user"></i></span>
                        <div><div class="dc-stat-num" id="dc-n-emp">0</div><div class="dc-stat-lbl">Employees linked</div></div>
                    </div>
                    <div class="dc-stat">
                        <span class="dc-stat-ico dlr"><i class="fa fa-building"></i></span>
                        <div><div class="dc-stat-num" id="dc-n-dlr">0</div><div class="dc-stat-lbl">Dealers linked</div></div>
                    </div>
                    <div class="dc-stat">
                        <span class="dc-stat-ico mail"><i class="fa fa-envelope"></i></span>
                        <div><div class="dc-stat-num" id="dc-n-mail">0</div><div class="dc-stat-lbl">Emails to send</div></div>
                    </div>
                </div>

                <div class="dc-lists">
                    <div class="dc-list">
                        <div class="dc-list-head">
                            <span class="dc-list-title"><i class="fa fa-user"></i> Employees <span class="cnt" id="dc-cnt-emp">0</span></span>
                            <label class="dc-selall"><input type="checkbox" class="dc-all" data-type="employee" checked> All</label>
                        </div>
                        <div class="dc-items" id="dc-list-employee"></div>
                    </div>
                    <div class="dc-list">
                        <div class="dc-list-head">
                            <span class="dc-list-title"><i class="fa fa-building"></i> Dealers <span class="cnt" id="dc-cnt-dlr">0</span></span>
                            <label class="dc-selall"><input type="checkbox" class="dc-all" data-type="dealer" checked> All</label>
                        </div>
                        <div class="dc-items" id="dc-list-dealer"></div>
                    </div>
                </div>

                <div class="dc-fields">
                    <div class="dc-field">
                        <label for="dc-remarks">Note to include in the email <span class="opt">(optional)</span></label>
                        <textarea id="dc-remarks" maxlength="1000" placeholder="e.g. Existing stock will be supplied until 30 Nov."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="dc-foot">
            <button type="button" class="dc-link" id="dc-mark-only">Save without sending emails</button>
            <span class="dc-busy-hint"><i class="fa fa-spinner fa-spin"></i> Sending — please don't close this window</span>
            <div class="dc-foot-right">
                <button type="button" class="dc-btn dc-btn-ghost" id="dc-cancel">Cancel</button>
                <button type="button" class="dc-btn dc-btn-warn dc-hidden" id="dc-retry"><i class="fa fa-refresh"></i> Retry failed</button>
                <button type="button" class="dc-btn dc-btn-primary" id="dc-go" disabled><i class="fa fa-ban" id="dc-go-icon"></i> <span>Save</span></button>
                <button type="button" class="dc-btn dc-btn-success dc-hidden" id="dc-done"><i class="fa fa-check"></i> Done</button>
            </div>
        </div>
    </div>
</div>

<div id="toast-wrap"></div>

<script>
$(document).ready(function () {

    var TODAY = '{{ $today }}';
    var CSRF  = '{{ csrf_token() }}';

    /* ══ 1. DIRTY TRACKING ══ */
    function origDpStr($row) {
        var v = $row.data('orig-dp');
        return (v !== '' && v !== undefined && v !== null) ? parseFloat(v).toFixed(2) : '';
    }
    function curDpStr($row) {
        var v = $row.find('.field-dp').val().trim();
        return v !== '' ? parseFloat(v).toFixed(2) : '';
    }

    function checkDirty($row) {
        var dirty = (
            $row.find('.field-moq').val().trim()      !== ($row.data('orig-moq') + '')      ||
            $row.find('.field-dispatch').val().trim() !== ($row.data('orig-dispatch') + '') ||
            ($row.find('.field-focus').is(':checked') ? 1 : 0) !== parseInt($row.data('orig-focus')) ||
            curDpStr($row) !== origDpStr($row)
        );
        $row.toggleClass('is-dirty', dirty);
        $row.find('.btn-update').prop('disabled', !dirty);
        $row.find('.field-moq').toggleClass('changed',
            $row.find('.field-moq').val().trim() !== ($row.data('orig-moq') + ''));
        $row.find('.field-dispatch').toggleClass('changed',
            $row.find('.field-dispatch').val().trim() !== ($row.data('orig-dispatch') + ''));
        $row.find('.field-dp').toggleClass('changed',
            curDpStr($row) !== origDpStr($row));
    }

    $(document).on('input change', '.product-row .inline-input', function () {
        checkDirty($(this).closest('.product-row'));
    });

    /* ══ 2–3. N.A. AND DISCONTINUED — independent of Update ══
       Ticking or unticking either box opens the notify popup (section 9).
       The box is put back straight away and only changes once the popup has
       saved the new value, so cancelling the popup leaves it as it was. */
    $(document).on('change', '.product-row .field-na', function () {
        var $row = $(this).closest('.product-row');
        var on   = this.checked;
        $(this).prop('checked', !on);

        if (on && $row.find('.field-disc').is(':checked')) {
            showToast('danger', '<i class="fa fa-exclamation-circle"></i> &nbsp;This product is discontinued. Untick Discont. first.');
            return;
        }
        NotifyModal.open($row, on ? 'na' : 'available');
    });

    $(document).on('change', '.product-row .field-disc', function () {
        var $row = $(this).closest('.product-row');
        var on   = this.checked;
        $(this).prop('checked', !on);

        if (on && $row.find('.field-na').is(':checked')) {
            showToast('danger', '<i class="fa fa-exclamation-circle"></i> &nbsp;This product is marked Not Available. Untick N.A. first.');
            return;
        }
        NotifyModal.open($row, on ? 'discontinue' : 'reinstate');
    });

    /* Keep a row's boxes and the summary cards in step with a saved flag.
       Discontinued and Not Available exclude each other, so ticking one
       locks the other. */
    function setRowFlag($row, flag, on) {
        var mine  = flag === 'disc' ? '.field-disc' : '.field-na';
        var other = flag === 'disc' ? '.field-na'   : '.field-disc';
        $row.data(flag === 'disc' ? 'orig-disc' : 'orig-na', on ? 1 : 0);
        $row.find(mine).prop('checked', on);
        $row.find(other).prop('disabled', on);
        $('#sum-disc').text($('#pricing-tbody .field-disc:checked').length);
        $('#sum-na').text($('#pricing-tbody .field-na:checked').length);
        if ($('#filter-disc').is(':checked') || $('#filter-na').is(':checked')) applyFilters();
    }

    function flashRow($row) {
        $row.addClass('row-saved');
        setTimeout(function () { $row.removeClass('row-saved'); }, 1700);
    }

    function escapeHtml(v) {
        return $('<div>').text(v == null ? '' : String(v)).html();
    }

    function ajaxError(xhr, fallback) {
        if (xhr && xhr.responseJSON) {
            if (xhr.responseJSON.errors) {
                var k = Object.keys(xhr.responseJSON.errors)[0];
                return escapeHtml(xhr.responseJSON.errors[k][0]);
            }
            if (xhr.responseJSON.message) return escapeHtml(xhr.responseJSON.message);
        }
        if (xhr && xhr.status === 0) return 'Network error — check your connection.';
        return fallback;
    }

    /* ══ 4. FOCUS PRODUCT — fully independent, just track dirty ══ */
    $(document).on('change', '.product-row .field-focus', function () {
        checkDirty($(this).closest('.product-row'));
    });

    /* ══ 5. UPDATE ══
       A new dealer price goes through the notify popup, so the linked
       employees and dealers can be told about it. MOQ, dispatch time and
       focus alone are saved straight away. */
    function priceChanged($row) {
        return curDpStr($row) !== '' && curDpStr($row) !== origDpStr($row);
    }

    /* Save the row. Resolves with the server response, rejects with an
       error message. */
    function saveRow($row) {
        var curDp = $row.find('.field-dp').val().trim();
        var dpChg = curDpStr($row) !== origDpStr($row);

        var payload = {
            _token:                CSRF,
            moq:                   $row.find('.field-moq').val().trim(),
            average_dispatch_time: $row.find('.field-dispatch').val().trim(),
            focus_product:         $row.find('.field-focus').is(':checked') ? 1 : 0,
            dealer_price:          curDp !== '' ? curDp : null,
            dp_changed:            dpChg ? 1 : 0,
            pricing_id:            $row.data('pricing-id') || '',
        };

        return $.ajax({
            url:    '/admin/product-pricing/update/' + $row.data('product-id'),
            method: 'POST',
            data:   payload
        }).then(function (resp) {
            if (!resp.success) return $.Deferred().reject(escapeHtml(resp.message || 'Update failed.')).promise();

            $row.data('orig-moq',      payload.moq);
            $row.data('orig-dispatch', payload.average_dispatch_time);
            $row.data('orig-focus',    payload.focus_product);
            $row.data('orig-dp',       curDp !== '' ? parseFloat(curDp).toFixed(2) : '');
            $row.data('focus',         payload.focus_product);

            if (dpChg && resp.pricing_id) {
                $row.data('pricing-id', resp.pricing_id);
                $row.data('has-price', 1);
                $row.data('is-today',  1);
                $row.find('.price-date-label')
                    .removeClass('pd-old pd-none').addClass('pd-today').text('Today');
            }

            $row.find('.inline-input').removeClass('changed');
            checkDirty($row);
            flashRow($row);
            return resp;
        }, function (xhr) {
            return $.Deferred().reject(ajaxError(xhr, 'Update failed.')).promise();
        });
    }

    $(document).on('click', '.btn-update:not(:disabled)', function () {
        var $btn = $(this);
        var $row = $btn.closest('.product-row');

        if (priceChanged($row)) {
            NotifyModal.open($row, 'price', { oldPrice: origDpStr($row), newPrice: curDpStr($row) });
            return;
        }

        $btn.addClass('saving').html('<i class="fa fa-spinner fa-spin"></i>');
        saveRow($row)
            .done(function (resp) { showToast('success', '<i class="fa fa-check-circle"></i> &nbsp;' + escapeHtml(resp.message)); })
            .fail(function (msg)  { showToast('danger',  '<i class="fa fa-times-circle"></i> &nbsp;' + msg); })
            .always(function () {
                $btn.removeClass('saving').html('<i class="fa fa-save"></i> Update');
                checkDirty($row);
            });
    });

    /* ══ 6. FILTERS + RENUMBER ══ */
    function isFiltered() {
        return $('#filter-product').val()        !== '' ||
               $('#filter-price-status').val()   !== '' ||
               $('#filter-search').val().trim()  !== '' ||
               $('#filter-na').is(':checked')           ||
               $('#filter-disc').is(':checked')         ||
               $('#filter-focus').is(':checked');
    }

    function renumberVisible() {
        var n = 0;
        $('#pricing-tbody .product-row:visible').each(function () {
            $(this).find('.sr-no-cell').text(++n);
        });
    }

    function applyFilters() {
        var prodId      = $('#filter-product').val();
        var priceStatus = $('#filter-price-status').val();
        var search      = $('#filter-search').val().toLowerCase().trim();
        var naOnly      = $('#filter-na').is(':checked');
        var discOnly    = $('#filter-disc').is(':checked');
        var focusOnly   = $('#filter-focus').is(':checked');
        var visible     = 0;

        $('#pricing-tbody .product-row').each(function () {
            var $r = $(this);

            if (prodId && $r.data('product-id') + '' !== prodId)           { $r.hide(); return; }
            if (priceStatus === 'has_price' && $r.data('has-price') != 1)  { $r.hide(); return; }
            if (priceStatus === 'no_price'  && $r.data('has-price') == 1)  { $r.hide(); return; }
            if (priceStatus === 'today'     && $r.data('is-today')  != 1)  { $r.hide(); return; }
            if (search) {
                var name = $r.data('name') || '';
                var code = $r.data('code') || '';
                if (name.indexOf(search) === -1 && code.indexOf(search) === -1) { $r.hide(); return; }
            }
            if (naOnly   && !$r.find('.field-na').is(':checked'))    { $r.hide(); return; }
            if (discOnly && !$r.find('.field-disc').is(':checked'))  { $r.hide(); return; }
            if (focusOnly && !$r.find('.field-focus').is(':checked')) { $r.hide(); return; }

            $r.show();
            visible++;
        });

        $('#visible-count').text(visible);
        $('#empty-row').toggle(visible === 0);
        renumberVisible();
        updatePdfLink();

        if (isFiltered()) {
            $('#btn-clear-filters').removeClass('hidden');
        } else {
            $('#btn-clear-filters').addClass('hidden');
        }
    }

    $('#filter-product, #filter-price-status').on('change', applyFilters);
    $('#filter-search').on('input', applyFilters);
    $('#filter-na, #filter-disc, #filter-focus').on('change', applyFilters);

    /* ══ Clear all filters ══ */
    $('#btn-clear-filters').on('click', function () {
        $('#filter-product').val('');
        $('#filter-price-status').val('');
        $('#filter-search').val('');
        $('#filter-na').prop('checked', false);
        $('#filter-disc').prop('checked', false);
        $('#filter-focus').prop('checked', false);
        applyFilters();
    });

    /* ══ 7. PDF EXPORT LINK ══ */
    function updatePdfLink() {
        var params = new URLSearchParams();
        var prodId      = $('#filter-product').val();
        var priceStatus = $('#filter-price-status').val();
        var search      = $('#filter-search').val().trim();
        var naOnly      = $('#filter-na').is(':checked');
        var discOnly    = $('#filter-disc').is(':checked');
        var focusOnly   = $('#filter-focus').is(':checked');

        if (prodId)      params.set('product_id',    prodId);
        if (priceStatus) params.set('price_status',  priceStatus);
        if (search)      params.set('search',        search);
        if (naOnly)      params.set('not_available', 1);
        if (discOnly)    params.set('discontinued',  1);
        if (focusOnly)   params.set('focus_product', 1);

        $('#btn-export-pdf').attr('href', '{{ route("admin.product-pricing.export-pdf") }}?' + params.toString());
    }

    updatePdfLink();

    /* ══ 8. TOAST ══ */
    function showToast(type, html) {
        var $t = $('<div class="toast-item toast-' + type + '"></div>').html(html);
        $('#toast-wrap').append($t);
        setTimeout(function () { $t.css('opacity', 1); }, 10);
        setTimeout(function () {
            $t.css('opacity', 0);
            setTimeout(function () { $t.remove(); }, 320);
        }, 3200);
    }

    /* ══ 9. NOTIFY POPUP ══
       One popup for every change the linked employees and dealers hear
       about: discontinue, continue again, not available, available again
       and a new dealer price. Each has an entry in ACTIONS. The main button
       saves the change first, then emails the ticked people one per request
       so the progress bar moves after every email. It cannot be closed
       while a request is in flight. */
    var NotifyModal = (function () {
        var BATCH = 1;   // one email per request, so the bar moves after every send
        var $ov = $('#dc-overlay'), $m = $('#dc-modal');
        var st  = null;  // state of the popup currently open

        function money(v) {
            return '₹' + parseFloat(v).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function postFlag(path, data) {
            return $.ajax({
                url:    '/admin/product-pricing/' + path + '/' + st.pid,
                method: 'POST',
                data:   $.extend({ _token: CSRF }, data)
            }).then(function (resp) {
                if (!resp.success) return $.Deferred().reject(escapeHtml(resp.message)).promise();
                return resp;
            }, function (xhr) {
                return $.Deferred().reject(ajaxError(xhr, 'Could not save the change.')).promise();
            });
        }

        function flagSaver(path, field, flag, on) {
            return function () {
                var data = {}; data[field] = on ? 1 : 0;
                return postFlag(path, data).then(function (resp) {
                    setRowFlag(st.$row, flag, on);
                    flashRow(st.$row);
                    return resp;
                });
            };
        }

        var ACTIONS = {
            discontinue: {
                event: 'discontinued', theme: 't-purple', icon: 'fa-ban',
                kicker: 'Discontinue Product', step1: 'Mark discontinued', go: 'Mark as Discontinued',
                markOnly: 'Mark discontinued without sending emails', doneKicker: 'Discontinued',
                summary: function () {
                    return 'Linked employees and dealers will be told that this product has been <b>discontinued</b> and can no longer be ordered.';
                },
                save: flagSaver('discontinue', 'discontinued', 'disc', true)
            },
            reinstate: {
                event: 'reinstated', theme: 't-green', icon: 'fa-undo',
                kicker: 'Continue Product', step1: 'Mark as continued', go: 'Continue Product',
                markOnly: 'Continue without sending emails', doneKicker: 'Available again',
                summary: function () {
                    return 'This product was discontinued. Linked employees and dealers will be told it is <b>available again</b> and can be ordered.';
                },
                save: flagSaver('discontinue', 'discontinued', 'disc', false)
            },
            na: {
                event: 'not_available', theme: 't-red', icon: 'fa-pause-circle',
                kicker: 'Mark Not Available', step1: 'Mark not available', go: 'Mark as Not Available',
                markOnly: 'Mark not available without sending emails', doneKicker: 'Marked not available',
                summary: function () {
                    return 'Linked employees and dealers will be told this product is <b>temporarily not available</b>. It is not discontinued.';
                },
                save: flagSaver('not-available', 'not_available', 'na', true)
            },
            available: {
                event: 'available', theme: 't-green', icon: 'fa-check-circle',
                kicker: 'Mark Available', step1: 'Mark available', go: 'Mark as Available',
                markOnly: 'Mark available without sending emails', doneKicker: 'Marked available',
                summary: function () {
                    return 'This product was not available. Linked employees and dealers will be told it is <b>back in stock</b>.';
                },
                save: flagSaver('not-available', 'not_available', 'na', false)
            },
            price: {
                event: 'price_changed', theme: 't-blue', icon: 'fa-inr',
                kicker: 'Update Dealer Price', step1: 'Save new price', go: 'Update Price',
                markOnly: 'Update price without sending emails', doneKicker: 'Price updated',
                summary: function (o) {
                    var oldP = o.oldPrice === '' ? null : parseFloat(o.oldPrice);
                    var newP = parseFloat(o.newPrice);
                    var delta;
                    if (oldP === null) {
                        delta = '<div class="dc-price-delta same"><i class="fa fa-star"></i> First price for this product</div>';
                    } else {
                        var diff = newP - oldP, up = diff > 0;
                        var pct  = oldP > 0 ? Math.abs(diff / oldP * 100).toFixed(1) + '%' : '';
                        delta = '<div class="dc-price-delta ' + (up ? 'up' : 'down') + '">' +
                                    '<i class="fa fa-arrow-' + (up ? 'up' : 'down') + '"></i> ' + money(Math.abs(diff)) +
                                    (pct ? ' (' + (up ? '+' : '−') + pct + ')' : '') +
                                    '<span>Price ' + (up ? 'increase' : 'decrease') + '</span>' +
                                '</div>';
                    }
                    return '<div class="dc-price">' +
                               (oldP === null ? '' :
                               '<div class="dc-price-col"><div class="cap">Current</div><div class="old">' + money(oldP) + '</div></div>' +
                               '<div class="dc-price-arrow"><i class="fa fa-long-arrow-right"></i></div>') +
                               '<div class="dc-price-col"><div class="cap">New price</div><div class="new">' + money(newP) + '</div></div>' +
                               delta +
                           '</div>' +
                           '<div class="dc-change-sub">Linked employees and dealers will be told about the new dealer price. ' +
                           'Any MOQ, dispatch time or focus change on this row is saved with it.</div>';
                },
                save: function () {
                    return saveRow(st.$row);
                }
            }
        };

        function initials(name) {
            var p = String(name || '?').trim().split(/\s+/);
            return ((p[0] || '?').charAt(0) + (p.length > 1 ? p[p.length - 1].charAt(0) : '')).toUpperCase();
        }

        function itemHtml(r) {
            var key = r.type + '-' + r.id;
            var sub = r.has_email
                ? escapeHtml(r.email) + (r.sub ? ' · ' + escapeHtml(r.sub) : '')
                : '<span class="warn"><i class="fa fa-exclamation-circle"></i> No email on record</span>' + (r.sub ? ' · ' + escapeHtml(r.sub) : '');
            return '<label class="dc-item' + (r.has_email ? '' : ' no-email') + '" data-key="' + key + '">' +
                       '<input type="checkbox" class="dc-pick" data-type="' + r.type + '" data-id="' + r.id + '"' +
                           (r.has_email ? ' checked' : ' disabled') + '>' +
                       '<span class="dc-avatar ' + (r.type === 'dealer' ? 'dlr' : 'emp') + '">' + escapeHtml(initials(r.name)) + '</span>' +
                       '<span class="dc-who">' +
                           '<span class="dc-who-name" title="' + escapeHtml(r.name) + '">' + escapeHtml(r.name) + '</span>' +
                           '<span class="dc-who-sub">' + sub + '</span>' +
                       '</span>' +
                       '<span class="dc-pill"></span>' +
                   '</label>';
        }

        function renderList(type, rows) {
            var $l = $('#dc-list-' + type);
            if (!rows.length) {
                $l.html('<div class="dc-empty"><i class="fa fa-' + (type === 'dealer' ? 'building-o' : 'user-o') + '"></i>' +
                        'No ' + (type === 'dealer' ? 'dealers' : 'employees') + ' are linked to this product.</div>');
            } else {
                $l.html(rows.map(itemHtml).join(''));
            }
            $('.dc-all[data-type="' + type + '"]').prop('disabled', !rows.some(function (r) { return r.has_email; }));
        }

        function selected() {
            return $m.find('.dc-pick:checked:not(:disabled)').map(function () {
                return { type: $(this).data('type'), id: $(this).data('id') };
            }).get();
        }

        function refreshCount() {
            var n = selected().length;
            $('#dc-n-mail').text(n);
            ['employee', 'dealer'].forEach(function (t) {
                var $p = $m.find('.dc-pick[data-type="' + t + '"]:not(:disabled)');
                $('.dc-all[data-type="' + t + '"]').prop('checked', $p.length > 0 && $p.filter(':checked').length === $p.length);
            });
            $('#dc-go span').text(st.action.go + (n > 0 ? ' & Email ' + n + (n === 1 ? ' person' : ' people') : ''));
            $('#dc-go').prop('disabled', false);
        }

        function setBusy(on) {
            st.busy = on;
            $m.toggleClass('is-busy', on);
            $m.find('.dc-pick, .dc-all, #dc-remarks').prop('disabled', on);
            if (!on) $m.find('.dc-item.no-email .dc-pick').prop('disabled', true);
            $('#dc-cancel, #dc-go').prop('disabled', on);
        }

        function showError(msg) {
            $('#dc-error').html('<i class="fa fa-times-circle"></i> &nbsp;' + msg).toggle(!!msg);
        }

        function step(n, state) {
            $('#dc-step-' + n).removeClass('active done').addClass(state || '');
            if (n > 1) $('#dc-line-' + (n - 1)).toggleClass('done', state === 'active' || state === 'done');
        }

        function setPill(key, status, title) {
            var labels = { queued: 'Queued', sending: 'Sending', sent: 'Sent', failed: 'Failed', skipped: 'Skipped', excluded: '—' };
            var $it = $m.find('.dc-item[data-key="' + key + '"]');
            $it.removeClass('st-sent st-failed').addClass(status === 'sent' ? 'st-sent' : status === 'failed' ? 'st-failed' : '');
            $it.find('.dc-pill').attr('class', 'dc-pill ' + status).attr('title', title || '').text(labels[status] || status);
        }

        function updateProgress(label) {
            var total = st.total || 0;
            var done  = st.sent + st.failed + st.skipped;
            var pct   = total ? Math.round(done / total * 100) : 100;
            $('#dc-bar-fill').css('width', pct + '%');
            $('#dc-bar-pct').text(pct + '%');
            $('#dc-c-sent').text(st.sent);
            $('#dc-c-fail').text(st.failed);
            $('#dc-c-skip').text(st.skipped);
            $('#dc-c-left').text(Math.max(total - done, 0));
            if (label) $('#dc-bar-label').html(label);
        }

        /* ── open ── */
        function open($row, actionKey, opts) {
            var action = ACTIONS[actionKey];
            st = { $row: $row, pid: $row.data('product-id'), action: action, opts: opts || {},
                   busy: false, saved: false, sent: 0, failed: 0, skipped: 0, total: 0, failedList: [] };

            $m.removeClass('is-busy is-sending is-finished is-done is-failed has-failures t-purple t-green t-red t-blue')
              .addClass(action.theme);
            $('#dc-head-icon, #dc-change-icon').attr('class', 'fa ' + action.icon);
            $('#dc-go-icon').attr('class', 'fa ' + action.icon);
            $('#dc-kicker').text(action.kicker);
            $('#dc-title').text($row.find('.prod-name').text());
            $('#dc-code').text($row.find('.prod-code').text()).toggle(!!$.trim($row.find('.prod-code').text()));
            $('#dc-change-body').html(action.summary(st.opts));
            $('#dc-step-1-label').text(action.step1);
            $('#dc-mark-only').text(action.markOnly);
            $('#dc-go span').text(action.go);
            $('#dc-loading').removeClass('dc-hidden');
            $('#dc-content').addClass('dc-hidden');
            $('#dc-remarks').val('');
            $('#dc-go').prop('disabled', true).removeClass('dc-hidden');
            $('#dc-cancel, #dc-mark-only').removeClass('dc-hidden').prop('disabled', false);
            $('#dc-done, #dc-retry').addClass('dc-hidden');
            $('#dc-bar-fill').css('width', 0);
            [1, 2, 3].forEach(function (n) { step(n, ''); });
            showError('');

            $ov.addClass('open').attr('aria-hidden', 'false');
            $('body').addClass('dc-lock');
            $m.find('.dc-body').scrollTop(0);

            var mine = st;
            $.getJSON('/admin/product-pricing/notify/' + st.pid + '/recipients')
                .done(function (resp) {
                    if (st !== mine) return;
                    $('#dc-loading').addClass('dc-hidden');
                    $('#dc-content').removeClass('dc-hidden');
                    if (!resp.success) { showError(escapeHtml(resp.message)); return; }
                    $('#dc-n-emp').text(resp.employees.length);
                    $('#dc-n-dlr').text(resp.dealers.length);
                    $('#dc-cnt-emp').text(resp.employees.length);
                    $('#dc-cnt-dlr').text(resp.dealers.length);
                    renderList('employee', resp.employees);
                    renderList('dealer', resp.dealers);
                    refreshCount();
                })
                .fail(function (xhr) {
                    if (st !== mine) return;
                    $('#dc-loading').addClass('dc-hidden');
                    $('#dc-content').removeClass('dc-hidden');
                    showError(ajaxError(xhr, 'Could not load the linked employees and dealers.'));
                });
        }

        function close() {
            if (!st || st.busy) return;
            $ov.removeClass('open').attr('aria-hidden', 'true');
            $('body').removeClass('dc-lock');
            st = null;
        }

        /* ── emails, one per request ── */
        function sendAll(queue) {
            st.total = st.sent + st.failed + st.skipped + queue.length;
            queue.forEach(function (r) { setPill(r.type + '-' + r.id, 'queued'); });
            updateProgress('Sending emails…');

            var payloadBase = {
                _token:  CSRF,
                event:   st.action.event,
                remarks: $.trim($('#dc-remarks').val())
            };
            if (st.action.event === 'price_changed') payloadBase.old_price = st.opts.oldPrice;

            function next(i) {
                if (i >= queue.length) { finish(); return; }
                var batch = queue.slice(i, i + BATCH);
                batch.forEach(function (r) { setPill(r.type + '-' + r.id, 'sending'); });
                var name = $m.find('.dc-item[data-key="' + batch[0].type + '-' + batch[0].id + '"] .dc-who-name').text();
                updateProgress('Sending ' + (i + 1) + ' of ' + queue.length + ' — <b>' + escapeHtml(name) + '</b>');
                scrollIntoList(batch[0]);

                $.ajax({
                    url:     '/admin/product-pricing/notify/' + st.pid,
                    method:  'POST',
                    data:    $.extend({ recipients: batch }, payloadBase),
                    timeout: 180000
                }).done(function (resp) {
                    var byKey = {};
                    (resp.results || []).forEach(function (r) { byKey[r.type + '-' + r.id] = r; });
                    batch.forEach(function (r) {
                        var res = byKey[r.type + '-' + r.id] || { status: 'failed', message: resp.message || 'No response' };
                        record(r, res.status, res.message);
                    });
                }).fail(function (xhr) {
                    var msg = ajaxError(xhr, 'Request failed');
                    batch.forEach(function (r) { record(r, 'failed', msg); });
                }).always(function () {
                    updateProgress();
                    next(i + BATCH);
                });
            }

            next(0);
        }

        /* Keep the person being emailed visible inside their list. */
        function scrollIntoList(r) {
            var $it  = $m.find('.dc-item[data-key="' + r.type + '-' + r.id + '"]');
            var $box = $it.closest('.dc-items');
            if (!$it.length || !$box.length) return;
            var top = $it.position().top + $box.scrollTop() - $box.height() / 2 + $it.outerHeight() / 2;
            $box.stop().animate({ scrollTop: Math.max(top, 0) }, 200);
        }

        function record(r, status, message) {
            if (status === 'sent') st.sent++;
            else if (status === 'skipped') st.skipped++;
            else { st.failed++; st.failedList.push(r); status = 'failed'; }
            setPill(r.type + '-' + r.id, status, message);
        }

        function finish() {
            var a = st.action;
            setBusy(false);
            step(2, 'done'); step(3, 'done');
            $m.removeClass('is-sending').addClass('is-finished').toggleClass('has-failures', st.failed > 0);
            $m.toggleClass('is-done', st.failed === 0).toggleClass('is-failed', st.failed > 0);
            $('#dc-head-icon').attr('class', st.failed ? 'fa fa-exclamation-triangle' : 'fa fa-check');
            $('#dc-kicker').text(a.doneKicker + (st.failed ? ' — some emails failed' : (st.sent ? ' & notified' : '')));
            updateProgress(st.total ? 'Finished' : 'Saved');

            var msg;
            if (!st.total) {
                msg = '<span style="color:#2f855a;"><i class="fa fa-check-circle"></i> ' + a.doneKicker + '. No emails were sent.</span>';
            } else if (st.failed) {
                msg = '<span style="color:#c05621;"><i class="fa fa-exclamation-triangle"></i> ' + st.sent + ' sent, ' + st.failed +
                      ' failed. The change is saved — you can retry the failed ones now.</span>';
            } else {
                msg = '<span style="color:#2f855a;"><i class="fa fa-check-circle"></i> All done — ' + st.sent + ' email' + (st.sent === 1 ? '' : 's') +
                      ' sent' + (st.skipped ? ', ' + st.skipped + ' skipped' : '') + '.</span>';
            }
            $('#dc-result-msg').html(msg);

            $('#dc-go, #dc-cancel, #dc-mark-only').addClass('dc-hidden');
            $('#dc-retry').toggleClass('dc-hidden', st.failed === 0).html('<i class="fa fa-refresh"></i> Retry failed (' + st.failed + ')');
            $('#dc-done').removeClass('dc-hidden');
            // The change is saved; the lists are now a record of who heard.
            $m.find('.dc-pick, .dc-all, #dc-remarks').prop('disabled', true);

            showToast(st.failed ? 'danger' : 'success',
                st.failed ? '<i class="fa fa-exclamation-circle"></i> &nbsp;' + a.doneKicker + '. ' + st.failed + ' email(s) failed.'
                          : '<i class="fa fa-check-circle"></i> &nbsp;' + a.doneKicker + (st.sent ? ' — ' + st.sent + ' email(s) sent.' : '.'));
        }

        function run(queue, withEmails) {
            showError('');
            setBusy(true);
            $m.addClass('is-sending').removeClass('is-finished is-done is-failed has-failures');
            $m.find('.dc-body').stop().animate({ scrollTop: 0 }, 200);

            // People left out get a muted dash, so it is clear it was on purpose.
            $m.find(withEmails ? '.dc-pick:not(:checked)' : '.dc-pick').each(function () {
                setPill($(this).data('type') + '-' + $(this).data('id'), 'excluded');
            });

            var go = function () {
                step(1, 'done');
                if (!queue.length) { finish(); return; }
                step(2, 'active');
                sendAll(queue);
            };

            if (st.saved) { go(); return; }

            step(1, 'active');
            updateProgress(escapeHtml(st.action.step1) + '…');
            st.action.save().then(function () {
                st.saved = true;
                go();
            }, function (msg) {
                setBusy(false);
                $m.removeClass('is-sending');
                step(1, '');
                showError(msg || 'Could not save the change.');
            });
        }

        /* ── events ── */
        $m.on('change', '.dc-pick', refreshCount);
        $m.on('change', '.dc-all', function () {
            $m.find('.dc-pick[data-type="' + $(this).data('type') + '"]:not(:disabled)').prop('checked', this.checked);
            refreshCount();
        });

        $('#dc-go').on('click', function () {
            if (!st || st.busy) return;
            run(selected(), true);
        });
        $('#dc-mark-only').on('click', function () {
            if (!st || st.busy) return;
            run([], false);
        });
        $('#dc-retry').on('click', function () {
            if (!st || st.busy || !st.failedList.length) return;
            var queue = st.failedList;
            st.failed = 0;
            st.failedList = [];
            $('#dc-retry, #dc-done').addClass('dc-hidden');
            run(queue, true);
        });

        $('#dc-close, #dc-cancel, #dc-done').on('click', close);
        $ov.on('mousedown', function (e) { if (e.target === this && !$m.hasClass('is-finished')) close(); });
        $(document).on('keydown', function (e) { if (e.key === 'Escape' && $ov.hasClass('open')) close(); });

        // Last line of defence while a change or an email is in flight.
        window.addEventListener('beforeunload', function (e) {
            if (st && st.busy) { e.preventDefault(); e.returnValue = ''; return ''; }
        });

        return { open: open };
    })();

});
</script>
@endsection