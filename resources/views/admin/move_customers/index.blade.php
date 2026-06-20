@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    /* ── Base ─────────────────────────────────── */
    .page-content { padding-bottom: 90px !important; }

    .portlet.light.bordered {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    }

    /* ── Search / Filter Bar ─────────────────── */
    .top-filter-bar {
        background: #f8f9fb;
        border: 1px solid #e1e5ec;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .top-filter-bar label {
        font-weight: 600;
        color: #4a5568;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
        white-space: nowrap;
    }
    .top-filter-bar .search-input {
        border-radius: 6px !important;
        border: 1px solid #c8d0dc;
        height: 38px;
        font-size: 14px;
        padding: 6px 12px;
        min-width: 220px;
        outline: none;
    }
    .top-filter-bar .search-input:focus {
        border-color: #3598dc;
        box-shadow: 0 0 0 3px rgba(53,152,220,0.12);
    }
    .emp-count-badge {
        margin-left: auto;
        font-size: 12px;
        color: #718096;
        white-space: nowrap;
    }

    /* ── Employee Table ──────────────────────── */
    .emp-table-wrap {
        border: 1px solid #e1e5ec;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .emp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .emp-table thead th {
        background: #f0f4f9;
        color: #4a5568;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 11px 14px;
        border-bottom: 2px solid #dce4f0;
        white-space: nowrap;
    }
    .emp-table thead th:first-child { width: 46px; text-align: center; }
    .emp-table thead th.th-num  { text-align: center; }
    .emp-table thead th.th-cnt  { text-align: center; min-width: 90px; }
    .emp-table thead th.th-act  { text-align: center; min-width: 140px; }

    .emp-table tbody tr {
        border-bottom: 1px solid #f0f4f8;
        transition: background 0.15s;
    }
    .emp-table tbody tr:last-child { border-bottom: none; }
    .emp-table tbody tr:hover { background: #fafcff; }
    .emp-table tbody tr.row-active { background: #f0f7ff; }

    .emp-table td { padding: 11px 14px; vertical-align: middle; }
    .emp-table td.td-no { text-align: center; color: #a0aec0; font-size: 12px; }
    .emp-table td.td-cnt { text-align: center; }

    /* Employee name cell */
    .emp-cell { display: flex; align-items: center; gap: 10px; }
    .emp-avatar-sm {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: #3598dc;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700;
        flex-shrink: 0;
    }
    .emp-name-text  { font-weight: 600; color: #2d3748; line-height: 1.2; }
    .emp-desig-text { font-size: 11px; color: #a0aec0; }
    .badge-inactive {
        display: inline-block;
        font-size: 10px; padding: 1px 7px;
        background: #fed7d7; color: #c53030;
        border-radius: 8px !important;
        font-weight: 700; margin-left: 4px;
    }

    /* Count pills */
    .cnt-pill {
        display: inline-block;
        padding: 3px 11px;
        border-radius: 10px !important;
        font-size: 11px; font-weight: 700;
        min-width: 34px; text-align: center;
    }
    .cnt-pill.direct { background: #e6fffa; color: #276749; }
    .cnt-pill.open   { background: #fffbeb; color: #975a16; }
    .cnt-pill.dealer { background: #ebf8ff; color: #2b6cb0; }
    .cnt-pill.total  { background: #edf2f7; color: #4a5568; }
    .cnt-zero        { color: #cbd5e0; font-size: 12px; }

    /* Move button */
    .btn-move-row {
        border-radius: 20px !important;
        font-size: 12px; font-weight: 700;
        padding: 6px 16px;
        letter-spacing: 0.3px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-move-row.state-idle {
        background: #3598dc; color: #fff;
        box-shadow: 0 2px 6px rgba(53,152,220,0.3);
    }
    .btn-move-row.state-idle:hover {
        background: #2980b9;
        box-shadow: 0 4px 10px rgba(53,152,220,0.4);
        transform: translateY(-1px);
    }
    .btn-move-row.state-active {
        background: #e53e3e; color: #fff;
        box-shadow: 0 2px 6px rgba(229,62,62,0.3);
    }
    .btn-move-row.state-active:hover {
        background: #c53030;
        box-shadow: 0 4px 10px rgba(229,62,62,0.4);
        transform: translateY(-1px);
    }

    /* No results row */
    .no-emp-row td {
        text-align: center;
        padding: 36px 20px;
        color: #a0aec0;
        font-size: 13px;
    }
    .no-emp-row i { font-size: 28px; display: block; margin-bottom: 8px; }

    /* ── Customers Panel (inline, below table) ── */
    #customers-panel { display: none; margin-top: 4px; }

    .panel-header-bar {
        display: flex; align-items: center; gap: 12px;
        background: linear-gradient(135deg, #3598dc 0%, #2980b9 100%);
        border-radius: 8px 8px 0 0;
        padding: 12px 20px;
        flex-wrap: wrap;
    }
    .panel-header-bar .ph-title {
        font-weight: 700; color: #fff; font-size: 14px;
        display: flex; align-items: center; gap: 8px;
    }
    .panel-header-bar .ph-close {
        margin-left: auto;
        background: rgba(255,255,255,0.2);
        border: none; color: #fff;
        width: 28px; height: 28px;
        border-radius: 50%; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
        transition: background 0.2s;
        flex-shrink: 0;
    }
    .panel-header-bar .ph-close:hover { background: rgba(255,255,255,0.35); }

    /* ── Filter Bar ─────────────────────────────────────────── */
    .filter-bar {
        display: flex; align-items: center; gap: 10px;
        background: #f8f9fb; border: 1px solid #e1e5ec;
        border-top: none;
        padding: 10px 16px;
        flex-wrap: wrap;
    }
    .filter-bar label { font-size: 12px; font-weight: 600; color: #4a5568; margin: 0; white-space: nowrap; }
    .filter-bar select { border-radius: 6px !important; font-size: 13px; height: 36px; min-width: 180px; border: 1px solid #c8d0dc; }
    .filter-count { font-size: 12px; color: #718096; }
    .btn-export-pdf {
        margin-left: auto;
        border-radius: 6px !important;
        font-size: 12px; font-weight: 700;
        padding: 6px 16px;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    /* ── Employee Group Card ────────────────────────────────── */
    .customers-body {
        border: 1px solid #e1e5ec;
        border-top: none;
        border-radius: 0 0 8px 8px;
        overflow: hidden;
    }
    .employee-group {
        border-bottom: 1px solid #edf2f7;
        overflow: hidden;
    }
    .employee-group:last-child { border-bottom: none; }

    .employee-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 18px;
        background: #eef3fb;
        border-bottom: 1px solid #dce4f0;
        cursor: pointer;
        transition: background 0.2s;
    }
    .employee-header:hover { background: #e3ecf9; }
    .employee-header.is-root {
        background: linear-gradient(135deg, #3598dc 0%, #2980b9 100%);
        border-bottom: none;
    }
    .employee-header.is-root .emp-name,
    .employee-header.is-root .emp-designation { color: #fff !important; }
    .employee-header.is-root .badge-count { background: rgba(255,255,255,0.25); color: #fff; }

    .emp-left { display: flex; align-items: center; gap: 12px; }
    .emp-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: #3598dc; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 700;
        flex-shrink: 0;
    }
    .is-root .emp-avatar { background: rgba(255,255,255,0.3); }
    .emp-name        { font-weight: 600; color: #2d3748; font-size: 14px; line-height: 1.2; }
    .emp-designation { font-size: 12px; color: #718096; }

    .emp-right { display: flex; align-items: center; gap: 12px; }
    .badge-count {
        background: #3598dc; color: #fff;
        padding: 2px 10px; border-radius: 12px !important;
        font-size: 11px; font-weight: 700;
    }

    .select-all-wrap {
        display: flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; color: #4a5568;
    }
    .is-root .select-all-wrap { color: #fff; }
    .select-all-cb { transform: scale(1.2); cursor: pointer; accent-color: #3598dc; }

    .chevron-icon { color: #888; transition: transform 0.25s; font-size: 13px; }
    .employee-header.collapsed .chevron-icon { transform: rotate(-90deg); }

    /* Customer items */
    .customer-list { padding: 0; }
    .customer-item {
        display: flex; align-items: center;
        padding: 11px 18px;
        border-bottom: 1px solid #f0f4f8;
        transition: background 0.15s;
    }
    .customer-item:last-child { border-bottom: none; }
    .customer-item:hover { background: #fafcff; }
    .customer-item.is-selected { background: #f0f8ff; }

    .cust-no {
        width: 26px; height: 26px;
        background: #e2e8f0; color: #4a5568;
        border-radius: 50%; display: flex;
        align-items: center; justify-content: center;
        font-size: 10px; font-weight: 700;
        flex-shrink: 0; margin-right: 12px;
    }
    .cust-cb-wrap { margin-right: 14px; display: flex; align-items: center; flex-shrink: 0; }
    .cust-cb      { transform: scale(1.2); cursor: pointer; accent-color: #3598dc; }
    .cust-info    { flex: 1 1 0; min-width: 0; }
    .cust-center  { flex: 1 1 0; min-width: 0; padding-left: 16px; border-left: 1px solid #edf2f7; }
    .cust-right   { flex: 1 1 0; min-width: 0; display: flex; justify-content: flex-end; align-items: center; }

    .cust-name { font-weight: 500; color: #2d3748; font-size: 13px; display: block; line-height: 1.3; }
    .cust-meta { font-size: 11px; color: #a0aec0; display: block; margin-top: 1px; }

    .cust-center-city {
        font-size: 12px; font-weight: 600; color: #276749;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .cust-center-city i { font-size: 10px; color: #52b788; }

    .cust-badge {
        display: inline-block;
        font-size: 11px; padding: 3px 10px;
        border-radius: 10px !important;
        font-weight: 600; white-space: nowrap; flex-shrink: 0;
        background: #ebf8ff; color: #2b6cb0;
    }
    .cust-badge.bm-direct { background: #e6fffa; color: #276749; }
    .cust-badge.bm-open   { background: #fffbeb; color: #975a16; }
    .cust-badge.bm-dealer { background: #ebf8ff; color: #2b6cb0; }

    .empty-state { text-align: center; padding: 30px 20px; color: #a0aec0; }
    .empty-state i { font-size: 32px; margin-bottom: 10px; display: block; }

    /* Loader */
    #loader { display:none; text-align:center; padding:40px; }
    #loader .spinner { font-size:30px; color:#3598dc; }

    /* ── Floating Move Bar ──────────────────────────────────── */
    .floating-save-bar {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        padding: 14px 0;
        border-top: 1px solid #e1e5ec;
        z-index: 1050;
        box-shadow: 0 -4px 15px rgba(0,0,0,0.08);
    }
    .save-bar-inner {
        display: flex; align-items: center;
        justify-content: center; gap: 20px; flex-wrap: wrap;
    }
    .selected-counter {
        display: inline-flex; align-items: center; gap: 8px;
        background: #ebf5ff; border: 1px solid #3598dc;
        padding: 6px 16px; border-radius: 20px !important;
    }
    .selected-counter .cnt-label { font-size: 12px; color: #3598dc; font-weight: 600; text-transform: uppercase; }
    .selected-counter .cnt-val   { font-size: 16px; color: #2b78ad; font-weight: 800; }

    .move-to-wrap { display: flex; align-items: center; gap: 10px; }
    .move-to-wrap label { font-size: 13px; font-weight: 600; color: #4a5568; margin: 0; white-space: nowrap; }
    .move-to-wrap select { border-radius: 6px !important; font-size: 13px; height: 38px; min-width: 220px; }

    .btn-move {
        padding: 9px 30px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        border-radius: 25px !important; font-size: 13px;
        box-shadow: 0 4px 12px rgba(53,152,220,0.35);
        transition: all 0.2s;
    }
    .btn-move:hover    { box-shadow: 0 6px 18px rgba(53,152,220,0.45); transform: translateY(-1px); }
    .btn-move:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* Alerts */
    .alert { border-radius: 6px !important; }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">

            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-exchange font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">{{ $title }}</span>
                </div>
            </div>

            <div class="portlet-body">

                {{-- Flash Messages --}}
                @if(Session::has('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-check-circle"></i> {{ Session::get('success') }}
                    </div>
                @endif
                @if(Session::has('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-exclamation-circle"></i> {{ Session::get('error') }}
                    </div>
                @endif

                {{-- ── Employee Search / Filter Bar ── --}}
                <div class="top-filter-bar">
                    <label><i class="fa fa-search"></i> &nbsp;Search Employee:</label>
                    <input type="text"
                           id="emp-search"
                           class="form-control search-input"
                           placeholder="Type name or designation...">
                    <span class="emp-count-badge">
                        Showing <strong id="emp-visible-count">{{ count($employeeStats) }}</strong>
                        of {{ count($employeeStats) }} employees
                    </span>
                </div>

                {{-- ── Employee Table ── --}}
                <div class="emp-table-wrap">
                    <table class="emp-table" id="emp-table">
                        <thead>
                            <tr>
                                <th class="th-num">Sr.No</th>
                                <th>Employee Name</th>
                                <th class="th-cnt">Direct Customer</th>
                                <th class="th-cnt">Open</th>
                                <th class="th-cnt">Dealer</th>
                                <th class="th-cnt">Total</th>
                                <th class="th-act">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="emp-tbody">
                            @forelse($employeeStats as $i => $emp)
                                <tr class="emp-row" data-user-id="{{ $emp->id }}" data-name="{{ strtolower($emp->name) }}" data-desig="{{ strtolower($emp->designation ?? '') }}">
                                    <td class="td-no">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="emp-cell">
                                            <div class="emp-avatar-sm">{{ substr($emp->name, 0, 1) }}</div>
                                            <div>
                                                <div class="emp-name-text">
                                                    {{ $emp->name }}
                                                    @if(!$emp->status)
                                                        <span class="badge-inactive">Inactive</span>
                                                    @endif
                                                </div>
                                                @if($emp->designation)
                                                    <div class="emp-desig-text">{{ $emp->designation }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="td-cnt">
                                        @if($emp->direct_count > 0)
                                            <span class="cnt-pill direct">{{ $emp->direct_count }}</span>
                                        @else
                                            <span class="cnt-zero">—</span>
                                        @endif
                                    </td>
                                    <td class="td-cnt">
                                        @if($emp->open_count > 0)
                                            <span class="cnt-pill open">{{ $emp->open_count }}</span>
                                        @else
                                            <span class="cnt-zero">—</span>
                                        @endif
                                    </td>
                                    <td class="td-cnt">
                                        @if($emp->dealer_count > 0)
                                            <span class="cnt-pill dealer">{{ $emp->dealer_count }}</span>
                                        @else
                                            <span class="cnt-zero">—</span>
                                        @endif
                                    </td>
                                    <td class="td-cnt">
                                        @if($emp->customer_count > 0)
                                            <span class="cnt-pill total">{{ $emp->customer_count }}</span>
                                        @else
                                            <span class="cnt-zero">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if($emp->customer_count > 0)
                                            <button type="button"
                                                    class="btn-move-row state-idle"
                                                    data-user-id="{{ $emp->id }}"
                                                    data-user-name="{{ $emp->name }}">
                                                <i class="fa fa-exchange"></i> More Details
                                            </button>
                                        @else
                                            <span style="font-size:11px;color:#cbd5e0;font-style:italic;">No customers</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:30px; color:#a0aec0;">
                                        <i class="fa fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        No employees with customers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ── Inline Customers Panel ── --}}
                <div id="customers-panel">

                    {{-- Panel header --}}
                    <div class="panel-header-bar">
                        <div class="ph-title">
                            <i class="fa fa-users"></i>
                            <span id="panel-title-text">Customers</span>
                        </div>
                        <button class="ph-close" id="btn-close-panel" title="Close">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    {{-- Filter bar --}}
                    <div class="filter-bar">
                        <label><i class="fa fa-filter"></i> &nbsp;Business Model:</label>
                        <select id="bm-filter" class="form-control">
                            <option value="">-- All Models --</option>
                            <option value="Direct Customer">Direct Customer</option>
                            <option value="Open">Open</option>
                            {{-- Dealer options injected by JS --}}
                        </select>

                        <label style="margin-left:4px;"><i class="fa fa-map-marker"></i> &nbsp;City:</label>
                        <select id="city-filter" class="form-control">
                            <option value="">-- All Cities --</option>
                        </select>

                        <span class="filter-count">
                            Showing <strong id="filter-visible-count">0</strong> customers
                        </span>

                        <a href="#" id="btn-export-pdf"
                           class="btn btn-danger btn-sm btn-export-pdf" target="_blank">
                            <i class="fa fa-file-pdf-o"></i> Export PDF
                        </a>
                    </div>

                    {{-- Loader --}}
                    <div id="loader" style="border:1px solid #e1e5ec; border-top:none; border-radius: 0 0 8px 8px;">
                        <i class="fa fa-circle-o-notch fa-spin spinner"></i>
                        <p style="color:#a0aec0; margin-top:10px;">Loading customers...</p>
                    </div>

                    {{-- Customers Container --}}
                    <div class="customers-body" id="customers-container"></div>

                </div>{{-- /customers-panel --}}

            </div>{{-- /portlet-body --}}
        </div>{{-- /portlet --}}
    </div>
</div>

{{-- ── Floating Move Bar ── --}}
<div class="floating-save-bar" id="floating-bar" style="display:none;">
    <form method="POST" action="{{ route('admin.move-customers.move') }}" id="move-form">
        @csrf
        <input type="hidden" name="from_user_id"      id="from-user-id-input"      value="">
        <input type="hidden" name="bm_filter"          id="bm-filter-hidden"         value="">
        <input type="hidden" name="city_filter"        id="city-filter-hidden"       value="">
        <input type="hidden" name="source_employee_id" id="source-employee-hidden"   value="">
        <div id="hidden-customer-inputs"></div>

        <div class="save-bar-inner">
            <div class="selected-counter">
                <span class="cnt-label">Selected:</span>
                <span class="cnt-val" id="selected-count">0</span>
            </div>

            <div class="move-to-wrap">
                <label><i class="fa fa-arrow-right"></i> Move To:</label>
                <select name="to_user_id" class="form-control" id="move-to-select" required>
                    <option value="">-- Select Target Employee --</option>
                    @foreach($moveToUsers as $emp)
                        <option value="{{ $emp->id }}">
                            {{ $emp->name }}
                            @if($emp->designation) ({{ $emp->designation }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-success btn-move" id="btn-move" disabled>
                <i class="fa fa-exchange"></i> More Details
            </button>
        </div>
    </form>
</div>

<script>
$(document).ready(function () {

    var currentSourceUserId = null;
    var exportPdfBase       = '{{ route("admin.move-customers.export-pdf") }}';

    /* ════════════════════════════════════════
       EMPLOYEE SEARCH FILTER
    ════════════════════════════════════════ */
    $('#emp-search').on('input', function () {
        var q = $(this).val().toLowerCase().trim();
        var visible = 0;
        $('.emp-row').each(function () {
            var name  = $(this).data('name')  || '';
            var desig = $(this).data('desig') || '';
            var show  = !q || name.indexOf(q) !== -1 || desig.indexOf(q) !== -1;
            $(this).toggle(show);
            if (show) visible++;
        });
        $('#emp-visible-count').text(visible);

        // Show no-results row if needed
        var $noRow = $('#emp-no-results');
        if (visible === 0) {
            if ($noRow.length === 0) {
                $('#emp-tbody').append(
                    '<tr id="emp-no-results" class="no-emp-row"><td colspan="7">' +
                    '<i class="fa fa-search"></i>No employees match "<strong>' + escHtml(q) + '</strong>".</td></tr>'
                );
            }
        } else {
            $noRow.remove();
        }
    });

    /* ════════════════════════════════════════
       MOVE CUSTOMERS BUTTON (per row)
    ════════════════════════════════════════ */
    $(document).on('click', '.btn-move-row', function () {
        var userId   = $(this).data('user-id');
        var userName = $(this).data('user-name');

        // If already active row — close panel
        if ($(this).hasClass('state-active')) {
            closePanel();
            return;
        }

        // Reset all other buttons
        $('.btn-move-row').removeClass('state-active').addClass('state-idle')
            .html('<i class="fa fa-exchange"></i> More Details');
        $('.emp-row').removeClass('row-active');

        // Activate this row
        $(this).removeClass('state-idle').addClass('state-active')
               .html('<i class="fa fa-times"></i> Close');
        $(this).closest('tr').addClass('row-active');

        loadCustomers(userId, userName, null, null);
    });

    /* ════════════════════════════════════════
       CLOSE PANEL
    ════════════════════════════════════════ */
    $('#btn-close-panel').on('click', function () {
        closePanel();
    });

    function closePanel() {
        $('.btn-move-row').removeClass('state-active').addClass('state-idle')
            .html('<i class="fa fa-exchange"></i> More Details');
        $('.emp-row').removeClass('row-active');
        $('#customers-panel').slideUp(200);
        resetSelectionUI();
        currentSourceUserId = null;
    }

    /* ════════════════════════════════════════
       LOAD CUSTOMERS
    ════════════════════════════════════════ */
    function loadCustomers(userId, userName, bmFilterToRestore, cityFilterToRestore) {
        currentSourceUserId = userId;
        $('#from-user-id-input').val(userId);
        $('#source-employee-hidden').val(userId);

        $('#panel-title-text').text('Customers — ' + userName);

        $('#customers-container').hide().empty();
        $('#loader').show();
        if ($('#customers-panel').is(':visible')) {
            // Already open — just scroll back to top of panel immediately
            $('html, body').stop(true).animate({ scrollTop: $('#customers-panel').offset().top - 10 }, 300);
        } else {
            $('#customers-panel').slideDown(250);
        }
        resetFilters();
        resetSelectionUI();

        $.ajax({
            url:    '{{ route("admin.move-customers.load-customers") }}',
            method: 'GET',
            data:   { user_id: userId },
            success: function (resp) {
                $('#loader').hide();
                if (!resp.success || !resp.data || resp.data.length === 0) {
                    $('#customers-container').html(
                        '<div class="empty-state" style="padding:40px;"><i class="fa fa-inbox"></i><p>No customers found for this employee.</p></div>'
                    ).show();
                    scrollToPanel();
                    return;
                }

                renderCustomerGroups(resp.data);
                $('#customers-container').show();
                populateFilters(resp.data);

                var restoreBm   = bmFilterToRestore   || '';
                var restoreCity = cityFilterToRestore  || '';
                $('#bm-filter').val(restoreBm);
                $('#city-filter').val(restoreCity);

                if (restoreBm || restoreCity) {
                    applyFilter(true);
                } else {
                    updateFilterCount();
                }
                updatePdfExportLink();
                scrollToPanel();
            },
            error: function () {
                $('#loader').hide();
                $('#customers-container').html(
                    '<div class="empty-state" style="padding:40px;"><i class="fa fa-exclamation-circle"></i><p>Failed to load customers. Please try again.</p></div>'
                ).show();
                scrollToPanel();
            }
        });
    }

    /* ════════════════════════════════════════
       RENDER GROUPS
    ════════════════════════════════════════ */
    function renderCustomerGroups(groups) {
        var html = '';
        $.each(groups, function (i, group) {
            var isRoot     = group.is_root;
            var customers  = group.customers || [];
            var collapseId = 'collapse-' + group.user_id;
            var initials   = getInitials(group.user_name);

            html += '<div class="employee-group" data-user-id="' + group.user_id + '">';

            /* Header */
            html += '<div class="employee-header' + (isRoot ? ' is-root' : '') + '" data-toggle-collapse="' + collapseId + '">';
            html += '  <div class="emp-left">';
            html += '    <div class="emp-avatar">' + initials + '</div>';
            html += '    <div>';
            html += '      <div class="emp-name">' + escHtml(group.user_name) + (isRoot ? ' <small style="font-weight:400;opacity:.8">(Selected)</small>' : '') + '</div>';
            html += '      <div class="emp-designation">' + escHtml(group.designation || '') + '</div>';
            html += '    </div>';
            html += '  </div>';
            html += '  <div class="emp-right">';
            html += '    <span class="badge-count group-total-badge" data-group="' + group.user_id + '">' + customers.length + ' Customers</span>';
            html += '    <div class="select-all-wrap" onclick="event.stopPropagation();">';
            html += '      <input type="checkbox" class="select-all-cb" data-group="' + group.user_id + '" title="Select / Deselect All">';
            html += '      <span>All</span>';
            html += '    </div>';
            html += '    <i class="fa fa-chevron-down chevron-icon"></i>';
            html += '  </div>';
            html += '</div>';

            /* Customer list */
            html += '<div class="customer-list" id="' + collapseId + '">';
            if (customers.length === 0) {
                html += '<div class="empty-state"><i class="fa fa-inbox"></i><p>No customers assigned.</p></div>';
            } else {
                $.each(customers, function (ci, cust) {
                    var cbVal       = group.user_id + '_' + cust.customer_id;
                    var bmVal       = cust.business_model || 'Open';
                    var dealerBmVal = (bmVal === 'Dealer' && cust.dealer_business_name) ? cust.dealer_business_name : '';
                    var bmClass = 'bm-open';
                    var bmLabel = bmVal;
                    if (bmVal === 'Direct Customer') { bmClass = 'bm-direct'; bmLabel = 'Direct Customer'; }
                    else if (bmVal === 'Open')        { bmClass = 'bm-open';   bmLabel = 'Open'; }
                    else if (bmVal === 'Dealer')      { bmClass = 'bm-dealer'; bmLabel = cust.dealer_business_name || 'Dealer'; }

                    html += '<div class="customer-item" id="ci-' + cbVal + '" data-bm="' + escHtml(bmVal) + '" data-dealer-bm="' + escHtml(dealerBmVal) + '" data-city="' + escHtml(cust.city_name || '') + '">';
                    html += '  <div class="cust-no">' + (ci + 1) + '</div>';
                    html += '  <div class="cust-cb-wrap"><input type="checkbox" class="cust-cb" data-group="' + group.user_id + '" value="' + cbVal + '"></div>';
                    html += '  <div class="cust-info">';
                    html += '    <span class="cust-name">' + escHtml(cust.customer_name) + '</span>';
                    html += '    <span class="cust-meta">';
                    if (cust.contact_person_name) html += escHtml(cust.contact_person_name);
                    if (cust.designation)         html += ' &middot; ' + escHtml(cust.designation);
                    if (cust.department)          html += ' &middot; ' + escHtml(cust.department);
                    html += '    </span>';
                    html += '  </div>';
                    html += '  <div class="cust-center">';
                    if (cust.city_name) {
                        html += '<div class="cust-center-city"><i class="fa fa-map-marker"></i>' + escHtml(cust.city_name) + '</div>';
                    }
                    html += '  </div>';
                    html += '  <div class="cust-right"><span class="cust-badge ' + bmClass + '">' + escHtml(bmLabel) + '</span></div>';
                    html += '</div>';
                });
            }
            html += '</div></div>'; // /customer-list /employee-group
        });

        $('#customers-container').html(html);
        bindEvents();
    }

    /* ════════════════════════════════════════
       BIND EVENTS
    ════════════════════════════════════════ */
    function bindEvents() {
        $(document).off('click', '.employee-header').on('click', '.employee-header', function () {
            var targetId = $(this).data('toggle-collapse');
            $('#' + targetId).slideToggle(200);
            $(this).toggleClass('collapsed');
        });

        $(document).off('change', '.select-all-cb').on('change', '.select-all-cb', function () {
            var group   = $(this).data('group');
            var checked = $(this).is(':checked');
            $('.cust-cb[data-group="' + group + '"]').each(function () {
                if ($(this).closest('.customer-item').is(':visible')) {
                    $(this).prop('checked', checked);
                    var val = $(this).val();
                    $('#ci-' + val).toggleClass('is-selected', checked);
                }
            });
            updateSelectionUI();
        });

        $(document).off('change', '.cust-cb').on('change', '.cust-cb', function () {
            var group = $(this).data('group');
            syncSelectAll(group);
            updateSelectionUI();
            var val = $(this).val();
            $('#ci-' + val).toggleClass('is-selected', $(this).is(':checked'));
        });
    }

    function syncSelectAll(group) {
        var $visible = $('.cust-cb[data-group="' + group + '"]').filter(function () {
            return $(this).closest('.customer-item').is(':visible');
        });
        var total   = $visible.length;
        var checked = $visible.filter(':checked').length;
        var $allCb  = $('.select-all-cb[data-group="' + group + '"]');
        $allCb.prop('checked',       total > 0 && checked === total);
        $allCb.prop('indeterminate', checked > 0 && checked < total);
    }

    /* ════════════════════════════════════════
       FLOATING BAR
    ════════════════════════════════════════ */
    function updateSelectionUI() {
        var $checked = $('.cust-cb:checked');
        var count    = $checked.length;
        $('#selected-count').text(count);

        var $hidden = $('#hidden-customer-inputs').empty();
        $checked.each(function () {
            $hidden.append('<input type="hidden" name="customer_ids[]" value="' + $(this).val() + '">');
        });

        $('#bm-filter-hidden').val($('#bm-filter').val());
        $('#city-filter-hidden').val($('#city-filter').val());
        $('#source-employee-hidden').val(currentSourceUserId || '');

        if (count > 0) {
            $('#floating-bar').slideDown(200);
            $('#btn-move').prop('disabled', false);
        } else {
            $('#floating-bar').slideUp(200);
            $('#btn-move').prop('disabled', true);
        }
    }

    function resetSelectionUI() {
        $('#selected-count').text(0);
        $('#hidden-customer-inputs').empty();
        $('#floating-bar').hide();
        $('#btn-move').prop('disabled', true);
        updatePdfExportLink();
    }

    function resetFilters() {
        $('#bm-filter').val('');
        $('#bm-filter option:gt(2)').remove();
        $('#city-filter').val('');
        $('#city-filter option:gt(0)').remove();
        $('#filter-visible-count').text(0);
    }

    /* ════════════════════════════════════════
       FORM SUBMIT
    ════════════════════════════════════════ */
    $('#move-form').on('submit', function (e) {
        var toUser = $('#move-to-select').val();
        var count  = $('.cust-cb:checked').length;

        if (!toUser) {
            e.preventDefault();
            alert('Please select a target employee to move customers to.');
            return;
        }
        if (count === 0) {
            e.preventDefault();
            alert('Please select at least one customer to move.');
            return;
        }

        var allSameUser = true;
        $('.cust-cb:checked').each(function () {
            if ($(this).val().split('_')[0] != toUser) { allSameUser = false; return false; }
        });
        if (allSameUser) {
            e.preventDefault();
            alert('All selected customers already belong to the target employee.');
            return;
        }
        if (!confirm('Move ' + count + ' customer(s) to the selected employee? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    /* ════════════════════════════════════════
       FILTERS
    ════════════════════════════════════════ */
    function populateFilters(groups) {
        var dealers = {}, cities = {};
        $.each(groups, function (i, group) {
            $.each(group.customers || [], function (j, cust) {
                if (cust.business_model === 'Dealer' && cust.dealer_business_name)
                    dealers[cust.dealer_business_name] = true;
                if (cust.city_name) cities[cust.city_name] = true;
            });
        });
        $('#bm-filter option:gt(2)').remove();
        $.each(Object.keys(dealers).sort(), function (i, name) {
            $('#bm-filter').append('<option value="' + name + '">' + name + '</option>');
        });
        $('#city-filter option:gt(0)').remove();
        $.each(Object.keys(cities).sort(), function (i, name) {
            $('#city-filter').append('<option value="' + name + '">' + name + '</option>');
        });
    }

    function applyFilter(silent) {
        var bmVal   = $('#bm-filter').val();
        var cityVal = $('#city-filter').val();
        var groupCounts = {};

        $('.customer-item').each(function () {
            var bm       = $(this).data('bm')        || '';
            var dealerBm = $(this).data('dealer-bm') || '';
            var city     = $(this).data('city')      || '';
            var bmMatch  = !bmVal   || bm === bmVal || dealerBm === bmVal;
            var cityMatch= !cityVal || city === cityVal;
            var show     = bmMatch && cityMatch;
            $(this).toggle(show);

            var groupId = $(this).closest('.employee-group').data('user-id');
            if (!groupCounts[groupId]) groupCounts[groupId] = { visible: 0, total: 0 };
            groupCounts[groupId].total++;
            if (show) groupCounts[groupId].visible++;
        });

        var isFiltered = bmVal || cityVal;
        $.each(groupCounts, function (groupId, counts) {
            var $badge = $('.group-total-badge[data-group="' + groupId + '"]');
            $badge.text(isFiltered
                ? counts.visible + ' / ' + counts.total + ' Customers'
                : counts.total + ' Customers');
            syncSelectAll(groupId);
        });

        updateFilterCount();
        if (!silent) {
            $('#bm-filter-hidden').val(bmVal);
            $('#city-filter-hidden').val(cityVal);
        }
        updatePdfExportLink();
    }

    function updateFilterCount() {
        $('#filter-visible-count').text($('.customer-item:visible').length);
    }

    $('#bm-filter, #city-filter').on('change', function () { applyFilter(false); });

    /* ════════════════════════════════════════
       PDF EXPORT
    ════════════════════════════════════════ */
    function updatePdfExportLink() {
        if (!currentSourceUserId) { $('#btn-export-pdf').attr('href', '#'); return; }
        var href = exportPdfBase + '?user_id=' + encodeURIComponent(currentSourceUserId);
        var bm   = $('#bm-filter').val();
        var city = $('#city-filter').val();
        if (bm)   href += '&bm_filter='   + encodeURIComponent(bm);
        if (city) href += '&city_filter=' + encodeURIComponent(city);
        $('#btn-export-pdf').attr('href', href);
    }

    /* ════════════════════════════════════════
       HELPERS
    ════════════════════════════════════════ */
    function getInitials(name) {
        return name.trim().split(' ').slice(0, 2).map(function (w) { return w[0].toUpperCase(); }).join('');
    }
    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ════════════════════════════════════════
       SCROLL TO BOTTOM OF CUSTOMER PANEL
    ════════════════════════════════════════ */
    function scrollToPanel() {
        setTimeout(function () {
            var top = $('#customers-panel').offset().top - 10;
            $('html, body').stop(true).animate({ scrollTop: top }, 400);
        }, 50);
    }

});
</script>
@endsection