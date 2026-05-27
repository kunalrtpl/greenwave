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

    /* ── Customer List Cards ────────────────────────────────── */
    .customer-list-card {
        border: 1px solid #e1e5ec;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }

    .customer-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 18px;
        background: linear-gradient(135deg, #3598dc 0%, #2980b9 100%);
    }
    .customer-list-header .header-left  { display: flex; align-items: center; gap: 12px; }
    .customer-list-header .header-title { font-weight: 700; color: #fff; font-size: 14px; }
    .customer-list-header .header-sub   { font-size: 12px; color: rgba(255,255,255,0.8); }
    .customer-list-header .badge-count  { background: rgba(255,255,255,0.25); color: #fff; padding: 2px 10px; border-radius: 12px !important; font-size: 11px; font-weight: 700; }

    /* Select All area */
    .select-all-wrap {
        display: flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; color: #fff;
    }
    .select-all-cb { transform: scale(1.2); cursor: pointer; accent-color: #fff; }

    /* ── Customer Item ──────────────────────────────────────── */
    .customer-item {
        display: flex;
        align-items: center;
        padding: 11px 18px;
        border-bottom: 1px solid #f0f4f8;
        transition: background 0.15s;
    }
    .customer-item:last-child { border-bottom: none; }
    .customer-item:hover      { background: #fafcff; }
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

    /* Three equal columns */
    .cust-info   { flex: 1 1 0; min-width: 0; }
    .cust-center { flex: 1 1 0; min-width: 0; padding-left: 16px; border-left: 1px solid #edf2f7; }
    .cust-right  { flex: 1 1 0; min-width: 0; display: flex; justify-content: flex-end; align-items: center; }

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
    }
    .bm-direct { background: #e6fffa; color: #276749; }
    .bm-open   { background: #fffbeb; color: #975a16; }
    .bm-dealer { background: #ebf8ff; color: #2b6cb0; }

    /* Empty State */
    .empty-state { text-align: center; padding: 30px 20px; color: #a0aec0; }
    .empty-state i { font-size: 32px; margin-bottom: 10px; display: block; }

    /* ── Placeholder ────────────────────────────────────────── */
    #customers-placeholder {
        text-align: center; padding: 50px 20px; color: #a0aec0;
        border: 2px dashed #e1e5ec; border-radius: 8px;
    }
    #customers-placeholder i { font-size: 40px; margin-bottom: 12px; display: block; }

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

    /* ── Floating Save Bar ──────────────────────────────────── */
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
        display: flex; align-items: center; justify-content: center;
        gap: 20px; flex-wrap: wrap;
    }
    .selected-counter {
        display: inline-flex; align-items: center; gap: 8px;
        background: #ebf5ff; border: 1px solid #3598dc;
        padding: 6px 16px; border-radius: 20px !important;
    }
    .selected-counter .cnt-label { font-size: 12px; color: #3598dc; font-weight: 600; text-transform: uppercase; }
    .selected-counter .cnt-val   { font-size: 16px; color: #2b78ad; font-weight: 800; }

    .move-to-wrap { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .move-to-wrap label { font-size: 13px; font-weight: 600; color: #4a5568; margin: 0; white-space: nowrap; }
    .move-to-wrap select { border-radius: 6px !important; font-size: 13px; height: 38px; min-width: 200px; }

    .btn-move {
        padding: 9px 30px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        border-radius: 25px !important; font-size: 13px;
        box-shadow: 0 4px 12px rgba(53,152,220,0.35);
        transition: all 0.2s;
    }
    .btn-move:hover    { box-shadow: 0 6px 18px rgba(53,152,220,0.45); transform: translateY(-1px); }
    .btn-move:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* ── Loader ─────────────────────────────────────────────── */
    #loader { display: none; text-align: center; padding: 40px; }
    #loader .spinner { font-size: 30px; color: #3598dc; }

    /* ── Alerts ─────────────────────────────────────────────── */
    .alert { border-radius: 6px !important; }

    /* ── Dealer sub-select (shown when "dealer" chosen) ─────── */
    #dealer-sub-wrap { display: none; }
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

                {{-- ── Step 1: Select Source ── --}}
                <div class="control-panel">
                    <div class="row">
                        {{-- Source Type --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><i class="fa fa-filter"></i> &nbsp;Select Source</label>
                                <select id="source-type" class="form-control">
                                    <option value="">-- Choose Source --</option>
                                    <option value="Direct Customer">Direct Customer</option>
                                    <option value="Open">Open</option>
                                    <optgroup label="── Dealers ──">
                                        @foreach($dealers as $dealer)
                                            <option value="dealer" data-dealer-id="{{ $dealer->id }}">
                                                {{ $dealer->business_name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Customer Area ── --}}
                <div id="loader">
                    <i class="fa fa-circle-o-notch fa-spin spinner"></i>
                    <p style="color:#a0aec0; margin-top:10px;">Loading customers...</p>
                </div>

                <div id="customers-placeholder">
                    <i class="fa fa-users"></i>
                    <p>Select a source above to load customers.</p>
                </div>

                {{-- City filter --}}
                <div id="filter-bar-wrapper" style="display:none;">
                    <div class="filter-bar">
                        <label><i class="fa fa-map-marker"></i> &nbsp;City:</label>
                        <select id="city-filter" class="form-control">
                            <option value="">-- All Cities --</option>
                        </select>
                        <span class="filter-count">
                            Showing <strong id="filter-visible-count">0</strong> customers
                        </span>
                    </div>
                </div>

                <div id="customers-container" style="display:none;"></div>

            </div>{{-- /portlet-body --}}
        </div>{{-- /portlet --}}
    </div>{{-- /page-content --}}
</div>{{-- /page-content-wrapper --}}

{{-- ── Floating Move Bar ── --}}
<div class="floating-save-bar" id="floating-bar" style="display:none;">
    <form method="POST" action="{{ route('admin.dealer-move-customers.move') }}" id="move-form">
        @csrf
        {{-- hidden state for post-redirect restore --}}
        <input type="hidden" name="source_type"      id="source-type-hidden"       value="">
        <input type="hidden" name="source_dealer_id" id="source-dealer-id-hidden"  value="">
        <input type="hidden" name="city_filter"      id="city-filter-hidden"       value="">
        {{-- Customer IDs injected by JS --}}
        <div id="hidden-customer-inputs"></div>

        <div class="save-bar-inner">
            <div class="selected-counter">
                <span class="cnt-label">Selected:</span>
                <span class="cnt-val" id="selected-count">0</span>
            </div>

            {{-- Move To: type --}}
            <div class="move-to-wrap">
                <label><i class="fa fa-arrow-right"></i> Move To:</label>
                <select name="to_type" class="form-control" id="move-to-type" required>
                    <option value="">-- Select Target --</option>
                    <option value="Direct Customer">Direct Customer</option>
                    <option value="Open">Open</option>
                    <optgroup label="── Dealers ──">
                        @foreach($moveToOptions as $dealer)
                            <option value="dealer" data-dealer-id="{{ $dealer->id }}">
                                {{ $dealer->business_name }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
                {{-- Hidden dealer ID input filled by JS when a dealer option is chosen --}}
                <input type="hidden" name="to_dealer_id" id="to-dealer-id-input" value="">
            </div>

            <button type="submit" class="btn btn-success btn-move" id="btn-move" disabled>
                <i class="fa fa-exchange"></i> Move Customers
            </button>
        </div>
    </form>
</div>

<script>
$(document).ready(function () {

    /* ─── State ──────────────────────────────────────────────── */
    var currentSourceType    = null;
    var currentSourceDealerId = null;

    /* ─── Restore from URL on page load ─────────────────────── */
    (function restoreFromUrl() {
        var params     = new URLSearchParams(window.location.search);
        var srcType    = params.get('source_type');
        var srcDealer  = params.get('source_dealer_id');
        var cityFilter = params.get('city_filter');

        if (!srcType) return;

        // Restore the source-type dropdown
        if (srcType === 'Direct Customer' || srcType === 'Open') {
            $('#source-type').val(srcType);
        } else if (srcType === 'dealer' && srcDealer) {
            // Find the option whose data-dealer-id matches
            $('#source-type option[data-dealer-id="' + srcDealer + '"]').prop('selected', true);
        }

        loadCustomers(srcType, srcDealer, cityFilter);
    })();

    /* ─── Source dropdown change ─────────────────────────────── */
    $('#source-type').on('change', function () {
        var $opt      = $(this).find(':selected');
        var srcType   = $opt.val();                         // 'Direct Customer' | 'Open' | 'dealer'
        var dealerId  = $opt.data('dealer-id') || null;     // only for dealer options

        loadCustomers(srcType, dealerId, null);
    });

    /* ─── Load Customers ─────────────────────────────────────── */
    function loadCustomers(sourceType, dealerId, cityFilterToRestore) {
        if (!sourceType) {
            resetAll();
            return;
        }

        currentSourceType     = sourceType;
        currentSourceDealerId = dealerId || null;

        // Update URL params
        var url = new URL(window.location.href);
        url.searchParams.set('source_type', sourceType);
        if (dealerId) {
            url.searchParams.set('source_dealer_id', dealerId);
        } else {
            url.searchParams.delete('source_dealer_id');
        }
        if (cityFilterToRestore === null) {
            url.searchParams.delete('city_filter');
        }
        history.replaceState(null, '', url.toString());

        // UI reset
        $('#customers-placeholder').hide();
        $('#customers-container').hide().empty();
        $('#filter-bar-wrapper').hide();
        $('#loader').show();
        resetSelectionUI();

        var ajaxData = { source_type: sourceType };
        if (sourceType === 'dealer' && dealerId) {
            ajaxData.dealer_id = dealerId;
        }

        $.ajax({
            url: '{{ route("admin.dealer-move-customers.load-customers") }}',
            method: 'GET',
            data: ajaxData,
            success: function (resp) {
                $('#loader').hide();

                if (!resp.success || !resp.data || resp.data.length === 0) {
                    $('#customers-placeholder').show();
                    $('#filter-bar-wrapper').hide();
                    return;
                }

                renderCustomers(resp.data);
                populateCityFilter(resp.data);
                $('#customers-container').show();
                $('#filter-bar-wrapper').show();

                var restoreCity = (cityFilterToRestore !== null) ? cityFilterToRestore : '';
                $('#city-filter').val(restoreCity);
                if (restoreCity) {
                    applyFilter(true);
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

    /* ─── Render Customer List ───────────────────────────────── */
    function renderCustomers(customers) {
        var html = '';
        html += '<div class="customer-list-card">';

        // Header
        html += '<div class="customer-list-header">';
        html += '  <div class="header-left">';
        html += '    <div>';
        html += '      <div class="header-title">Customers</div>';
        html += '    </div>';
        html += '  </div>';
        html += '  <div style="display:flex;align-items:center;gap:12px;">';
        html += '    <span class="badge-count" id="total-badge">' + customers.length + ' Customers</span>';
        html += '    <div class="select-all-wrap">';
        html += '      <input type="checkbox" class="select-all-cb" id="select-all-main" title="Select / Deselect All">';
        html += '      <span>All</span>';
        html += '    </div>';
        html += '  </div>';
        html += '</div>'; // /header

        // Customer rows
        $.each(customers, function (i, cust) {
            var bm = cust.business_model || 'Open';
            var bmClass = 'bm-open';
            var bmLabel = bm;
            if (bm === 'Direct Customer') { bmClass = 'bm-direct'; }
            else if (bm === 'Dealer')     { bmClass = 'bm-dealer'; bmLabel = cust.dealer_business_name || 'Dealer'; }

            html += '<div class="customer-item" id="ci-' + cust.customer_id + '" data-city="' + escHtml(cust.city_name || '') + '">';
            html += '  <div class="cust-no">' + (i + 1) + '</div>';
            html += '  <div class="cust-cb-wrap">';
            html += '    <input type="checkbox" class="cust-cb" value="' + cust.customer_id + '">';
            html += '  </div>';
            html += '  <div class="cust-info">';
            html += '    <span class="cust-name">' + escHtml(cust.customer_name) + '</span>';
            html += '    <span class="cust-meta">';
            if (cust.contact_person_name) html += escHtml(cust.contact_person_name);
            if (cust.customer_designation) html += ' &middot; ' + escHtml(cust.customer_designation);
            if (cust.department)           html += ' &middot; ' + escHtml(cust.department);
            html += '    </span>';
            html += '  </div>';
            // Col 2: city
            html += '  <div class="cust-center">';
            if (cust.city_name) {
                html += '<div class="cust-center-city"><i class="fa fa-map-marker"></i>' + escHtml(cust.city_name) + '</div>';
            }
            html += '  </div>';
            // Col 3: BM badge
            html += '  <div class="cust-right">';
            html += '    <span class="cust-badge ' + bmClass + '">' + escHtml(bmLabel) + '</span>';
            html += '  </div>';
            html += '</div>'; // /customer-item
        });

        html += '</div>'; // /customer-list-card

        $('#customers-container').html(html);
        bindEvents();
    }

    /* ─── Bind Events After Render ──────────────────────────── */
    function bindEvents() {

        // Select All
        $(document).off('change', '#select-all-main').on('change', '#select-all-main', function () {
            var checked = $(this).is(':checked');
            $('.cust-cb').each(function () {
                if ($(this).closest('.customer-item').is(':visible')) {
                    $(this).prop('checked', checked);
                    toggleRowHighlight($(this));
                }
            });
            updateSelectionUI();
        });

        // Individual checkbox
        $(document).off('change', '.cust-cb').on('change', '.cust-cb', function () {
            toggleRowHighlight($(this));
            syncSelectAll();
            updateSelectionUI();
        });
    }

    function toggleRowHighlight($cb) {
        var id = $cb.val();
        if ($cb.is(':checked')) {
            $('#ci-' + id).addClass('is-selected');
        } else {
            $('#ci-' + id).removeClass('is-selected');
        }
    }

    function syncSelectAll() {
        var $visible  = $('.cust-cb').filter(function () { return $(this).closest('.customer-item').is(':visible'); });
        var total     = $visible.length;
        var checked   = $visible.filter(':checked').length;
        var $allCb    = $('#select-all-main');
        $allCb.prop('checked', total > 0 && checked === total);
        $allCb.prop('indeterminate', checked > 0 && checked < total);
    }

    /* ─── Update Floating Bar ────────────────────────────────── */
    function updateSelectionUI() {
        var $checked = $('.cust-cb:checked');
        var count    = $checked.length;

        $('#selected-count').text(count);

        // Sync hidden customer id inputs
        var $hidden = $('#hidden-customer-inputs').empty();
        $checked.each(function () {
            $hidden.append('<input type="hidden" name="customer_ids[]" value="' + $(this).val() + '">');
        });

        // Keep source state hidden fields in sync
        $('#source-type-hidden').val(currentSourceType || '');
        $('#source-dealer-id-hidden').val(currentSourceDealerId || '');
        $('#city-filter-hidden').val($('#city-filter').val());

        if (count > 0) {
            $('#floating-bar').slideDown(200);
            $('#btn-move').prop('disabled', false);
        } else {
            $('#floating-bar').slideUp(200);
            $('#btn-move').prop('disabled', true);
        }
    }

    /* ─── "Move To" select: extract dealer ID from data attr ─── */
    $('#move-to-type').on('change', function () {
        var $opt     = $(this).find(':selected');
        var val      = $opt.val();
        var dealerId = $opt.data('dealer-id') || '';
        $('#to-dealer-id-input').val(val === 'dealer' ? dealerId : '');
    });

    /* ─── Form submit validation ─────────────────────────────── */
    $('#move-form').on('submit', function (e) {
        var toType = $('#move-to-type').val();
        var count  = $('.cust-cb:checked').length;

        if (!toType) {
            e.preventDefault();
            alert('Please select a target to move customers to.');
            return;
        }
        if (toType === 'dealer' && !$('#to-dealer-id-input').val()) {
            e.preventDefault();
            alert('Please select a target dealer.');
            return;
        }
        if (count === 0) {
            e.preventDefault();
            alert('Please select at least one customer to move.');
            return;
        }

        if (!confirm('Move ' + count + ' customer(s) to the selected target? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    /* ─── City Filter ────────────────────────────────────────── */
    function populateCityFilter(customers) {
        var cities = {};
        $.each(customers, function (i, c) {
            if (c.city_name) cities[c.city_name] = true;
        });

        $('#city-filter option:gt(0)').remove();
        $.each(Object.keys(cities).sort(), function (i, name) {
            $('#city-filter').append('<option value="' + name + '">' + name + '</option>');
        });
    }

    function applyFilter(silent) {
        var cityVal = $('#city-filter').val();
        var visible = 0;

        $('.customer-item').each(function () {
            var city    = $(this).data('city') || '';
            var show    = !cityVal || city === cityVal;
            $(this).toggle(show);
            if (show) visible++;
        });

        // Update badge
        var total = $('.customer-item').length;
        if (cityVal) {
            $('#total-badge').text(visible + ' / ' + total + ' Customers');
        } else {
            $('#total-badge').text(total + ' Customers');
        }

        syncSelectAll();
        updateFilterCount();

        if (!silent) {
            var url = new URL(window.location.href);
            if (cityVal) { url.searchParams.set('city_filter', cityVal); }
            else         { url.searchParams.delete('city_filter'); }
            history.replaceState(null, '', url.toString());
            $('#city-filter-hidden').val(cityVal);
        }
    }

    function updateFilterCount() {
        $('#filter-visible-count').text($('.customer-item:visible').length);
    }

    $('#city-filter').on('change', function () { applyFilter(false); });

    /* ─── Helpers ────────────────────────────────────────────── */
    function resetAll() {
        currentSourceType      = null;
        currentSourceDealerId  = null;
        $('#customers-container').hide().empty();
        $('#customers-placeholder').show();
        $('#filter-bar-wrapper').hide();
        resetSelectionUI();

        var url = new URL(window.location.href);
        url.searchParams.delete('source_type');
        url.searchParams.delete('source_dealer_id');
        url.searchParams.delete('city_filter');
        history.replaceState(null, '', url.toString());
    }

    function resetSelectionUI() {
        $('#selected-count').text(0);
        $('#hidden-customer-inputs').empty();
        $('#floating-bar').hide();
        $('#btn-move').prop('disabled', true);
        $('#city-filter').val('');
        $('#city-filter option:gt(0)').remove();
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g,  '&amp;')
            .replace(/</g,  '&lt;')
            .replace(/>/g,  '&gt;')
            .replace(/"/g, '&quot;');
    }

});
</script>
@endsection