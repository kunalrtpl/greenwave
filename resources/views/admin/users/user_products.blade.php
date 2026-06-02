@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    .page-content { padding-bottom: 90px !important; }

    /* ── Header counter ── */
    .total-counter-wrapper {
        display: inline-flex; align-items: center; gap: 6px;
        background: #ebf5ff; border: 1px solid #3598dc;
        padding: 4px 14px; border-radius: 20px !important;
        margin-left: 15px; vertical-align: middle;
    }
    .total-label        { font-size: 12px; color: #3598dc; font-weight: 600; text-transform: uppercase; }
    .total-count-global { font-size: 15px; color: #2b78ad; font-weight: 800; }

    /* ── Search ── */
    .search-wrap { position: relative; margin-bottom: 20px; }
    .search-wrap .fa-search {
        position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
        color: #a0aec0; font-size: 14px; pointer-events: none;
    }
    #product-search {
        width: 100%; height: 42px; padding: 0 38px 0 38px;
        border: 1px solid #c8d0dc; border-radius: 8px !important;
        font-size: 14px; color: #2d3748;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    #product-search:focus {
        outline: none; border-color: #3598dc;
        box-shadow: 0 0 0 3px rgba(53,152,220,0.15);
    }
    #search-clear {
        position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
        color: #a0aec0; cursor: pointer; font-size: 14px; display: none;
    }
    #search-clear:hover { color: #e53e3e; }

    /* ── Parent row (full width) ── */
    .parent-section { margin-bottom: 10px; border: 1px solid #d1d9e3; border-radius: 8px; overflow: hidden; }

    .parent-toggle {
        width: 100%; display: flex; align-items: center; justify-content: space-between;
        padding: 13px 18px;
        background: linear-gradient(135deg, #3598dc 0%, #2980b9 100%);
        border: none; cursor: pointer; text-align: left;
    }
    .parent-toggle:hover { filter: brightness(1.05); }
    .parent-toggle .pt-left  { display: flex; align-items: center; gap: 10px; }
    .parent-toggle .pt-name  { font-size: 14px; font-weight: 700; color: #fff; }
    .parent-toggle .pt-badge {
        background: rgba(255,255,255,0.25); color: #fff;
        font-size: 11px; font-weight: 700; padding: 2px 10px;
        border-radius: 12px !important;
    }
    .parent-toggle .pt-arrow { color: rgba(255,255,255,0.8); font-size: 11px; transition: transform 0.2s; }
    .parent-toggle.collapsed .pt-arrow { transform: rotate(-90deg); }

    .parent-body { display: block; }
    .parent-body.collapsed { display: none; }

    /* ── Child row (full width, inside parent) ── */
    .child-section { border-top: 1px solid #e1e5ec; }
    .child-section:first-child { border-top: none; }

    .child-toggle {
        width: 100%; display: flex; align-items: center; justify-content: space-between;
        padding: 11px 18px 11px 28px;
        background: #f4f7fb; border: none; cursor: pointer; text-align: left;
    }
    .child-toggle:hover { background: #edf2f7; }
    .child-toggle .ct-left   { display: flex; align-items: center; gap: 8px; }
    .child-toggle .ct-name   { font-size: 13px; font-weight: 600; color: #2d3748; }
    .badge-blue     { background: #3598dc; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px !important; }
    .badge-selected { background: #36c6d3; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px !important; }
    .child-toggle .ct-arrow  { color: #a0aec0; font-size: 10px; transition: transform 0.2s; }
    .child-toggle.collapsed .ct-arrow { transform: rotate(-90deg); }

    .child-body { display: block; }
    .child-body.collapsed { display: none; }

    /* ── Product rows ── */
    .product-list-item {
        display: flex; align-items: center;
        padding: 10px 18px 10px 42px;
        border-top: 1px solid #f0f4f8;
        transition: background 0.15s;
    }
    .product-list-item:hover { background: #fafcff; }
    .product-list-item.hidden { display: none; }

    .sr-no {
        width: 26px; height: 26px; background: #e2e8f0; color: #4a5568;
        border-radius: 50% !important; display: flex; align-items: center;
        justify-content: center; font-size: 10px; font-weight: 700;
        flex-shrink: 0; margin-right: 12px;
    }
    .product-action { margin-right: 14px; display: flex; align-items: center; flex-shrink: 0; }
    .custom-checkbox { transform: scale(1.2); cursor: pointer; accent-color: #3598dc; }
    .product-info { flex: 1; min-width: 0; }
    .product-name { font-weight: 500; color: #2d3748; display: block; line-height: 1.3; }
    .product-desc { font-size: 12px; color: #718096; font-style: italic; }

    /* no results */
    .no-results-msg { text-align: center; padding: 30px; color: #a0aec0; display: none; }
    .no-results-msg i { font-size: 30px; margin-bottom: 8px; display: block; }

    /* ── Floating Save Bar ── */
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
                <form method="POST" action="{{ route('admin.users.products.save', $user->id) }}">
                    @csrf

                    {{-- Search --}}
                    <div class="search-wrap">
                        <i class="fa fa-search"></i>
                        <input type="text" id="product-search" placeholder="Search products by name...">
                        <i class="fa fa-times" id="search-clear"></i>
                    </div>

                    <div id="no-results-msg" class="no-results-msg">
                        <i class="fa fa-search"></i>
                        No products found matching your search.
                    </div>

                    {{-- ── Full-width accordion ── --}}
                    @foreach($hierarchy as $pKey => $parent)
                        @php
                            $allChildProductIds = [];
                            foreach($parent['children'] ?? [] as $c) {
                                foreach($c['products'] ?? [] as $p) { $allChildProductIds[] = $p['id']; }
                            }
                            $parentSelectedCount = count(array_intersect($allChildProductIds, $selectedProducts));
                        @endphp

                        <div class="parent-section" data-parent-id="{{ $parent['id'] }}">

                            {{-- Parent full-width toggle --}}
                            <button type="button" class="parent-toggle" data-target="pb-{{ $parent['id'] }}">
                                <div class="pt-left">
                                    <i class="fa fa-folder-open" style="color:rgba(255,255,255,0.8);"></i>
                                    <span class="pt-name">{{ $parent['name'] }}</span>
                                    <span class="pt-badge parent-count" data-parent-id="{{ $parent['id'] }}">{{ $parentSelectedCount }} selected</span>
                                </div>
                                <i class="fa fa-chevron-down pt-arrow"></i>
                            </button>

                            {{-- Parent body --}}
                            <div class="parent-body" id="pb-{{ $parent['id'] }}">

                                @foreach($parent['children'] ?? [] as $cKey => $child)
                                    @php
                                        $childProductIds    = array_column($child['products'] ?? [], 'id');
                                        $childSelectedCount = count(array_intersect($childProductIds, $selectedProducts));
                                    @endphp

                                    <div class="child-section" data-child-id="{{ $child['id'] }}">

                                        {{-- Child full-width toggle --}}
                                        <button type="button" class="child-toggle" data-target="cb-{{ $child['id'] }}">
                                            <div class="ct-left">
                                                <i class="fa fa-tag" style="color:#3598dc; font-size:11px;"></i>
                                                <span class="ct-name">{{ $child['name'] }}</span>
                                                <span class="badge-blue">{{ count($child['products'] ?? []) }} Products</span>
                                                <span class="badge-selected child-count" data-child-id="{{ $child['id'] }}">{{ $childSelectedCount }} Selected</span>
                                            </div>
                                            <i class="fa fa-chevron-down ct-arrow"></i>
                                        </button>

                                        {{-- Products --}}
                                        <div class="child-body" id="cb-{{ $child['id'] }}">
                                            @foreach($child['products'] ?? [] as $index => $product)
                                            <div class="product-list-item"
                                                 data-name="{{ strtolower($product['product_name']) }}"
                                                 data-child-id="{{ $child['id'] }}"
                                                 data-parent-id="{{ $parent['id'] }}">
                                                <div class="sr-no">{{ $index + 1 }}</div>
                                                <div class="product-action">
                                                    <input type="checkbox"
                                                           class="custom-checkbox product-cb"
                                                           name="products[]"
                                                           value="{{ $product['id'] }}"
                                                           data-child-id="{{ $child['id'] }}"
                                                           data-parent-id="{{ $parent['id'] }}"
                                                           {{ in_array($product['id'], $selectedProducts) ? 'checked' : '' }}>
                                                </div>
                                                <div class="product-info">
                                                    <span class="product-name">{{ $product['product_name'] }}</span>
                                                    <span class="product-desc">({{ $product['description'] ?? '' }})</span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>{{-- /child-body --}}

                                    </div>{{-- /child-section --}}
                                @endforeach

                            </div>{{-- /parent-body --}}
                        </div>{{-- /parent-section --}}
                    @endforeach

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

    /* ── 1. Parent toggle (full-width blue row) ── */
    $(document).on('click', '.parent-toggle', function () {
        var id    = $(this).data('target');
        var $body = $('#' + id);
        var open  = $body.is(':visible');
        $body.toggleClass('collapsed', open);
        $body.toggle(!open);
        $(this).toggleClass('collapsed', open);
    });

    /* ── 2. Child toggle (full-width grey row) ── */
    $(document).on('click', '.child-toggle', function () {
        var id    = $(this).data('target');
        var $body = $('#' + id);
        var open  = $body.is(':visible');
        $body.toggleClass('collapsed', open);
        $body.toggle(!open);
        $(this).toggleClass('collapsed', open);
    });

    /* ── 3. Checkbox counter update ── */
    $(document).on('change', '.product-cb', function () {
        var childId  = $(this).data('child-id');
        var parentId = $(this).data('parent-id');

        var childChecked  = $('input.product-cb[data-child-id="'  + childId  + '"]:checked').length;
        var parentChecked = $('input.product-cb[data-parent-id="' + parentId + '"]:checked').length;
        var total         = $('.product-cb:checked').length;

        $('.child-count[data-child-id="' + childId + '"]').text(childChecked + ' Selected');
        $('.parent-count[data-parent-id="' + parentId + '"]').text(parentChecked + ' selected');
        $('#top-total-count, #bottom-total-count').text(total);
    });

    /* ── 4. Search ── */
    $('#product-search').on('input', function () {
        var q = $.trim($(this).val()).toLowerCase();

        if (q === '') {
            // show everything, restore all rows
            $('.product-list-item').removeClass('hidden');
            $('.parent-section, .child-section').show();
            // re-open parent/child bodies
            $('.parent-body, .child-body').show().removeClass('collapsed');
            $('.parent-toggle, .child-toggle').removeClass('collapsed');
            $('#search-clear').hide();
            $('#no-results-msg').hide();
            renumberAll();
            return;
        }

        $('#search-clear').show();

        var anyVisible = false;

        // Walk each child section
        $('.child-section').each(function () {
            var $child     = $(this);
            var childHasAny = false;

            $child.find('.product-list-item').each(function () {
                var name = $(this).data('name') || '';
                if (name.indexOf(q) !== -1) {
                    $(this).removeClass('hidden');
                    childHasAny = true;
                    anyVisible  = true;
                } else {
                    $(this).addClass('hidden');
                }
            });

            // show/hide child and open its body if matched
            if (childHasAny) {
                $child.show();
                var bodyId = $child.find('.child-toggle').data('target');
                $('#' + bodyId).show().removeClass('collapsed');
                $child.find('.child-toggle').removeClass('collapsed');
            } else {
                $child.hide();
            }
        });

        // Show/hide parent sections based on whether any child matched
        $('.parent-section').each(function () {
            var $p = $(this);
            if ($p.find('.child-section:visible').length > 0) {
                $p.show();
                var pbId = $p.find('.parent-toggle').data('target');
                $('#' + pbId).show().removeClass('collapsed');
                $p.find('.parent-toggle').removeClass('collapsed');
            } else {
                $p.hide();
            }
        });

        $('#no-results-msg').toggle(!anyVisible);
        renumberAll();
    });

    $('#search-clear').on('click', function () {
        $('#product-search').val('').trigger('input');
    });

    /* ── 5. Renumber visible rows per child ── */
    function renumberAll() {
        $('.child-section:visible').each(function () {
            var n = 0;
            $(this).find('.product-list-item:not(.hidden) .sr-no').each(function () {
                $(this).text(++n);
            });
        });
    }

});
</script>
@endsection