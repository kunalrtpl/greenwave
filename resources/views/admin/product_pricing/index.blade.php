@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    .page-content { padding-bottom: 40px !important; }

    .portlet.light.bordered {
        border-radius: 6px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.08);
        border: 1px solid #dde3ec;
    }

    /* ── Filter strip ── */
    .filter-strip {
        display: flex; align-items: flex-end; gap: 14px; flex-wrap: wrap;
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
    .filter-strip select { min-width: 220px; }
    .filter-strip input[type="text"] { width: 200px; }
    .filter-strip .fg-check { display: flex; align-items: center; gap: 6px; padding-bottom: 6px; }
    .filter-strip .fg-check label {
        font-size: 12px; font-weight: 600; color: #5a6a85;
        text-transform: none; letter-spacing: 0; cursor: pointer;
    }
    .filter-strip .fg-check input[type="checkbox"] {
        transform: scale(1.2); accent-color: #e53e3e; cursor: pointer;
    }

    /* ── Filter bottom row ── */
    .filter-bottom-row {
        display: flex; align-items: center; justify-content: space-between;
        width: 100%; margin-top: 4px; flex-wrap: wrap; gap: 8px;
    }
    .filter-result { font-size: 12px; color: #718096; white-space: nowrap; }
    .filter-result strong { color: #3598dc; }

    .btn-clear-filters {
        height: 34px; padding: 0 14px; font-size: 11px; font-weight: 700;
        border-radius: 4px !important; border: 1px solid #c8d0dc;
        background: #fff; color: #718096; cursor: pointer;
        text-transform: uppercase; letter-spacing: 0.4px;
        display: inline-flex; align-items: center; gap: 5px;
        transition: all 0.15s; white-space: nowrap;
    }
    .btn-clear-filters:hover { background: #fed7d7; border-color: #fc8181; color: #c53030; }
    .btn-clear-filters.hidden { visibility: hidden; pointer-events: none; }

    .btn-export-pdf {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; font-size: 12px; font-weight: 600;
        border-radius: 4px !important; border: 1px solid #e53e3e;
        background: #fff5f5; color: #c53030; cursor: pointer;
        text-decoration: none; white-space: nowrap; transition: all 0.15s;
        height: 34px;
    }
    .btn-export-pdf:hover { background: #e53e3e; color: #fff; text-decoration: none; }

    /* ── Table ── */
    .pricing-wrap { width: 100%; overflow-x: auto; }
    .pricing-table { width: 100%; border-collapse: collapse; font-size: 13px; table-layout: fixed; }
    .pricing-table col.c-no       { width: 50px; }
    .pricing-table col.c-name     { width: 15%; }
    .pricing-table col.c-moq      { width: 12%; }
    .pricing-table col.c-dispatch { width: 12%; }
    .pricing-table col.c-na       { width: 10%; }
    .pricing-table col.c-disc     { width: 10%; }
    .pricing-table col.c-dp       { width: 12%; }
    .pricing-table col.c-date     { width: 12%; }
    .pricing-table col.c-action   { width: 10%; }

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

    .na-toggle { text-align: center; }
    .na-toggle input[type="checkbox"] { transform: scale(1.3); accent-color: #e53e3e; cursor: pointer; }

    .disc-toggle { text-align: center; }
    .disc-toggle input[type="checkbox"] { transform: scale(1.3); accent-color: #805ad5; cursor: pointer; }

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
                </div>

                {{-- Filters --}}
                <div class="filter-strip">

                    <div class="fg">
                        <label><i class="fa fa-cube"></i> &nbsp;Product</label>
                        <select id="filter-product">
                            <option value="">— All Products —</option>
                            @foreach($products->sortBy('product_name') as $p)
                                <option value="{{ $p->id }}">{{ $p->product_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fg">
                        <label><i class="fa fa-inr"></i> &nbsp;Price Status</label>
                        <select id="filter-price-status">
                            <option value="">— All —</option>
                            <option value="has_price">Has Price</option>
                            <option value="no_price">No Price</option>
                            <option value="today">Updated Today</option>
                        </select>
                    </div>

                    <div class="fg">
                        <label><i class="fa fa-search"></i> &nbsp;Search</label>
                        <input type="text" id="filter-search" placeholder="Name or code...">
                    </div>

                    <div class="fg">
                        <label>&nbsp;</label>
                        <div class="fg-check">
                            <input type="checkbox" id="filter-na">
                            <label for="filter-na">Not Available only</label>
                        </div>
                    </div>

                    <div class="fg">
                        <label>&nbsp;</label>
                        <div class="fg-check">
                            <input type="checkbox" id="filter-disc">
                            <label for="filter-disc">Discontinued only</label>
                        </div>
                    </div>

                    {{-- PDF Export --}}
                    <div class="fg">
                        <label>&nbsp;</label>
                        <a href="#" id="btn-export-pdf" class="btn-export-pdf">
                            <i class="fa fa-file-pdf-o"></i> Export PDF
                        </a>
                    </div>

                    <div class="filter-bottom-row">
                        <div class="filter-result">
                            Showing <strong id="visible-count">{{ $products->count() }}</strong>
                            of {{ $products->count() }} products
                        </div>
                        <button type="button" id="btn-clear-filters" class="btn-clear-filters hidden">
                            <i class="fa fa-times-circle"></i> Clear Filters
                        </button>
                    </div>

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
                            <col class="c-dp">
                            <col class="c-date">
                            <col class="c-action">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="center">#</th>
                                <th>Product Name</th>
                                <th>MOQ</th>
                                <th>Dispatch</th>
                                <th class="center">Not Avail.</th>
                                <th class="center">Discontinued</th>
                                <th>DP (₹)</th>
                                <th class="center">Price Date</th>
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
                            data-orig-dp="{{ $hasPrice ? number_format((float)$product->dealer_price,2,'.','') : '' }}"
                            data-pricing-id="{{ $product->pricing_id }}"
                            data-has-price="{{ $hasPrice ? 1 : 0 }}"
                            data-is-today="{{ $isToday ? 1 : 0 }}"
                            data-name="{{ strtolower($product->product_name) }}"
                            data-code="{{ strtolower($product->product_code) }}">

                            <td class="sr-no-cell">{{ $index + 1 }}</td>

                            <td>
                                <span class="prod-name">{{ $product->product_name }}</span>
                                <span class="prod-code">{{ $product->product_code }}</span>
                            </td>

                            <td>
                                <input type="text" class="inline-input field-moq"
                                       value="{{ $product->moq }}" maxlength="191">
                            </td>

                            <td>
                                <input type="number" class="inline-input field-dispatch"
                                       value="{{ $product->average_dispatch_time }}"
                                       min="0" step="0.5">
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
                            <td colspan="9">
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

<div id="toast-wrap"></div>

<script>
$(document).ready(function () {

    var TODAY = '{{ $today }}';

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
            ($row.find('.field-na').is(':checked') ? 1 : 0)   !== parseInt($row.data('orig-na'))   ||
            ($row.find('.field-disc').is(':checked') ? 1 : 0) !== parseInt($row.data('orig-disc'))  ||
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

    /* ══ 2. NOT AVAILABLE — mutual exclusion ══ */
    $(document).on('change', '.product-row .field-na', function () {
        var $this = $(this);
        var $row  = $this.closest('.product-row');
        var $disc = $row.find('.field-disc');

        if ($this.is(':checked') && $disc.is(':checked')) {
            alert('Cannot mark as Not Available because this product is already marked as Discontinued.');
            $this.prop('checked', false);
            return;
        }

        if ($this.is(':checked')) {
            $disc.prop('disabled', true);
        } else {
            $disc.prop('disabled', false);
        }

        checkDirty($row);
    });

    /* ══ 3. DISCONTINUED — mutual exclusion ══ */
    $(document).on('change', '.product-row .field-disc', function () {
        var $this = $(this);
        var $row  = $this.closest('.product-row');
        var $na   = $row.find('.field-na');

        if ($this.is(':checked') && $na.is(':checked')) {
            alert('Cannot mark as Discontinued because this product is already marked as Not Available.');
            $this.prop('checked', false);
            return;
        }

        if ($this.is(':checked')) {
            $na.prop('disabled', true);
        } else {
            $na.prop('disabled', false);
        }

        checkDirty($row);
    });

    /* ══ 4. UPDATE AJAX ══ */
    $(document).on('click', '.btn-update:not(:disabled)', function () {
        var $btn  = $(this);
        var $row  = $btn.closest('.product-row');
        var pid   = $row.data('product-id');
        var curDp = $row.find('.field-dp').val().trim();
        var dpChg = curDpStr($row) !== origDpStr($row);

        var payload = {
            _token:                '{{ csrf_token() }}',
            moq:                   $row.find('.field-moq').val().trim(),
            average_dispatch_time: $row.find('.field-dispatch').val().trim(),
            not_available:         $row.find('.field-na').is(':checked') ? 1 : 0,
            discontinued:          $row.find('.field-disc').is(':checked') ? 1 : 0,
            dealer_price:          curDp !== '' ? curDp : null,
            dp_changed:            dpChg ? 1 : 0,
            pricing_id:            $row.data('pricing-id') || '',
        };

        $btn.addClass('saving').html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url:    '/admin/product-pricing/update/' + pid,
            method: 'POST',
            data:   payload,
            success: function (resp) {
                if (!resp.success) { showToast('danger', resp.message); return; }

                $row.data('orig-moq',      payload.moq);
                $row.data('orig-dispatch', payload.average_dispatch_time);
                $row.data('orig-na',       payload.not_available);
                $row.data('orig-disc',     payload.discontinued);
                $row.data('orig-dp',       curDp !== '' ? parseFloat(curDp).toFixed(2) : '');

                if (dpChg && resp.pricing_id) {
                    $row.data('pricing-id', resp.pricing_id);
                    $row.data('has-price', 1);
                    $row.data('is-today',  1);
                    $row.find('.price-date-label')
                        .removeClass('pd-old pd-none').addClass('pd-today').text('Today');
                }

                $row.removeClass('is-dirty');
                $row.find('.inline-input').removeClass('changed');
                $row.addClass('row-saved');
                setTimeout(function () { $row.removeClass('row-saved'); }, 1700);
                showToast('success', '<i class="fa fa-check-circle"></i> &nbsp;' + resp.message);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Update failed.';
                showToast('danger', '<i class="fa fa-times-circle"></i> &nbsp;' + msg);
            },
            complete: function () {
                $btn.removeClass('saving').html('<i class="fa fa-save"></i> Update');
                checkDirty($row);
            }
        });
    });

    /* ══ 5. FILTERS + RENUMBER ══ */
    function isFiltered() {
        return $('#filter-product').val()        !== '' ||
               $('#filter-price-status').val()   !== '' ||
               $('#filter-search').val().trim()  !== '' ||
               $('#filter-na').is(':checked')           ||
               $('#filter-disc').is(':checked');
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
            if (naOnly   && !$r.find('.field-na').is(':checked'))   { $r.hide(); return; }
            if (discOnly && !$r.find('.field-disc').is(':checked'))  { $r.hide(); return; }

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
    $('#filter-na, #filter-disc').on('change', applyFilters);

    /* ══ Clear all filters ══ */
    $('#btn-clear-filters').on('click', function () {
        $('#filter-product').val('');
        $('#filter-price-status').val('');
        $('#filter-search').val('');
        $('#filter-na').prop('checked', false);
        $('#filter-disc').prop('checked', false);
        applyFilters();
    });

    /* ══ 6. PDF EXPORT LINK ══ */
    function updatePdfLink() {
        var params = new URLSearchParams();
        var prodId      = $('#filter-product').val();
        var priceStatus = $('#filter-price-status').val();
        var search      = $('#filter-search').val().trim();
        var naOnly      = $('#filter-na').is(':checked');
        var discOnly    = $('#filter-disc').is(':checked');

        if (prodId)      params.set('product_id',   prodId);
        if (priceStatus) params.set('price_status',  priceStatus);
        if (search)      params.set('search',         search);
        if (naOnly)      params.set('not_available',  1);
        if (discOnly)    params.set('discontinued',   1);

        $('#btn-export-pdf').attr('href', '{{ route("admin.product-pricing.export-pdf") }}?' + params.toString());
    }

    updatePdfLink();

    /* ══ 7. TOAST ══ */
    function showToast(type, html) {
        var $t = $('<div class="toast-item toast-' + type + '"></div>').html(html);
        $('#toast-wrap').append($t);
        setTimeout(function () { $t.css('opacity', 1); }, 10);
        setTimeout(function () {
            $t.css('opacity', 0);
            setTimeout(function () { $t.remove(); }, 320);
        }, 3200);
    }

});
</script>
@endsection