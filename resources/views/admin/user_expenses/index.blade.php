@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');

.exp-wrap * { font-family: 'DM Sans', sans-serif !important; box-sizing: border-box; }
.exp-wrap { padding: 24px 0 60px; }

/* ── Portlet ── */
.exp-portlet {
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.08);
    border: 1px solid #dde3ec;
}
.exp-portlet-title {
    padding: 14px 18px 0;
    border-bottom: 1px solid #eef1f7;
    display: flex; align-items: center; justify-content: space-between;
    padding-bottom: 12px;
}
.exp-portlet-title .caption {
    font-size: 14px; font-weight: 700; color: #3598dc;
    text-transform: uppercase; letter-spacing: 0.5px;
    display: flex; align-items: center; gap: 7px;
}
.exp-portlet-body { padding: 18px; }

/* ── Summary Bar ── */
.summary-bar { display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.sum-card {
    background: #f4f6fa; border: 1px solid #dde3ec; border-radius: 6px;
    padding: 8px 16px; font-size: 12px; color: #5a6a85;
    display: flex; align-items: center; gap: 6px;
}
.sum-card strong { font-size: 15px; color: #2d3748; }
.sum-card .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-total   { background: #3598dc; }
.dot-pending { background: #e53e3e; }
.dot-approved{ background: #38a169; }

/* ── Filter Strip ── */
.filter-strip {
    display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;
    background: #f4f6fa; border: 1px solid #dde3ec;
    border-radius: 6px; padding: 14px 18px; margin-bottom: 18px;
}
.filter-strip .fg { display: flex; flex-direction: column; gap: 4px; }
.filter-strip label {
    font-size: 11px; font-weight: 700; color: #5a6a85;
    text-transform: uppercase; letter-spacing: 0.5px; margin: 0;
}
.filter-strip select,
.filter-strip input[type="text"] {
    height: 34px; border: 1px solid #c8d0dc; border-radius: 4px !important;
    font-size: 13px; color: #2d3748; background: #fff; padding: 0 10px;
}
.filter-strip select:focus, .filter-strip input:focus {
    outline: none; border-color: #3598dc;
    box-shadow: 0 0 0 2px rgba(53,152,220,0.15);
}
.filter-strip select { min-width: 180px; }
.filter-strip .filter-actions { display: flex; align-items: center; gap: 6px; padding-bottom: 1px; }

.btn-fa {
    display: inline-flex; align-items: center; gap: 6px;
    height: 34px; padding: 0 16px; font-size: 12px; font-weight: 700;
    border-radius: 4px !important; border: none;
    background: #3598dc; color: #fff; cursor: pointer;
    font-family: 'DM Sans', sans-serif; transition: background .15s;
    text-transform: uppercase; letter-spacing: 0.4px;
}
.btn-fa:hover { background: #2980b9; }

.btn-fr {
    display: inline-flex; align-items: center; gap: 6px;
    height: 34px; padding: 0 14px; font-size: 12px; font-weight: 600;
    border-radius: 4px !important; border: 1px solid #c8d0dc;
    background: #fff; color: #5a6a85; cursor: pointer; text-decoration: none;
    font-family: 'DM Sans', sans-serif; transition: all .15s;
}
.btn-fr:hover { background: #f0f2f7; text-decoration: none; color: #2d3748; }

.btn-pdf {
    display: none; align-items: center; gap: 6px;
    height: 34px; padding: 0 14px; font-size: 12px; font-weight: 700;
    border-radius: 4px !important; border: 1px solid #e53e3e;
    background: #fff5f5; color: #c53030; cursor: pointer; text-decoration: none;
    font-family: 'DM Sans', sans-serif; transition: all .15s;
}
.btn-pdf:hover { background: #e53e3e; color: #fff; text-decoration: none; }
.btn-pdf.visible { display: inline-flex; }

/* ── Table ── */
.exp-table-wrap { width: 100%; overflow-x: auto; }
.exp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    table-layout: fixed;
}

/* ── Sr No column fix ── */
.exp-table col.col-srno { width: 40px; }
.exp-table thead tr th.col-srno,
.exp-table tbody tr td.col-srno {
    width: 40px !important;
    min-width: 40px !important;
    max-width: 40px !important;
    padding: 10px 4px !important;
    text-align: center !important;
    overflow: hidden;
}

.exp-table thead tr th {
    background: #eef1f7; color: #4a5568; font-weight: 700; font-size: 11px;
    text-transform: uppercase; letter-spacing: 0.55px; padding: 10px 12px;
    border: 1px solid #d5dbe8; white-space: nowrap; text-align: left;
    overflow: hidden;
}
.exp-table thead tr th.center { text-align: center; }
.exp-table tbody td {
    padding: 10px 12px; border: 1px solid #e4e9f2;
    vertical-align: middle; color: #2d3748; background: #fff;
    overflow: hidden;
}
.exp-table tbody tr:hover td { background: #f8faff; }
.exp-table tbody tr:nth-child(even) td { background: #fafbfd; }
.exp-table tbody tr:nth-child(even):hover td { background: #f0f5ff; }

/* Row status left border */
.exp-table tbody tr { position: relative; }
.exp-table tbody tr td:first-child { border-left: 3px solid #e4e9f2; }
.exp-table tbody tr.s-Approved td:first-child        { border-left-color: #38a169; }
.exp-table tbody tr.s-Partially td:first-child        { border-left-color: #f39c12; }
.exp-table tbody tr.s-Rejected td:first-child         { border-left-color: #e53e3e; }
.exp-table tbody tr.s-PendingApproval td:first-child  { border-left-color: #e53e3e; }
.exp-table tbody tr.s-Requested td:first-child        { border-left-color: #b0bcc8; }

/* ── Cell content styles ── */
.sr-no-cell { color: #a0aec0; font-size: 11px; text-align: center; }
.emp-n  { font-size: 13px; font-weight: 700; color: #1a2333; }
.emp-m  { font-size: 12px; color: #7a8a9a; margin-top: 1px; }
.cat-t  { display: inline-flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 20px; margin-top: 5px; background: #edf3fb; color: #2d6faa; border: 1px solid #cde0f4; }
.miss-t { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; background: #d32f2f; color: #fff; margin-left: 3px; }
.remarks-txt { font-size: 12px; color: #8a9ab5; font-style: italic; margin-top: 4px; }
.missed-reason-txt {
    font-size: 11px;
    color: #c47d00;
    background: #fff9ee;
    border: 1px solid #ffe0a0;
    border-radius: 5px;
    padding: 3px 8px;
    margin-top: 3px;
    display: inline-block;
    max-width: 180px;
    white-space: normal;
    word-wrap: break-word;
}

.date-main { font-size: 13px; font-weight: 700; color: #1a2333; }
.date-sub  { font-size: 11px; color: #8a9ab5; margin-top: 1px; }
.a-req  { font-size: 15px; font-weight: 800; color: #1a2333; }
.a-apr  { font-size: 15px; font-weight: 800; color: #1e9e58; }
.a-nil  { font-size: 16px; color: #dde4ee; }

/* ── Travel row — single line, km + visits inline ── */
.tr-travel-row {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px dashed #e0e8f0;
    overflow: hidden;
}
.tr-km  { font-size: 12px; font-weight: 700; color: #3a7fc1; }
.tr-rt  { font-size: 11px; color: #8a9ab5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }

.btn-view-visits {
    display: inline-flex; align-items: center; gap: 5px;
    background: #edf8f0; color: #1a9a50; border: 1px solid #b8e6c8;
    border-radius: 4px; padding: 3px 10px; font-size: 11px; font-weight: 700;
    cursor: pointer; transition: all .15s; font-family: 'DM Sans', sans-serif;
    white-space: nowrap; flex-shrink: 0;
}
.btn-view-visits:hover { background: #1a9a50; color: #fff; border-color: #1a9a50; }
.vc-badge { background: #1a9a50; color: #fff; border-radius: 8px; font-size: 10px; font-weight: 800; padding: 1px 6px; }
.btn-view-visits:hover .vc-badge { background: #fff; color: #1a9a50; }
.btn-view-visits.visits-zero { background: #feeaea; color: #c0392b; border-color: #f5c0c0; }
.btn-view-visits.visits-zero .vc-badge { background: #e74c3c; }
.btn-view-visits.visits-zero:hover { background: #c0392b; color: #fff; }
.btn-view-visits.visits-zero:hover .vc-badge { background: #fff; color: #c0392b; }

/* Receipt thumbs */
.r-thumb { width: 48px; height: 48px; border-radius: 5px; overflow: hidden; cursor: pointer; border: 1.5px solid #e4eaf3; background: #f4f7fb; display: inline-flex; align-items: center; justify-content: center; transition: border-color .15s, transform .15s; margin: 2px; position: relative; }
.r-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.r-thumb:hover { border-color: #3598dc; transform: scale(1.06); box-shadow: 0 2px 8px rgba(53,152,220,.2); }
.r-thumb.is-pdf { background: #fff5f5; border-color: #f5c0c0; }
.r-thumb.is-pdf:hover { border-color: #e53e3e; background: #feeaea; }
.r-pdf-icon { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; }
.r-pdf-icon .pi-txt { font-size: 9px; font-weight: 800; color: #c53030; font-family: 'DM Sans', sans-serif; letter-spacing: 0.3px; }
.r-none { width: 48px; height: 48px; border-radius: 5px; border: 1.5px dashed #dde4ee; display: inline-flex; align-items: center; justify-content: center; color: #c5cdd8; margin: 2px; }

/* Verify */
.v-cb { width: 16px; height: 16px; cursor: pointer; accent-color: #38a169; }
.v-lb { font-size: 11px; font-weight: 700; letter-spacing: .3px; display: block; margin-top: 2px; }
.v-lb.on  { color: #38a169; }
.v-lb.off { color: #c5cdd8; }
.btn-int-remarks {
    background: none; border: 1px solid #dde4ee; border-radius: 4px;
    padding: 3px 7px; cursor: pointer; font-size: 11px; color: #8a9ab5;
    display: inline-flex; align-items: center; gap: 3px; margin-top: 5px;
    transition: all .13s; font-family: 'DM Sans', sans-serif;
}
.btn-int-remarks:hover { border-color: #3598dc; color: #2d6faa; background: #edf3fb; }
.btn-int-remarks.has-remark { border-color: #f39c12; color: #c47d00; background: #fff9ee; }

/* Query button */
.btn-query {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    background: #f0f4ff; color: #3d5a9a; border: 1px solid #c8d4f0;
    border-radius: 5px; padding: 6px 10px; font-size: 12px; font-weight: 700;
    cursor: pointer; transition: all .15s; font-family: 'DM Sans', sans-serif; min-width: 50px;
}
.btn-query:hover { background: #3d5a9a; color: #fff; border-color: #3d5a9a; }
.q-count-pill { background: #3d5a9a; color: #fff; border-radius: 8px; font-size: 10px; font-weight: 800; padding: 1px 6px; min-width: 18px; text-align: center; }
.btn-query:hover .q-count-pill { background: #fff; color: #3d5a9a; }
.btn-query.has-unread { background: #fff0f0; color: #c0392b; border-color: #f0b0b0; }
.btn-query.has-unread:hover { background: #c0392b; color: #fff; border-color: #c0392b; }
.btn-query.has-unread .q-count-pill { background: #e74c3c; }
.btn-query.has-unread:hover .q-count-pill { background: #fff; color: #c0392b; }
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.25;} }
.q-unread-dot { width: 7px; height: 7px; border-radius: 50%; background: #e74c3c; flex-shrink: 0; animation: blink 1.1s infinite; }

/* Status badge */
.s-badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 10px; white-space: nowrap; }
.sb-Requested       { background: #f0f3f8; color: #7a8a9a; }
.sb-Approved        { background: #c6f6d5; color: #276749; }
.sb-Partially       { background: #fef3cd; color: #92640a; }
.sb-Rejected        { background: #fed7d7; color: #9b2c2c; }
.sb-PendingApproval { background: #fed7d7; color: #9b2c2c; }
.appr-remarks-txt { font-size: 11px; color: #8a9ab5; font-style: italic; margin-top: 3px; }

/* Update button */
.btn-upd {
    display: inline-flex; align-items: center; gap: 4px;
    background: #f0f4ff; color: #3598dc; border: 1px solid #c8d4f0;
    border-radius: 4px; padding: 4px 10px; font-size: 11px; font-weight: 700;
    cursor: pointer; transition: all .15s; font-family: 'DM Sans', sans-serif;
    text-transform: uppercase; letter-spacing: 0.3px;
}
.btn-upd:hover { background: #3598dc; color: #fff; border-color: #3598dc; }

/* Unverified placeholder in actions col */
.upd-locked {
    font-size: 11px;
    color: #d0d8e8;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Empty */
.exp-empty { text-align: center; padding: 50px 20px; color: #b0bcc8; }
.exp-empty p { font-size: 14px; margin-top: 8px; }

/* Pagination */
.exp-pager { padding: 12px 18px; background: #f7f9fc; border-top: 1px solid #edf1f8; overflow: hidden; }
.exp-pager .pi { float: left; line-height: 36px; font-size: 12px; color: #a0aab8; font-weight: 500; }
.exp-pager .pagination { float: right; margin: 0; }
.exp-pager .pagination > li > a,
.exp-pager .pagination > li > span { border-radius: 4px !important; margin: 0 2px; border-color: #dde4ee; color: #3598dc; font-size: 12px; padding: 5px 11px; font-weight: 600; font-family: 'DM Sans', sans-serif; }
.exp-pager .pagination > .active > a,
.exp-pager .pagination > .active > span { background: #3598dc !important; border-color: #3598dc !important; color: #fff !important; }

/* Lightbox */
.exp-lb { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(8,14,28,.95); z-index: 999999; align-items: center; justify-content: center; }
.exp-lb.on { display: flex; }
.exp-lb img { max-width: 90%; max-height: 84vh; border-radius: 10px; box-shadow: 0 20px 60px rgba(0,0,0,.6); cursor: default; }
.exp-lbx { position: fixed; top: 18px; right: 22px; color: rgba(255,255,255,.8); font-size: 32px; cursor: pointer; line-height: 1; z-index: 1000000; background: rgba(255,255,255,.1); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all .18s; }
.exp-lbx:hover { color: #fff; background: rgba(255,255,255,.22); transform: rotate(90deg); }
.exp-lb-newtab { position: fixed; top: 18px; right: 70px; z-index: 1000000; background: rgba(255,255,255,.1); color: rgba(255,255,255,.8); border: 1px solid rgba(255,255,255,.2); border-radius: 6px; padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 5px; transition: all .18s; font-family: 'DM Sans', sans-serif; }

/* Modals */
.modal-content { border-radius: 8px !important; border: none !important; box-shadow: 0 20px 60px rgba(10,20,50,.22) !important; overflow: hidden; }
.mod-hdr-blue   { background: linear-gradient(135deg,#2d6faa,#1a4e80); padding: 16px 20px; border: none; }
.mod-hdr-indigo { background: linear-gradient(135deg,#3d5a9a,#253870); padding: 16px 20px; border: none; }
.mod-hdr-orange { background: linear-gradient(135deg,#f39c12,#d68910); padding: 16px 20px; border: none; }
.mod-hdr-green  { background: linear-gradient(135deg,#27ae60,#1a7a45); padding: 16px 20px; border: none; }
.mod-title { color: #fff; font-size: 14px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 7px; }
.mod-close { color: rgba(255,255,255,.9) !important; opacity: 1 !important; font-size: 24px !important; line-height: 1 !important; padding: 0 !important; }
.modal-footer-plain { padding: 12px 20px; background: #f7f9fc; border-top: 1px solid #edf1f8; display: flex; align-items: center; justify-content: flex-end; gap: 8px; }

.m-amt { background: #edf3fb; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
.m-amt .ml { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #7a9ab5; }
.m-amt .mv { font-size: 22px; font-weight: 800; color: #2d6faa; margin-top: 3px; }
.m-slbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #8a9ab5; margin-bottom: 9px; }
.m-sgrid { display: grid; grid-template-columns: repeat(3,1fr); gap: 9px; margin-bottom: 14px; }
.m-sopt { border: 2px solid #e8edf5; border-radius: 8px; padding: 12px 6px 10px; text-align: center; cursor: pointer; transition: all .16s; background: #fafbfc; }
.m-sopt .msicon { font-size: 26px; display: block; margin-bottom: 5px; line-height: 1; }
.m-sopt .msname { font-size: 11px; font-weight: 700; color: #6a7a8a; }
.m-sopt:hover { border-color: #3598dc; background: #f0f6fc; }
.m-sopt.sa { border-color: #38a169; background: #e8faf1; } .m-sopt.sa .msname { color: #276749; }
.m-sopt.sp { border-color: #f39c12; background: #fff6e4; } .m-sopt.sp .msname { color: #a06800; }
.m-sopt.sr { border-color: #e53e3e; background: #feeaea; } .m-sopt.sr .msname { color: #9b2c2c; }
.m-pbox { background: #fffcf0; border: 1.5px dashed #f39c12; border-radius: 7px; padding: 12px 14px; margin-bottom: 12px; }
.m-pbox label { font-size: 11px; font-weight: 700; color: #a06800; display: block; margin-bottom: 6px; }
.m-pbox .form-control { border-radius: 5px; border: 1.5px solid #f39c12; font-size: 16px; font-weight: 700; color: #a06800; box-shadow: none; }
.m-pbox .ph { font-size: 11px; color: #c47d00; margin-top: 4px; display: block; }
.m-remarks-box { margin-top: 12px; }
.m-remarks-box label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #8a9ab5; display: block; margin-bottom: 6px; }
.m-remarks-box textarea { width: 100%; border: 1.5px solid #e4eaf3; border-radius: 6px; padding: 9px 12px; font-size: 13px; color: #2d3748; font-family: 'DM Sans', sans-serif; resize: none; outline: none; background: #fafbfc; transition: border-color .18s; }
.m-remarks-box textarea:focus { border-color: #3598dc; background: #fff; box-shadow: 0 0 0 2px rgba(53,152,220,.1); }
.m-err { background: #feeaea; border: none; border-left: 3px solid #e53e3e; border-radius: 5px; padding: 9px 12px; font-size: 12px; font-weight: 600; color: #9b2c2c; }
.btn-msave { display: inline-flex; align-items: center; gap: 6px; background: #3598dc; color: #fff; border: none; border-radius: 5px; padding: 9px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: opacity .18s; }
.btn-msave:hover { opacity: .88; } .btn-msave:disabled { opacity: .5; cursor: not-allowed; }
.btn-mcancel { display: inline-flex; align-items: center; gap: 5px; background: #f0f3f8; color: #6a7a8a; border: 1.5px solid #dde4ee; border-radius: 5px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; }

/* Query chat */
.q-chat-box { height: 280px; overflow-y: auto; padding: 14px 18px; background: #f7f9fc; border-bottom: 1px solid #e8edf5; }
.q-chat-box::-webkit-scrollbar { width: 4px; } .q-chat-box::-webkit-scrollbar-thumb { background: #d0d8e8; border-radius: 4px; }
.q-bubble { display: block; max-width: 80%; margin-bottom: 10px; }
.q-bubble.admin    { margin-left: 20%; text-align: right; }
.q-bubble.employee { margin-right: 20%; }
.q-binner { padding: 9px 13px; border-radius: 10px; font-size: 13px; line-height: 1.5; word-break: break-word; }
.q-bubble.admin .q-binner    { background: linear-gradient(135deg,#3598dc,#2d6faa); color: #fff; border-radius: 10px 10px 2px 10px; }
.q-bubble.employee .q-binner { background: #fff; color: #2d3748; border: 1px solid #e4eaf3; border-radius: 10px 10px 10px 2px; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
.q-meta { font-size: 11px; color: #a0aab8; margin-top: 3px; }
.q-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #b0bcc8; font-size: 13px; font-weight: 500; gap: 8px; }
.q-reply { padding: 12px 18px 6px; background: #fff; }
.q-reply label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #8a9ab5; display: block; margin-bottom: 6px; }
.q-reply textarea { width: 100%; border: 1.5px solid #e4eaf3; border-radius: 6px; padding: 9px 12px; font-size: 13px; color: #2d3748; font-family: 'DM Sans', sans-serif; resize: none; outline: none; background: #fafbfc; transition: border-color .18s; }
.q-reply textarea:focus { border-color: #3598dc; background: #fff; box-shadow: 0 0 0 2px rgba(53,152,220,.1); }
.btn-qsend { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg,#3d5a9a,#253870); color: #fff; border: none; border-radius: 5px; padding: 9px 20px; font-size: 13px; font-weight: 700; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: opacity .18s; }
.btn-qsend:hover { opacity: .88; } .btn-qsend:disabled { opacity: .5; cursor: not-allowed; }
.btn-qclose { display: inline-flex; align-items: center; gap: 5px; background: #f0f3f8; color: #6a7a8a; border: 1.5px solid #dde4ee; border-radius: 5px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; }
.q-loading { display: flex; align-items: center; justify-content: center; height: 100%; color: #b0bcc8; gap: 8px; font-size: 13px; }

/* Internal remarks */
.ir-current-box { background: #fff9ee; border-radius: 7px; padding: 12px; border-left: 3px solid #f39c12; margin-bottom: 14px; }
.ir-current-box .ir-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #f39c12; margin-bottom: 5px; }
.ir-current-box .ir-text  { font-size: 13px; color: #2d3748; line-height: 1.5; }
.ir-by { font-size: 11px; color: #a0aab8; margin-top: 6px; }

/* Visits table */
.visits-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.visits-table th { background: #eef1f7; padding: 9px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #8a9ab5; border-bottom: 1.5px solid #e4eaf3; text-align: left; }
.visits-table td { padding: 10px 12px; border-bottom: 1px solid #edf1f8; color: #2d3748; vertical-align: top; }
.visits-table tr:last-child td { border-bottom: none; }
.visits-table tr:hover td { background: #f8fafd; }
.vt-customer { font-weight: 700; color: #1a2333; font-size: 13px; }
.vt-sub      { font-size: 11px; color: #8a9ab5; margin-top: 1px; }
.vt-purpose  { display: inline-block; background: #edf3fb; color: #2d6faa; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px; }
.vt-time     { font-weight: 600; color: #2d3748; font-size: 12px; }
.vt-loc      { font-size: 11px; color: #6a7a8a; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.visits-empty { text-align: center; padding: 40px 20px; color: #b0bcc8; }
.visits-count-badge { display: inline-block; background: rgba(255,255,255,.22); color: #fff; font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 10px; margin-left: 8px; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin-ico { animation: spin .8s linear infinite; display: inline-block; }
.exp-table col.col-srno {
    width: 28px !important;
    min-width: 28px !important;
    max-width: 28px !important;
}
.exp-table.table > thead > tr > th.col-srno,
.exp-table.table > tbody > tr > td.col-srno {
    width: 28px !important;
    min-width: 28px !important;
    max-width: 28px !important;
    padding: 10px 2px !important;
    text-align: center !important;
    overflow: hidden !important;
    white-space: nowrap !important;
}
</style>

{{-- Inline SVG symbols --}}
<svg style="display:none" xmlns="http://www.w3.org/2000/svg">
  <symbol id="ico-card"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></symbol>
  <symbol id="ico-list"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></symbol>
  <symbol id="ico-eye"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></symbol>
  <symbol id="ico-user"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></symbol>
  <symbol id="ico-cal"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></symbol>
  <symbol id="ico-filter"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></symbol>
  <symbol id="ico-check"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></symbol>
  <symbol id="ico-shield"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></symbol>
  <symbol id="ico-msg"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></symbol>
  <symbol id="ico-flag"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></symbol>
  <symbol id="ico-cog"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></symbol>
  <symbol id="ico-phone"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.5 2 2 0 0 1 3.6 1.32h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></symbol>
  <symbol id="ico-tag"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></symbol>
  <symbol id="ico-warn"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></symbol>
  <symbol id="ico-comment"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></symbol>
  <symbol id="ico-road"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="21" x2="21" y2="21"/><line x1="3" y1="7" x2="21" y2="7"/><polyline points="8 21 8 7 16 3 16 21"/></symbol>
  <symbol id="ico-map"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></symbol>
  <symbol id="ico-zoom"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></symbol>
  <symbol id="ico-img"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></symbol>
  <symbol id="ico-pencil"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></symbol>
  <symbol id="ico-save"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></symbol>
  <symbol id="ico-send"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></symbol>
  <symbol id="ico-x"         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></symbol>
  <symbol id="ico-xcirc"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></symbol>
  <symbol id="ico-checkcirc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></symbol>
  <symbol id="ico-half"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20V2z" fill="currentColor" stroke="none"/></symbol>
  <symbol id="ico-money"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></symbol>
  <symbol id="ico-reply"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></symbol>
  <symbol id="ico-lock"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></symbol>
  <symbol id="ico-inbox"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></symbol>
  <symbol id="ico-reset"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></symbol>
  <symbol id="ico-pdf"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/><line x1="9" y1="11" x2="15" y2="11"/></symbol>
  <symbol id="ico-spin"      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2a10 10 0 0 1 10 10" opacity=".9"/><path d="M12 2a10 10 0 0 0-10 10" opacity=".25"/><path d="M2 12a10 10 0 0 0 10 10" opacity=".5"/><path d="M22 12a10 10 0 0 1-10 10" opacity=".1"/></symbol>
</svg>

@php
function svgico($id, $size=14, $extra='') {
    return '<span style="display:inline-block;vertical-align:middle;flex-shrink:0;"><svg width="'.$size.'" height="'.$size.'" '.$extra.'><use href="#ico-'.$id.'"/></svg></span>';
}
$currentMonth = date('n');
$currentYear  = date('Y');
@endphp

<div class="page-content-wrapper">
<div class="page-content exp-wrap">

    <ul class="page-breadcrumb breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
        <li><span>User Expenses</span></li>
    </ul>

    <div class="exp-portlet">
        <div class="exp-portlet-title">
            <div class="caption">
                {!! svgico('card',14,'style="color:#3598dc"') !!}
                <span>Expense Management</span>
            </div>
            <button type="button" id="btnOpenCatSummary" style="
                display:inline-flex; align-items:center; gap:6px;
                height:34px; padding:0 14px; font-size:12px; font-weight:700;
                border-radius:4px; border:1px solid #c8d0dc;
                background:#fff; color:#334155; cursor:pointer;
                font-family:'DM Sans',sans-serif; transition:all .15s;
                text-transform:uppercase; letter-spacing:0.4px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="9" y1="15" x2="15" y2="15"/>
                    <line x1="9" y1="11" x2="15" y2="11"/>
                </svg>
                Category Summary
            </button>
        </div>

        <div class="exp-portlet-body">

            {{-- ══ SUMMARY BAR ══ --}}
            <div class="summary-bar">
                <div class="sum-card">
                    <span class="dot dot-total"></span>
                    Total Records &nbsp;<strong>{{ $expenses->total() }}</strong>
                </div>
                <div class="sum-card">
                    <span class="dot" style="background:#38a169;"></span>
                    Showing &nbsp;<strong>{{ $expenses->firstItem() ?? 0 }}–{{ $expenses->lastItem() ?? 0 }}</strong>
                </div>
                @if(request('status'))
                <div class="sum-card">
                    <span class="dot" style="background:#f39c12;"></span>
                    Status: &nbsp;<strong style="font-size:13px;">{{ request('status') }}</strong>
                </div>
                @endif
                @if(request('verified'))
                <div class="sum-card">
                    <span class="dot" style="background:{{ request('verified')==='yes' ? '#38a169' : '#b0bcc8' }};"></span>
                    Verification: &nbsp;<strong style="font-size:13px;">{{ request('verified')==='yes' ? 'Verified Only' : 'Not Verified' }}</strong>
                </div>
                @endif
            </div>

            {{-- ══ FILTER STRIP ══ --}}
            <form method="GET" action="{{ url('admin/user-expenses') }}" id="filterForm">
            <div class="filter-strip">

                {{-- Employee --}}
                <div class="fg">
                    <label>{!! svgico('user',10) !!} &nbsp;Employee</label>
                    <select name="employee_id" id="filterEmployee" class="select2" style="min-width:220px;">
                        <option value="">— All Employees —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}{{ $emp->mobile ? ' ('.$emp->mobile.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Month --}}
                <div class="fg">
                    <label>{!! svgico('cal',10) !!} &nbsp;Month</label>
                    <select name="month" id="filterMonth" style="min-width:80px;">
                        <option value="">All</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}"
                                {{ (request()->has('month') ? request('month') == $m : $currentMonth == $m) ? 'selected' : '' }}>
                                {{ date('M', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Year --}}
                <div class="fg">
                    <label>{!! svgico('cal',10) !!} &nbsp;Year</label>
                    <select name="year" id="filterYear" style="min-width:80px;">
                        <option value="">All</option>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}"
                                {{ (request()->has('year') ? request('year') == $yr : $currentYear == $yr) ? 'selected' : '' }}>
                                {{ $yr }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="fg">
                    <label>{!! svgico('filter',10) !!} &nbsp;Status</label>
                    <select name="status" id="filterStatus" style="min-width:160px;">
                        <option value="">— All Statuses —</option>
                        <option value="Pending Approval"   {{ request('status')==='Pending Approval'   ? 'selected':'' }}>Pending Approval</option>
                        <option value="Approved"           {{ request('status')==='Approved'           ? 'selected':'' }}>Approved</option>
                        <option value="Partially Approved" {{ request('status')==='Partially Approved' ? 'selected':'' }}>Partially Approved</option>
                        <option value="Rejected"           {{ request('status')==='Rejected'           ? 'selected':'' }}>Rejected</option>
                    </select>
                </div>

                {{-- Verified --}}
                <div class="fg">
                    <label>{!! svgico('shield',10) !!} &nbsp;Verified</label>
                    <select name="verified" id="filterVerified" style="min-width:130px;">
                        <option value="">— All —</option>
                        <option value="yes" {{ request('verified')==='yes' ? 'selected':'' }}>Verified</option>
                        <option value="no"  {{ request('verified')==='no'  ? 'selected':'' }}>Not Verified</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="fg">
                    <label>&nbsp;</label>
                    <div class="filter-actions">
                        <button type="submit" class="btn-fa">
                            {!! svgico('filter',12,'style="color:#fff"') !!} Apply
                        </button>
                        <a href="{{ url('admin/user-expenses') }}" class="btn-fr">
                            {!! svgico('reset',12) !!} Reset
                        </a>
                        <a id="btnExportPdf" href="#" class="btn-pdf" target="_blank" title="Export to PDF">
                            {!! svgico('pdf',12,'style="color:currentColor"') !!} PDF
                        </a>
                    </div>
                </div>

            </div>
            </form>

            {{-- ══ TABLE ══ --}}
            <div class="exp-table-wrap">
                <table class="exp-table">
                    <colgroup>
                        <col class="col-srno" style="width:28px;min-width:28px;max-width:28px;">
                        <col style="width:17%;">
                        <col style="width:16%;">
                        <col style="width:10%;">
                        <col style="width:9%;">
                        <col style="width:9%;">
                        <col style="width:9%;">
                        <col style="width:13%;">
                        <col style="width:10%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="center col-srno" style="width:28px !important; min-width:28px !important; max-width:28px !important; padding:10px 2px !important;">#</th>
                            <th style="width:16%;">{!! svgico('user',11) !!} Employee</th>
                            <th style="width:15%;">{!! svgico('cal',11) !!} Date &amp; Amount</th>
                            <th class="center" style="width:10%;">{!! svgico('check',11) !!} Approved</th>
                            <th class="center" style="width:9%;">{!! svgico('img',11) !!} Bill</th>
                            <th class="center" style="width:9%;">{!! svgico('shield',11) !!} Verified</th>
                            <th class="center" style="width:8%;">{!! svgico('msg',11) !!} Query</th>
                            <th class="center" style="width:12%;">{!! svgico('flag',11) !!} Status</th>
                            <th class="center" style="width:9%;">{!! svgico('cog',11) !!} Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    @forelse($expenses as $index => $expense)
                    @php
                        $sk = str_replace(' ', '', $expense->status);
                        $sk = ($sk === 'PartiallyApproved') ? 'Partially' : $sk;
                        $receiptPath    = !empty($expense->image)             ? asset('ExpenseReceipts/'.$expense->user_id.'/'.$expense->image)             : null;
                        $altReceiptPath = !empty($expense->alternative_image) ? asset('ExpenseReceipts/'.$expense->user_id.'/'.$expense->alternative_image) : null;
                        $qCount  = $queryCounts[$expense->id]       ?? 0;
                        $unread  = $unreadQueryCounts[$expense->id] ?? 0;
                        $key     = $expense->user_id . '_' . $expense->expense_date;
                        $vCount  = $visitCounts[$key] ?? 0;
                        $expDate = \Carbon\Carbon::parse($expense->expense_date);
                        $isVerified = !empty($expense->verified_by);
                    @endphp
                    <tr class="s-{{ $sk }}" id="row-{{ $expense->id }}">

                        {{-- # --}}
                        <td class="sr-no-cell col-srno">{{ (($expenses->currentPage() - 1) * $expenses->perPage()) + $loop->iteration }}</td>

                        {{-- Employee --}}
                        <td>
                            <div class="emp-n">{{ $expense->employee_name ?? 'N/A' }}</div>
                            @if(!empty($expense->employee_mobile))
                                <div class="emp-m">{!! svgico('phone',10,'style="color:#b0bcc8"') !!} {{ $expense->employee_mobile }}</div>
                            @endif
                            <div style="margin-top:4px;">
                                <span class="cat-t">{!! svgico('tag',9,'style="color:#3598dc"') !!} {{ $expense->category_name }}</span>
                                @if($expense->missed_entry)
                                    <span class="miss-t">{!! svgico('warn',9,'style="color:#fff"') !!} Missed</span>
                                @endif
                            </div>
                            @if($expense->missed_entry && !empty($expense->missed_entry_reason))
                                <div style="margin-top:3px;">
                                    <span class="missed-reason-txt" title="{{ $expense->missed_entry_reason }}">{{ $expense->missed_entry_reason }}</span>
                                </div>
                            @endif
                            @if(!empty($expense->remarks))
                                <div class="remarks-txt" title="{{ $expense->remarks }}">{!! svgico('comment',10,'style="color:#b0bcc8"') !!} {{ $expense->remarks }}</div>
                            @endif
                        </td>

                        {{-- Date & Amount --}}
                        <td>
                            <div class="date-main">{{ $expDate->format('d M Y') }} ({{ $expDate->format('D') }})</div>
                            <div class="date-sub">{{ \Carbon\Carbon::parse($expense->created_at)->format('d M, h:i A') }}</div>
                            <div style="margin-top:5px;"><span class="a-req">Rs. {{ number_format($expense->requested_amount, 2) }}</span></div>
                            @if($expense->is_travel && !empty($expense->travel_km))
                            <div class="tr-travel-row">
                                <span class="tr-km">{!! svgico('road',11,'style="color:#3598dc"') !!} {{ $expense->travel_km }} km</span>
                                @if($expense->is_intercity && !empty($expense->intercity_route))
                                    <span class="tr-rt">{!! svgico('map',9,'style="color:#b0bcc8"') !!} {{ $expense->intercity_route }}</span>
                                @endif
                                <button class="btn-view-visits {{ $vCount == 0 ? 'visits-zero' : '' }}"
                                    data-user-id="{{ $expense->user_id }}"
                                    data-date="{{ $expense->expense_date }}"
                                    data-employee="{{ $expense->employee_name ?? 'Employee' }}">
                                    {!! svgico('map',11,'style="color:currentColor"') !!} Visits
                                    <span class="vc-badge">{{ $vCount }}</span>
                                </button>
                            </div>
                            @endif
                        </td>

                        {{-- Approved Amount --}}
                        <td style="text-align:right;" id="appr-{{ $expense->id }}">
                            @if($expense->approved_amount > 0)
                                <span class="a-apr">Rs. {{ number_format($expense->approved_amount, 2) }}</span>
                            @else
                                <span class="a-nil">&mdash;</span>
                            @endif
                        </td>

                        {{-- Bills --}}
                        @php
                            $isPdf1 = $receiptPath    && strtolower(pathinfo($expense->image ?? '', PATHINFO_EXTENSION)) === 'pdf';
                            $isPdf2 = $altReceiptPath && strtolower(pathinfo($expense->alternative_image ?? '', PATHINFO_EXTENSION)) === 'pdf';
                        @endphp
                        <td style="text-align:center; padding:8px 6px;">
                            @if($receiptPath)
                                @if($isPdf1)
                                    <a href="{{ $receiptPath }}" target="_blank" title="View Bill (PDF)" class="r-thumb is-pdf" style="text-decoration:none;">
                                        <div class="r-pdf-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c53030" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/><line x1="9" y1="11" x2="15" y2="11"/></svg>
                                            <span class="pi-txt">PDF</span>
                                        </div>
                                    </a>
                                @else
                                    <div class="r-thumb" onclick="openLb('{{ $receiptPath }}')" title="View Bill">
                                        <img src="{{ $receiptPath }}" alt="Bill" loading="lazy"
                                             onerror="this.style.display='none';this.parentNode.innerHTML='<svg width=\'18\' height=\'18\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#c5cdd8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                                    </div>
                                @endif
                            @else
                                <div class="r-none" title="No receipt">{!! svgico('img',16,'style="color:#c5cdd8"') !!}</div>
                            @endif
                            @if($altReceiptPath)
                                @if($isPdf2)
                                    <a href="{{ $altReceiptPath }}" target="_blank" title="View Alt Bill (PDF)" class="r-thumb is-pdf" style="text-decoration:none;">
                                        <div class="r-pdf-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c53030" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/><line x1="9" y1="11" x2="15" y2="11"/></svg>
                                            <span class="pi-txt">PDF</span>
                                        </div>
                                    </a>
                                @else
                                    <div class="r-thumb" onclick="openLb('{{ $altReceiptPath }}')" title="View Alt Bill">
                                        <img src="{{ $altReceiptPath }}" alt="Alt Bill" loading="lazy"
                                             onerror="this.style.display='none';this.parentNode.innerHTML='<svg width=\'18\' height=\'18\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#c5cdd8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
                                    </div>
                                @endif
                            @else
                                <div class="r-none" title="No alt bill" style="opacity:.3;">{!! svgico('img',16,'style="color:#c5cdd8"') !!}</div>
                            @endif
                        </td>

                        {{-- Verified --}}
                        <td style="text-align:center;">
                            <input type="checkbox" class="v-cb verify-checkbox" data-id="{{ $expense->id }}"
                                {{ $isVerified ? 'checked' : '' }}>
                            <span class="v-lb {{ $isVerified ? 'on' : 'off' }}" id="vlbl-{{ $expense->id }}">
                                {{ $isVerified ? 'YES' : 'NO' }}
                            </span>
                            <br>
                            <button class="btn-int-remarks btn-open-int-remarks {{ !empty($expense->internal_remarks) ? 'has-remark' : '' }}"
                                id="irbtn-{{ $expense->id }}"
                                data-id="{{ $expense->id }}"
                                data-remarks="{{ addslashes($expense->internal_remarks ?? '') }}"
                                data-verified-by="{{ addslashes($expense->verified_by_name ?? '') }}">
                                {!! svgico('pencil',10) !!} Note
                            </button>
                        </td>

                        {{-- Query --}}
                        <td style="text-align:center;">
                            <button class="btn-query btn-open-query {{ $unread>0 ? 'has-unread' : '' }}"
                                data-id="{{ $expense->id }}"
                                data-employee="{{ $expense->employee_name ?? 'Employee' }}"
                                id="qbtn-{{ $expense->id }}">
                                @if($unread>0)<span class="q-unread-dot"></span>@endif
                                {!! svgico('msg',13,'style="color:currentColor"') !!}
                                <span class="q-count-pill" id="qcount-{{ $expense->id }}"
                                    style="{{ ($unread==0 && $qCount==0) ? 'display:none;' : '' }}">
                                    {{ $unread>0 ? ($unread>99?'99+':$unread) : ($qCount>99?'99+':$qCount) }}
                                </span>
                            </button>
                        </td>

                        {{-- Status --}}
                        <td style="text-align:center;" id="sc-{{ $expense->id }}">
                            <span class="s-badge sb-{{ $sk }}" id="sbadge-{{ $expense->id }}">{{ $expense->status }}</span>
                            @if(!empty($expense->admin_remarks))
                                <div class="appr-remarks-txt" id="appr-rmk-{{ $expense->id }}" title="{{ $expense->admin_remarks }}">
                                    {{ \Illuminate\Support\Str::limit($expense->admin_remarks, 28) }}
                                </div>
                            @else
                                <div class="appr-remarks-txt" id="appr-rmk-{{ $expense->id }}" style="display:none;"></div>
                            @endif
                        </td>

                        {{-- Actions: only show Update if verified --}}
                        <td style="text-align:center;" class="actions-cell" id="act-{{ $expense->id }}">
                            @if($isVerified)
                            <button class="btn-upd btn-update-status"
                                data-id="{{ $expense->id }}"
                                data-status="{{ $expense->status }}"
                                data-requested="{{ $expense->requested_amount }}"
                                data-approved="{{ $expense->approved_amount }}"
                                data-admin-remarks="{{ $expense->admin_remarks ?? '' }}">
                                {!! svgico('pencil',11,'style="color:currentColor"') !!} Update
                            </button>
                            @else
                            <span class="upd-locked">{!! svgico('lock',11,'style="color:#d0d8e8"') !!}</span>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="exp-empty">
                            {!! svgico('inbox',36,'style="color:#c5cdd8;display:block;margin:0 auto 8px"') !!}
                            <p>No expense records found.</p>
                        </td>
                    </tr>
                    @endforelse

                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
            <div class="exp-pager">
                <span class="pi">Page {{ $expenses->currentPage() }} of {{ $expenses->lastPage() }}</span>
                {{ $expenses->links() }}
            </div>
            @endif

        </div>{{-- /portlet-body --}}
    </div>{{-- /portlet --}}

</div>
</div>

{{-- ══ LIGHTBOX ══ --}}
<div class="exp-lb" id="expLb" onclick="closeLb()">
    <div class="exp-lbx" onclick="closeLb(event)">&times;</div>
    <a id="lbNewTab" href="#" target="_blank" class="exp-lb-newtab" onclick="event.stopPropagation()">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Open in New Tab
    </a>
    <img src="" id="lbImg" alt="Receipt" onclick="event.stopPropagation()">
</div>

{{-- ══ INTERNAL REMARKS MODAL ══ --}}
<div class="modal fade" id="internalRemarksModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:420px;max-width:95vw;margin:80px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-orange">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title">{!! svgico('pencil',14,'style="color:#fff"') !!} Internal Remarks</h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <input type="hidden" id="ir_expense_id">
                <div class="ir-current-box" id="ir_current_wrap" style="display:none;">
                    <div class="ir-label">{!! svgico('lock',11,'style="color:#f39c12"') !!} Current Note</div>
                    <div class="ir-text" id="ir_current_text"></div>
                    <div class="ir-by" id="ir_current_by"></div>
                </div>
                <div class="m-remarks-box">
                    <label>Edit / Add Internal Remark <span style="color:#aab;">(admin only)</span></label>
                    <textarea id="ir_textarea" rows="4" placeholder="e.g. Bill checked and confirmed..."></textarea>
                </div>
                <div id="ir_err" class="m-err" style="display:none;margin-top:10px;"></div>
            </div>
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnSaveInternalRemarks" class="btn-msave" style="background:#f39c12;">
                    {!! svgico('save',13,'style="color:#fff"') !!} Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ STATUS MODAL ══ --}}
<div class="modal fade" id="stModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:440px;max-width:95vw;margin:55px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-blue">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title">{!! svgico('pencil',14,'style="color:#fff"') !!} Update Expense Status</h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <input type="hidden" id="m_id">
                <div class="m-amt">
                    <div>
                        <div class="ml">Requested Amount</div>
                        <div class="mv" id="m_req">—</div>
                    </div>
                    {!! svgico('money',28,'style="color:#3598dc;opacity:.2"') !!}
                </div>
                <div class="m-slbl">Select New Status</div>
                <input type="hidden" id="m_status">
                <div class="m-sgrid">
                    <div class="m-sopt" data-v="Approved" onclick="pickSt('Approved')">
                        <span class="msicon">{!! svgico('checkcirc',24,'style="color:#38a169"') !!}</span>
                        <span class="msname">Approved</span>
                    </div>
                    <div class="m-sopt" data-v="Partially Approved" onclick="pickSt('Partially Approved')">
                        <span class="msicon">{!! svgico('half',24,'style="color:#f39c12"') !!}</span>
                        <span class="msname">Partial</span>
                    </div>
                    <div class="m-sopt" data-v="Rejected" onclick="pickSt('Rejected')">
                        <span class="msicon">{!! svgico('xcirc',24,'style="color:#e53e3e"') !!}</span>
                        <span class="msname">Rejected</span>
                    </div>
                </div>
                <div class="m-pbox" id="m_partial" style="display:none;">
                    <label>Amount to Approve <span style="color:#e53e3e;">*</span></label>
                    <input type="number" id="m_appr" class="form-control" min="0" step="0.01" placeholder="0.00">
                    <span class="ph">Must not exceed requested amount.</span>
                </div>
                <div class="m-remarks-box">
                    <label>Admin Remarks <span style="color:#aab;">(optional)</span></label>
                    <textarea id="m_admin_remarks" rows="2" placeholder="Add a note for this decision..."></textarea>
                </div>
                <div id="m_err" class="m-err" style="display:none;margin-top:10px;"></div>
            </div>
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnSave" class="btn-msave">{!! svgico('save',13,'style="color:#fff"') !!} Save</button>
            </div>
        </div>
    </div>
</div>

{{-- ══ QUERY MODAL ══ --}}
<div class="modal fade" id="queryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:530px;max-width:96vw;margin:50px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-indigo">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title">
                    {!! svgico('msg',14,'style="color:#fff"') !!}
                    Queries — <span id="q_employee_name"></span>
                    <small style="font-size:11px;opacity:.7;margin-left:6px;">(#<span id="q_expense_id_label"></span>)</small>
                </h4>
            </div>
            <div class="modal-body" style="padding:0;">
                <input type="hidden" id="q_expense_id">
                <div class="q-chat-box" id="qChatBox">
                    <div class="q-loading"><span class="spin-ico">{!! svgico('spin',24,'style="color:#b0bcc8"') !!}</span> Loading...</div>
                </div>
                <div class="q-reply">
                    <label>{!! svgico('reply',11,'style="color:#8a9ab5"') !!} &nbsp;Write a Query / Reply</label>
                    <textarea id="q_message" rows="3" placeholder="Type your query or reply... (Ctrl+Enter to send)"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="padding:10px 18px 14px;background:#fff;border-top:1px solid #edf1f8;display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                <button type="button" class="btn-qclose" data-dismiss="modal">Close</button>
                <button type="button" id="btnSendQuery" class="btn-qsend">{!! svgico('send',13,'style="color:#fff"') !!} Send</button>
            </div>
        </div>
    </div>
</div>

{{-- ══ VISITS MODAL ══ --}}
<div class="modal fade" id="visitsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width:760px;max-width:96vw;margin:50px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-green">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title">
                    {!! svgico('map',14,'style="color:#fff"') !!}
                    Visits — <span id="v_employee_name"></span>
                    <small style="font-size:11px;opacity:.7;margin-left:6px;">(<span id="v_date_label"></span>)</small>
                    <span class="visits-count-badge" id="v_count_badge" style="display:none;"></span>
                </h4>
            </div>
            <div class="modal-body" style="padding:0;max-height:66vh;overflow-y:auto;">
                <div id="visitsContent">
                    <div class="visits-empty">
                        <span class="spin-ico" style="display:block;margin:0 auto 8px;">{!! svgico('spin',32,'style="color:#b0bcc8"') !!}</span>
                        <p>Loading visits...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="catSummaryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:420px;max-width:95vw;margin:80px auto;">
        <div class="modal-content">

            {{-- Header --}}
            <div class="modal-header" style="background:linear-gradient(135deg,#1e293b,#334155);padding:16px 20px;border:none;">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title" style="color:#fff;font-size:14px;font-weight:700;display:flex;align-items:center;gap:7px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Export Category Summary PDF
                </h4>
            </div>

            {{-- Body --}}
            <div class="modal-body" style="padding:20px;">

                {{-- Employee --}}
                <div style="margin-bottom:14px;">
                    <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:6px;">
                        Employee &nbsp;<span style="font-weight:400;color:#94a3b8;text-transform:none;">(optional — leave blank for all)</span>
                    </label>
                    <select id="csp_employee" style="width:100%;height:34px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;color:#334155;padding:0 10px;background:#fff;font-family:'DM Sans',sans-serif;">
                        <option value="">— All Employees —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}{{ $emp->mobile ? ' ('.$emp->mobile.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Month --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:6px;">Month</label>
                        <select id="csp_month" style="width:100%;height:34px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;color:#334155;padding:0 10px;background:#fff;font-family:'DM Sans',sans-serif;">
                            <option value="">All Months</option>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                    {{ date('F', mktime(0,0,0,$m,1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:6px;">Year</label>
                        <select id="csp_year" style="width:100%;height:34px;border:1px solid #cbd5e1;border-radius:4px;font-size:13px;color:#334155;padding:0 10px;background:#fff;font-family:'DM Sans',sans-serif;">
                            <option value="">All Years</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ $yr == date('Y') ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Info note --}}
                <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:5px;padding:9px 12px;font-size:11px;color:#0369a1;line-height:1.5;">
                    <strong>What's included:</strong> Organisation-wide totals at top, then each employee's category breakdown sorted alphabetically. Pending amounts highlighted in pink.
                </div>

            </div>

            {{-- Footer --}}
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnDownloadCatSummary" style="
                    display:inline-flex; align-items:center; gap:6px;
                    background:#1e293b; color:#fff; border:none;
                    border-radius:5px; padding:9px 20px; font-size:13px; font-weight:700;
                    cursor:pointer; font-family:'DM Sans',sans-serif; transition:opacity .18s;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download PDF
                </button>
            </div>

        </div>
    </div>
</div>
<script>
/* ── Notify ── */
function notify(type, msg) {
    if (typeof toastr !== 'undefined') { toastr[type](msg); return; }
    var colors = {success:'#38a169',error:'#e53e3e',warning:'#f39c12',info:'#3598dc'};
    var el = document.createElement('div');
    el.innerHTML = msg;
    el.style.cssText = 'position:fixed;top:18px;right:18px;z-index:999999;background:'+(colors[type]||'#3598dc')+';color:#fff;padding:11px 18px;border-radius:6px;font-size:13px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.2);font-family:"DM Sans",sans-serif;max-width:320px;opacity:0;transition:opacity .28s;';
    document.body.appendChild(el);
    setTimeout(function(){ el.style.opacity='1'; }, 10);
    setTimeout(function(){ el.style.opacity='0'; setTimeout(function(){ el.parentNode&&el.parentNode.removeChild(el); }, 320); }, 3200);
}

/* ── Lightbox ── */
function openLb(s) {
    document.getElementById('lbImg').src = s;
    document.getElementById('lbNewTab').href = s;
    document.getElementById('expLb').classList.add('on');
}
function closeLb(e) { if(e){e.stopPropagation();} document.getElementById('expLb').classList.remove('on'); }
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeLb(); });

/* ── Status picker ── */
function pickSt(v){
    document.querySelectorAll('.m-sopt').forEach(function(el){ el.classList.remove('sa','sp','sr'); });
    var k = v==='Partially Approved'?'sp':(v==='Approved'?'sa':'sr');
    document.querySelector('.m-sopt[data-v="'+v+'"]').classList.add(k);
    document.getElementById('m_status').value=v;
    document.getElementById('m_partial').style.display = v==='Partially Approved'?'block':'none';
    if(v!=='Partially Approved') document.getElementById('m_appr').value='';
    document.getElementById('m_err').style.display='none';
}

/* ── Query helpers ── */
function formatQDate(d){
    if(!d) return '';
    var dt = new Date(d.replace(' ','T'));
    return dt.toLocaleDateString('en-IN',{day:'2-digit',month:'short'})+' '+dt.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',hour12:true});
}
function escHtml(t){ return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function renderBubble(q){
    var isAdmin = q.sender_type==='admin';
    return '<div class="q-bubble '+(isAdmin?'admin':'employee')+'">'
        +'<div class="q-binner">'+escHtml(q.message)+'</div>'
        +'<div class="q-meta"><strong>'+(q.sender_name||'Unknown')+'</strong> &middot; '+formatQDate(q.created_at)+'</div>'
        +'</div>';
}
function scrollChatBottom(){ var b=document.getElementById('qChatBox'); if(b) b.scrollTop=b.scrollHeight; }

var spinSvg = '<span class="spin-ico"><svg width="18" height="18" style="color:#b0bcc8"><use href="#ico-spin"/></svg></span>';

$(document).ready(function(){

    /* ── PDF button: only show after Apply is clicked with employee selected ── */
    function buildPdfUrl() {
        var empVal   = $('#filterEmployee').val();
        var month    = $('#filterMonth').val();
        var year     = $('#filterYear').val();
        var status   = $('#filterStatus').val();
        var verified = $('#filterVerified').val();
        var $btn     = $('#btnExportPdf');

        /* Only enable PDF when an employee is selected */
        if (empVal && empVal !== '') {
            var p = new URLSearchParams();
            p.set('employee_id', empVal);
            if (month    && month    !== '') p.set('month',    month);
            if (year     && year     !== '') p.set('year',     year);
            if (status   && status   !== '') p.set('status',   status);
            if (verified && verified !== '') p.set('verified', verified);
            $btn.attr('href', '{{ url("admin/user-expenses/export-pdf") }}?' + p.toString());
            $btn.addClass('visible');
        } else {
            $btn.removeClass('visible');
        }
    }

    /* Show PDF only after the Apply button is clicked AND employee is set */
    $('#filterForm').on('submit', function(){
        buildPdfUrl();
    });

    /* On page load: if URL already has employee_id param, show the button */
    (function(){
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('employee_id')) {
            buildPdfUrl();
        }
    })();

    /* ── Verify checkbox ── */
    $(document).on('change', '.verify-checkbox', function(){
        var $cb  = $(this);
        var id   = $cb.data('id');
        var was  = !$cb.prop('checked'); /* state BEFORE this change */
        $cb.prop('disabled', true);

        $.ajax({
            url: '/admin/user-expenses/' + id + '/toggle-verified',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(r){
                if(r.success){
                    var $lbl  = $('#vlbl-' + id);
                    var $cell = $('#act-' + id);

                    if(r.verified){
                        $cb.prop('checked', true);
                        $lbl.text('YES').attr('class', 'v-lb on');
                        notify('success', 'Marked as Verified');

                        /* Show Update button — pull current data from badge/cell */
                        var currentStatus  = $('#sbadge-' + id).text().trim();
                        var currentRemarks = '';
                        $cell.html(
                            '<button class="btn-upd btn-update-status"' +
                            ' data-id="' + id + '"' +
                            ' data-status="' + escHtml(currentStatus) + '"' +
                            ' data-requested="0"' +
                            ' data-approved="0"' +
                            ' data-admin-remarks="">' +
                            '<svg width="11" height="11"><use href="#ico-pencil"/></svg> Update' +
                            '</button>'
                        );
                    } else {
                        $cb.prop('checked', false);
                        $lbl.text('NO').attr('class', 'v-lb off');
                        notify('info', 'Verification removed');

                        /* Hide Update button — show lock icon */
                        $cell.html('<span class="upd-locked"><svg width="11" height="11" style="color:#d0d8e8"><use href="#ico-lock"/></svg></span>');
                    }
                } else {
                    $cb.prop('checked', was);
                    notify('error', 'Failed to update.');
                }
            },
            error: function(){
                $cb.prop('checked', was);
                notify('error', 'An error occurred.');
            },
            complete: function(){ $cb.prop('disabled', false); }
        });
    });

    /* ── Internal remarks ── */
    $(document).on('click','.btn-open-int-remarks',function(){
        var id=$(this).data('id'), remarks=$(this).data('remarks')||'', vby=$(this).data('verified-by')||'';
        $('#ir_expense_id').val(id); $('#ir_textarea').val(remarks); $('#ir_err').hide();
        if(remarks.trim()){ $('#ir_current_text').text(remarks); $('#ir_current_by').text(vby?'Verified by: '+vby:''); $('#ir_current_wrap').show(); }
        else { $('#ir_current_wrap').hide(); }
        $('#internalRemarksModal').modal('show');
        setTimeout(function(){ $('#ir_textarea').focus(); },400);
    });
    $('#btnSaveInternalRemarks').on('click',function(){
        var id=$('#ir_expense_id').val(), remarks=$('#ir_textarea').val().trim(), $btn=$(this);
        $btn.prop('disabled',true).html(spinSvg+' Saving...');
        $('#ir_err').hide();
        $.ajax({
            url:'/admin/user-expenses/'+id+'/internal-remarks', type:'POST',
            data:{_token:'{{ csrf_token() }}',internal_remarks:remarks},
            success:function(r){
                if(r.success){
                    var $irBtn=$('#irbtn-'+id);
                    $irBtn.data('remarks',remarks);
                    remarks ? $irBtn.addClass('has-remark').attr('title','Edit note') : $irBtn.removeClass('has-remark').attr('title','Add note');
                    $('#internalRemarksModal').modal('hide'); notify('success','Remark saved.');
                } else { $('#ir_err').html(r.message||'Failed to save.').show(); }
            },
            error:function(x){
                var m='An error occurred.';
                if(x.responseJSON&&x.responseJSON.errors) m=Object.values(x.responseJSON.errors).flat().join('<br>');
                $('#ir_err').html(m).show();
            },
            complete:function(){ $btn.prop('disabled',false).html('<svg width="13" height="13"><use href="#ico-save"/></svg> Save'); }
        });
    });

    /* ── Status modal ── */
    $(document).on('click','.btn-update-status',function(){
        var id=$(this).data('id'), st=$(this).data('status'), req=$(this).data('requested'), appr=$(this).data('approved'), rmks=$(this).data('admin-remarks')||'';
        $('#m_id').val(id); $('#m_req').text('Rs. '+parseFloat(req||0).toFixed(2));
        $('#m_status').val(''); $('#m_appr').val(''); $('#m_admin_remarks').val(rmks);
        $('#m_partial').hide(); $('#m_err').hide();
        document.querySelectorAll('.m-sopt').forEach(function(el){ el.classList.remove('sa','sp','sr'); });
        if(st==='Approved'||st==='Partially Approved'||st==='Rejected'){ pickSt(st); if(st==='Partially Approved') $('#m_appr').val(appr); }
        $('#stModal').modal('show');
    });
    $('#btnSave').on('click',function(){
        var id=$('#m_id').val(), st=$('#m_status').val(), appr=$('#m_appr').val(), rmks=$('#m_admin_remarks').val().trim(), $b=$(this);
        if(!st){ $('#m_err').html('Please select a status.').show(); return; }
        if(st==='Partially Approved'){
            var av=parseFloat(appr), rv=parseFloat($('#m_req').text().replace('Rs. ','').replace(/,/g,''));
            if(!appr||av<=0){ $('#m_err').html('Please enter a valid approved amount.').show(); return; }
            if(av>rv){ $('#m_err').html('Approved (\u20b9'+av.toFixed(2)+') cannot exceed requested (\u20b9'+rv.toFixed(2)+').').show(); return; }
        }
        $b.prop('disabled',true).html(spinSvg+' Saving...');
        $('#m_err').hide();
        $.ajax({
            url:'/admin/user-expenses/'+id+'/update-status', type:'POST',
            data:{_token:'{{ csrf_token() }}',status:st,approved_amount:appr,admin_remarks:rmks},
            success:function(r){
                if(r.success){
                    var k=st==='Partially Approved'?'Partially':st;
                    $('#sbadge-'+id).attr('class','s-badge sb-'+k).text(st);
                    $('#row-'+id).removeClass('s-Requested s-Approved s-Partially s-Rejected s-PendingApproval').addClass('s-'+k);
                    if(st==='Approved'||st==='Partially Approved'){
                        var a=st==='Partially Approved'?parseFloat(appr):parseFloat($('#m_req').text().replace('Rs. ','').replace(/,/g,''));
                        $('#appr-'+id).html('<span class="a-apr">Rs. '+a.toFixed(2)+'</span>');
                    } else { $('#appr-'+id).html('<span class="a-nil">&mdash;</span>'); }
                    var $rmkEl=$('#appr-rmk-'+id);
                    rmks ? $rmkEl.text(rmks.substring(0,28)+(rmks.length>28?'..':'')).attr('title',rmks).show() : $rmkEl.hide();
                    $('.btn-update-status[data-id="'+id+'"]').data('status',st).data('approved',st==='Partially Approved'?appr:0).data('admin-remarks',rmks);
                    $('#stModal').modal('hide'); notify('success','Status updated!');
                } else { $('#m_err').html(r.message||'Something went wrong.').show(); }
            },
            error:function(x){
                var m='An error occurred.';
                if(x.responseJSON&&x.responseJSON.errors) m=Object.values(x.responseJSON.errors).flat().join('<br>');
                $('#m_err').html(m).show();
            },
            complete:function(){ $b.prop('disabled',false).html('<svg width="13" height="13"><use href="#ico-save"/></svg> Save'); }
        });
    });

    /* ── Query modal ── */
    $(document).on('click','.btn-open-query',function(){
        var id=$(this).data('id'), name=$(this).data('employee');
        $('#q_expense_id').val(id); $('#q_expense_id_label').text(id); $('#q_employee_name').text(name);
        $('#q_message').val('');
        $('#qChatBox').html('<div class="q-loading">'+spinSvg+' Loading...</div>');
        $('#queryModal').modal('show');
        $.ajax({
            url:'/admin/user-expenses/'+id+'/queries', type:'GET',
            success:function(r){
                if(r.success){
                    if(r.queries.length===0){
                        $('#qChatBox').html('<div class="q-empty"><svg width="32" height="32" style="color:#c5cdd8"><use href="#ico-msg"/></svg><span>No queries yet. Start a conversation.</span></div>');
                    } else {
                        var html=''; $.each(r.queries,function(i,q){ html+=renderBubble(q); });
                        $('#qChatBox').html(html); scrollChatBottom();
                    }
                    $('#qbtn-'+id).removeClass('has-unread').find('.q-unread-dot').remove();
                    var total=r.queries.length, $pill=$('#qcount-'+id);
                    total>0 ? $pill.text(total>99?'99+':total).show() : $pill.hide();
                }
            },
            error:function(){ $('#qChatBox').html('<div class="q-empty"><svg width="24" height="24" style="color:#e53e3e"><use href="#ico-warn"/></svg><span>Failed to load.</span></div>'); }
        });
    });
    $('#btnSendQuery').on('click',function(){
        var id=$('#q_expense_id').val(), msg=$('#q_message').val().trim(), $b=$(this);
        if(!msg){ notify('warning','Please enter a message.'); $('#q_message').focus(); return; }
        $b.prop('disabled',true).html(spinSvg+' Sending...');
        $.ajax({
            url:'/admin/user-expenses/'+id+'/raise-query', type:'POST',
            data:{_token:'{{ csrf_token() }}',message:msg},
            success:function(r){
                if(r.success){
                    $('#qChatBox .q-empty').remove();
                    $('#qChatBox').append(renderBubble(r.query)); scrollChatBottom();
                    $('#q_message').val('');
                    var newTotal=r.total||parseInt($('#qcount-'+id).text()||'0')+1;
                    $('#qcount-'+id).text(newTotal>99?'99+':newTotal).show();
                    notify('success','Query sent!');
                } else { notify('error',r.message||'Failed.'); }
            },
            error:function(){ notify('error','An error occurred.'); },
            complete:function(){ $b.prop('disabled',false).html('<svg width="13" height="13" style="color:#fff"><use href="#ico-send"/></svg> Send'); }
        });
    });
    $('#q_message').on('keydown',function(e){ if(e.ctrlKey&&e.key==='Enter') $('#btnSendQuery').trigger('click'); });

    /* ── Visits modal ── */
    $(document).on('click','.btn-view-visits',function(){
        var userId=$(this).data('user-id'), date=$(this).data('date'), employee=$(this).data('employee');
        $('#v_employee_name').text(employee); $('#v_date_label').text(date); $('#v_count_badge').hide();
        $('#visitsContent').html('<div class="visits-empty"><span class="spin-ico" style="display:block;margin:0 auto 8px;"><svg width="28" height="28" style="color:#b0bcc8"><use href="#ico-spin"/></svg></span><p>Loading...</p></div>');
        $('#visitsModal').modal('show');
        $.ajax({
            url:'/admin/user-expenses/get-visits', type:'GET',
            data:{user_id:userId,date:date},
            success:function(r){
                if(r.success&&r.visits.length>0){
                    var html='<table class="visits-table"><thead><tr><th>#</th><th>Customer</th><th>Time</th><th>Start</th><th>End</th><th>Purpose</th></tr></thead><tbody>';
                    $.each(r.visits,function(i,v){
                        var cn=v.customer_name||v.crr_name||'—', ci=v.customer_address||v.crr_address||'';
                        html+='<tr><td style="color:#b0bcc8;font-weight:700;text-align:center;">'+(i+1)+'</td>'
                            +'<td><div class="vt-customer">'+escHtml(cn)+'</div>'+(ci?'<div class="vt-sub">'+escHtml(ci)+'</div>':'')+'</td>'
                            +'<td><div class="vt-time">'+(v.start_time||'—')+' → '+(v.end_time||'—')+'</div></td>'
                            +'<td><div class="vt-loc" title="'+(v.start_location||'')+'">'+(v.start_location||'—')+'</div></td>'
                            +'<td><div class="vt-loc" title="'+(v.end_location||'')+'">'+(v.end_location||'—')+'</div></td>'
                            +'<td>'+(v.purpose_of_visit?'<span class="vt-purpose">'+escHtml(v.purpose_of_visit)+'</span>':'—')+'</td></tr>';
                    });
                    html+='</tbody></table>';
                    $('#visitsContent').html(html);
                    $('#v_count_badge').text(r.visits.length+' visit'+(r.visits.length>1?'s':'')).show();
                } else {
                    $('#visitsContent').html('<div class="visits-empty"><svg width="36" height="36" style="color:#c5cdd8;display:block;margin:0 auto 8px"><use href="#ico-cal"/></svg><p>No visits found for this date.</p></div>');
                }
            },
            error:function(){
                $('#visitsContent').html('<div class="visits-empty"><svg width="32" height="32" style="color:#e53e3e;display:block;margin:0 auto 8px"><use href="#ico-warn"/></svg><p>Failed to load.</p></div>');
            }
        });
    });
});
</script>
<script>
// Category Summary PDF export
$('#btnOpenCatSummary').on('click', function(){
    $('#catSummaryModal').modal('show');
});

$('#btnDownloadCatSummary').on('click', function(){
    var empId = $('#csp_employee').val();
    var month = $('#csp_month').val();
    var year  = $('#csp_year').val();

    var p = new URLSearchParams();
    if (empId) p.set('employee_id', empId);
    if (month) p.set('month', month);
    if (year)  p.set('year', year);

    var url = '{{ url("admin/user-expenses/export-category-summary") }}';
    if (p.toString()) url += '?' + p.toString();

    window.open(url, '_blank');
    $('#catSummaryModal').modal('hide');
});
</script>
@endsection