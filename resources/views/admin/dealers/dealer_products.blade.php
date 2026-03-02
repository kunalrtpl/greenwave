@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    /* MATCHING USER PRODUCTS DESIGN */
    .category-card { border-radius: 8px !important; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .panel-heading { background-color: #f8f9fb !important; cursor: pointer; transition: background 0.3s; }
    .panel-heading:hover { background-color: #f1f4f7 !important; }
    .panel-title { font-weight: 600; color: #333; display: flex; justify-content: space-between; align-items: center; }
    .product-list-item { display: flex; justify-content: flex-start; align-items: center; padding: 12px 15px; border-bottom: 1px solid #edf2f7; transition: background 0.2s; }
    .product-list-item:last-child { border-bottom: none; }
    .product-list-item:hover { background-color: #fafafa; }
    
    .sr-no { width: 28px; height: 28px; background: #e2e8f0; color: #4a5568; border-radius: 50% !important; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; margin-right: 12px; flex-shrink: 0; }
    .product-action { margin-right: 15px; display: flex; align-items: center; }
    .custom-checkbox { transform: scale(1.2); cursor: pointer; }
    
    .product-info { flex-grow: 1; }
    .product-name { font-weight: 500; color: #2d3748; display: block; line-height: 1.2; }
    .product-desc { font-size: 0.85em; color: #718096; font-style: italic; }
    
    .nav-pills.nav-stacked > li.active > a { background-color: #3598dc !important; }
    .nav-pills.nav-stacked > li > a { border-radius: 4px; margin-bottom: 5px; font-weight: 500; display: flex; justify-content: space-between; align-items: center; }

    /* Badge Styles */
    .badge-selected { background-color: #36c6d3; color: white; padding: 2px 8px; border-radius: 12px !important; font-size: 11px; margin-left: 5px; }
    .badge-blue { background-color: #3598DC; color: white; padding: 2px 8px; border-radius: 12px !important; font-size: 11px; margin-left: 5px; }
    .badge-tab { background-color: rgba(255,255,255,0.3); color: #fff; }
    li:not(.active) .badge-tab { background-color: #e1e5ec; color: #666; }

    /* FLOATING SAVE BAR - FIXED */
    .floating-save-bar {
        position: fixed;
        bottom: 0;
        left: 235px; /* Aligned for sidebar */
        right: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 15px 0;
        border-top: 1px solid #e1e5ec;
        z-index: 1000;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.08);
        text-align: center;
    }
    @media (max-width: 991px) { .floating-save-bar { left: 0; } }
    
    .page-content { padding-bottom: 120px !important; }
    
    .btn-save-float {
        padding: 10px 40px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 30px !important;
        box-shadow: 0 4px 10px rgba(53, 152, 220, 0.3);
    }

    .total-counter-wrapper {
        display: inline-block;
        background: #ebf5ff;
        border: 1px solid #3598dc;
        padding: 4px 12px;
        border-radius: 20px !important;
        margin-left: 15px;
        vertical-align: middle;
    }
    .total-label { font-size: 12px; color: #3598dc; font-weight: 600; text-transform: uppercase; }
    .total-count-global { font-size: 14px; color: #2b78ad; font-weight: 800; }
    .bottom-total { margin-bottom: 10px; display: block; font-weight: 600; color: #444; }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-link font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">{{ $title }}</span>
                    <div class="total-counter-wrapper">
                        <span class="total-label">Total Selected: </span>
                        <span class="total-count-global" id="top-total-count">{{ count($selectedProducts) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="portlet-body">
                <form method="POST" action="{{ url('admin/dealers/'.$dealer->id.'/products') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <ul class="nav nav-pills nav-stacked">
                                @foreach($hierarchy as $key => $parent)
                                    @php
                                        $allChildProductIds = [];
                                        foreach($parent['children'] ?? [] as $c) {
                                            foreach($c['products'] ?? [] as $p) { $allChildProductIds[] = $p['id']; }
                                        }
                                        $tabSelectedCount = count(array_intersect($allChildProductIds, $selectedProducts));
                                    @endphp
                                    <li class="{{ $key==0 ? 'active' : '' }}">
                                        <a href="#tab{{$parent['id']}}" data-toggle="tab">
                                            {{$parent['name']}}
                                            <span class="badge badge-tab parent-count" data-parent-id="{{$parent['id']}}">{{ $tabSelectedCount }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="col-md-9">
                            <div class="tab-content">
                                @foreach($hierarchy as $key => $parent)
                                <div class="tab-pane {{ $key==0 ? 'active' : '' }}" id="tab{{$parent['id']}}">
                                    
                                    @foreach($parent['children'] ?? [] as $child)
                                        @php
                                            $childProductIds = array_column($child['products'] ?? [], 'id');
                                            $childSelectedCount = count(array_intersect($childProductIds, $selectedProducts));
                                        @endphp
                                        <div class="panel panel-default category-card" data-parent-ref="{{$parent['id']}}">
                                            <div class="panel-heading" data-toggle="collapse" href="#collapse{{$child['id']}}">
                                                <h4 class="panel-title">
                                                    <span>
                                                        {{$child['name']}} 
                                                        <small class="badge-blue">({{ count($child['products'] ?? []) }} Products)</small>
                                                        <span class="badge-selected child-count">{{ $childSelectedCount }} Selected</span>
                                                    </span>
                                                    <i class="fa fa-chevron-down small text-muted"></i>
                                                </h4>
                                            </div>

                                            <div id="collapse{{$child['id']}}" class="panel-collapse collapse in">
                                                <div class="panel-body" style="padding: 0;">
                                                    @if(!empty($child['products']))
                                                        @foreach($child['products'] as $index => $product)
                                                        <div class="product-list-item">
                                                            <div class="sr-no">{{ $index + 1 }}</div>
                                                            
                                                            <div class="product-action">
                                                                <input type="checkbox" 
                                                                       class="custom-checkbox product-cb"
                                                                       name="products[]" 
                                                                       value="{{$product['id']}}"
                                                                       {{ in_array($product['id'], $selectedProducts) ? 'checked' : '' }}>
                                                            </div>

                                                            <div class="product-info">
                                                                <span class="product-name">{{$product['product_name']}}</span>
                                                                <span class="product-desc">({{$product['description']}})</span>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="floating-save-bar">
                        <div class="bottom-total">
                            Total Products Selected: <span class="total-count-global" id="bottom-total-count">{{ count($selectedProducts) }}</span>
                        </div>
                        <button type="submit" class="btn btn-primary blue btn-save-float">
                            <i class="fa fa-check"></i> Save Dealer Products
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function updateGlobalCounters() {
        let total = $('.product-cb:checked').length;
        $('#top-total-count').text(total);
        $('#bottom-total-count').text(total);
    }

    $('.product-cb').on('change', function() {
        // 1. Update Category Count (The badge on the panel header)
        let $panel = $(this).closest('.category-card');
        let selectedInChild = $panel.find('.product-cb:checked').length;
        $panel.find('.child-count').text(selectedInChild + ' Selected');

        // 2. Update Parent Count (The badge on the left-side tab)
        let parentId = $panel.data('parent-ref');
        let totalSelectedInParent = 0;
        
        $(`.category-card[data-parent-ref="${parentId}"]`).each(function() {
            totalSelectedInParent += $(this).find('.product-cb:checked').length;
        });
        $(`.parent-count[data-parent-id="${parentId}"]`).text(totalSelectedInParent);

        // 3. Update Global Counters
        updateGlobalCounters();
    });
});
</script>
@endsection