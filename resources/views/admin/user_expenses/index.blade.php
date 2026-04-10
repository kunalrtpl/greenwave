@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');

/* ── SVG icon helper ── */
.ico { display:inline-block; vertical-align:middle; flex-shrink:0; }
.ico svg { display:block; }

.exp-wrap * { font-family: 'DM Sans', sans-serif !important; box-sizing: border-box; }
.exp-wrap { padding: 24px 0 60px; background: #eef1f7; min-height: 100vh; }

.exp-page-hd { margin-bottom: 22px; }
.exp-page-hd h2 { font-size: 26px; font-weight: 800; color: #1a2333; letter-spacing:-.4px; margin:0 0 4px; display:flex; align-items:center; gap:10px; }
.exp-page-hd p  { font-size: 14px; color: #8a9ab5; font-weight: 500; margin: 0; }

/* ---- Filter Card ---- */
.exp-filter-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 2px 12px rgba(30,50,100,.08);
    padding: 20px 24px; margin-bottom: 20px;
    border-top: 3px solid #4d8fcc;
}
.exp-filter-card label {
    font-size: 11px; font-weight: 700; color: #8a9ab5;
    text-transform: uppercase; letter-spacing: .7px;
    display: flex; align-items:center; gap:5px; margin-bottom: 6px;
}
.exp-filter-card .form-control {
    border: 1.5px solid #e4eaf3; border-radius: 7px;
    height: 38px; font-size: 14px; color: #2d3a4a;
    font-weight: 500; box-shadow: none; background: #fafbfc;
}
.exp-filter-card .form-control:focus { border-color: #4d8fcc; outline: none; box-shadow: 0 0 0 3px rgba(77,143,204,.12); }
.exp-filter-card .form-group { margin-bottom: 0; }

.btn-fa {
    display: inline-flex; align-items:center; gap:7px;
    background: linear-gradient(135deg,#4d8fcc,#2d6faa);
    color: #fff; border: none; border-radius: 7px;
    padding: 9px 20px; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: all .2s;
    box-shadow: 0 3px 10px rgba(45,111,170,.22);
    font-family: 'DM Sans',sans-serif;
}
.btn-fa:hover { background: linear-gradient(135deg,#3a7fc1,#1f5a94); transform:translateY(-1px); color:#fff; }
.btn-fr {
    display: inline-flex; align-items:center; gap:6px;
    background: #f0f3f8; color: #6a7a90;
    border: 1.5px solid #dde4ee; border-radius: 7px;
    padding: 8px 18px; font-size: 14px; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: all .2s;
    margin-left: 8px; font-family: 'DM Sans',sans-serif;
}
.btn-fr:hover { background: #e4e9f2; color: #3a4a5a; text-decoration: none; }

/* ---- Stats ---- */
.exp-stats { margin-bottom:18px; display:flex; flex-wrap:wrap; gap:10px; }
.exp-stat {
    background:#fff; border-radius:10px; padding:10px 18px;
    display:inline-flex; align-items:center; gap:12px;
    box-shadow:0 1px 8px rgba(30,50,100,.07);
}
.exp-stat .si { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.exp-stat .st { font-size:11px; color:#8a9ab5; font-weight:600; }
.exp-stat .sv { font-size:18px; font-weight:800; color:#1a2333; line-height:1.1; }

/* ---- List ---- */
.exp-list { background:#fff; border-radius:14px; box-shadow:0 4px 24px rgba(30,50,100,.10); overflow:hidden; border:1px solid #e4eaf3; }

.exp-grid {
    display: grid;
    grid-template-columns: 56px 210px 155px 140px 120px 108px 94px 140px 150px;
    align-items: center;
}

.exp-head { background: linear-gradient(135deg, #1e3a5f 0%, #2d5080 100%); border-bottom: 2px solid #1a3050; }
.exp-head .hc {
    padding: 13px 12px; font-size: 11px; font-weight: 700;
    color: rgba(255,255,255,.85); text-transform: uppercase; letter-spacing: .8px;
    border-right: 1px solid rgba(255,255,255,.1);
    display:flex; align-items:center; gap:6px;
}
.exp-head .hc:last-child { border-right: none; }

.exp-row { border-bottom: 1px solid #edf1f8; transition: background .15s; position: relative; }
.exp-row:last-child { border-bottom: none; }
.exp-row:hover { background: #f5f8fd; }
.exp-row::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 4px 0 0 4px;
}
.exp-row.s-Requested::before      { background: #b0bcc8; }
.exp-row.s-Approved::before       { background: #2ecc71; }
.exp-row.s-Partially::before      { background: #f39c12; }
.exp-row.s-Rejected::before       { background: #e74c3c; }
.exp-row.s-PendingApproval::before { background: #e74c3c; }

.dc { padding: 14px 12px; border-right: 1px solid #edf1f8; font-size: 14px; color: #2d3a4a; }
.dc:last-child { border-right: none; }
.dc-id { padding: 14px 8px; text-align: center; border-right: 1px solid #edf1f8; }
.id-num { font-size: 12px; font-weight: 700; color: #b0bcc8; }

.emp-n  { font-size: 15px; font-weight: 700; color: #1a2333; }
.emp-m  { font-size: 13px; color: #7a8a9a; margin-top: 2px; display:flex; align-items:center; gap:4px; }
.cat-t  { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; margin-top:6px; background:#edf3fb; color:#2d6faa; border:1px solid #cde0f4; }
.miss-t { 
    display: inline-flex; 
    align-items: center; 
    gap: 3px; 
    font-size: 11px; 
    font-weight: 700; 
    padding: 3px 9px; 
    border-radius: 20px; 
    background: #d32f2f; /* Darker red for better contrast */
    color: #ffffff;      /* Pure white text */
    border: 1px solid #b71c1c; /* Deep red border to define the edge */
    margin-left: 4px; 
}
.missed-reason-txt { display:inline-flex; align-items:center; gap:4px; font-size:11px; color:#c47d00; background:#fff9ee; border:1px solid #ffe0a0; border-radius:6px; padding:3px 10px; margin-top:5px; max-width:190px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:default; }
.remarks-txt { font-size:12px; color:#8a9ab5; font-style:italic; margin-top:5px; padding-top:5px; border-top:1px dashed #edf1f8; display:flex; align-items:flex-start; gap:4px; }

.date-main { font-size:14px; font-weight:700; color:#1a2333; display:flex; align-items:center; gap:5px; }
.date-sub  { font-size:12px; color:#8a9ab5; margin-top:2px; display:flex; align-items:center; gap:4px; }
.a-req  { font-size:17px; font-weight:800; color:#1a2333; }
.a-apr  { font-size:17px; font-weight:800; color:#1e9e58; }
.a-nil  { font-size:18px; color:#dde4ee; }

.travel-sub { margin-top:7px; padding-top:7px; border-top:1px dashed #e0e8f0; }
.tr-km   { font-size:13px; font-weight:700; color:#3a7fc1; display:flex; align-items:center; gap:5px; }
.tr-rt   { font-size:11px; color:#8a9ab5; margin-top:3px; display:flex; align-items:center; gap:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:140px; }

.btn-view-visits {
    display:inline-flex; align-items:center; gap:6px;
    background:#edf8f0; color:#1a9a50; border:1.5px solid #b8e6c8; border-radius:7px;
    padding:6px 12px; font-size:12px; font-weight:700;
    cursor:pointer; transition:all .18s; font-family:'DM Sans',sans-serif;
    margin-top:7px;
}
.btn-view-visits:hover { background:#1a9a50; color:#fff; border-color:#1a9a50; }
.btn-view-visits:hover svg path, .btn-view-visits:hover svg circle, .btn-view-visits:hover svg rect { stroke:#fff !important; fill:#fff !important; }
.vc-badge { background:#1a9a50; color:#fff; border-radius:10px; font-size:10px; font-weight:800; padding:1px 7px; min-width:20px; text-align:center; }
.btn-view-visits:hover .vc-badge { background:#fff; color:#1a9a50; }

/* Receipt */
.r-thumb { width:52px; height:52px; border-radius:8px; overflow:hidden; cursor:pointer; position:relative; border:2px solid #e4eaf3; background:#f4f7fb; display:inline-flex; align-items:center; justify-content:center; transition:border-color .2s, transform .2s, box-shadow .2s; vertical-align:middle; margin:2px; }
.r-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.r-thumb:hover { border-color:#4d8fcc; transform:scale(1.08); box-shadow:0 4px 12px rgba(77,143,204,.3); }
.r-thumb .rto { position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(45,111,170,0); display:flex; align-items:center; justify-content:center; color:#fff; transition:background .2s; }
.r-thumb:hover .rto { background:rgba(45,111,170,.62); }
.r-none { width:52px; height:52px; border-radius:8px; border:2px dashed #dde4ee; display:inline-flex; align-items:center; justify-content:center; color:#c5cdd8; vertical-align:middle; margin:2px; }

/* Verified */
.vc { padding:14px 10px; border-right:1px solid #edf1f8; text-align:center; }
.v-cb { width:18px; height:18px; cursor:pointer; }
.v-lb { font-size:11px; font-weight:700; letter-spacing:.3px; margin-top:2px; display:block; }
.v-lb.on { color:#1e9e58; } .v-lb.off { color:#c5cdd8; }
.btn-int-remarks { background:none; border:1.5px solid #dde4ee; border-radius:6px; padding:4px 8px; cursor:pointer; font-size:12px; color:#8a9ab5; display:inline-flex; align-items:center; gap:4px; margin-top:6px; transition:all .15s; font-family:'DM Sans',sans-serif; }
.btn-int-remarks:hover { border-color:#4d8fcc; color:#2d6faa; background:#edf3fb; }
.btn-int-remarks.has-remark { border-color:#f39c12; color:#c47d00; background:#fff9ee; }

/* Query */
.qc { padding:14px 10px; border-right:1px solid #edf1f8; text-align:center; }
.btn-query { display:inline-flex; align-items:center; justify-content:center; gap:6px; background:#f0f4ff; color:#3d5a9a; border:1.5px solid #c8d4f0; border-radius:8px; padding:7px 12px; font-size:13px; font-weight:700; cursor:pointer; transition:all .18s; font-family:'DM Sans',sans-serif; min-width:58px; }
.btn-query:hover { background:#3d5a9a; color:#fff; border-color:#3d5a9a; }
.q-count-pill { background:#3d5a9a; color:#fff; border-radius:10px; font-size:10px; font-weight:800; padding:1px 7px; min-width:20px; text-align:center; transition:background .18s; }
.btn-query:hover .q-count-pill { background:#fff; color:#3d5a9a; }
.btn-query.has-unread { background:#fff0f0; color:#c0392b; border-color:#f0b0b0; }
.btn-query.has-unread:hover { background:#c0392b; color:#fff; border-color:#c0392b; }
.btn-query.has-unread .q-count-pill { background:#e74c3c; }
.btn-query.has-unread:hover .q-count-pill { background:#fff; color:#c0392b; }
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.25;} }
.q-unread-dot { width:8px; height:8px; border-radius:50%; background:#e74c3c; flex-shrink:0; animation:blink 1.1s infinite; }

/* Status */
.sc { padding:14px 12px; border-right:1px solid #edf1f8; }
.s-badge { display:inline-block; font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px; white-space:nowrap; }
.sb-Requested       { background:#f0f3f8; color:#7a8a9a; }
.sb-Approved        { background:#2ecc71; color:#fff; }
.sb-Partially       { background:#f39c12; color:#fff; }
.sb-Rejected        { background:#feeaea; color:#b52a2a; border:1px solid #f5c0c0; }
.sb-PendingApproval { background:#e74c3c; color:#fff; }
.btn-upd { display:inline-flex; align-items:center; gap:5px; background:#f0f5fc; color:#4d8fcc; border:1.5px solid #cde0f4; border-radius:7px; padding:5px 12px; font-size:12px; font-weight:700; cursor:pointer; transition:all .18s; margin-top:6px; font-family:'DM Sans',sans-serif; }
.btn-upd:hover { background:#4d8fcc; color:#fff; border-color:#4d8fcc; }
.appr-remarks-txt { font-size:11px; color:#8a9ab5; font-style:italic; margin-top:4px; }
.amt-col { padding:14px 12px; border-right:1px solid #edf1f8; text-align:right; }

/* Empty / Pagination */
.exp-empty { text-align:center; padding:70px 20px; color:#b0bcc8; }
.exp-empty p { font-size:15px; margin-top:12px; }
.exp-pager { padding:14px 22px; background:#f7f9fc; border-top:1px solid #edf1f8; overflow:hidden; }
.exp-pager .pi { float:left; line-height:36px; font-size:13px; color:#a0aab8; font-weight:500; }
.exp-pager .pagination { float:right; margin:0; }
.exp-pager .pagination>li>a,.exp-pager .pagination>li>span { border-radius:6px!important; margin:0 2px; border-color:#dde4ee; color:#4d8fcc; font-size:13px; padding:5px 12px; font-weight:600; font-family:'DM Sans',sans-serif; }
.exp-pager .pagination>.active>a,.exp-pager .pagination>.active>span { background:#4d8fcc!important; border-color:#4d8fcc!important; color:#fff!important; }

/* Lightbox */
.exp-lb { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(8,14,28,.95); z-index:999999; align-items:center; justify-content:center; }
.exp-lb.on { display:flex; }
.exp-lb img { max-width:90%; max-height:88vh; border-radius:12px; box-shadow:0 30px 80px rgba(0,0,0,.6); cursor:default; }
.exp-lbx { position:fixed; top:20px; right:26px; color:rgba(255,255,255,.8); font-size:38px; cursor:pointer; line-height:1; z-index:1000000; transition:all .2s; background:rgba(255,255,255,.1); width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
.exp-lbx:hover { color:#fff; background:rgba(255,255,255,.22); transform:rotate(90deg); }

/* Modals */
.mod-hdr-blue   { background:linear-gradient(135deg,#2d6faa,#1a4e80); padding:18px 24px; border:none; }
.mod-hdr-indigo { background:linear-gradient(135deg,#3d5a9a,#253870); padding:18px 24px; border:none; }
.mod-hdr-orange { background:linear-gradient(135deg,#f39c12,#d68910); padding:18px 24px; border:none; }
.mod-hdr-green  { background:linear-gradient(135deg,#27ae60,#1a7a45); padding:18px 24px; border:none; }
.modal-content { border-radius:14px!important; border:none!important; box-shadow:0 30px 80px rgba(10,20,50,.25)!important; overflow:hidden; }
.modal-footer-plain { padding:14px 24px; background:#f7f9fc; border-top:1px solid #edf1f8; display:flex; align-items:center; justify-content:flex-end; gap:8px; }
.mod-title { color:#fff; font-size:16px; font-weight:700; margin:0; display:flex; align-items:center; gap:8px; }
.mod-close { color:rgba(255,255,255,.9)!important; opacity:1!important; font-size:26px!important; line-height:1!important; padding:0!important; margin-top:-2px!important; }

.m-amt { background:linear-gradient(135deg,#edf3fb,#dceefa); border-radius:10px; padding:16px 18px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; }
.m-amt .ml { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#7a9ab5; }
.m-amt .mv { font-size:24px; font-weight:800; color:#2d6faa; margin-top:4px; }
.m-slbl { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#8a9ab5; margin-bottom:10px; }
.m-sgrid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
.m-sopt { border:2px solid #e8edf5; border-radius:10px; padding:14px 8px 11px; text-align:center; cursor:pointer; transition:all .18s; background:#fafbfc; }
.m-sopt .msicon { font-size:28px; display:block; margin-bottom:6px; line-height:1; }
.m-sopt .msname { font-size:12px; font-weight:700; color:#6a7a8a; }
.m-sopt:hover { border-color:#4d8fcc; background:#f0f6fc; }
.m-sopt.sa { border-color:#2ecc71; background:#e8faf1; } .m-sopt.sa .msname { color:#1a7a45; }
.m-sopt.sp { border-color:#f39c12; background:#fff6e4; } .m-sopt.sp .msname { color:#a06800; }
.m-sopt.sr { border-color:#e74c3c; background:#feeaea; } .m-sopt.sr .msname { color:#b52a2a; }
.m-pbox { background:#fffcf0; border:2px dashed #f39c12; border-radius:9px; padding:14px 16px; margin-bottom:14px; }
.m-pbox label { font-size:12px; font-weight:700; color:#a06800; display:block; margin-bottom:8px; }
.m-pbox .form-control { border-radius:7px; border:2px solid #f39c12; font-size:17px; font-weight:700; color:#a06800; box-shadow:none; }
.m-pbox .ph { font-size:12px; color:#c47d00; margin-top:5px; display:block; }
.m-remarks-box { margin-top:14px; }
.m-remarks-box label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#8a9ab5; display:block; margin-bottom:7px; }
.m-remarks-box textarea { width:100%; border:1.5px solid #e4eaf3; border-radius:8px; padding:10px 13px; font-size:14px; color:#2d3a4a; font-family:'DM Sans',sans-serif; resize:none; transition:border-color .2s; outline:none; background:#fafbfc; }
.m-remarks-box textarea:focus { border-color:#4d8fcc; background:#fff; box-shadow:0 0 0 3px rgba(77,143,204,.1); }
.m-err { background:#feeaea; border:none; border-left:4px solid #e74c3c; border-radius:7px; padding:10px 14px; font-size:13px; font-weight:600; color:#b52a2a; }
.btn-msave { display:inline-flex; align-items:center; gap:7px; background:linear-gradient(135deg,#2d6faa,#1a4e80); color:#fff; border:none; border-radius:7px; padding:10px 24px; font-size:14px; font-weight:700; cursor:pointer; font-family:'DM Sans',sans-serif; box-shadow:0 3px 10px rgba(45,111,170,.25); transition:opacity .2s; }
.btn-msave:hover { opacity:.88; } .btn-msave:disabled { opacity:.55; cursor:not-allowed; }
.btn-mcancel { display:inline-flex; align-items:center; gap:6px; background:#f0f3f8; color:#6a7a8a; border:1.5px solid #dde4ee; border-radius:7px; padding:9px 20px; font-size:14px; font-weight:600; cursor:pointer; font-family:'DM Sans',sans-serif; }

/* Query chat */
.q-chat-box { height:300px; overflow-y:auto; padding:16px 20px; background:#f7f9fc; border-bottom:1px solid #e8edf5; }
.q-chat-box::-webkit-scrollbar { width:4px; } .q-chat-box::-webkit-scrollbar-thumb { background:#d0d8e8; border-radius:4px; }
.q-bubble { display:block; max-width:82%; margin-bottom:12px; }
.q-bubble.admin { margin-left:18%; text-align:right; }
.q-bubble.employee { margin-right:18%; }
.q-binner { padding:11px 15px; border-radius:12px; font-size:14px; line-height:1.5; word-break:break-word; }
.q-bubble.admin .q-binner { background:linear-gradient(135deg,#4d8fcc,#2d6faa); color:#fff; border-radius:12px 12px 2px 12px; }
.q-bubble.employee .q-binner { background:#fff; color:#2d3a4a; border:1.5px solid #e4eaf3; border-radius:12px 12px 12px 2px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.q-meta { font-size:11px; color:#a0aab8; margin-top:4px; }
.q-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#b0bcc8; font-size:14px; font-weight:500; gap:10px; }
.q-reply { padding:14px 20px 6px; background:#fff; }
.q-reply label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#8a9ab5; display:block; margin-bottom:7px; }
.q-reply textarea { width:100%; border:1.5px solid #e4eaf3; border-radius:8px; padding:10px 13px; font-size:14px; color:#2d3a4a; font-family:'DM Sans',sans-serif; resize:none; outline:none; background:#fafbfc; transition:border-color .2s; }
.q-reply textarea:focus { border-color:#4d8fcc; background:#fff; box-shadow:0 0 0 3px rgba(77,143,204,.1); }
.btn-qsend { display:inline-flex; align-items:center; gap:7px; background:linear-gradient(135deg,#3d5a9a,#253870); color:#fff; border:none; border-radius:7px; padding:10px 22px; font-size:14px; font-weight:700; cursor:pointer; font-family:'DM Sans',sans-serif; transition:opacity .2s; }
.btn-qsend:hover { opacity:.88; } .btn-qsend:disabled { opacity:.55; cursor:not-allowed; }
.btn-qclose { display:inline-flex; align-items:center; gap:6px; background:#f0f3f8; color:#6a7a8a; border:1.5px solid #dde4ee; border-radius:7px; padding:9px 18px; font-size:14px; font-weight:600; cursor:pointer; font-family:'DM Sans',sans-serif; }
.q-loading { display:flex; align-items:center; justify-content:center; height:100%; color:#b0bcc8; gap:10px; font-size:14px; }

/* Internal remarks */
.ir-current-box { background:#f7f9fc; border-radius:9px; padding:14px; border-left:4px solid #f39c12; margin-bottom:16px; }
.ir-current-box .ir-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#f39c12; margin-bottom:7px; display:flex; align-items:center; gap:5px; }
.ir-current-box .ir-text { font-size:14px; color:#2d3a4a; line-height:1.6; }
.ir-by { font-size:12px; color:#a0aab8; margin-top:8px; }

/* Visits table */
.visits-table { width:100%; border-collapse:collapse; font-size:13px; }
.visits-table th { background:#f0f3f8; padding:10px 12px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#8a9ab5; border-bottom:2px solid #e4eaf3; text-align:left; position:sticky; top:0; }
.visits-table td { padding:12px; border-bottom:1px solid #edf1f8; color:#2d3a4a; vertical-align:top; }
.visits-table tr:last-child td { border-bottom:none; }
.visits-table tr:hover td { background:#f8fafd; }
.vt-customer { font-weight:700; color:#1a2333; font-size:14px; }
.vt-sub { font-size:12px; color:#8a9ab5; margin-top:2px; }
.vt-purpose { display:inline-block; background:#edf3fb; color:#2d6faa; font-size:11px; font-weight:700; padding:3px 10px; border-radius:12px; }
.vt-time { font-weight:600; color:#2d3a4a; font-size:13px; }
.vt-loc { font-size:12px; color:#6a7a8a; max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.visits-empty { text-align:center; padding:50px 20px; color:#b0bcc8; }
.visits-empty p { font-size:14px; margin-top:10px; }
.visits-count-badge { display:inline-block; background:rgba(255,255,255,.22); color:#fff; font-size:12px; font-weight:700; padding:3px 12px; border-radius:12px; margin-left:10px; }

/* Spinner */
@keyframes spin { to { transform:rotate(360deg); } }
.spin-ico { animation:spin .8s linear infinite; display:inline-block; }
</style>

{{-- ══ INLINE SVG ICON DEFINITIONS (zero external deps) ══ --}}
<svg style="display:none" xmlns="http://www.w3.org/2000/svg">
  <symbol id="ico-card"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></symbol>
  <symbol id="ico-list"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></symbol>
  <symbol id="ico-eye"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></symbol>
  <symbol id="ico-user"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></symbol>
  <symbol id="ico-cal"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></symbol>
  <symbol id="ico-filter"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></symbol>
  <symbol id="ico-check"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></symbol>
  <symbol id="ico-shield"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></symbol>
  <symbol id="ico-msg"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></symbol>
  <symbol id="ico-flag"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></symbol>
  <symbol id="ico-cog"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></symbol>
  <symbol id="ico-phone"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.5 2 2 0 0 1 3.6 1.32h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"/></symbol>
  <symbol id="ico-tag"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></symbol>
  <symbol id="ico-warn"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></symbol>
  <symbol id="ico-comment" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></symbol>
  <symbol id="ico-road"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="21" x2="21" y2="21"/><line x1="3" y1="7" x2="21" y2="7"/><polyline points="8 21 8 7 16 3 16 21"/></symbol>
  <symbol id="ico-map"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></symbol>
  <symbol id="ico-zoom"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></symbol>
  <symbol id="ico-img"     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></symbol>
  <symbol id="ico-pencil"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></symbol>
  <symbol id="ico-save"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></symbol>
  <symbol id="ico-send"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></symbol>
  <symbol id="ico-x"       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></symbol>
  <symbol id="ico-xcirc"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></symbol>
  <symbol id="ico-checkcirc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></symbol>
  <symbol id="ico-half"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20V2z" fill="currentColor" stroke="none"/></symbol>
  <symbol id="ico-money"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></symbol>
  <symbol id="ico-reply"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></symbol>
  <symbol id="ico-lock"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></symbol>
  <symbol id="ico-inbox"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></symbol>
  <symbol id="ico-clock"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></symbol>
  <symbol id="ico-search"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></symbol>
  <symbol id="ico-reset"   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.5"/></symbol>
  <symbol id="ico-spin"    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2a10 10 0 0 1 10 10" opacity=".9"/><path d="M12 2a10 10 0 0 0-10 10" opacity=".25"/><path d="M2 12a10 10 0 0 0 10 10" opacity=".5"/><path d="M22 12a10 10 0 0 1-10 10" opacity=".1"/></symbol>
</svg>

{{-- ── SVG shorthand macro ── --}}
@php
function svgico($id, $size=16, $extra='') {
    return '<span class="ico"><svg width="'.$size.'" height="'.$size.'" '.$extra.'><use href="#ico-'.$id.'"/></svg></span>';
}
@endphp

<div class="page-content-wrapper">
<div class="page-content exp-wrap">

    <ul class="page-breadcrumb breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
        <li><span>User Expenses</span></li>
    </ul>

    <div class="exp-page-hd">
        <h2>
            {!! svgico('card',22,'style="color:#4d8fcc"') !!}
            Expense Management
        </h2>
        <p>Review, verify and approve employee expense claims</p>
    </div>

    {{-- FILTER --}}
    <div class="exp-filter-card">
        <form method="GET" action="{{ url('admin/user-expenses') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{!! svgico('user',11) !!} Employee</label>
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
                        <label>{!! svgico('cal',11) !!} Month</label>
                        <select name="month" class="form-control">
                            <option value="">All</option>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('M', mktime(0,0,0,$m,1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>{!! svgico('cal',11) !!} Year</label>
                        <select name="year" class="form-control">
                            <option value="">All</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>{!! svgico('filter',11) !!} Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Pending Approval"   {{ request('status')==='Pending Approval'   ? 'selected':'' }}>Pending Approval</option>
                            <option value="Approved"           {{ request('status')==='Approved'           ? 'selected':'' }}>Approved</option>
                            <option value="Partially Approved" {{ request('status')==='Partially Approved' ? 'selected':'' }}>Partially Approved</option>
                            <option value="Rejected"           {{ request('status')==='Rejected'           ? 'selected':'' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>{!! svgico('shield',11) !!} Verified</label>
                        <select name="verified" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('verified')==='yes' ? 'selected':'' }}>Verified</option>
                            <option value="no"  {{ request('verified')==='no'  ? 'selected':'' }}>Not Verified</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div style="white-space:nowrap;padding-top:2px;">
                            <button type="submit" class="btn-fa">{!! svgico('search',14,'style="color:#fff"') !!} Apply Filters</button>
                            <a href="{{ url('admin/user-expenses') }}" class="btn-fr">{!! svgico('reset',14) !!} Reset</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- STATS --}}
    <div class="exp-stats">
        <div class="exp-stat">
            <div class="si" style="background:linear-gradient(135deg,#4d8fcc,#2d6faa);">{!! svgico('list',17,'style="color:#fff"') !!}</div>
            <div><div class="st">Total Records</div><div class="sv">{{ $expenses->total() }}</div></div>
        </div>
        <div class="exp-stat">
            <div class="si" style="background:linear-gradient(135deg,#2ecc71,#1a9a50);">{!! svgico('eye',17,'style="color:#fff"') !!}</div>
            <div><div class="st">Showing</div><div class="sv">{{ $expenses->firstItem() ?? 0 }}–{{ $expenses->lastItem() ?? 0 }}</div></div>
        </div>
        @if(request('status'))
        <div class="exp-stat" style="border-left:3px solid {{ request('status')==='Approved'?'#2ecc71':(request('status')==='Rejected'?'#e74c3c':(request('status')==='Partially Approved'?'#f39c12':'#b0bcc8')) }};">
            <div class="si" style="background:#f0f3f8;">{!! svgico('flag',17,'style="color:#6a7a8a"') !!}</div>
            <div><div class="st">Status Filter</div><div class="sv" style="font-size:14px;">{{ request('status') }}</div></div>
        </div>
        @endif
        @if(request('verified'))
        <div class="exp-stat" style="border-left:3px solid {{ request('verified')==='yes'?'#2ecc71':'#b0bcc8' }};">
            <div class="si" style="background:{{ request('verified')==='yes'?'#e8faf1':'#f0f3f8' }};">{!! svgico('check',17,'style="color:'.(request('verified')==='yes'?'#1e9e58':'#8a9ab5').'"') !!}</div>
            <div><div class="st">Verification</div><div class="sv" style="font-size:14px;">{{ request('verified')==='yes'?'Verified Only':'Not Verified' }}</div></div>
        </div>
        @endif
    </div>

    {{-- LIST --}}
    <div class="exp-list">
        <div class="exp-grid exp-head">
            <div class="hc" style="justify-content:center;">#</div>
            <div class="hc">{!! svgico('user',13,'style="color:rgba(255,255,255,.6)"') !!} Employee</div>
            <div class="hc">{!! svgico('cal',13,'style="color:rgba(255,255,255,.6)"') !!} Date &amp; Amount</div>
            <div class="hc" style="justify-content:flex-end;">{!! svgico('check',13,'style="color:rgba(255,255,255,.6)"') !!} Approved</div>
            <div class="hc" style="justify-content:center;">{!! svgico('img',13,'style="color:rgba(255,255,255,.6)"') !!} Bill</div>
            <div class="hc" style="justify-content:center;">{!! svgico('shield',13,'style="color:rgba(255,255,255,.6)"') !!} Verified</div>
            <div class="hc" style="justify-content:center;">{!! svgico('msg',13,'style="color:rgba(255,255,255,.6)"') !!} Query</div>
            <div class="hc" style="justify-content:center;">{!! svgico('flag',13,'style="color:rgba(255,255,255,.6)"') !!} Status</div>
            <div class="hc" style="justify-content:center;">{!! svgico('cog',13,'style="color:rgba(255,255,255,.6)"') !!} Actions</div>
        </div>

        @forelse($expenses as $expense)
        @php
            $sk = str_replace(' ', '', $expense->status);
            $sk = ($sk === 'PartiallyApproved') ? 'Partially' : $sk;
            $receiptPath    = !empty($expense->image)             ? asset('ExpenseReceipts/'.$expense->user_id.'/'.$expense->image)             : null;
            $altReceiptPath = !empty($expense->alternative_image) ? asset('ExpenseReceipts/'.$expense->user_id.'/'.$expense->alternative_image) : null;
            $qCount   = $queryCounts[$expense->id]       ?? 0;
            $unread   = $unreadQueryCounts[$expense->id] ?? 0;
            $key = $expense->user_id . '_' . $expense->expense_date;
        @endphp

        <div class="exp-grid exp-row s-{{ $sk }}" id="row-{{ $expense->id }}">

            {{-- # --}}
            <div class="dc-id"><span class="id-num">{{ $expense->id }}</span></div>

            {{-- Employee --}}
            <div class="dc">
                <div class="emp-n">{{ $expense->employee_name ?? 'N/A' }}</div>
                @if(!empty($expense->employee_mobile))
                    <div class="emp-m">{!! svgico('phone',11,'style="color:#b0bcc8"') !!}{{ $expense->employee_mobile }}</div>
                @endif
                <div style="margin-top:5px;">
                    <span class="cat-t">{!! svgico('tag',10,'style="color:#4d8fcc"') !!}{{ $expense->category_name }}</span>
                    @if($expense->missed_entry)
                        <br><br>
                        <span class="miss-t">{!! svgico('warn',10,'style="color:#c47d00"') !!}Missed</span>
                    @endif
                </div>
                @if($expense->missed_entry && !empty($expense->missed_entry_reason))
                    <div style="margin-top:5px;">
                        <span class="missed-reason-txt" title="{{ $expense->missed_entry_reason }}">
                            {!! svgico('warn',11,'style="color:#c47d00;flex-shrink:0"') !!}{{ \Illuminate\Support\Str::limit($expense->missed_entry_reason, 32) }}
                        </span>
                    </div>
                @endif
                @if(!empty($expense->remarks))
                    <div class="remarks-txt" title="{{ $expense->remarks }}">
                        {!! svgico('comment',11,'style="color:#b0bcc8;flex-shrink:0;margin-top:1px"') !!}{{ \Illuminate\Support\Str::limit($expense->remarks, 45) }}
                    </div>
                @endif
            </div>

            {{-- Date + Amount --}}
            <div class="dc">
                <div class="date-main">{!! svgico('cal',13,'style="color:#8a9ab5"') !!}{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</div>
                <div class="date-sub">{!! svgico('clock',11,'style="color:#b0bcc8"') !!}{{ \Carbon\Carbon::parse($expense->created_at)->format('d M, h:i A') }}</div>
                <div style="margin-top:7px;"><span class="a-req">&#8377;{{ number_format($expense->requested_amount, 2) }}</span></div>
                @if($expense->is_travel && !empty($expense->travel_km))
                <div class="travel-sub">
                    <div class="tr-km">{!! svgico('road',13,'style="color:#3a7fc1"') !!}{{ $expense->travel_km }} km &nbsp;@&nbsp; &#8377;{{ $expense->charge_per_km }}/km</div>
                    @if($expense->is_intercity && !empty($expense->intercity_route))
                        <div class="tr-rt">{!! svgico('map',10,'style="color:#b0bcc8"') !!}{{ $expense->intercity_route }}</div>
                    @endif
                    <button class="btn-view-visits"
                        data-user-id="{{ $expense->user_id }}"
                        data-date="{{ $expense->expense_date }}"
                        data-employee="{{ $expense->employee_name ?? 'Employee' }}"
                        title="View visits for this date">
                        {!! svgico('map',13,'style="color:#1a9a50"') !!} View Visits
                        <span class="vc-badge">{{ $visitCounts[$key] ?? 0 }}</span>
                    </button>
                </div>
                @endif
            </div>

            {{-- Approved --}}
            <div class="amt-col" id="appr-{{ $expense->id }}">
                @if($expense->approved_amount > 0)
                    <span class="a-apr">&#8377;{{ number_format($expense->approved_amount, 2) }}</span>
                @else
                    <span class="a-nil">&mdash;</span>
                @endif
            </div>

            {{-- Bills --}}
            <div class="dc" style="padding:10px 6px;text-align:center;">
                @if($receiptPath)
                    <div class="r-thumb" onclick="openLb('{{ $receiptPath }}')" title="View Bill">
                        <img src="{{ $receiptPath }}" alt="Bill" loading="lazy">
                        <div class="rto">{!! svgico('zoom',18,'style="color:#fff"') !!}</div>
                    </div>
                @else
                    <div class="r-none" title="No receipt">{!! svgico('img',20,'style="color:#c5cdd8"') !!}</div>
                @endif
                @if($altReceiptPath)
                    <div class="r-thumb" onclick="openLb('{{ $altReceiptPath }}')" title="Alt Bill">
                        <img src="{{ $altReceiptPath }}" alt="Alt Bill" loading="lazy">
                        <div class="rto">{!! svgico('zoom',18,'style="color:#fff"') !!}</div>
                    </div>
                @else
                    <div class="r-none" title="No alt receipt" style="opacity:.3;">{!! svgico('img',20,'style="color:#c5cdd8"') !!}</div>
                @endif
            </div>

            {{-- Verified --}}
            <div class="vc">
                <input type="checkbox" class="v-cb verify-checkbox" data-id="{{ $expense->id }}"
                    {{ !empty($expense->verified_by) ? 'checked' : '' }}
                    title="{{ !empty($expense->verified_by) ? 'Verified. Click to remove.' : 'Click to verify' }}">
                <span class="v-lb {{ !empty($expense->verified_by) ? 'on' : 'off' }}" id="vlbl-{{ $expense->id }}">
                    {{ !empty($expense->verified_by) ? 'YES' : 'NO' }}
                </span>
                <button class="btn-int-remarks btn-open-int-remarks {{ !empty($expense->internal_remarks)?'has-remark':'' }}"
                    id="irbtn-{{ $expense->id }}"
                    data-id="{{ $expense->id }}"
                    data-remarks="{{ addslashes($expense->internal_remarks ?? '') }}"
                    data-verified-by="{{ addslashes($expense->verified_by_name ?? '') }}"
                    title="{{ !empty($expense->internal_remarks)?'Edit note':'Add note' }}">
                    {!! svgico('pencil',12,'style="color:currentColor"') !!} Note
                </button>
            </div>

            {{-- Query —always-visible count --}}
            <div class="qc">
                <button class="btn-query btn-open-query {{ $unread>0?'has-unread':'' }}"
                    data-id="{{ $expense->id }}"
                    data-employee="{{ $expense->employee_name ?? 'Employee' }}"
                    id="qbtn-{{ $expense->id }}"
                    title="Queries">
                    @if($unread>0)<span class="q-unread-dot"></span>@endif
                    {!! svgico('msg',15,'style="color:currentColor"') !!}
                    <span class="q-count-pill" id="qcount-{{ $expense->id }}">{{ $unread>0?($unread>99?'99+':$unread):($qCount>99?'99+':$qCount) }}</span>
                </button>
            </div>

            {{-- Status --}}
            <div class="sc" id="sc-{{ $expense->id }}">
                <span class="s-badge sb-{{ $sk }}" id="sbadge-{{ $expense->id }}">{{ $expense->status }}</span>
                @if(!empty($expense->admin_remarks))
                    <div class="appr-remarks-txt" id="appr-rmk-{{ $expense->id }}" title="{{ $expense->admin_remarks }}">
                        {{ \Illuminate\Support\Str::limit($expense->admin_remarks, 30) }}
                    </div>
                @else
                    <div class="appr-remarks-txt" id="appr-rmk-{{ $expense->id }}" style="display:none;"></div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="dc" style="text-align:center;">
                <button class="btn-upd btn-update-status"
                    data-id="{{ $expense->id }}"
                    data-status="{{ $expense->status }}"
                    data-requested="{{ $expense->requested_amount }}"
                    data-approved="{{ $expense->approved_amount }}"
                    data-admin-remarks="{{ $expense->admin_remarks ?? '' }}">
                    {!! svgico('pencil',13,'style="color:currentColor"') !!} Update
                </button>
            </div>

        </div>
        @empty
        <div class="exp-empty">
            {!! svgico('inbox',48,'style="color:#c5cdd8;display:block;margin:0 auto 12px"') !!}
            <p>No expense records found.</p>
        </div>
        @endforelse

        @if($expenses->hasPages())
        <div class="exp-pager">
            <span class="pi">Page {{ $expenses->currentPage() }} of {{ $expenses->lastPage() }}</span>
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

</div>
</div>

{{-- LIGHTBOX --}}
<div class="exp-lb" id="expLb" onclick="closeLb()">
    <div class="exp-lbx" onclick="closeLb(event)">{!! svgico('x',22,'style="color:#fff"') !!}</div>
    <img src="" id="lbImg" alt="Receipt" onclick="event.stopPropagation()">
</div>

{{-- INTERNAL REMARKS MODAL --}}
<div class="modal fade" id="internalRemarksModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:440px;max-width:95vw;margin:80px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-orange">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title">{!! svgico('pencil',16,'style="color:#fff"') !!} Internal Remarks</h4>
            </div>
            <div class="modal-body" style="padding:24px;">
                <input type="hidden" id="ir_expense_id">
                <div class="ir-current-box" id="ir_current_wrap" style="display:none;">
                    <span class="ir-label">{!! svgico('lock',12,'style="color:#f39c12"') !!} Current Note</span>
                    <div class="ir-text" id="ir_current_text"></div>
                    <div class="ir-by" id="ir_current_by"></div>
                </div>
                <div class="m-remarks-box">
                    <label>Edit / Add Internal Remark <span style="color:#aab;">(admin only)</span></label>
                    <textarea id="ir_textarea" rows="4" placeholder="e.g. Bill checked and confirmed, original submitted..."></textarea>
                </div>
                <div id="ir_err" class="m-err" style="display:none;margin-top:12px;"></div>
            </div>
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal">{!! svgico('x',14) !!} Cancel</button>
                <button type="button" id="btnSaveInternalRemarks" class="btn-msave" style="background:linear-gradient(135deg,#f39c12,#d68910);">
                    {!! svgico('save',14,'style="color:#fff"') !!} Save Remark
                </button>
            </div>
        </div>
    </div>
</div>

{{-- STATUS MODAL --}}
<div class="modal fade" id="stModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:460px;max-width:95vw;margin:55px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-blue">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title">{!! svgico('pencil',16,'style="color:#fff"') !!} Update Expense Status</h4>
            </div>
            <div class="modal-body" style="padding:24px;">
                <input type="hidden" id="m_id">
                <div class="m-amt">
                    <div>
                        <div class="ml">Requested Amount</div>
                        <div class="mv" id="m_req">—</div>
                    </div>
                    {!! svgico('money',32,'style="color:#4d8fcc;opacity:.2"') !!}
                </div>
                <div class="m-slbl">Select New Status</div>
                <input type="hidden" id="m_status">
                <div class="m-sgrid">
                    <div class="m-sopt" data-v="Approved" onclick="pickSt('Approved')">
                        <span class="msicon">{!! svgico('checkcirc',28,'style="color:#2ecc71"') !!}</span>
                        <span class="msname">Approved</span>
                    </div>
                    <div class="m-sopt" data-v="Partially Approved" onclick="pickSt('Partially Approved')">
                        <span class="msicon">{!! svgico('half',28,'style="color:#f39c12"') !!}</span>
                        <span class="msname">Partial</span>
                    </div>
                    <div class="m-sopt" data-v="Rejected" onclick="pickSt('Rejected')">
                        <span class="msicon">{!! svgico('xcirc',28,'style="color:#e74c3c"') !!}</span>
                        <span class="msname">Rejected</span>
                    </div>
                </div>
                <div class="m-pbox" id="m_partial" style="display:none;">
                    <label>Amount to Approve <span style="color:#e74c3c;">*</span></label>
                    <input type="number" id="m_appr" class="form-control" min="0" step="0.01" placeholder="0.00">
                    <span class="ph">Must not exceed requested amount.</span>
                </div>
                <div class="m-remarks-box">
                    <label>Admin Remarks <span style="color:#aab;">(optional)</span></label>
                    <textarea id="m_admin_remarks" rows="2" placeholder="Add a note for this decision..."></textarea>
                </div>
                <div id="m_err" class="m-err" style="display:none;margin-top:12px;"></div>
            </div>
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal">{!! svgico('x',14) !!} Cancel</button>
                <button type="button" id="btnSave" class="btn-msave">{!! svgico('save',14,'style="color:#fff"') !!} Save Status</button>
            </div>
        </div>
    </div>
</div>

{{-- QUERY MODAL --}}
<div class="modal fade" id="queryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:550px;max-width:96vw;margin:50px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-indigo">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title">
                    {!! svgico('msg',16,'style="color:#fff"') !!}
                    Queries — <span id="q_employee_name"></span>
                    <small style="font-size:12px;opacity:.7;margin-left:8px;">(#<span id="q_expense_id_label"></span>)</small>
                </h4>
            </div>
            <div class="modal-body" style="padding:0;">
                <input type="hidden" id="q_expense_id">
                <div class="q-chat-box" id="qChatBox">
                    <div class="q-loading">
                        <span class="spin-ico">{!! svgico('spin',28,'style="color:#b0bcc8"') !!}</span>
                        Loading...
                    </div>
                </div>
                <div class="q-reply">
                    <label>{!! svgico('reply',12,'style="color:#8a9ab5"') !!} &nbsp;Write a Query / Reply</label>
                    <textarea id="q_message" rows="3" placeholder="Type your query or reply... (Ctrl+Enter to send)"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="padding:12px 20px 16px;background:#fff;border-top:1px solid #edf1f8;display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                <button type="button" class="btn-qclose" data-dismiss="modal">{!! svgico('x',14) !!} Close</button>
                <button type="button" id="btnSendQuery" class="btn-qsend">{!! svgico('send',14,'style="color:#fff"') !!} Send</button>
            </div>
        </div>
    </div>
</div>

{{-- VISITS MODAL --}}
<div class="modal fade" id="visitsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width:780px;max-width:96vw;margin:50px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-green">
                <button type="button" class="close mod-close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title mod-title">
                    {!! svgico('map',16,'style="color:#fff"') !!}
                    Visits — <span id="v_employee_name"></span>
                    <small style="font-size:12px;opacity:.7;margin-left:8px;">(<span id="v_date_label"></span>)</small>
                    <span class="visits-count-badge" id="v_count_badge" style="display:none;">0 visits</span>
                </h4>
            </div>
            <div class="modal-body" style="padding:0;max-height:68vh;overflow-y:auto;">
                <div id="visitsContent">
                    <div class="visits-empty">
                        <span class="spin-ico" style="display:block;margin:0 auto 10px;">{!! svgico('spin',36,'style="color:#b0bcc8"') !!}</span>
                        <p>Loading visits...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal">{!! svgico('x',14) !!} Close</button>
            </div>
        </div>
    </div>
</div>

<script>
/* ── Notify ── */
function notify(type, msg) {
    if (typeof toastr !== 'undefined') { toastr[type](msg); return; }
    var colors = {success:'#2ecc71',error:'#e74c3c',warning:'#f39c12',info:'#4d8fcc'};
    var el = document.createElement('div');
    el.innerHTML = msg;
    el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:999999;background:'+(colors[type]||'#4d8fcc')+';color:#fff;padding:13px 20px;border-radius:9px;font-size:14px;font-weight:600;box-shadow:0 4px 18px rgba(0,0,0,.22);font-family:"DM Sans",sans-serif;max-width:340px;opacity:0;transition:opacity .3s;';
    document.body.appendChild(el);
    setTimeout(function(){ el.style.opacity='1'; }, 10);
    setTimeout(function(){ el.style.opacity='0'; setTimeout(function(){ el.parentNode&&el.parentNode.removeChild(el); }, 350); }, 3500);
}

/* ── Lightbox ── */
function openLb(s) { document.getElementById('lbImg').src=s; document.getElementById('expLb').classList.add('on'); }
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

var spinSvg = '<span class="spin-ico"><svg width="22" height="22" style="color:#b0bcc8"><use href="#ico-spin"/></svg></span>';

$(document).ready(function(){

    /* ── Verify checkbox ── */
    $(document).on('change','.verify-checkbox',function(){
        var $cb=$(this), id=$cb.data('id'), was=$cb.prop('checked');
        $cb.prop('disabled',true);
        $.ajax({
            url:'/admin/user-expenses/'+id+'/toggle-verified', type:'POST',
            data:{_token:'{{ csrf_token() }}'},
            success:function(r){
                if(r.success){
                    var $l=$('#vlbl-'+id);
                    if(r.verified){ $cb.prop('checked',true); $l.text('YES').attr('class','v-lb on'); notify('success','Marked as Verified'); }
                    else { $cb.prop('checked',false); $l.text('NO').attr('class','v-lb off'); notify('info','Verification removed'); }
                } else { $cb.prop('checked',!was); notify('error','Failed to update.'); }
            },
            error:function(){ $cb.prop('checked',!was); notify('error','An error occurred.'); },
            complete:function(){ $cb.prop('disabled',false); }
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
                    $('#internalRemarksModal').modal('hide'); notify('success','Internal remark saved.');
                } else { $('#ir_err').html(r.message||'Failed to save.').show(); }
            },
            error:function(x){
                var m='An error occurred.';
                if(x.responseJSON&&x.responseJSON.errors) m=Object.values(x.responseJSON.errors).flat().join('<br>');
                $('#ir_err').html(m).show();
            },
            complete:function(){ $btn.prop('disabled',false).html('<svg width="14" height="14"><use href="#ico-save"/></svg> Save Remark'); }
        });
    });

    /* ── Status modal ── */
    $(document).on('click','.btn-update-status',function(){
        var id=$(this).data('id'), st=$(this).data('status'), req=$(this).data('requested'), appr=$(this).data('approved'), rmks=$(this).data('admin-remarks')||'';
        $('#m_id').val(id); $('#m_req').text('\u20B9'+parseFloat(req).toFixed(2));
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
            var av=parseFloat(appr), rv=parseFloat($('#m_req').text().replace('\u20B9','').replace(/,/g,''));
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
                        var a=st==='Partially Approved'?parseFloat(appr):parseFloat($('#m_req').text().replace('\u20B9','').replace(/,/g,''));
                        $('#appr-'+id).html('<span class="a-apr">\u20B9'+a.toFixed(2)+'</span>');
                    } else { $('#appr-'+id).html('<span class="a-nil">&mdash;</span>'); }
                    var $rmkEl=$('#appr-rmk-'+id);
                    rmks ? $rmkEl.text(rmks.substring(0,30)+(rmks.length>30?'..':'')).attr('title',rmks).show() : $rmkEl.hide();
                    $('.btn-update-status[data-id="'+id+'"]').data('status',st).data('approved',st==='Partially Approved'?appr:0).data('admin-remarks',rmks);
                    $('#stModal').modal('hide'); notify('success','Status updated!');
                } else { $('#m_err').html(r.message||'Something went wrong.').show(); }
            },
            error:function(x){
                var m='An error occurred.';
                if(x.responseJSON&&x.responseJSON.errors) m=Object.values(x.responseJSON.errors).flat().join('<br>');
                $('#m_err').html(m).show();
            },
            complete:function(){ $b.prop('disabled',false).html('<svg width="14" height="14"><use href="#ico-save"/></svg> Save Status'); }
        });
    });

    /* ── Query modal — count always visible ── */
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
                        $('#qChatBox').html('<div class="q-empty"><svg width="36" height="36" style="color:#c5cdd8"><use href="#ico-msg"/></svg><span>No queries yet. Start a conversation.</span></div>');
                    } else {
                        var html=''; $.each(r.queries,function(i,q){ html+=renderBubble(q); });
                        $('#qChatBox').html(html); scrollChatBottom();
                    }
                    /* Mark read: remove unread style + dot, but keep count showing total */
                    $('#qbtn-'+id).removeClass('has-unread').find('.q-unread-dot').remove();
                    $('#qcount-'+id).text(r.queries.length>99?'99+':r.queries.length);
                }
            },
            error:function(){ $('#qChatBox').html('<div class="q-empty"><svg width="28" height="28" style="color:#e74c3c"><use href="#ico-warn"/></svg><span>Failed to load.</span></div>'); }
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
                    var newTotal=r.total||parseInt($('#qcount-'+id).text())+1;
                    $('#qcount-'+id).text(newTotal>99?'99+':newTotal);
                    notify('success','Query sent!');
                } else { notify('error',r.message||'Failed to send.'); }
            },
            error:function(){ notify('error','An error occurred.'); },
            complete:function(){ $b.prop('disabled',false).html('<svg width="14" height="14" style="color:#fff"><use href="#ico-send"/></svg> Send'); }
        });
    });
    $('#q_message').on('keydown',function(e){ if(e.ctrlKey&&e.key==='Enter') $('#btnSendQuery').trigger('click'); });

    /* ── View Visits ── */
    $(document).on('click','.btn-view-visits',function(){
        var userId=$(this).data('user-id'), date=$(this).data('date'), employee=$(this).data('employee');
        $('#v_employee_name').text(employee); $('#v_date_label').text(date); $('#v_count_badge').hide();
        $('#visitsContent').html('<div class="visits-empty"><span class="spin-ico" style="display:block;margin:0 auto 10px;"><svg width="32" height="32" style="color:#b0bcc8"><use href="#ico-spin"/></svg></span><p>Loading...</p></div>');
        $('#visitsModal').modal('show');
        $.ajax({
            url:'/admin/user-expenses/get-visits', type:'GET',
            data:{user_id:userId,date:date},
            success:function(r){
                if(r.success&&r.visits.length>0){
                    var html='<table class="visits-table"><thead><tr><th>#</th><th>Customer</th><th>Time</th><th>Start</th><th>End</th><th>Purpose</th></tr></thead><tbody>';
                    $.each(r.visits,function(i,v){
                        var cn=v.customer_name||v.crr_name||'—', ci=v.customer_address||v.crr_address||'';
                        html+='<tr><td style="color:#b0bcc8;font-weight:700;">'+(i+1)+'</td>'
                            +'<td><div class="vt-customer">'+escHtml(cn)+'</div>'+(ci?'<div class="vt-sub">'+escHtml(ci)+'</div>':'')+'</td>'
                            +'<td><div class="vt-time">'+(v.start_time||'—')+' &rarr; '+(v.end_time||'—')+'</div></td>'
                            +'<td><div class="vt-loc" title="'+(v.start_location||'')+'">'+(v.start_location||'—')+'</div></td>'
                            +'<td><div class="vt-loc" title="'+(v.end_location||'')+'">'+(v.end_location||'—')+'</div></td>'
                            +'<td>'+(v.purpose_of_visit?'<span class="vt-purpose">'+escHtml(v.purpose_of_visit)+'</span>':'—')+'</td></tr>';
                    });
                    html+='</tbody></table>';
                    $('#visitsContent').html(html);
                    $('#v_count_badge').text(r.visits.length+' visit'+(r.visits.length>1?'s':'')).show();
                } else {
                    $('#visitsContent').html('<div class="visits-empty"><svg width="40" height="40" style="color:#c5cdd8;display:block;margin:0 auto"><use href="#ico-cal"/></svg><p>No visits found for this date.</p></div>');
                }
            },
            error:function(){
                $('#visitsContent').html('<div class="visits-empty"><svg width="36" height="36" style="color:#e74c3c;display:block;margin:0 auto"><use href="#ico-warn"/></svg><p>Failed to load.</p></div>');
            }
        });
    });
});
</script>
@endsection