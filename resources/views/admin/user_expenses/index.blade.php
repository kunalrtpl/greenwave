@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.exp-wrap * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
.exp-wrap { padding: 20px 0 48px; background: #eef1f7; min-height: 100vh; }

.exp-page-hd { margin-bottom: 20px; }
.exp-page-hd h2 { font-size: 20px; font-weight: 800; color: #1a2333; letter-spacing:-.3px; margin:0 0 3px; }
.exp-page-hd p  { font-size: 12px; color: #8a9ab5; font-weight: 500; margin: 0; }

/* ---- Filter Card ---- */
.exp-filter-card {
    background: #fff; border-radius: 10px;
    box-shadow: 0 2px 10px rgba(30,50,100,.07);
    padding: 18px 22px; margin-bottom: 18px;
    border-top: 3px solid #4d8fcc;
}
.exp-filter-card label {
    font-size: 10px; font-weight: 700; color: #8a9ab5;
    text-transform: uppercase; letter-spacing: .7px;
    display: block; margin-bottom: 5px;
}
.exp-filter-card .form-control {
    border: 1.5px solid #e4eaf3; border-radius: 6px;
    height: 36px; font-size: 13px; color: #2d3a4a;
    font-weight: 500; box-shadow: none; background: #fafbfc;
}
.exp-filter-card .form-control:focus { border-color: #4d8fcc; outline: none; box-shadow: 0 0 0 3px rgba(77,143,204,.12); }
.exp-filter-card .form-group { margin-bottom: 0; }

.btn-fa {
    display: inline-block;
    background: linear-gradient(135deg,#4d8fcc,#2d6faa);
    color: #fff; border: none; border-radius: 6px;
    padding: 8px 18px; font-size: 13px; font-weight: 700;
    cursor: pointer; transition: all .2s;
    box-shadow: 0 3px 10px rgba(45,111,170,.22);
    font-family: 'Inter',sans-serif;
}
.btn-fa:hover { background: linear-gradient(135deg,#3a7fc1,#1f5a94); transform:translateY(-1px); color:#fff; }
.btn-fr {
    display: inline-block;
    background: #f0f3f8; color: #6a7a90;
    border: 1.5px solid #dde4ee; border-radius: 6px;
    padding: 7px 16px; font-size: 13px; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: all .2s;
    margin-left: 8px; font-family: 'Inter',sans-serif;
}
.btn-fr:hover { background: #e4e9f2; color: #3a4a5a; text-decoration: none; }

/* ---- Stats ---- */
.exp-stats { margin-bottom:16px; }
.exp-stats .exp-stat { margin-right:10px; margin-bottom:8px; }
.exp-stat {
    background:#fff; border-radius:8px; padding:8px 16px;
    display:inline-block;
    box-shadow:0 1px 6px rgba(30,50,100,.06);
    vertical-align:middle;
}
.exp-stat .si { margin-right:10px; }
.exp-stat .si { width:30px; height:30px; border-radius:7px; display:inline-block; text-align:center; line-height:30px; font-size:12px; color:#fff; vertical-align:middle; }
.exp-stat .st { font-size:10px; color:#8a9ab5; font-weight:600; }
.exp-stat .sv { font-size:16px; font-weight:800; color:#1a2333; }

/* ---- List ---- */
.exp-list { background:#fff; border-radius:12px; box-shadow:0 4px 20px rgba(30,50,100,.09); overflow:hidden; border:1px solid #e4eaf3; }

.exp-grid {
    display: grid;
    grid-template-columns: 52px 200px 130px 130px 110px 90px 80px 100px 145px;
    align-items: center;
}

.exp-head { background: linear-gradient(135deg, #2d4f7a 0%, #1e3a5f 100%); border-bottom: 2px solid #1a3050; }
.exp-head .hc {
    padding: 11px 10px; font-size: 10px; font-weight: 700;
    color: rgba(255,255,255,.7); text-transform: uppercase; letter-spacing: .7px;
    border-right: 1px solid rgba(255,255,255,.08);
}
.exp-head .hc:last-child { border-right: none; }

.exp-row { border-bottom: 1px solid #edf1f8; transition: background .15s; position: relative; }
.exp-row:last-child { border-bottom: none; }
.exp-row:hover { background: #f8fafd; }

.exp-row::before {
    content: ''; position: absolute;
    left: 0; top: 0; bottom: 0; width: 4px;
}
.exp-row.s-Requested::before  { background: #b0bcc8; }
.exp-row.s-Approved::before   { background: #2ecc71; }
.exp-row.s-Partially::before  { background: #f39c12; }
.exp-row.s-Rejected::before   { background: #e74c3c; }

.dc { padding: 12px 10px; border-right: 1px solid #edf1f8; font-size: 13px; color: #2d3a4a; }
.dc:last-child { border-right: none; }
.dc-id { padding: 12px 6px; text-align: center; border-right: 1px solid #edf1f8; }
.id-num { font-size: 11px; font-weight: 700; color: #b0bcc8; }

/* Employee */
.emp-n  { font-size: 13px; font-weight: 700; color: #1a2333; }
.emp-m  { font-size: 11px; color: #a0aab8; margin-top: 1px; }
.cat-t  { display:inline-block; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; margin-top:5px; background:#edf3fb; color:#2d6faa; }
.miss-t { display:inline-block; font-size:10px; font-weight:700; padding:2px 7px; border-radius:20px; background:#fff4de; color:#c47d00; border:1px solid #ffc83d; margin-left:3px; }

/* Missed entry reason */
.missed-reason-txt {
    display:inline-block; font-size:10px; color:#c47d00; background:#fff9ee;
    border:1px solid #ffe0a0; border-radius:5px; padding:2px 8px; margin-top:4px;
    max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    cursor:default;
}

/* Remarks (user submitted remarks) */
.remarks-txt { font-size:11px; color:#8a9ab5; font-style:italic; margin-top:4px; padding-top:4px; border-top:1px dashed #edf1f8; }

/* Amount + Travel info stacked */
.amt-block { }
.a-req  { font-size:14px; font-weight:700; color:#1a2333; }
.a-apr  { font-size:14px; font-weight:700; color:#1e9e58; }
.a-nil  { font-size:16px; color:#dde4ee; }
.date-main { font-size:12px; font-weight:600; color:#2d3a4a; }
.date-sub  { font-size:10px; color:#b0bcc8; margin-top:2px; }

/* Travel info under amount */
.travel-sub { margin-top:5px; padding-top:5px; border-top:1px dashed #edf1f8; }
.tr-km   { font-size:11px; font-weight:700; color:#3a7fc1; }
.tr-rate { font-size:10px; color:#8a9ab5; }
.tr-rt   { font-size:10px; color:#8a9ab5; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:120px; }

/* Receipt */
.r-thumb {
    width:34px; height:34px; border-radius:7px; overflow:hidden;
    cursor:pointer; position:relative; border:2px solid #e4eaf3;
    background:#f4f7fb; display:inline-block;
    transition:border-color .2s, transform .2s;
    vertical-align:middle;
}
.r-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.r-thumb:hover { border-color:#4d8fcc; transform:scale(1.06); }
.r-thumb .rto { position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(45,111,170,0); text-align:center; padding-top:8px; color:#fff; font-size:11px; transition:background .2s; }
.r-thumb:hover .rto { background:rgba(45,111,170,.55); }
.r-none { width:34px; height:34px; border-radius:7px; border:2px dashed #dde4ee; display:inline-block; text-align:center; line-height:30px; color:#c5cdd8; font-size:13px; vertical-align:middle; }

/* ---- Verified column ---- */
.vc { padding:12px 8px; border-right:1px solid #edf1f8; text-align:center; vertical-align:middle; }
.v-cb { width:16px; height:16px; cursor:pointer; }
.v-lb { font-size:9px; font-weight:700; letter-spacing:.3px; }
.v-lb.on { color:#1e9e58; } .v-lb.off { color:#c5cdd8; }

/* Internal remarks button — pencil icon, separate from verify */
.btn-int-remarks {
    background: none; border: 1.5px solid #dde4ee; border-radius: 5px;
    padding: 2px 6px; cursor: pointer; font-size: 11px; color: #8a9ab5;
    line-height: 1.4; display: inline-block; margin-top: 4px;
    transition: all .15s; font-family: 'Inter', sans-serif;
}
.btn-int-remarks:hover { border-color: #4d8fcc; color: #2d6faa; background: #edf3fb; }
.btn-int-remarks.has-remark { border-color: #f39c12; color: #c47d00; background: #fff9ee; }

/* Query */
.qc { padding:12px 8px; border-right:1px solid #edf1f8; text-align:center; }
.btn-query {
    display:inline-block;
    background:#f5f7ff; color:#5b6faa; border:1.5px solid #d0d8f0; border-radius:6px;
    padding:5px 9px; font-size:11px; font-weight:700;
    cursor:pointer; transition:all .18s; white-space:nowrap;
    font-family:'Inter',sans-serif; position:relative; line-height:1.4;
    vertical-align:middle;
}
.btn-query:hover { background:#5b6faa; color:#fff; border-color:#5b6faa; }
.q-badge {
    position:absolute; top:-7px; right:-7px;
    background:#e74c3c; color:#fff; font-size:9px; font-weight:800;
    width:16px; height:16px; border-radius:50%;
    text-align:center; line-height:13px;
    border:2px solid #fff;
}
/* Blinking unread indicator */
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.2;} }
.q-unread-dot {
    position:absolute; top:-4px; right:-4px;
    width:9px; height:9px; border-radius:50%;
    background:#e74c3c; border:1.5px solid #fff;
    animation: blink 1.2s infinite;
}

/* Status */
.sc { padding:12px 10px; }
.s-badge { display:inline-block; font-size:10px; font-weight:700; padding:3px 10px; border-radius:20px; white-space:nowrap; }
.sb-Requested { background:#f0f3f8; color:#7a8a9a; }
.sb-Approved  { background:#e4f9ee; color:#1a7a45; }
.sb-Partially { background:#fff6e4; color:#a06800; }
.sb-Rejected  { background:#feeaea; color:#b52a2a; }
.btn-upd {
    display:inline-block;
    background:#f0f5fc; color:#4d8fcc; border:1.5px solid #cde0f4; border-radius:6px;
    padding:4px 10px; font-size:11px; font-weight:700;
    cursor:pointer; transition:all .18s; margin-top:5px; font-family:'Inter',sans-serif;
}
.btn-upd:hover { background:#4d8fcc; color:#fff; border-color:#4d8fcc; }
/* Approval remarks under status */
.appr-remarks-txt { font-size:10px; color:#8a9ab5; font-style:italic; margin-top:3px; }

/* ---- Empty / Pagination ---- */
.exp-empty { text-align:center; padding:60px 20px; color:#b0bcc8; }
.exp-empty i { font-size:40px; display:block; margin-bottom:10px; }
.exp-pager { padding:12px 20px; background:#f7f9fc; border-top:1px solid #edf1f8; overflow:hidden; }
.exp-pager .pi { float:left; line-height:34px; }
.exp-pager .pagination { float:right; }
.exp-pager .pi { font-size:12px; color:#a0aab8; font-weight:500; }
.exp-pager .pagination { margin:0; }
.exp-pager .pagination>li>a,.exp-pager .pagination>li>span { border-radius:5px!important; margin:0 2px; border-color:#dde4ee; color:#4d8fcc; font-size:12px; padding:4px 10px; font-weight:600; font-family:'Inter',sans-serif; }
.exp-pager .pagination>.active>a,.exp-pager .pagination>.active>span { background:#4d8fcc!important; border-color:#4d8fcc!important; color:#fff!important; }

/* ---- Lightbox ---- */
.exp-lb { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(8,14,28,.93); z-index:999999; text-align:center; padding-top:8vh; }
.exp-lb.on { display:block; }
.exp-lb img { max-width:88%; max-height:82vh; border-radius:10px; box-shadow:0 30px 80px rgba(0,0,0,.6); display:inline-block; }
.exp-lbx { position:fixed; top:18px; right:24px; color:rgba(255,255,255,.65); font-size:36px; cursor:pointer; line-height:1; z-index:1000000; transition:all .2s; }
.exp-lbx:hover { color:#fff; transform:rotate(90deg) scale(1.1); }

/* ---- Modals shared ---- */
.mod-hdr-blue  { background:linear-gradient(135deg,#2d6faa,#1a4e80); padding:16px 22px; border:none; }
.mod-hdr-indigo { background:linear-gradient(135deg,#3d5a9a,#253870); padding:16px 22px; border:none; }
.mod-hdr-green { background:linear-gradient(135deg,#27ae60,#1a7a45); padding:16px 22px; border:none; }
.modal-content { border-radius:13px!important; border:none!important; box-shadow:0 30px 80px rgba(10,20,50,.22)!important; overflow:hidden; }
.modal-footer-plain { padding:13px 22px; background:#f7f9fc; border-top:1px solid #edf1f8; }

.m-amt { background:linear-gradient(135deg,#edf3fb,#dceefa); border-radius:10px; padding:14px 18px; margin-bottom:18px; overflow:hidden; }
.m-amt .ml { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#7a9ab5; float:left; }
.m-amt .mv { font-size:22px; font-weight:800; color:#2d6faa; margin-top:3px; }
.m-amt .mi { font-size:30px; color:#4d8fcc; opacity:.25; float:right; margin-top:-4px; }

.m-slbl { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#8a9ab5; margin-bottom:10px; }
.m-sgrid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
.m-sopt { border:2px solid #e8edf5; border-radius:10px; padding:13px 8px 10px; text-align:center; cursor:pointer; transition:all .18s; background:#fafbfc; font-family:'Inter',sans-serif; }
.m-sopt .msicon { font-size:22px; display:block; margin-bottom:5px; }
.m-sopt .msname { font-size:11px; font-weight:700; color:#6a7a8a; }
.m-sopt:hover { border-color:#4d8fcc; background:#f0f6fc; } .m-sopt:hover .msname { color:#2d6faa; }
.m-sopt.sa { border-color:#2ecc71; background:#e8faf1; } .m-sopt.sa .msname { color:#1a7a45; }
.m-sopt.sp { border-color:#f39c12; background:#fff6e4; } .m-sopt.sp .msname { color:#a06800; }
.m-sopt.sr { border-color:#e74c3c; background:#feeaea; } .m-sopt.sr .msname { color:#b52a2a; }

.m-pbox { background:#fffcf0; border:2px dashed #f39c12; border-radius:9px; padding:14px 16px; margin-bottom:13px; }
.m-pbox label { font-size:11px; font-weight:700; color:#a06800; display:block; margin-bottom:7px; }
.m-pbox .form-control { border-radius:7px; border:2px solid #f39c12; font-size:16px; font-weight:700; color:#a06800; box-shadow:none; }
.m-pbox .ph { font-size:11px; color:#c47d00; margin-top:5px; display:block; }

/* Remarks textarea in modals */
.m-remarks-box { margin-top:14px; }
.m-remarks-box label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#8a9ab5; display:block; margin-bottom:6px; }
.m-remarks-box textarea {
    width:100%; border:1.5px solid #e4eaf3; border-radius:8px;
    padding:10px 12px; font-size:13px; color:#2d3a4a;
    font-family:'Inter',sans-serif; resize:none; transition:border-color .2s; outline:none; background:#fafbfc;
}
.m-remarks-box textarea:focus { border-color:#4d8fcc; background:#fff; box-shadow:0 0 0 3px rgba(77,143,204,.1); }

.m-err { background:#feeaea; border:none; border-left:4px solid #e74c3c; border-radius:7px; padding:10px 14px; font-size:12px; font-weight:600; color:#b52a2a; }
.btn-msave { display:inline-block; background:linear-gradient(135deg,#2d6faa,#1a4e80); color:#fff; border:none; border-radius:7px; padding:9px 22px; font-size:13px; font-weight:700; cursor:pointer; font-family:'Inter',sans-serif; box-shadow:0 3px 10px rgba(45,111,170,.25); transition:opacity .2s; }
.btn-msave:hover { opacity:.88; } .btn-msave:disabled { opacity:.55; cursor:not-allowed; }
.btn-mcancel { background:#f0f3f8; color:#6a7a8a; border:1.5px solid #dde4ee; border-radius:7px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; }

/* ---- Query Modal chat ---- */
.q-chat-box { height:280px; overflow-y:auto; padding:14px 18px; background:#f7f9fc; border-bottom:1px solid #e8edf5; }
.q-chat-box::-webkit-scrollbar { width:4px; } .q-chat-box::-webkit-scrollbar-thumb { background:#d0d8e8; border-radius:4px; }
.q-bubble { display:block; max-width:82%; margin-bottom:10px; }
.q-bubble.admin { margin-left:18%; text-align:right; }
.q-bubble.employee { margin-right:18%; text-align:left; }
.q-binner { padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.5; color:#1a2333; word-break:break-word; }
.q-bubble.admin .q-binner { background:linear-gradient(135deg,#4d8fcc,#2d6faa); color:#fff; border-radius:12px 12px 2px 12px; }
.q-bubble.employee .q-binner { background:#fff; color:#2d3a4a; border:1.5px solid #e4eaf3; border-radius:12px 12px 12px 2px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.q-meta { font-size:10px; color:#a0aab8; margin-top:3px; } .q-meta strong { color:#8a9ab5; }
.q-empty { display:block; text-align:center; padding:40px 0; color:#b0bcc8; font-size:13px; font-weight:500; }
.q-empty i { font-size:32px; }
.q-reply { padding:12px 18px 4px; background:#fff; }
.q-reply label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#8a9ab5; display:block; margin-bottom:6px; }
.q-reply textarea { width:100%; border:1.5px solid #e4eaf3; border-radius:8px; padding:10px 12px; font-size:13px; color:#2d3a4a; font-family:'Inter',sans-serif; resize:none; transition:border-color .2s; outline:none; background:#fafbfc; }
.q-reply textarea:focus { border-color:#4d8fcc; background:#fff; box-shadow:0 0 0 3px rgba(77,143,204,.1); }
.btn-qsend { display:inline-block; background:linear-gradient(135deg,#3d5a9a,#253870); color:#fff; border:none; border-radius:7px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; font-family:'Inter',sans-serif; transition:opacity .2s; }
.btn-qsend:hover { opacity:.88; } .btn-qsend:disabled { opacity:.55; cursor:not-allowed; }
.btn-qclose { background:#f0f3f8; color:#6a7a8a; border:1.5px solid #dde4ee; border-radius:7px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; margin-right:8px; }
.q-loading { text-align:center; padding:40px 0; color:#b0bcc8; }

/* ---- Internal Remarks Modal ---- */
#internalRemarksModal .modal-body { padding:22px; }
.ir-current-box { background:#f7f9fc; border-radius:8px; padding:14px; border-left:4px solid #f39c12; margin-bottom:14px; }
.ir-current-box .ir-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#f39c12; margin-bottom:6px; display:block; }
.ir-current-box .ir-text { font-size:13px; color:#2d3a4a; line-height:1.6; }
.ir-by { font-size:11px; color:#a0aab8; margin-top:8px; }
.ir-none-txt { font-size:12px; color:#b0bcc8; font-style:italic; }
</style>

<div class="page-content-wrapper">
<div class="page-content exp-wrap">

    <ul class="page-breadcrumb breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
        <li><span>User Expenses</span></li>
    </ul>

    <div class="exp-page-hd">
        <h2>Expense Management</h2>
        <p>Review, verify and approve employee expense claims</p>
    </div>

    {{-- FILTER --}}
    <div class="exp-filter-card">
        <form method="GET" action="{{ url('admin/user-expenses') }}">
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
                        <select name="month" class="form-control">
                            <option value="">All</option>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ date('M', mktime(0,0,0,$m,1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>Year</label>
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
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Requested"          {{ request('status') === 'Requested'          ? 'selected' : '' }}>Pending Approval</option>
                            <option value="Approved"           {{ request('status') === 'Approved'           ? 'selected' : '' }}>Approved</option>
                            <option value="Partially Approved" {{ request('status') === 'Partially Approved' ? 'selected' : '' }}>Partially Approved</option>
                            <option value="Rejected"           {{ request('status') === 'Rejected'           ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Verified</label>
                        <select name="verified" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('verified') === 'yes' ? 'selected' : '' }}>Verified</option>
                            <option value="no"  {{ request('verified') === 'no'  ? 'selected' : '' }}>Not Verified</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div style="white-space:nowrap;">
                            <button type="submit" class="btn-fa"><i class="fa fa-search"></i> Apply</button>
                            <a href="{{ url('admin/user-expenses') }}" class="btn-fr"><i class="fa fa-times"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- STATS --}}
    <div class="exp-stats">
        <div class="exp-stat">
            <div class="si" style="background:linear-gradient(135deg,#4d8fcc,#2d6faa);"><i class="fa fa-list"></i></div>
            <div><div class="st">Total</div><div class="sv">{{ $expenses->total() }}</div></div>
        </div>
        <div class="exp-stat">
            <div class="si" style="background:linear-gradient(135deg,#2ecc71,#1a9a50);"><i class="fa fa-eye"></i></div>
            <div><div class="st">Showing</div><div class="sv">{{ $expenses->firstItem() ?? 0 }}–{{ $expenses->lastItem() ?? 0 }}</div></div>
        </div>
        @if(request('status'))
        <div class="exp-stat" style="border-left:3px solid {{ request('status')==='Approved' ? '#2ecc71' : (request('status')==='Rejected' ? '#e74c3c' : (request('status')==='Partially Approved' ? '#f39c12' : '#b0bcc8')) }};">
            <div>
                <div class="st">Filtered</div>
                <div class="sv" style="font-size:13px;">{{ request('status')==='Requested' ? 'Pending' : request('status') }}</div>
            </div>
        </div>
        @endif
        @if(request('verified'))
        <div class="exp-stat" style="border-left:3px solid {{ request('verified')==='yes' ? '#2ecc71' : '#b0bcc8' }};">
            <div>
                <div class="st">Verification</div>
                <div class="sv" style="font-size:13px;">{{ request('verified')==='yes' ? 'Verified' : 'Not Verified' }}</div>
            </div>
        </div>
        @endif
    </div>

    {{-- LIST --}}
    <div class="exp-list">
        <div class="exp-grid exp-head">
            <div class="hc" style="text-align:center;">#</div>
            <div class="hc">Employee / Remarks</div>
            <div class="hc">Date &amp; Amount</div>
            <div class="hc text-right">Approved</div>
            <div class="hc text-center">Bill</div>
            <div class="hc text-center">Verified</div>
            <div class="hc text-center">Query</div>
            <div class="hc text-center">Status</div>
            <div class="hc text-center">Actions</div>
        </div>

        @forelse($expenses as $expense)
        @php
            $sk = str_replace(' ', '', $expense->status);
            $sk = ($sk === 'PartiallyApproved') ? 'Partially' : $sk;
            $receiptPath    = !empty($expense->image)             ? asset('ExpenseReceipts/'.$expense->user_id.'/'.$expense->image)             : null;
            $altReceiptPath = !empty($expense->alternative_image) ? asset('ExpenseReceipts/'.$expense->user_id.'/'.$expense->alternative_image) : null;
            $qCount   = $queryCounts[$expense->id]       ?? 0;
            $unread   = $unreadQueryCounts[$expense->id] ?? 0;
        @endphp

        <div class="exp-grid exp-row s-{{ $sk }}" id="row-{{ $expense->id }}">

            {{-- # --}}
            <div class="dc-id"><span class="id-num">{{ $expense->id }}</span></div>

            {{-- Employee + Remarks --}}
            <div class="dc">
                <div class="emp-n">{{ $expense->employee_name ?? 'N/A' }}</div>
                @if(!empty($expense->employee_mobile))
                    <div class="emp-m">{{ $expense->employee_mobile }}</div>
                @endif
                <div style="margin-top:4px;">
                    <span class="cat-t">{{ $expense->category_name }}</span>
                    @if($expense->missed_entry)
                        <span class="miss-t">Missed</span>
                    @endif
                </div>
                {{-- Missed entry reason --}}
                @if($expense->missed_entry && !empty($expense->missed_entry_reason))
                    <div style="margin-top:4px;">
                        <span class="missed-reason-txt" title="{{ $expense->missed_entry_reason }}">
                            <i class="fa fa-exclamation-circle" style="margin-right:3px;"></i>{{ \Illuminate\Support\Str::limit($expense->missed_entry_reason, 30) }}
                        </span>
                    </div>
                @endif
                {{-- User submitted remarks --}}
                @if(!empty($expense->remarks))
                    <div class="remarks-txt" title="{{ $expense->remarks }}">
                        <i class="fa fa-comment-o"></i> {{ \Illuminate\Support\Str::limit($expense->remarks, 40) }}
                    </div>
                @endif
            </div>

            {{-- Date + Amount + Travel stacked --}}
            <div class="dc">
                <div class="date-main">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</div>
                <div class="date-sub">{{ \Carbon\Carbon::parse($expense->created_at)->format('d M, h:i A') }}</div>
                <div style="margin-top:5px;">
                    <span class="a-req">&#8377;{{ number_format($expense->requested_amount, 2) }}</span>
                </div>
                @if($expense->is_travel && !empty($expense->travel_km))
                <div class="travel-sub">
                    <div class="tr-km"><i class="fa fa-road"></i> {{ $expense->travel_km }} km @ &#8377;{{ $expense->charge_per_km }}/km</div>
                    @if($expense->is_intercity && !empty($expense->intercity_route))
                        <div class="tr-rt" title="{{ $expense->intercity_route }}">{{ $expense->intercity_route }}</div>
                    @endif
                </div>
                @endif
            </div>

            {{-- Approved --}}
            <div class="dc text-right" id="appr-{{ $expense->id }}">
                @if($expense->approved_amount > 0)
                    <span class="a-apr">&#8377;{{ number_format($expense->approved_amount, 2) }}</span>
                @else
                    <span class="a-nil">&mdash;</span>
                @endif
            </div>

            {{-- Bill thumbnails --}}
            <div class="dc" style="padding:10px 5px; text-align:center;">
                <div style="text-align:center;">
                    @if($receiptPath)
                        <div class="r-thumb" onclick="openLb('{{ $receiptPath }}')" title="Bill">
                            <img src="{{ $receiptPath }}" alt="Bill" loading="lazy">
                            <div class="rto"><i class="fa fa-search-plus"></i></div>
                        </div>
                    @else
                        <div class="r-none" title="No receipt"><i class="fa fa-file-image-o"></i></div>
                    @endif
                    @if($altReceiptPath)
                        <div class="r-thumb" onclick="openLb('{{ $altReceiptPath }}')" title="Alt Bill">
                            <img src="{{ $altReceiptPath }}" alt="Alt Bill" loading="lazy">
                            <div class="rto"><i class="fa fa-search-plus"></i></div>
                        </div>
                    @else
                        <div class="r-none" title="No alt receipt" style="opacity:.35;"><i class="fa fa-file-image-o"></i></div>
                    @endif
                </div>
            </div>

            {{-- Verified checkbox + separate Internal Remarks button --}}
            <div class="vc">
                {{-- Checkbox: direct toggle, no popup --}}
                <div style="margin-bottom:3px;">
                    <input type="checkbox"
                        class="v-cb verify-checkbox"
                        data-id="{{ $expense->id }}"
                        {{ !empty($expense->verified_by) ? 'checked' : '' }}
                        title="{{ !empty($expense->verified_by) ? 'Verified by '.($expense->verified_by_name ?? 'Admin').'. Click to un-verify.' : 'Click to verify' }}">
                </div>
                <div>
                    <span class="v-lb {{ !empty($expense->verified_by) ? 'on' : 'off' }}" id="vlbl-{{ $expense->id }}">
                        {{ !empty($expense->verified_by) ? 'YES' : 'NO' }}
                    </span>
                </div>
                {{-- Separate internal remarks button --}}
                <div style="margin-top:4px;">
                    <button
                        class="btn-int-remarks btn-open-int-remarks {{ !empty($expense->internal_remarks) ? 'has-remark' : '' }}"
                        id="irbtn-{{ $expense->id }}"
                        data-id="{{ $expense->id }}"
                        data-remarks="{{ addslashes($expense->internal_remarks ?? '') }}"
                        data-verified-by="{{ addslashes($expense->verified_by_name ?? '') }}"
                        title="{{ !empty($expense->internal_remarks) ? 'Edit internal remarks' : 'Add internal remarks' }}"
                    ><i class="fa fa-pencil"></i> Note</button>
                </div>
            </div>

            {{-- Query button with unread blink --}}
            <div class="qc">
                <button class="btn-query btn-open-query"
                    data-id="{{ $expense->id }}"
                    data-employee="{{ $expense->employee_name ?? 'Employee' }}"
                    id="qbtn-{{ $expense->id }}"
                    title="View / Send Queries">
                    <i class="fa fa-comments"></i>
                    @if($unread > 0)
                        <span class="q-unread-dot" id="qdot-{{ $expense->id }}"></span>
                        <span class="q-badge" id="qbadge-{{ $expense->id }}">{{ $unread > 9 ? '9+' : $unread }}</span>
                    @elseif($qCount > 0)
                        <span class="q-badge" id="qbadge-{{ $expense->id }}" style="background:#8a9ab5;">{{ $qCount > 9 ? '9+' : $qCount }}</span>
                        <span class="q-unread-dot" id="qdot-{{ $expense->id }}" style="display:none;"></span>
                    @else
                        <span class="q-badge" id="qbadge-{{ $expense->id }}" style="display:none;">0</span>
                        <span class="q-unread-dot" id="qdot-{{ $expense->id }}" style="display:none;"></span>
                    @endif
                </button>
            </div>

            {{-- Status badge + approval remarks --}}
            <div class="sc" id="sc-{{ $expense->id }}">
                <span class="s-badge sb-{{ $sk }}" id="sbadge-{{ $expense->id }}">{{ $expense->status }}</span>
                @if(!empty($expense->admin_remarks))
                    <div class="appr-remarks-txt" id="appr-rmk-{{ $expense->id }}" title="{{ $expense->admin_remarks }}">
                        <i class="fa fa-comment"></i> {{ \Illuminate\Support\Str::limit($expense->admin_remarks, 28) }}
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
                    <i class="fa fa-pencil"></i> Update
                </button>
            </div>

        </div>
        @empty
        <div class="exp-empty">
            <i class="fa fa-inbox"></i>
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
    <span class="exp-lbx" onclick="closeLb()">&times;</span>
    <img src="" id="lbImg" alt="Receipt" onclick="event.stopPropagation()">
</div>


{{-- INTERNAL REMARKS MODAL (Add / Edit / View) --}}
<div class="modal fade" id="internalRemarksModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:430px;max-width:95vw;margin:80px auto;">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#f39c12,#d68910);padding:16px 22px;border:none;">
                <button type="button" class="close mod-close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title mod-title"><i class="fa fa-pencil"></i>&nbsp; Internal Remarks</h4>
            </div>
            <div class="modal-body" style="padding:22px;">
                <input type="hidden" id="ir_expense_id">
                {{-- Show existing remark (read-only preview) --}}
                <div class="ir-current-box" id="ir_current_wrap" style="display:none;">
                    <span class="ir-label"><i class="fa fa-lock"></i> Current Note</span>
                    <div class="ir-text" id="ir_current_text"></div>
                    <div class="ir-by" id="ir_current_by"></div>
                </div>
                <div class="m-remarks-box">
                    <label><i class="fa fa-pencil-square-o"></i>&nbsp; Edit / Add Internal Remark <span style="color:#aab;">(admin only)</span></label>
                    <textarea id="ir_textarea" rows="4" placeholder="e.g. Bill checked and confirmed, original submitted..."></textarea>
                </div>
                <div id="ir_err" class="m-err" style="display:none;margin-top:10px;"></div>
            </div>
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                <button type="button" id="btnSaveInternalRemarks" class="btn-msave" style="background:linear-gradient(135deg,#f39c12,#d68910);">
                    <i class="fa fa-save"></i> Save Remark
                </button>
            </div>
        </div>
    </div>
</div>


{{-- STATUS UPDATE MODAL --}}
<div class="modal fade" id="stModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:440px;max-width:95vw;margin:55px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-blue">
                <button type="button" class="close mod-close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title mod-title"><i class="fa fa-pencil-square-o"></i>&nbsp; Update Expense Status</h4>
            </div>
            <div class="modal-body" style="padding:22px;">
                <input type="hidden" id="m_id">
                <div class="m-amt">
                    <div><div class="ml">Requested Amount</div><div class="mv" id="m_req">—</div></div>
                    <i class="fa fa-money mi"></i>
                </div>
                <div class="m-slbl">Select New Status</div>
                <input type="hidden" id="m_status">
                <div class="m-sgrid">
                    <div class="m-sopt" data-v="Approved" onclick="pickSt('Approved')">
                        <span class="msicon" style="color:#2ecc71;">&#10003;</span>
                        <span class="msname">Approved</span>
                    </div>
                    <div class="m-sopt" data-v="Partially Approved" onclick="pickSt('Partially Approved')">
                        <span class="msicon" style="color:#f39c12;">&#9680;</span>
                        <span class="msname">Partial</span>
                    </div>
                    <div class="m-sopt" data-v="Rejected" onclick="pickSt('Rejected')">
                        <span class="msicon" style="color:#e74c3c;">&#10007;</span>
                        <span class="msname">Rejected</span>
                    </div>
                </div>
                <div class="m-pbox" id="m_partial" style="display:none;">
                    <label><i class="fa fa-inr"></i>&nbsp; Amount to Approve <span style="color:#e74c3c;">*</span></label>
                    <input type="number" id="m_appr" class="form-control" min="0" step="0.01" placeholder="0.00">
                    <span class="ph">Must not exceed requested amount.</span>
                </div>
                <div class="m-remarks-box">
                    <label><i class="fa fa-comment"></i>&nbsp; Admin Remarks <span style="color:#aab;">(optional)</span></label>
                    <textarea id="m_admin_remarks" rows="2" placeholder="Add a note for this decision..."></textarea>
                </div>
                <div id="m_err" class="m-err" style="display:none;margin-top:10px;"></div>
            </div>
            <div class="modal-footer modal-footer-plain">
                <button type="button" class="btn-mcancel" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                <button type="button" id="btnSave" class="btn-msave"><i class="fa fa-save"></i> Save Status</button>
            </div>
        </div>
    </div>
</div>


{{-- QUERY MODAL --}}
<div class="modal fade" id="queryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width:530px;max-width:96vw;margin:50px auto;">
        <div class="modal-content">
            <div class="modal-header mod-hdr-indigo">
                <button type="button" class="close mod-close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title mod-title">
                    <i class="fa fa-comments"></i>&nbsp;
                    Queries — <span id="q_employee_name"></span>
                    <small style="font-size:11px;opacity:.7;margin-left:6px;">(Expense #<span id="q_expense_id_label"></span>)</small>
                </h4>
            </div>
            <div class="modal-body" style="padding:0;">
                <input type="hidden" id="q_expense_id">
                <div class="q-chat-box" id="qChatBox">
                    <div class="q-loading"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
                </div>
                <div class="q-reply">
                    <label><i class="fa fa-reply"></i>&nbsp; Write a Query / Reply</label>
                    <textarea id="q_message" rows="3" placeholder="Type your query or reply here... (Ctrl+Enter to send)"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="padding:0 18px 16px;background:#fff;border-top:none;">
                <button type="button" class="btn-qclose" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                <button type="button" id="btnSendQuery" class="btn-qsend"><i class="fa fa-paper-plane"></i> Send</button>
            </div>
        </div>
    </div>
</div>

<script>
/* ---- Notify helper ---- */
function notify(type, msg) {
    if (typeof toastr !== "undefined") {
        toastr[type](msg);
    } else {
        var colors = { success:"#2ecc71", error:"#e74c3c", warning:"#f39c12", info:"#4d8fcc" };
        var el = document.createElement("div");
        el.innerHTML = msg;
        el.style.cssText = "position:fixed;top:20px;right:20px;z-index:999999;background:"+(colors[type]||"#4d8fcc")+";color:#fff;padding:11px 18px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.2);font-family:Inter,sans-serif;max-width:320px;opacity:0;transition:opacity .3s;";
        document.body.appendChild(el);
        setTimeout(function(){ el.style.opacity="1"; },10);
        setTimeout(function(){ el.style.opacity="0"; setTimeout(function(){ el.parentNode && el.parentNode.removeChild(el); },350); },3200);
    }
}

/* ---- Lightbox ---- */
function openLb(s){ document.getElementById('lbImg').src=s; document.getElementById('expLb').classList.add('on'); }
function closeLb(){ document.getElementById('expLb').classList.remove('on'); }
document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeLb(); });

/* ---- Status picker ---- */
function pickSt(v){
    document.querySelectorAll('.m-sopt').forEach(function(el){ el.classList.remove('sa','sp','sr'); });
    var k = v==='Partially Approved'?'sp':(v==='Approved'?'sa':'sr');
    document.querySelector('.m-sopt[data-v="'+v+'"]').classList.add(k);
    document.getElementById('m_status').value = v;
    document.getElementById('m_partial').style.display = v==='Partially Approved'?'block':'none';
    if(v!=='Partially Approved') document.getElementById('m_appr').value='';
    document.getElementById('m_err').style.display='none';
}

/* ---- Query helpers ---- */
function formatQDate(d){
    if(!d) return '';
    var dt = new Date(d.replace(' ','T'));
    return dt.toLocaleDateString('en-IN',{day:'2-digit',month:'short'})
           +' '+dt.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',hour12:true});
}
function renderBubble(q){
    var isAdmin = q.sender_type === 'admin';
    return '<div class="q-bubble '+(isAdmin?'admin':'employee')+'">'
        +'<div class="q-binner">'+escHtml(q.message)+'</div>'
        +'<div class="q-meta"><strong>'+(q.sender_name||'Unknown')+'</strong> &middot; '+formatQDate(q.created_at)+'</div>'
        +'</div>';
}
function escHtml(t){ return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function scrollChatBottom(){ var b=document.getElementById('qChatBox'); if(b) b.scrollTop=b.scrollHeight; }

$(document).ready(function(){

    /* ======================================================
       VERIFY CHECKBOX — instant direct toggle, no popup
       ====================================================== */
    $(document).on('change', '.verify-checkbox', function(){
        var $cb = $(this);
        var id  = $cb.data('id');
        var isChecking = $cb.prop('checked');

        // Optimistic UI — disable while saving
        $cb.prop('disabled', true);

        $.ajax({
            url: '/admin/user-expenses/'+id+'/toggle-verified',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(r){
                if(r.success){
                    var $l = $('#vlbl-'+id);
                    if(r.verified){
                        $cb.prop('checked', true);
                        $l.text('YES').attr('class','v-lb on');
                        notify('success', 'Marked as Verified');
                    } else {
                        $cb.prop('checked', false);
                        $l.text('NO').attr('class','v-lb off');
                        notify('info', 'Verification removed');
                    }
                } else {
                    // Revert on failure
                    $cb.prop('checked', !isChecking);
                    notify('error', 'Failed to update.');
                }
            },
            error: function(){
                $cb.prop('checked', !isChecking);
                notify('error', 'An error occurred.');
            },
            complete: function(){
                $cb.prop('disabled', false);
            }
        });
    });

    /* ======================================================
       INTERNAL REMARKS — separate button (pencil)
       ====================================================== */
    $(document).on('click', '.btn-open-int-remarks', function(){
        var id      = $(this).data('id');
        var remarks = $(this).data('remarks') || '';
        var vby     = $(this).data('verified-by') || '';

        $('#ir_expense_id').val(id);
        $('#ir_textarea').val(remarks);
        $('#ir_err').hide();

        // Show current remark preview if exists
        if(remarks && remarks.trim()){
            $('#ir_current_text').text(remarks);
            $('#ir_current_by').text(vby ? 'Verified by: '+vby : '');
            $('#ir_current_wrap').show();
        } else {
            $('#ir_current_wrap').hide();
        }

        $('#internalRemarksModal').modal('show');
        setTimeout(function(){ $('#ir_textarea').focus(); }, 400);
    });

    $('#btnSaveInternalRemarks').on('click', function(){
        var id      = $('#ir_expense_id').val();
        var remarks = $('#ir_textarea').val().trim();
        var $btn    = $(this);

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $('#ir_err').hide();

        $.ajax({
            url: '/admin/user-expenses/'+id+'/internal-remarks',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', internal_remarks: remarks },
            success: function(r){
                if(r.success){
                    var $irBtn = $('#irbtn-'+id);
                    // Update button data & style
                    $irBtn.data('remarks', remarks);
                    if(remarks){
                        $irBtn.addClass('has-remark').attr('title','Edit internal remarks');
                    } else {
                        $irBtn.removeClass('has-remark').attr('title','Add internal remarks');
                    }
                    $('#internalRemarksModal').modal('hide');
                    notify('success', 'Internal remark saved.');
                } else {
                    $('#ir_err').html('<i class="fa fa-exclamation-circle"></i> '+(r.message||'Failed to save.')).show();
                }
            },
            error: function(x){
                var m = 'An error occurred.';
                if(x.responseJSON && x.responseJSON.errors) m = Object.values(x.responseJSON.errors).flat().join('<br>');
                $('#ir_err').html('<i class="fa fa-exclamation-circle"></i> '+m).show();
            },
            complete: function(){
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Remark');
            }
        });
    });

    /* ======================================================
       STATUS MODAL
       ====================================================== */
    $(document).on('click', '.btn-update-status', function(){
        var id   = $(this).data('id');
        var st   = $(this).data('status');
        var req  = $(this).data('requested');
        var appr = $(this).data('approved');
        var rmks = $(this).data('admin-remarks') || '';

        $('#m_id').val(id);
        $('#m_req').text('\u20B9'+parseFloat(req).toFixed(2));
        $('#m_status').val('');
        $('#m_appr').val('');
        $('#m_admin_remarks').val(rmks);
        $('#m_partial').hide();
        $('#m_err').hide();
        document.querySelectorAll('.m-sopt').forEach(function(el){ el.classList.remove('sa','sp','sr'); });

        if(st==='Approved'||st==='Partially Approved'||st==='Rejected'){
            pickSt(st);
            if(st==='Partially Approved') $('#m_appr').val(appr);
        }
        $('#stModal').modal('show');
    });

    $('#btnSave').on('click', function(){
        var id   = $('#m_id').val();
        var st   = $('#m_status').val();
        var appr = $('#m_appr').val();
        var rmks = $('#m_admin_remarks').val().trim();
        var $b   = $(this);

        if(!st){ $('#m_err').html('<i class="fa fa-exclamation-circle"></i> Please select a status.').show(); return; }
        if(st==='Partially Approved'){
            var apprVal = parseFloat(appr);
            var reqVal  = parseFloat($('#m_req').text().replace('\u20B9','').replace(/,/g,''));
            if(!appr||apprVal<=0){ $('#m_err').html('<i class="fa fa-exclamation-circle"></i> Please enter a valid approved amount.').show(); return; }
            if(apprVal>reqVal){ $('#m_err').html('<i class="fa fa-exclamation-circle"></i> Approved amount (\u20b9'+apprVal.toFixed(2)+') cannot exceed requested (\u20b9'+reqVal.toFixed(2)+').').show(); return; }
        }

        $b.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $('#m_err').hide();

        $.ajax({
            url: '/admin/user-expenses/'+id+'/update-status',
            type: 'POST',
            data: { _token:'{{ csrf_token() }}', status:st, approved_amount:appr, admin_remarks:rmks },
            success: function(r){
                if(r.success){
                    var k = st==='Partially Approved'?'Partially':st;
                    $('#sbadge-'+id).attr('class','s-badge sb-'+k).text(st);
                    $('#row-'+id).removeClass('s-Requested s-Approved s-Partially s-Rejected').addClass('s-'+k);

                    if(st==='Approved'||st==='Partially Approved'){
                        var a = st==='Partially Approved'?parseFloat(appr):parseFloat($('#m_req').text().replace('\u20B9','').replace(/,/g,''));
                        $('#appr-'+id).html('<span class="a-apr">\u20B9'+a.toFixed(2)+'</span>');
                    } else {
                        $('#appr-'+id).html('<span class="a-nil">&mdash;</span>');
                    }

                    var $rmkEl = $('#appr-rmk-'+id);
                    if(rmks){
                        $rmkEl.html('<i class="fa fa-comment"></i> '+rmks.substring(0,28)+(rmks.length>28?'..':'')).attr('title',rmks).show();
                    } else {
                        $rmkEl.hide();
                    }

                    $('.btn-update-status[data-id="'+id+'"]').data('status',st).data('approved',st==='Partially Approved'?appr:0).data('admin-remarks',rmks);
                    $('#stModal').modal('hide');
                    notify('success', 'Status updated successfully!');
                } else { $('#m_err').html(r.message||'Something went wrong.').show(); }
            },
            error: function(x){
                var m='An error occurred.';
                if(x.responseJSON&&x.responseJSON.errors) m=Object.values(x.responseJSON.errors).flat().join('<br>');
                $('#m_err').html(m).show();
            },
            complete: function(){ $b.prop('disabled',false).html('<i class="fa fa-save"></i> Save Status'); }
        });
    });

    /* ======================================================
       QUERY MODAL
       ====================================================== */
    $(document).on('click', '.btn-open-query', function(){
        var id   = $(this).data('id');
        var name = $(this).data('employee');

        $('#q_expense_id').val(id);
        $('#q_expense_id_label').text(id);
        $('#q_employee_name').text(name);
        $('#q_message').val('');
        $('#qChatBox').html('<div class="q-loading"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $('#queryModal').modal('show');

        $.ajax({
            url: '/admin/user-expenses/'+id+'/queries',
            type: 'GET',
            success: function(r){
                if(r.success){
                    if(r.queries.length===0){
                        $('#qChatBox').html('<div class="q-empty"><i class="fa fa-comments-o"></i><span>No queries yet. Start a conversation.</span></div>');
                    } else {
                        var html=''; $.each(r.queries,function(i,q){ html+=renderBubble(q); });
                        $('#qChatBox').html(html); scrollChatBottom();
                    }
                    $('#qbadge-'+id).hide();
                    $('#qdot-'+id).hide();
                }
            },
            error: function(){
                $('#qChatBox').html('<div class="q-empty"><i class="fa fa-exclamation-circle"></i><span>Failed to load.</span></div>');
            }
        });
    });

    $('#btnSendQuery').on('click', function(){
        var id  = $('#q_expense_id').val();
        var msg = $('#q_message').val().trim();
        var $b  = $(this);

        if(!msg){ notify('warning','Please enter a message.'); $('#q_message').focus(); return; }
        $b.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: '/admin/user-expenses/'+id+'/raise-query',
            type: 'POST',
            data: { _token:'{{ csrf_token() }}', message:msg },
            success: function(r){
                if(r.success){
                    $('#qChatBox .q-empty').remove();
                    $('#qChatBox').append(renderBubble(r.query));
                    scrollChatBottom();
                    $('#q_message').val('');
                    var $badge = $('#qbadge-'+id);
                    $badge.text(r.total>9?'9+':r.total).css('background','#8a9ab5').show();
                    notify('success','Query sent!');
                } else { notify('error', r.message||'Failed to send.'); }
            },
            error: function(){ notify('error','An error occurred.'); },
            complete: function(){ $b.prop('disabled',false).html('<i class="fa fa-paper-plane"></i> Send'); }
        });
    });

    $('#q_message').on('keydown',function(e){ if(e.ctrlKey&&e.key==='Enter') $('#btnSendQuery').trigger('click'); });

});
</script>
@endsection