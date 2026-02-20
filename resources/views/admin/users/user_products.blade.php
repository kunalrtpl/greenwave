@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    /* Existing Styles */
    .category-card { border-radius: 8px !important; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .panel-heading { background-color: #f8f9fb !important; cursor: pointer; transition: background 0.3s; }
    .panel-heading:hover { background-color: #f1f4f7 !important; }
    .panel-title { font-weight: 600; color: #333; display: flex; justify-content: space-between; align-items: center; }
    .product-list-item { display: flex; justify-content: flex-start; align-items: center; padding: 12px 15px; border-bottom: 1px solid #edf2f7; transition: background 0.2s; }
    .product-list-item:last-child { border-bottom: none; }
    .product-list-item:hover { background-color: #fafafa; }
    .sr-no { width: 28px; height: 28px; background: #e2e8f0; color: #4a5568; border-radius: 50% !important; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; margin-right: 15px; flex-shrink: 0; }
    .product-info { flex-grow: 1; }
    .product-name { font-weight: 500; color: #2d3748; display: block; line-height: 1.2; }
    .product-desc { font-size: 0.85em; color: #718096; font-style: italic; }
    .custom-checkbox { transform: scale(1.2); cursor: pointer; margin-left: 10px; }
    .nav-pills.nav-stacked > li.active > a { background-color: #3598dc !important; }
    .nav-pills.nav-stacked > li > a { border-radius: 4px; margin-bottom: 5px; font-weight: 500; display: flex; justify-content: space-between; align-items: center; }

    /* Badge Styles */
    .badge-selected { background-color: #36c6d3; color: white; padding: 2px 8px; border-radius: 12px !important; font-size: 11px; margin-left: 5px; }
    .badge-tab { background-color: rgba(255,255,255,0.3); color: #fff; }
    li:not(.active) .badge-tab { background-color: #e1e5ec; color: #666; }

    /* FLOATING SAVE BAR STYLES */
    .floating-save-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.9); /* Glass effect */
        backdrop-filter: blur(10px);
        padding: 15px 0;
        border-top: 1px solid #e1e5ec;
        z-index: 1000;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.08);
        text-align: center;
    }
    
    /* Add some padding to the bottom of the page content so the last product isn't hidden by the bar */
    .page-content { padding-bottom: 80px !important; }
    
    .btn-save-float {
        padding: 10px 40px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 30px !important;
        box-shadow: 0 4px 10px rgba(53, 152, 220, 0.3);
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-link font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">{{ $title }}</span>
                </div>
            </div>
            
            <div class="portlet-body">
                <form method="POST" action="{{ route('admin.users.products.save', $user->id) }}">
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
                                                        <small class="text-muted">({{ count($child['products'] ?? []) }} Products)</small>
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
                                                            <div class="product-info">
                                                                <span class="product-name">{{$product['product_name']}}</span>
                                                                <span class="product-desc">({{$product['description']}})</span>
                                                            </div>
                                                            <div class="product-action">
                                                                <input type="checkbox" 
                                                                       class="custom-checkbox product-cb"
                                                                       name="products[]" 
                                                                       value="{{$product['id']}}"
                                                                       {{ in_array($product['id'],$selectedProducts) ? 'checked' : '' }}>
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
                        <button type="submit" class="btn btn-primary blue btn-save-float">
                            <i class="fa fa-check"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.product-cb').on('change', function() {
        // Update Child Count (Current Group)
        let $panel = $(this).closest('.category-card');
        let selectedInChild = $panel.find('.product-cb:checked').length;
        $panel.find('.child-count').text(selectedInChild + ' Selected');

        // Update Parent Count (Left Tab)
        let parentId = $panel.data('parent-ref');
        let totalSelectedInParent = 0;
        
        $(`.category-card[data-parent-ref="${parentId}"]`).each(function() {
            totalSelectedInParent += $(this).find('.product-cb:checked').length;
        });

        $(`.parent-count[data-parent-id="${parentId}"]`).text(totalSelectedInParent);
    });
});
</script>
@endsection