@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    .page-content { padding-bottom: 90px !important; }

    .portlet.light.bordered {
        border-radius: 6px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.08);
        border: 1px solid #dde3ec;
    }

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
    .dot-selected { background: #36c6d3; }
    .dot-na       { background: #ed8936; }
    .dot-disc     { background: #805ad5; }
    .dot-focus    { background: #f59e0b; }

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
    .filter-strip select { min-width: 190px; }
    .filter-strip input[type="text"] { width: 180px; }
    .filter-strip .fg-check { display: flex; align-items: center; gap: 6px; padding-bottom: 6px; }
    .filter-strip .fg-check label {
        font-size: 12px; font-weight: 600; color: #5a6a85;
        text-transform: none; letter-spacing: 0; cursor: pointer;
    }
    .filter-strip .fg-check input[type="checkbox"] {
        transform: scale(1.2); cursor: pointer;
    }
    .filter-strip .fg-check input[type="checkbox"]#filter-na    { accent-color: #e53e3e; }
    .filter-strip .fg-check input[type="checkbox"]#filter-disc  { accent-color: #805ad5; }
    .filter-strip .fg-check input[type="checkbox"]#filter-focus { accent-color: #f59e0b; }

    .filter-result { margin-left: auto; font-size: 12px; color: #718096; padding-bottom: 6px; white-space: nowrap; }
    .filter-result strong { color: #3598dc; }

    /* ── Table ── */
    .link-wrap { width: 100%; overflow-x: auto; }
    .link-table { width: 100%; border-collapse: collapse; font-size: 13px; }

    .link-table thead tr th {
        background: #eef1f7; color: #4a5568; font-weight: 700; font-size: 11px;
        text-transform: uppercase; letter-spacing: 0.55px; padding: 10px 12px;
        border: 1px solid #d5dbe8; white-space: nowrap; text-align: left;
    }
    .link-table thead tr th.center { text-align: center; }

    /* Category group header */
    .link-table tr.group-parent-row td {
        background: linear-gradient(135deg, #3598dc 0%, #2980b9 100%);
        color: #fff; font-weight: 700; font-size: 13px;
        padding: 10px 14px; border: none; cursor: pointer;
        user-select: none;
    }
    .link-table tr.group-parent-row td .gp-inner {
        display: flex; align-items: center; justify-content: space-between;
    }
    .link-table tr.group-parent-row td .gp-left { display: flex; align-items: center; gap: 10px; }
    .link-table tr.group-parent-row td .gp-badge {
        background: rgba(255,255,255,0.25); color: #fff;
        font-size: 11px; font-weight: 700; padding: 2px 10px;
        border-radius: 12px; display: inline-block;
    }
    .link-table tr.group-parent-row td .gp-arrow {
        font-size: 11px; transition: transform 0.2s; color: rgba(255,255,255,0.8);
    }
    .link-table tr.group-parent-row.collapsed td .gp-arrow { transform: rotate(-90deg); }

    /* Child group sub-header */
    .link-table tr.group-child-row td {
        background: #f4f7fb; color: #2d3748; font-weight: 600; font-size: 12px;
        padding: 9px 14px 9px 28px; border: 1px solid #e1e5ec; cursor: pointer;
        user-select: none;
    }
    .link-table tr.group-child-row td .gc-inner {
        display: flex; align-items: center; justify-content: space-between;
    }
    .link-table tr.group-child-row td .gc-left { display: flex; align-items: center; gap: 8px; }
    .badge-blue     { background: #3598dc; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px; display: inline-block; }
    .badge-selected { background: #36c6d3; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px; display: inline-block; }
    .link-table tr.group-child-row td .gc-arrow {
        font-size: 10px; color: #a0aec0; transition: transform 0.2s;
    }
    .link-table tr.group-child-row.collapsed td .gc-arrow { transform: rotate(-90deg); }

    /* Product rows */
    .link-table tr.product-row td {
        padding: 8px 12px; border: 1px solid #e4e9f2;
        vertical-align: middle; color: #2d3748; background: #fff;
    }
    .link-table tr.product-row:hover td { background: #f8faff; }
    .link-table tr.product-row:nth-child(even) td { background: #fafbfd; }
    .link-table tr.product-row:nth-child(even):hover td { background: #f0f5ff; }
    .link-table tr.product-row.hidden { display: none; }
    .link-table tr.product-row.selected-row td { background: #eaf7fb !important; }

    .sr-no-cell { color: #a0aec0; font-size: 11px; text-align: center; width: 40px; }
    .cb-cell { text-align: center; width: 40px; }
    .custom-checkbox { transform: scale(1.25); cursor: pointer; accent-color: #3598dc; }

    .prod-name { font-weight: 600; color: #2d3748; font-size: 13px; display: block; line-height: 1.3; }
    .prod-desc { font-size: 11px; color: #a0aec0; font-style: italic; display: block; margin-top: 1px; }

    /* Read-only info pills */
    .info-pill {
        display: inline-block; background: #f4f6fa; border: 1px solid #dde3ec;
        border-radius: 4px; padding: 3px 9px; font-size: 12px;
        color: #4a5568; font-weight: 500; white-space: nowrap;
    }
    .info-pill.na-yes {
        background: #fff5f5; border-color: #feb2b2; color: #c53030; font-weight: 700;
        padding: 3px 8px;
    }
    .info-pill.na-no {
        background: #f0fff4; border-color: #9ae6b4; color: #276749;
        padding: 3px 8px;
    }
    .info-pill.disc-yes {
        background: #faf5ff; border-color: #d6bcfa; color: #6b21a8; font-weight: 700;
        padding: 3px 8px;
    }
    .info-pill.disc-no {
        background: #f0fff4; border-color: #9ae6b4; color: #276749;
        padding: 3px 8px;
    }
    .focus-star-yes {
        font-size: 18px; color: #f59e0b; line-height: 1; display: inline-block;
    }
    .focus-star-no {
        font-size: 18px; color: #e2e8f0; line-height: 1; display: inline-block;
    }
    .pd-badge {
        display: inline-block; font-size: 10px; padding: 2px 8px;
        border-radius: 10px; font-weight: 700; white-space: nowrap; border: 1px solid transparent;
    }
    .pd-today { background: #e6fffa; color: #276749; border-color: #9ae6b4; }
    .pd-old   { background: #f7fafc; color: #718096; border-color: #e2e8f0; }
    .pd-none  { background: #fff5f5; color: #c53030; border-color: #feb2b2; }
    .info-pill.dp-val {
        background: #ebf5ff; border-color: #90cdf4; color: #2b78ad; font-weight: 700;
    }
    .info-pill.dp-none {
        background: #fff5f5; border-color: #feb2b2; color: #c53030;
    }
    .center-cell { text-align: center; }

    /* No results */
    #empty-row td {
        text-align: center; padding: 30px; color: #a0aec0;
        font-style: italic; border: 1px solid #e4e9f2;
    }

    /* ── Floating save bar ── */
    .floating-save-bar {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
        padding: 14px 0; border-top: 1px solid #e1e5ec;
        z-index: 1050; box-shadow: 0 -4px 15px rgba(0,0,0,0.08);
        text-align: center;
    }
    .bottom-total { margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #444; }
    .btn-save-float {
        padding: 10px 40px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 1px; border-radius: 30px !important;
        box-shadow: 0 4px 12px rgba(53,152,220,0.35);
    }

    /* Top header counter */
    .total-counter-wrapper {
        display: inline-flex; align-items: center; gap: 6px;
        background: #ebf5ff; border: 1px solid #3598dc;
        padding: 4px 14px; border-radius: 20px;
        margin-left: 15px; vertical-align: middle;
    }
    .total-label        { font-size: 12px; color: #3598dc; font-weight: 600; text-transform: uppercase; }
    .total-count-global { font-size: 15px; color: #2b78ad; font-weight: 800; }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">

            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-link font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">{{ $title }}</span>
                    <div class="total-counter-wrapper">
                        <span class="total-label">Total Selected:</span>
                        <span class="total-count-global" id="top-total-count">{{ count($selectedProducts) }}</span>
                    </div>
                </div>
            </div>

            <div class="portlet-body">

                {{-- ── Summary Cards ── --}}
                @php
                    $totalProducts = 0; $naCount = 0; $discCount = 0; $focusCount = 0;
                    foreach($hierarchy as $p)
                        foreach($p['children'] ?? [] as $c)
                            foreach($c['products'] ?? [] as $pr) {
                                $totalProducts++;
                                if(!empty($pr['not_available']))  $naCount++;
                                if(!empty($pr['discontinued']))   $discCount++;
                                if(!empty($pr['focus_product']))  $focusCount++;
                            }
                @endphp
                <div class="summary-bar">
                    <div class="sum-card">
                        <span class="dot dot-total"></span>
                        Total Products &nbsp;<strong id="sum-total">{{ $totalProducts }}</strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-selected"></span>
                        Selected &nbsp;<strong id="sum-selected" style="color:#36c6d3;">{{ count($selectedProducts) }}</strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-na"></span>
                        Not Available &nbsp;<strong id="sum-na" style="color:#ed8936;">{{ $naCount }}</strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-disc"></span>
                        Discontinued &nbsp;<strong id="sum-disc" style="color:#805ad5;">{{ $discCount }}</strong>
                    </div>
                    <div class="sum-card">
                        <span class="dot dot-focus"></span>
                        Focus Products &nbsp;<strong id="sum-focus" style="color:#f59e0b;">{{ $focusCount }}</strong>
                    </div>
                </div>

                {{-- ── Filter strip ── --}}
                <div class="filter-strip">
                    <div class="fg">
                        <label><i class="fa fa-folder"></i> &nbsp;Category</label>
                        <select id="filter-parent">
                            <option value="">— All Categories —</option>
                            @foreach($hierarchy as $parent)
                                <option value="{{ $parent['id'] }}">{{ $parent['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fg">
                        <label><i class="fa fa-tag"></i> &nbsp;Sub-Category</label>
                        <select id="filter-child">
                            <option value="">— All —</option>
                            @foreach($hierarchy as $parent)
                                @foreach($parent['children'] ?? [] as $child)
                                    <option value="{{ $child['id'] }}" data-parent="{{ $parent['id'] }}">{{ $child['name'] }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div class="fg">
                        <label><i class="fa fa-check-square-o"></i> &nbsp;Selection</label>
                        <select id="filter-selection">
                            <option value="">— All —</option>
                            <option value="selected">Selected</option>
                            <option value="unselected">Not Selected</option>
                        </select>
                    </div>

                    <div class="fg">
                        <label><i class="fa fa-search"></i> &nbsp;Search</label>
                        <input type="text" id="filter-search" placeholder="Search by product name...">
                    </div>

                    <div class="fg">
                        <label>&nbsp;</label>
                        <div class="fg-check">
                            <input type="checkbox" id="filter-na">
                            <label for="filter-na" style="color:#c53030;">Not Available only</label>
                        </div>
                    </div>

                    <div class="fg">
                        <label>&nbsp;</label>
                        <div class="fg-check">
                            <input type="checkbox" id="filter-disc">
                            <label for="filter-disc" style="color:#6b21a8;">Discontinued only</label>
                        </div>
                    </div>

                    <div class="fg">
                        <label>&nbsp;</label>
                        <div class="fg-check">
                            <input type="checkbox" id="filter-focus">
                            <label for="filter-focus" style="color:#b45309;">&#9733; Focus only</label>
                        </div>
                    </div>

                    <div class="filter-result">
                        Showing <strong id="visible-count">{{ $totalProducts }}</strong>
                        of {{ $totalProducts }} products
                    </div>
                </div>

                {{-- ── Table ── --}}
                <form method="POST" action="{{ route('admin.users.products.save', $user->id) }}">
                    @csrf
                    <div class="link-wrap">
                        <table class="link-table" id="link-table">
                            <thead>
                                <tr>
                                    <th class="center">#</th>
                                    <th class="center"><i class="fa fa-check"></i></th>
                                    <th>Product Name</th>
                                    <th class="center">Not Avail.</th>
                                    <th class="center">Discont.</th>
                                    <th class="center">Focus</th>
                                    <th>MOQ</th>
                                    <th>Dispatch (days)</th>
                                    <th>DP (₹)</th>
                                    <th class="center">Price Date</th>
                                </tr>
                            </thead>
                            <tbody id="link-tbody">

                            @foreach($hierarchy as $parent)
                                @php
                                    $allChildIds = [];
                                    foreach($parent['children'] ?? [] as $c) foreach($c['products'] ?? [] as $p) $allChildIds[] = $p['id'];
                                    $parentSel = count(array_intersect($allChildIds, $selectedProducts));
                                @endphp

                                {{-- Parent group header row --}}
                                <tr class="group-parent-row" data-parent-id="{{ $parent['id'] }}" data-target="parent-{{ $parent['id'] }}">
                                    <td colspan="10">
                                        <div class="gp-inner">
                                            <div class="gp-left">
                                                <i class="fa fa-folder-open" style="color:rgba(255,255,255,0.8);"></i>
                                                <span>{{ $parent['name'] }}</span>
                                                <span class="gp-badge parent-count" data-parent-id="{{ $parent['id'] }}">{{ $parentSel }} selected</span>
                                            </div>
                                            <i class="fa fa-chevron-down gp-arrow"></i>
                                        </div>
                                    </td>
                                </tr>

                                @foreach($parent['children'] ?? [] as $child)
                                    @php
                                        $childIds  = array_column($child['products'] ?? [], 'id');
                                        $childSel  = count(array_intersect($childIds, $selectedProducts));
                                    @endphp

                                    {{-- Child group sub-header row --}}
                                    <tr class="group-child-row child-of-{{ $parent['id'] }}" data-child-id="{{ $child['id'] }}" data-parent-id="{{ $parent['id'] }}" data-target="child-{{ $child['id'] }}">
                                        <td colspan="10">
                                            <div class="gc-inner">
                                                <div class="gc-left">
                                                    <i class="fa fa-tag" style="color:#3598dc; font-size:11px;"></i>
                                                    <span>{{ $child['name'] }}</span>
                                                    <span class="badge-blue">{{ count($child['products'] ?? []) }} Products</span>
                                                    <span class="badge-selected child-count" data-child-id="{{ $child['id'] }}">{{ $childSel }} Selected</span>
                                                </div>
                                                <i class="fa fa-chevron-down gc-arrow"></i>
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach($child['products'] ?? [] as $index => $product)
                                    @php
                                        $hasPrice  = !is_null($product['dealer_price'] ?? null);
                                        $isNA      = !empty($product['not_available']);
                                        $isDisc    = !empty($product['discontinued']);
                                        $isFocus   = !empty($product['focus_product']);
                                        $isChecked = in_array($product['id'], $selectedProducts);
                                    @endphp
                                    <tr class="product-row child-of-{{ $child['id'] }} parent-of-{{ $parent['id'] }} {{ $isChecked ? 'selected-row' : '' }}"
                                        id="row-{{ $product['id'] }}"
                                        data-product-id="{{ $product['id'] }}"
                                        data-child-id="{{ $child['id'] }}"
                                        data-parent-id="{{ $parent['id'] }}"
                                        data-name="{{ strtolower($product['product_name']) }}"
                                        data-selected="{{ $isChecked ? 1 : 0 }}"
                                        data-na="{{ $isNA ? 1 : 0 }}"
                                        data-disc="{{ $isDisc ? 1 : 0 }}"
                                        data-focus="{{ $isFocus ? 1 : 0 }}">

                                        <td class="sr-no-cell">{{ $index + 1 }}</td>

                                        <td class="cb-cell">
                                            <input type="checkbox"
                                                   class="custom-checkbox product-cb"
                                                   name="products[]"
                                                   value="{{ $product['id'] }}"
                                                   data-child-id="{{ $child['id'] }}"
                                                   data-parent-id="{{ $parent['id'] }}"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                        </td>

                                        <td>
                                            <span class="prod-name">
                                                {{ $product['product_name'] }}
                                                @if($isFocus)
                                                    <span style="color:#f59e0b; font-size:14px; margin-left:4px;">&#9733;</span>
                                                @endif
                                            </span>
                                            @if(!empty($product['description']))
                                                <span class="prod-desc">({{ $product['description'] }})</span>
                                            @endif
                                        </td>

                                        {{-- Not Available --}}
                                        <td class="center-cell">
                                            @if($isNA)
                                                <span class="info-pill na-yes"><i class="fa fa-times"></i></span>
                                            @else
                                                <span class="info-pill na-no"><i class="fa fa-check"></i></span>
                                            @endif
                                        </td>

                                        {{-- Discontinued --}}
                                        <td class="center-cell">
                                            @if($isDisc)
                                                <span class="info-pill disc-yes"><i class="fa fa-times"></i></span>
                                            @else
                                                <span class="info-pill disc-no"><i class="fa fa-check"></i></span>
                                            @endif
                                        </td>

                                        {{-- Focus Product --}}
                                        <td class="center-cell">
                                            @if($isFocus)
                                                <span class="focus-star-yes">&#9733;</span>
                                            @else
                                                <span class="focus-star-no">&#9733;</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="info-pill">{{ $product['moq'] ?? '—' }}</span>
                                        </td>

                                        <td>
                                            <span class="info-pill">{{ $product['average_dispatch_time'] ?? '—' }}</span>
                                        </td>

                                        <td>
                                            @if($hasPrice)
                                                <span class="info-pill dp-val">₹ {{ number_format((float)$product['dealer_price'], 2) }}</span>
                                            @else
                                                <span class="info-pill dp-none">No Price</span>
                                            @endif
                                        </td>

                                        <td class="center-cell">
                                            @php
                                                $priceDate = $product['price_date'] ?? null;
                                                $isToday   = $priceDate && $priceDate === \Carbon\Carbon::today()->toDateString();
                                            @endphp
                                            @if($priceDate)
                                                <span class="pd-badge {{ $isToday ? 'pd-today' : 'pd-old' }}">
                                                    {{ $isToday ? 'Today' : \Carbon\Carbon::parse($priceDate)->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="pd-badge pd-none">No Price</span>
                                            @endif
                                        </td>

                                    </tr>
                                    @endforeach

                                @endforeach
                            @endforeach

                            <tr id="empty-row" style="display:none;">
                                <td colspan="10">
                                    <i class="fa fa-search" style="font-size:22px; display:block; margin-bottom:6px;"></i>
                                    No products match the current filters.
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>

                    {{-- Floating save bar --}}
                    <div class="floating-save-bar">
                        <div class="bottom-total">
                            Total Products Selected: <strong class="total-count-global" id="bottom-total-count">{{ count($selectedProducts) }}</strong>
                        </div>
                        <button type="submit" class="btn btn-primary blue btn-save-float">
                            <i class="fa fa-check"></i> Save Changes
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    /* ── 1. Parent group toggle ── */
    $(document).on('click', '.group-parent-row', function () {
        var parentId   = $(this).data('parent-id');
        var $children  = $('.child-of-' + parentId);
        var isCollapsed = $(this).hasClass('collapsed');

        if (isCollapsed) {
            $(this).removeClass('collapsed');
            $children.show();
        } else {
            $(this).addClass('collapsed');
            $children.hide();
            $children.filter('.group-child-row').addClass('collapsed');
            $children.filter('.group-child-row').each(function () {
                var cid = $(this).data('child-id');
                $('.child-of-' + cid).hide();
            });
        }
    });

    /* ── 2. Child group toggle ── */
    $(document).on('click', '.group-child-row', function (e) {
        e.stopPropagation();
        var childId    = $(this).data('child-id');
        var $rows      = $('.product-row.child-of-' + childId);
        var isCollapsed = $(this).hasClass('collapsed');
        $(this).toggleClass('collapsed', !isCollapsed);
        $rows.toggle(isCollapsed);
    });

    /* ── 3. Checkbox change ── */
    $(document).on('change', '.product-cb', function () {
        var $cb      = $(this);
        var childId  = $cb.data('child-id');
        var parentId = $cb.data('parent-id');
        var $row     = $cb.closest('.product-row');
        var checked  = $cb.is(':checked');

        $row.data('selected', checked ? 1 : 0);
        $row.toggleClass('selected-row', checked);

        var childChecked  = $('input.product-cb[data-child-id="'  + childId  + '"]:checked').length;
        var parentChecked = $('input.product-cb[data-parent-id="' + parentId + '"]:checked').length;
        var total         = $('.product-cb:checked').length;

        $('.child-count[data-child-id="'   + childId  + '"]').text(childChecked  + ' Selected');
        $('.parent-count[data-parent-id="' + parentId + '"]').text(parentChecked + ' selected');
        $('#top-total-count, #bottom-total-count, #sum-selected').text(total);
    });

    /* ── 4. Filters ── */
    function applyFilters() {
        var parentId  = $('#filter-parent').val();
        var childId   = $('#filter-child').val();
        var selection = $('#filter-selection').val();
        var search    = $('#filter-search').val().toLowerCase().trim();
        var naOnly    = $('#filter-na').is(':checked');
        var discOnly  = $('#filter-disc').is(':checked');
        var focusOnly = $('#filter-focus').is(':checked');
        var visible   = 0;

        $('#link-tbody .product-row').each(function () {
            var $r = $(this);
            if (parentId  && $r.data('parent-id') + '' !== parentId)     { $r.hide(); return; }
            if (childId   && $r.data('child-id')  + '' !== childId)      { $r.hide(); return; }
            if (selection === 'selected'   && $r.data('selected') != 1)  { $r.hide(); return; }
            if (selection === 'unselected' && $r.data('selected') == 1)  { $r.hide(); return; }
            if (naOnly    && $r.data('na')   != 1)                       { $r.hide(); return; }
            if (discOnly  && $r.data('disc') != 1)                       { $r.hide(); return; }
            if (focusOnly && $r.data('focus')!= 1)                       { $r.hide(); return; }
            if (search) {
                var name = $r.data('name') || '';
                if (name.indexOf(search) === -1)                         { $r.hide(); return; }
            }
            $r.show();
            visible++;
        });

        /* Show/hide child group headers */
        $('#link-tbody .group-child-row').each(function () {
            var cid = $(this).data('child-id');
            $(this).toggle($('.product-row.child-of-' + cid + ':visible').length > 0);
        });

        /* Show/hide parent group headers */
        $('#link-tbody .group-parent-row').each(function () {
            var pid = $(this).data('parent-id');
            $(this).toggle($('.group-child-row.child-of-' + pid + ':visible').length > 0);
        });

        $('#visible-count').text(visible);
        $('#empty-row').toggle(visible === 0);
        renumber();
    }

    /* Filter child dropdown based on parent selection */
    $('#filter-parent').on('change', function () {
        var pid = $(this).val();
        $('#filter-child option').each(function () {
            var $o = $(this);
            if (!$o.val()) { $o.show(); return; }
            $o.toggle(!pid || $o.data('parent') + '' === pid);
        });
        $('#filter-child').val('');
        applyFilters();
    });

    $('#filter-child, #filter-selection').on('change', applyFilters);
    $('#filter-search').on('input', applyFilters);
    $('#filter-na, #filter-disc, #filter-focus').on('change', function () {
        /* Uncheck other toggles when one is checked (mutually exclusive) */
        if ($(this).is(':checked')) {
            $('#filter-na, #filter-disc, #filter-focus').not(this).prop('checked', false);
        }
        applyFilters();
    });

    /* ── 5. Renumber visible product rows ── */
    function renumber() {
        var n = 0;
        $('#link-tbody .product-row:visible').each(function () {
            $(this).find('.sr-no-cell').text(++n);
        });
    }

});
</script>
@endsection