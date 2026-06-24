@extends('layouts.adminLayout.backendLayout')
@section('content')

{{-- ════════════════════════════════════════════════════════════════════
     CHANNEL PARTNERS — redesigned to match the Expense Management UI
     · Clean white card surface, soft slate background
     · Pill summary chips (Total / Showing)
     · Refined filter bar with labelled fields
     · Icon-only action column with hover tooltips (native title)
     · "Send Link" + "Onboard" stage actions removed
═══════════════════════════════════════════════════════════════════════ --}}

<style>
    /* ── Page surface ─────────────────────────────────────────── */
    .cp-wrap          { --ink:#1f2d3d; --muted:#8a97a8; --line:#e8edf3; --soft:#f4f6fa;
                        --brand:#2f6fed; --brand-soft:#eaf1ff;
                        --green:#1f9d6b; --green-soft:#e7f7ef;
                        --amber:#b6841f; --amber-soft:#fdf3dd;
                        --blue:#1d6fd0;  --blue-soft:#e6f0fc;
                        --red:#d64550;   --red-soft:#fde9eb;
                        --purple:#6f42c1;--purple-soft:#efeaff;
                        font-family:inherit; color:var(--ink); }

    .cp-card          { background:#fff; border:1px solid var(--line); border-radius:14px;
                        box-shadow:0 1px 2px rgba(31,45,61,.04); }

    /* ── Header strip ─────────────────────────────────────────── */
    .cp-head          { display:flex; align-items:center; justify-content:space-between;
                        padding:18px 22px; border-bottom:1px solid var(--line); }
    .cp-head h2       { margin:0; font-size:17px; font-weight:700; letter-spacing:.2px;
                        color:var(--brand); display:flex; align-items:center; gap:10px; }
    .cp-head h2 i     { font-size:16px; }
    .cp-head .cp-addbtn { background:var(--green); color:#fff; border:none; border-radius:9px;
                        padding:9px 16px; font-size:13px; font-weight:600; text-decoration:none;
                        display:inline-flex; align-items:center; gap:7px; transition:.15s; }
    .cp-head .cp-addbtn:hover { background:#178457; color:#fff; }

    /* ── Summary chips ────────────────────────────────────────── */
    .cp-summary       { display:flex; gap:12px; flex-wrap:wrap; padding:18px 22px 4px; }
    .cp-chip          { display:inline-flex; align-items:center; gap:9px; background:#fff;
                        border:1px solid var(--line); border-radius:11px; padding:11px 16px;
                        font-size:13px; color:var(--muted); }
    .cp-chip .dot     { width:9px; height:9px; border-radius:50%; }
    .cp-chip b        { color:var(--ink); font-size:15px; font-weight:700; }
    .dot-total   { background:var(--brand); }
    .dot-eval    { background:var(--amber); }
    .dot-onb     { background:var(--blue); }
    .dot-conf    { background:var(--green); }

    /* ── Filter bar ───────────────────────────────────────────── */
    .cp-filter        { margin:14px 22px 0; background:var(--soft); border:1px solid var(--line);
                        border-radius:12px; padding:16px 18px; }
    .cp-filter .frow  { display:flex; gap:16px; flex-wrap:wrap; align-items:flex-end; }
    .cp-field         { display:flex; flex-direction:column; gap:6px; flex:1; min-width:150px; }
    .cp-field label   { font-size:11px; font-weight:600; letter-spacing:.5px; text-transform:uppercase;
                        color:var(--muted); display:flex; align-items:center; gap:6px; }
    .cp-field input,
    .cp-field select  { height:40px; border:1px solid #dbe2ec; border-radius:9px; padding:0 12px;
                        font-size:13px; background:#fff; color:var(--ink); outline:none; transition:.15s; }
    .cp-field input:focus,
    .cp-field select:focus { border-color:var(--brand); box-shadow:0 0 0 3px var(--brand-soft); }
    .cp-actions-f     { display:flex; gap:8px; }
    .cp-btn           { height:40px; border:none; border-radius:9px; padding:0 18px; font-size:13px;
                        font-weight:600; cursor:pointer; display:inline-flex; align-items:center;
                        gap:7px; text-decoration:none; transition:.15s; }
    .cp-btn.apply     { background:var(--brand); color:#fff; }
    .cp-btn.apply:hover { background:#1f5cd4; }
    .cp-btn.reset     { background:#fff; color:var(--ink); border:1px solid #dbe2ec; }
    .cp-btn.reset:hover { background:#f0f3f8; }

    /* ── Table ────────────────────────────────────────────────── */
    .cp-tablewrap     { padding:18px 22px 22px; }
    .cp-table         { width:100%; border-collapse:separate; border-spacing:0;
                        border:1px solid var(--line); border-radius:10px; overflow:hidden; }
    .cp-table thead th{ background:var(--soft); font-size:11px; font-weight:700; letter-spacing:.5px;
                        text-transform:uppercase; color:var(--muted); padding:13px 14px; text-align:left;
                        border-bottom:1px solid var(--line); border-right:1px solid var(--line); white-space:nowrap; }
    .cp-table thead th:last-child  { border-right:none; }
    .cp-table tbody td{ padding:14px; border-bottom:1px solid var(--line); border-right:1px solid var(--line);
                        font-size:13px; vertical-align:middle; }
    .cp-table tbody td:last-child  { border-right:none; }
    .cp-table tbody tr:last-child td { border-bottom:none; }
    .cp-table tbody tr:hover td { background:#fafcff; }

    .cp-biz           { font-weight:700; color:var(--ink); }
    .cp-biz small     { display:block; font-weight:400; color:var(--muted); margin-top:2px; }
    .cp-mobile        { color:var(--muted); }
    .cp-count         { display:inline-flex; min-width:26px; height:24px; padding:0 7px;
                        align-items:center; justify-content:center; background:var(--soft);
                        border:1px solid var(--line); border-radius:7px; font-weight:600; font-size:12px; }

    /* badges */
    .badge-pill       { display:inline-block; padding:4px 11px; border-radius:20px; font-size:10.5px;
                        font-weight:700; letter-spacing:.4px; text-transform:uppercase; }
    .type-primary     { background:var(--purple-soft); color:var(--purple); }
    .type-sub         { background:#fff1e6; color:#d2691e; }
    .stage-evaluation { background:var(--amber-soft); color:var(--amber); }
    .stage-onboarding { background:var(--blue-soft);  color:var(--blue); }
    .stage-confirmed  { background:var(--green-soft); color:var(--green); }
    .status-active    { background:var(--green-soft); color:var(--green); }
    .status-inactive  { background:#eef1f5; color:#8a97a8; }
    .form-flag        { display:inline-flex; align-items:center; gap:4px; font-size:10px;
                        color:var(--green); margin-top:5px; }

    /* ── Icon action buttons w/ tooltip ───────────────────────── */
    .cp-iconbar       { display:flex; gap:6px; flex-wrap:wrap; }
    .cp-ico           { position:relative; width:32px; height:32px; border-radius:8px;
                        display:inline-flex; align-items:center; justify-content:center;
                        border:1px solid var(--line); background:#fff; color:#56657a;
                        font-size:13px; text-decoration:none; cursor:pointer; transition:.15s; }
    .cp-ico:hover     { transform:translateY(-1px); box-shadow:0 3px 8px rgba(31,45,61,.12); }
    .cp-ico.i-edit:hover    { background:var(--green-soft);  color:var(--green);  border-color:var(--green); }
    .cp-ico.i-link:hover    { background:var(--purple-soft); color:var(--purple); border-color:var(--purple); }
    .cp-ico.i-stock:hover   { background:var(--red-soft);    color:var(--red);    border-color:var(--red); }
    .cp-ico.i-disc:hover    { background:var(--green-soft);  color:var(--green);  border-color:var(--green); }
    .cp-ico.i-qty:hover     { background:var(--blue-soft);   color:var(--blue);   border-color:var(--blue); }
    .cp-ico.i-users:hover   { background:var(--amber-soft);  color:var(--amber);  border-color:var(--amber); }
    .cp-ico.i-pin:hover     { background:var(--red-soft);    color:var(--red);    border-color:var(--red); }

    /* tooltip */
    .cp-ico[data-tip]:hover::after {
        content:attr(data-tip); position:absolute; bottom:calc(100% + 7px); left:50%;
        transform:translateX(-50%); background:#1f2d3d; color:#fff; font-size:11px;
        font-weight:500; letter-spacing:.2px; padding:5px 9px; border-radius:6px;
        white-space:nowrap; z-index:30; pointer-events:none; }
    .cp-ico[data-tip]:hover::before {
        content:''; position:absolute; bottom:calc(100% + 2px); left:50%;
        transform:translateX(-50%); border:5px solid transparent; border-top-color:#1f2d3d; z-index:30; }

    /* empty state */
    .cp-empty         { text-align:center; padding:54px 20px; color:var(--muted); }
    .cp-empty i       { font-size:40px; opacity:.35; }
    .cp-empty p       { margin:14px 0 16px; font-size:14px; }

    /* footer */
    .cp-foot          { display:flex; align-items:center; justify-content:space-between;
                        flex-wrap:wrap; gap:10px; margin-top:16px; }
    .cp-foot .text-muted { font-size:13px; color:var(--muted); }
    .cp-foot .pagination { margin:0; }

    @media (max-width:768px){
        .cp-field { min-width:100%; }
        .cp-tablewrap { overflow-x:auto; }
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content cp-wrap" style="background:#eef1f6; padding:22px;">

        {{-- Breadcrumb --}}
        <ul class="page-breadcrumb breadcrumb" style="background:transparent; padding:0 2px 14px;">
            <li><a href="{{ url('admin/dashboard') }}" style="color:var(--brand);">Dashboard</a><i class="fa fa-angle-right" style="margin:0 8px; color:#b8c2cf;"></i></li>
            <li><span style="color:var(--muted);">Channel Partners</span></li>
        </ul>

        {{-- Flash --}}
        @if(Session::has('flash_message_error'))
            <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Error!</strong> {!! session('flash_message_error') !!}</div>
        @endif
        @if(Session::has('flash_message_success'))
            <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Success!</strong> {!! session('flash_message_success') !!}</div>
        @endif
        @if(isset($_GET['s']))
            <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Success!</strong> Record saved successfully.</div>
        @endif

        @php
            $totalAll  = \App\Dealer::whereNull('parent_id')->count();
            $totalEval = \App\Dealer::whereNull('parent_id')->where('stage','evaluation')->count();
            $totalOnb  = \App\Dealer::whereNull('parent_id')->where('stage','onboarding')->count();
            $totalConf = \App\Dealer::whereNull('parent_id')->where('stage','confirmed')->count();
        @endphp

        <div class="cp-card">

            {{-- Header --}}
            <div class="cp-head">
                <h2><i class="fa fa-handshake-o"></i> Channel Partner Management</h2>
                <a href="{{ route('admin.channel-partners.create') }}" class="cp-addbtn">
                    <i class="fa fa-plus"></i> Add Partner
                </a>
            </div>

            {{-- Summary chips --}}
            <div class="cp-summary">
                <div class="cp-chip"><span class="dot dot-total"></span> Total Partners <b>{{ $totalAll }}</b></div>
                <div class="cp-chip"><span class="dot dot-eval"></span> Evaluation <b>{{ $totalEval }}</b></div>
                <div class="cp-chip"><span class="dot dot-onb"></span> Onboarding <b>{{ $totalOnb }}</b></div>
                <div class="cp-chip"><span class="dot dot-conf"></span> Confirmed <b>{{ $totalConf }}</b></div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.channel-partners.index') }}">
                <div class="cp-filter">
                    <div class="frow">
                        <div class="cp-field">
                            <label><i class="fa fa-building-o"></i> Business Name</label>
                            <input type="text" name="business_name" placeholder="Search business name…" value="{{ request('business_name') }}">
                        </div>
                        <div class="cp-field">
                            <label><i class="fa fa-flag-o"></i> Stage</label>
                            <select name="stage">
                                <option value="">All Stages</option>
                                <option value="evaluation" {{ request('stage')=='evaluation' ? 'selected':'' }}>Evaluation</option>
                                <option value="onboarding" {{ request('stage')=='onboarding' ? 'selected':'' }}>Onboarding</option>
                                <option value="confirmed"  {{ request('stage')=='confirmed'  ? 'selected':'' }}>Confirmed</option>
                            </select>
                        </div>
                        <div class="cp-field">
                            <label><i class="fa fa-tag"></i> Type</label>
                            <select name="dealer_type">
                                <option value="">All Types</option>
                                <option value="dealer"     {{ request('dealer_type')=='dealer'     ? 'selected':'' }}>Primary Dealer</option>
                                <option value="sub dealer" {{ request('dealer_type')=='sub dealer' ? 'selected':'' }}>Sub Dealer</option>
                            </select>
                        </div>
                        <div class="cp-field">
                            <label><i class="fa fa-map-marker"></i> City</label>
                            <input type="text" name="city" placeholder="City…" value="{{ request('city') }}">
                        </div>
                        <div class="cp-actions-f">
                            <button type="submit" class="cp-btn apply"><i class="fa fa-filter"></i> Apply</button>
                            <a href="{{ route('admin.channel-partners.index') }}" class="cp-btn reset"><i class="fa fa-refresh"></i> Reset</a>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="cp-tablewrap">
                <table class="cp-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Business</th>
                            <th>Type</th>
                            <th>City</th>
                            <th>Mobile</th>
                            <th>Stage</th>
                            <th class="text-center">Cust.</th>
                            <th class="text-center">Prod.</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($dealers as $dealer)
                        @php
                            $stage = $dealer->stage ?? 'evaluation';
                            $stageClass = ['evaluation'=>'stage-evaluation','onboarding'=>'stage-onboarding','confirmed'=>'stage-confirmed'][$stage] ?? 'stage-evaluation';
                            $isSub = $dealer->dealer_type === 'sub dealer';
                        @endphp
                        <tr id="dealer-row-{{ $dealer->id }}">
                            <td><span class="cp-mobile">{{ $dealer->id }}</span></td>
                            <td>
                                <span class="cp-biz">{{ $dealer->business_name ?: '—' }}</span>
                            </td>
                            <td>
                                @if($isSub)
                                    <span class="badge-pill type-sub">Sub</span>
                                @else
                                    <span class="badge-pill type-primary">Primary</span>
                                @endif
                            </td>
                            <td>{{ $dealer->city ?: '—' }}</td>
                            <td class="cp-mobile">{{ $dealer->owner_mobile ?: '—' }}</td>
                            <td>
                                <span class="badge-pill {{ $stageClass }}">{{ ucfirst($stage) }}</span>
                                @if($stage === 'onboarding' && $dealer->onboarding_form_submitted)
                                    <span class="form-flag"><i class="fa fa-check-circle"></i> Form submitted</span>
                                @endif
                            </td>
                            <td class="text-center"><span class="cp-count">{{ $dealer->customers_count ?? 0 }}</span></td>
                            <td class="text-center"><span class="cp-count">{{ $dealer->linked_products_count ?? 0 }}</span></td>
                            <td>
                                <span id="status-badge-{{ $dealer->id }}" class="badge-pill {{ $dealer->status ? 'status-active' : 'status-inactive' }}">
                                    {{ $dealer->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="cp-iconbar">
                                    <a href="{{ route('admin.channel-partners.edit', $dealer->id) }}"
                                       class="cp-ico i-edit" data-tip="Edit Partner">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="{{ route('admin.dealers.products', $dealer->id) }}"
                                       class="cp-ico i-link" data-tip="Link Products">
                                        <i class="fa fa-link"></i>
                                    </a>
                                    <a href="{{ url('/admin/manage-dealer-stock/'.$dealer->id) }}"
                                       class="cp-ico i-stock" data-tip="Manage Stock">
                                        <i class="fa fa-cubes"></i>
                                    </a>
                                    <a href="{{ url('/admin/dealer-special-discount/'.$dealer->id) }}"
                                       class="cp-ico i-disc" data-tip="Special Discount">
                                        <i class="fa fa-tags"></i>
                                    </a>
                                    <a href="{{ url('/admin/qty-discounts?dealer_id='.$dealer->id) }}"
                                       class="cp-ico i-qty" data-tip="Quantity Discounts">
                                        <i class="fa fa-list-ol"></i>
                                    </a>
                                    <a href="{{ url('/admin/dealer-users/'.$dealer->id) }}"
                                       class="cp-ico i-users" data-tip="Add-on Users">
                                        <i class="fa fa-users"></i>
                                    </a>
                                    @if(!empty($dealer->hash_salt))
                                        <a href="{{ url('admin/dealer-reset-pin/'.$dealer->id) }}"
                                           class="cp-ico i-pin" data-tip="Reset PIN"
                                           onclick="return confirm('Reset PIN for this partner?');">
                                            <i class="fa fa-key"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="cp-empty">
                                    <i class="fa fa-inbox"></i>
                                    <p>No channel partners found.</p>
                                    <a href="{{ route('admin.channel-partners.create') }}" class="cp-addbtn" style="display:inline-flex;">
                                        <i class="fa fa-plus"></i> Add First Partner
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                {{-- Footer / pagination --}}
                <div class="cp-foot">
                    <div>
                        @if($dealers->total() > 0)
                            <span class="text-muted">Showing {{ $dealers->firstItem() }}–{{ $dealers->lastItem() }} of {{ $dealers->total() }} records</span>
                        @endif
                    </div>
                    <div>{{ $dealers->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection