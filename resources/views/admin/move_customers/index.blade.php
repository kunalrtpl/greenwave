@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    /* ── Layout & Base ─────────────────────────────────────── */
    .page-content { padding-bottom: 90px !important; }

    .portlet.light.bordered {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.07);
    }

    /* ── Top Control Panel ─────────────────────────────────── */
    .control-panel {
        background: #f8f9fb;
        border: 1px solid #e1e5ec;
        border-radius: 8px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .control-panel .form-group { margin-bottom: 0; }
    .control-panel label {
        font-weight: 600;
        color: #4a5568;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: block;
    }
    .control-panel select.form-control {
        border-radius: 6px !important;
        border: 1px solid #c8d0dc;
        height: 42px;
        font-size: 14px;
    }


    /* ── Employee Group Card ────────────────────────────────── */
    .employee-group {
        border: 1px solid #e1e5ec;
        border-radius: 8px;
        margin-bottom: 18px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }

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
    .employee-header.is-root .emp-designation,
    .employee-header.is-root .emp-count { color: #fff !important; }
    .employee-header.is-root .badge-count { background: rgba(255,255,255,0.25); color: #fff; }

    .emp-left { display: flex; align-items: center; gap: 12px; }
    .emp-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: #3598dc;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 700;
        flex-shrink: 0;
    }
    .is-root .emp-avatar { background: rgba(255,255,255,0.3); }

    .emp-name { font-weight: 600; color: #2d3748; font-size: 14px; line-height: 1.2; }
    .emp-designation { font-size: 12px; color: #718096; }

    .emp-right { display: flex; align-items: center; gap: 12px; }
    .badge-count {
        background: #3598dc;
        color: #fff;
        padding: 2px 10px;
        border-radius: 12px !important;
        font-size: 11px;
        font-weight: 700;
    }

    /* Select All Checkbox Area */
    .select-all-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #4a5568;
    }
    .is-root .select-all-wrap { color: #fff; }
    .select-all-cb { transform: scale(1.2); cursor: pointer; accent-color: #3598dc; }

    .chevron-icon { color: #888; transition: transform 0.25s; font-size: 13px; }
    .employee-header.collapsed .chevron-icon { transform: rotate(-90deg); }

    /* ── Customer List ──────────────────────────────────────── */
    .customer-list { padding: 0; }

    .customer-item {
        display: flex;
        align-items: center;
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
    .cust-cb { transform: scale(1.2); cursor: pointer; accent-color: #3598dc; }

    /* ── Three equal columns ── */
    .cust-info   { flex: 1 1 0; min-width: 0; }
    .cust-center { flex: 1 1 0; min-width: 0; padding-left: 16px; border-left: 1px solid #edf2f7; }
    .cust-right  { flex: 1 1 0; min-width: 0; display: flex; justify-content: flex-end; align-items: center; }

    .cust-name { font-weight: 500; color: #2d3748; font-size: 13px; display: block; line-height: 1.3; }
    .cust-meta { font-size: 11px; color: #a0aec0; display: block; margin-top: 1px; }

    .cust-center-city {
        font-size: 12px;
        font-weight: 600;
        color: #276749;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .cust-center-city i { font-size: 10px; color: #52b788; }

    .cust-badge {
        display: inline-block;
        font-size: 11px; padding: 3px 10px;
        border-radius: 10px !important;
        font-weight: 600; white-space: nowrap;
        flex-shrink: 0;
        background: #ebf8ff; color: #2b6cb0;
    }
    .cust-badge.bm-direct { background: #e6fffa; color: #276749; }
    .cust-badge.bm-open   { background: #fffbeb; color: #975a16; }
    .cust-badge.bm-dealer { background: #ebf8ff; color: #2b6cb0; }

    /* Empty State */
    .empty-state {
        text-align: center; padding: 30px 20px; color: #a0aec0;
    }
    .empty-state i { font-size: 32px; margin-bottom: 10px; display: block; }

    /* ── Placeholder before load ────────────────────────────── */
    #customers-placeholder {
        text-align: center;
        padding: 50px 20px;
        color: #a0aec0;
        border: 2px dashed #e1e5ec;
        border-radius: 8px;
    }
    #customers-placeholder i { font-size: 40px; margin-bottom: 12px; display: block; }

    /* ── Floating Save Bar ──────────────────────────────────── */
    .floating-save-bar {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        padding: 14px 0;
        border-top: 1px solid #e1e5ec;
        z-index: 1050;
        box-shadow: 0 -4px 15px rgba(0,0,0,0.08);
    }
    .save-bar-inner {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .selected-counter {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ebf5ff;
        border: 1px solid #3598dc;
        padding: 6px 16px;
        border-radius: 20px !important;
    }
    .selected-counter .cnt-label { font-size: 12px; color: #3598dc; font-weight: 600; text-transform: uppercase; }
    .selected-counter .cnt-val { font-size: 16px; color: #2b78ad; font-weight: 800; }

    .move-to-wrap { display: flex; align-items: center; gap: 10px; }
    .move-to-wrap label { font-size: 13px; font-weight: 600; color: #4a5568; margin: 0; white-space: nowrap; }
    .move-to-wrap select { border-radius: 6px !important; font-size: 13px; height: 38px; min-width: 220px; }

    .btn-move {
        padding: 9px 30px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 25px !important;
        font-size: 13px;
        box-shadow: 0 4px 12px rgba(53,152,220,0.35);
        transition: all 0.2s;
    }
    .btn-move:hover { box-shadow: 0 6px 18px rgba(53,152,220,0.45); transform: translateY(-1px); }
    .btn-move:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* ── Loader ─────────────────────────────────────────────── */
    #loader { display: none; text-align: center; padding: 40px; }
    #loader .spinner { font-size: 30px; color: #3598dc; }

    /* ── Business Model Badges ─────────────────────────────── */
    .bm-badge {
        display: inline-block;
        font-size: 10px; font-weight: 700;
        padding: 2px 8px; border-radius: 10px !important;
        white-space: nowrap; margin-left: 6px;
    }
    .bm-direct  { background: #e6fffa; color: #276749; }
    .bm-open    { background: #fffbeb; color: #975a16; }
    .bm-dealer  { background: #ebf4ff; color: #2c5282; }

    .dealer-name {
        font-size: 10px; color: #4a5568; font-style: italic; margin-left: 4px;
    }

    /* ── Filter Bar ─────────────────────────────────────────── */
    .filter-bar {
        display: flex; align-items: center; gap: 10px;
        background: #f8f9fb; border: 1px solid #e1e5ec;
        border-radius: 8px; padding: 10px 16px;
        margin-bottom: 16px; flex-wrap: wrap;
    }
    .filter-bar label { font-size: 12px; font-weight: 600; color: #4a5568; margin: 0; white-space: nowrap; }
    .filter-bar select { border-radius: 6px !important; font-size: 13px; height: 36px; min-width: 200px; border: 1px solid #c8d0dc; }
    .filter-count { font-size: 12px; color: #718096; margin-left: 6px; }

    .cust-badge.cust-city-badge { background: #f0fff4; color: #276749; }

    /* ── Alerts ─────────────────────────────────────────────── */
    .alert { border-radius: 6px !important; }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">

            {{-- Portlet Header --}}
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

                {{-- ── Step 1: Select Source Employee ── --}}
                <div class="control-panel">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label><i class="fa fa-user"></i> &nbsp;Select Source Employee</label>
                                <select id="source-employee" class="form-control">
                                    <option value="">-- Choose Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">
                                            {{ $emp->name }}
                                            @if($emp->designation)
                                                ({{ $emp->designation }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ── Customer Groups Area ── --}}
                <div id="loader">
                    <i class="fa fa-circle-o-notch fa-spin spinner"></i>
                    <p style="color:#a0aec0; margin-top:10px;">Loading customers...</p>
                </div>

                <div id="customers-placeholder">
                    <i class="fa fa-users"></i>
                    <p>Select an employee above to load their customers.</p>
                </div>

                <div id="filter-bar-wrapper" style="display:none;">
                    <div class="filter-bar">
                        <label><i class="fa fa-filter"></i> &nbsp;Business Model:</label>
                        <select id="bm-filter" class="form-control">
                            <option value="">-- All Models --</option>
                            <option value="Direct Customer">Direct Customer</option>
                            <option value="Open">Open</option>
                            {{-- Dealer options injected by JS --}}
                        </select>

                        <label style="margin-left:6px;"><i class="fa fa-map-marker"></i> &nbsp;City:</label>
                        <select id="city-filter" class="form-control">
                            <option value="">-- All Cities --</option>
                            {{-- City options injected by JS --}}
                        </select>

                        <span class="filter-count">Showing <strong id="filter-visible-count">0</strong> customers</span>
                    </div>
                </div>

                <div id="customers-container" style="display:none;"></div>

            </div>{{-- /portlet-body --}}
        </div>{{-- /portlet --}}
    </div>{{-- /page-content --}}
</div>{{-- /page-content-wrapper --}}

{{-- ── Floating Move Bar ── --}}
<div class="floating-save-bar" id="floating-bar" style="display:none;">
    <form method="POST" action="{{ route('admin.move-customers.move') }}" id="move-form">
        @csrf
        <input type="hidden" name="from_user_id" id="from-user-id-input" value="">
        <input type="hidden" name="bm_filter" id="bm-filter-hidden" value="">
        <input type="hidden" name="city_filter" id="city-filter-hidden" value="">
        <input type="hidden" name="source_employee_id" id="source-employee-hidden" value="">
        {{-- Customer IDs injected by JS --}}
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
                <i class="fa fa-exchange"></i> Move Customers
            </button>
        </div>
    </form>
</div>

<script>
$(document).ready(function () {

    var currentSourceUserId = null;

    /* ─── On Page Load: restore employee + filter from URL params ── */
    (function restoreFromUrl() {
        var urlParams    = new URLSearchParams(window.location.search);
        var savedEmployee = urlParams.get('source_employee');
        // Use null when param absent so loadCustomers knows not to restore
        var savedBm       = urlParams.get('bm_filter');   // null if absent
        var savedCity     = urlParams.get('city_filter'); // null if absent

        if (savedEmployee) {
            $('#source-employee').val(savedEmployee);
            loadCustomers(savedEmployee, savedBm, savedCity);
        }
    })();

    /* ─── Load Customers on dropdown change ─────────────── */
    function loadCustomers(userId, bmFilterToRestore, cityFilterToRestore) {
        if (!userId) {
            $('#customers-container').hide().empty();
            $('#customers-placeholder').show();
            resetSelectionUI();
            // Clear URL params
            var url = new URL(window.location.href);
            url.searchParams.delete('source_employee');
            url.searchParams.delete('bm_filter');
            url.searchParams.delete('city_filter');
            history.replaceState(null, '', url.toString());
            return;
        }

        currentSourceUserId = userId;
        $('#from-user-id-input').val(userId);
        $('#source-employee-hidden').val(userId);

        // Update URL — keep filter params only on redirect-restore, clear on fresh load
        var url = new URL(window.location.href);
        url.searchParams.set('source_employee', userId);
        if (bmFilterToRestore === null) {
            url.searchParams.delete('bm_filter');
            url.searchParams.delete('city_filter');
        }
        history.replaceState(null, '', url.toString());

        $('#customers-placeholder').hide();
        $('#customers-container').hide().empty();
        $('#loader').show();
        resetSelectionUI();

        $.ajax({
            url: '{{ route("admin.move-customers.load-customers") }}',
            method: 'GET',
            data: { user_id: userId },
            success: function (resp) {
                $('#loader').hide();
                if (!resp.success || !resp.data || resp.data.length === 0) {
                    $('#filter-bar-wrapper').hide();
                    $('#customers-placeholder').show();
                    return;
                }
                renderCustomerGroups(resp.data);
                $('#customers-container').show();
                populateFilters(resp.data);
                $('#filter-bar-wrapper').show();

                // Only restore filters when explicitly provided (post-move redirect).
                // On a fresh manual employee change bmFilterToRestore & cityFilterToRestore
                // are both null (not empty string '') — empty string '' means "user cleared it".
                var restoreBm   = (bmFilterToRestore   !== null) ? bmFilterToRestore   : '';
                var restoreCity = (cityFilterToRestore !== null) ? cityFilterToRestore : '';

                $('#bm-filter').val(restoreBm);
                $('#city-filter').val(restoreCity);

                if (restoreBm || restoreCity) {
                    applyFilter(true); // silent — URL already correct from redirect
                } else {
                    updateFilterCount();
                }
            },
            error: function () {
                $('#loader').hide();
                alert('Failed to load customers. Please try again.');
                $('#customers-placeholder').show();
            }
        });
    }

    $('#source-employee').on('change', function () {
        // null = fresh load, don't restore any filters
        loadCustomers($(this).val(), null, null);
    });

    /* ─── Render Groups ──────────────────────────────────── */
    function renderCustomerGroups(groups) {
        var html = '';

        $.each(groups, function (i, group) {
            var isRoot    = group.is_root;
            var customers = group.customers || [];
            var groupId   = 'group-' + group.user_id;
            var collapseId = 'collapse-' + group.user_id;
            var initials  = getInitials(group.user_name);

            html += '<div class="employee-group" id="' + groupId + '" data-user-id="' + group.user_id + '">';

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
            html += '</div>'; /* /employee-header */

            /* Collapsible customer list */
            html += '<div class="customer-list" id="' + collapseId + '">';

            if (customers.length === 0) {
                html += '<div class="empty-state"><i class="fa fa-inbox"></i><p>No customers assigned.</p></div>';
            } else {
                $.each(customers, function (ci, cust) {
                    var cbVal = group.user_id + '_' + cust.customer_id;
                    var bmVal = cust.business_model || 'Open';
                    var dealerBmVal = (bmVal === 'Dealer' && cust.dealer_business_name) ? cust.dealer_business_name : '';

                    var bmClass = 'bm-open';
                    var bmLabel = bmVal;
                    if (bmVal === 'Direct Customer') { bmClass = 'bm-direct'; bmLabel = 'Direct Customer'; }
                    else if (bmVal === 'Open')        { bmClass = 'bm-open';   bmLabel = 'Open'; }
                    else if (bmVal === 'Dealer')      { bmClass = 'bm-dealer'; bmLabel = cust.dealer_business_name || 'Dealer'; }

                    html += '<div class="customer-item" id="ci-' + cbVal + '" data-bm="' + escHtml(bmVal) + '" data-dealer-bm="' + escHtml(dealerBmVal) + '" data-city="' + escHtml(cust.city_name || '') + '">';
                    html += '  <div class="cust-no">' + (ci + 1) + '</div>';
                    html += '  <div class="cust-cb-wrap">';
                    html += '    <input type="checkbox" class="cust-cb" data-group="' + group.user_id + '" value="' + cbVal + '">';
                    html += '  </div>';
                    html += '  <div class="cust-info">';
                    html += '    <span class="cust-name">' + escHtml(cust.customer_name) + '</span>';
                    html += '    <span class="cust-meta">';
                    if (cust.contact_person_name) html += escHtml(cust.contact_person_name);
                    if (cust.designation)         html += ' &middot; ' + escHtml(cust.designation);
                    if (cust.department)          html += ' &middot; ' + escHtml(cust.department);
                    html += '    </span>';
                    html += '  </div>';
                    /* ── Col 2: city ── */
                    html += '  <div class="cust-center">';
                    if (cust.city_name) {
                        html += '    <div class="cust-center-city"><i class="fa fa-map-marker"></i>' + escHtml(cust.city_name) + '</div>';
                    }
                    html += '  </div>';
                    /* ── Col 3: business model badge ── */
                    html += '  <div class="cust-right">';
                    html += '    <span class="cust-badge ' + bmClass + '">' + escHtml(bmLabel) + '</span>';
                    html += '  </div>';
                    html += '</div>'; /* /customer-item */
                });
            }

            html += '</div>'; /* /customer-list */
            html += '</div>'; /* /employee-group */
        });

        $('#customers-container').html(html);
        bindEvents();
    }

    /* ─── Bind Events After Render ──────────────────────── */
    function bindEvents() {

        /* Collapse/Expand header */
        $(document).off('click', '.employee-header').on('click', '.employee-header', function () {
            var targetId = $(this).data('toggle-collapse');
            $('#' + targetId).slideToggle(200);
            $(this).toggleClass('collapsed');
        });

        /* Select All checkbox */
        $(document).off('change', '.select-all-cb').on('change', '.select-all-cb', function () {
            var group   = $(this).data('group');
            var checked = $(this).is(':checked');
            // Only check VISIBLE (not filtered out) customers
            $('.cust-cb[data-group="' + group + '"]').each(function () {
                if ($(this).closest('.customer-item').is(':visible')) {
                    $(this).prop('checked', checked);
                    var val = $(this).val();
                    if (checked) {
                        $('#ci-' + val).addClass('is-selected');
                    } else {
                        $('#ci-' + val).removeClass('is-selected');
                    }
                }
            });
            updateSelectionUI();
        });

        /* Individual customer checkbox */
        $(document).off('change', '.cust-cb').on('change', '.cust-cb', function () {
            var group = $(this).data('group');
            syncSelectAll(group);
            updateSelectionUI();
            var val = $(this).val();
            if ($(this).is(':checked')) {
                $('#ci-' + val).addClass('is-selected');
            } else {
                $('#ci-' + val).removeClass('is-selected');
            }
        });
    }

    /* Keep Select All in sync with individual checkboxes (only visible ones) */
    function syncSelectAll(group) {
        var $visible = $('.cust-cb[data-group="' + group + '"]').filter(function () {
            return $(this).closest('.customer-item').is(':visible');
        });
        var total   = $visible.length;
        var checked = $visible.filter(':checked').length;
        var $allCb  = $('.select-all-cb[data-group="' + group + '"]');
        $allCb.prop('checked', total > 0 && checked === total);
        $allCb.prop('indeterminate', checked > 0 && checked < total);
    }

    /* ─── Update Floating Bar ────────────────────────────── */
    function updateSelectionUI() {
        var $checked = $('.cust-cb:checked');
        var count    = $checked.length;

        $('#selected-count').text(count);

        /* Sync hidden inputs for form submit */
        var $hidden = $('#hidden-customer-inputs').empty();
        $checked.each(function () {
            $hidden.append('<input type="hidden" name="customer_ids[]" value="' + $(this).val() + '">');
        });

        /* Keep bm_filter + city_filter + source_employee in sync for post-redirect restore */
        $('#bm-filter-hidden').val($('#bm-filter').val());
        $('#city-filter-hidden').val($('#city-filter').val());
        $('#source-employee-hidden').val($('#source-employee').val());

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
        $('#filter-bar-wrapper').hide();
        // Reset BM filter — remove dynamic dealer options, keep first 3
        $('#bm-filter').val('');
        $('#bm-filter option:gt(2)').remove();
        // Reset city filter — remove all dynamic options, keep "All Cities"
        $('#city-filter').val('');
        $('#city-filter option:gt(0)').remove();
    }

    /* ─── Move Form Submit Validation ───────────────────── */
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
            var parts = $(this).val().split('_');
            var originalUserId = parts[0];
            if (originalUserId != toUser) {
                allSameUser = false;
                return false;
            }
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

    /* ─── Populate Filter Dropdowns ─────────────────────── */
    function populateFilters(groups) {
        var dealers = {};
        var cities  = {};

        $.each(groups, function (i, group) {
            $.each(group.customers || [], function (j, cust) {
                if (cust.business_model === 'Dealer' && cust.dealer_business_name) {
                    dealers[cust.dealer_business_name] = true;
                }
                if (cust.city_name) {
                    cities[cust.city_name] = true;
                }
            });
        });

        // Rebuild dealer options (keep first 3: All, Direct Customer, Open)
        $('#bm-filter option:gt(2)').remove();
        $.each(Object.keys(dealers).sort(), function (i, name) {
            $('#bm-filter').append('<option value="' + name + '">' + name + '</option>');
        });

        // Rebuild city options (keep first: All Cities)
        $('#city-filter option:gt(0)').remove();
        $.each(Object.keys(cities).sort(), function (i, name) {
            $('#city-filter').append('<option value="' + name + '">' + name + '</option>');
        });
    }

    /**
     * Apply the business model + city filters.
     * @param {boolean} silent - if true, skip updating the URL (already set)
     */
    function applyFilter(silent) {
        var bmVal   = $('#bm-filter').val();
        var cityVal = $('#city-filter').val();
        var groupCounts = {};

        $('.customer-item').each(function () {
            var bm       = $(this).data('bm') || '';
            var dealerBm = $(this).data('dealer-bm') || '';
            var city     = $(this).data('city') || '';

            var bmMatch   = !bmVal   || bm === bmVal || dealerBm === bmVal;
            var cityMatch = !cityVal || city === cityVal;
            var show      = bmMatch && cityMatch;

            $(this).toggle(show);

            // Count visible per group
            var groupId = $(this).closest('.employee-group').data('user-id');
            if (!groupCounts[groupId]) groupCounts[groupId] = { visible: 0, total: 0 };
            groupCounts[groupId].total++;
            if (show) groupCounts[groupId].visible++;
        });

        // Update each group's badge with filtered / total
        var isFiltered = bmVal || cityVal;
        $.each(groupCounts, function (groupId, counts) {
            var $badge = $('.group-total-badge[data-group="' + groupId + '"]');
            if (isFiltered) {
                $badge.text(counts.visible + ' / ' + counts.total + ' Customers');
            } else {
                $badge.text(counts.total + ' Customers');
            }
            syncSelectAll(groupId);
        });

        updateFilterCount();

        // Persist filters in URL (unless silent restore)
        if (!silent) {
            var url = new URL(window.location.href);
            if (bmVal)   { url.searchParams.set('bm_filter', bmVal); }   else { url.searchParams.delete('bm_filter'); }
            if (cityVal) { url.searchParams.set('city_filter', cityVal); } else { url.searchParams.delete('city_filter'); }
            history.replaceState(null, '', url.toString());
        }

        // Keep hidden form inputs in sync
        $('#bm-filter-hidden').val(bmVal);
        $('#city-filter-hidden').val(cityVal);
    }

    function updateFilterCount() {
        var visible = $('.customer-item:visible').length;
        $('#filter-visible-count').text(visible);
    }

    $('#bm-filter, #city-filter').on('change', function () {
        applyFilter(false);
    });

    /* ─── Helpers ────────────────────────────────────────── */
    function getInitials(name) {
        return name.trim().split(' ')
            .slice(0, 2)
            .map(function (w) { return w[0].toUpperCase(); })
            .join('');
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

});
</script>
@endsection