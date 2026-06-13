@extends('layouts.adminLayout.backendLayout')
@section('content')

@php
    $isSampling   = !empty($productdata) && ($productdata['product_status_type'] ?? '') === 'sampling';
    $isGeneration = !empty($productdata) && ($productdata['product_status_type'] ?? '') === 'generation';
    $isApproved   = !empty($productdata) && ($productdata['product_status_type'] ?? '') === 'approved';
    $isEditMode   = !empty($productdata);
    $currentType  = $productdata['product_status_type'] ?? 'sampling';
    $showTypeSelector = !$isEditMode || $isSampling;
@endphp

<style>
/* ═══════════════════════════════════════════
   BASE
═══════════════════════════════════════════ */
.page-content { padding-bottom: 80px !important; background: #f0f3f8 !important; }

/* ═══════════════════════════════════════════
   PAGE HEADER
═══════════════════════════════════════════ */
.pf-page-header {
    background: linear-gradient(135deg, #1a3a5c 0%, #2d6a9f 100%);
    border-radius: 8px; padding: 18px 24px; margin-bottom: 18px;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: 0 4px 16px rgba(26,58,92,0.22);
}
.pf-header-left { display: flex; align-items: center; gap: 12px; }
.pf-header-icon {
    width: 42px; height: 42px; background: rgba(255,255,255,0.15);
    border-radius: 10px; display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #fff; flex-shrink: 0;
}
.pf-header-title { color: #fff; font-size: 17px; font-weight: 700; margin: 0; }
.pf-header-sub   { color: rgba(255,255,255,0.6); font-size: 11px; margin-top: 1px; }
.pf-breadcrumb   { display: flex; align-items: center; gap: 6px; }
.pf-breadcrumb a { color: rgba(255,255,255,0.65); font-size: 11px; text-decoration: none; }
.pf-breadcrumb a:hover { color: #fff; }
.pf-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); font-size: 9px; }

/* ═══════════════════════════════════════════
   CARD
═══════════════════════════════════════════ */
.pf-card {
    background: #fff; border-radius: 8px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.06);
    border: 1px solid #e4e9f2; margin-bottom: 16px; overflow: hidden;
}
.pf-card-header {
    padding: 13px 22px; border-bottom: 1px solid #eef1f7;
    display: flex; align-items: center; gap: 10px; background: #fafbfd;
}
.pf-section-icon {
    width: 30px; height: 30px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
}
.pf-section-title { font-size: 13px; font-weight: 700; color: #2d3748; margin: 0; }
.pf-section-sub   { font-size: 11px; color: #a0aec0; margin-top: 1px; }
.pf-card-body     { padding: 20px 22px; }

.icon-blue   { background: #ebf4ff; color: #2b6cb0; }
.icon-green  { background: #f0fff4; color: #276749; }
.icon-purple { background: #faf5ff; color: #553c9a; }
.icon-teal   { background: #e6fffa; color: #2c7a7b; }

/* ═══════════════════════════════════════════
   PRODUCT TYPE CARDS — COMPACT
═══════════════════════════════════════════ */
.ptype-grid {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 10px;
}
.ptype-card {
    position: relative; border: 2px solid #e4e9f2; border-radius: 8px;
    padding: 12px 14px 12px; cursor: pointer; transition: all 0.18s;
    background: #fff; overflow: hidden; display: flex; align-items: flex-start; gap: 10px;
}
.ptype-card::before {
    content:''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: transparent; transition: background 0.18s; border-radius: 8px 8px 0 0;
}
.ptype-card:hover { transform: translateY(-1px); box-shadow: 0 3px 12px rgba(0,0,0,0.08); }
.ptype-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }

.ptype-card .ptype-icon-wrap {
    font-size: 20px; flex-shrink: 0; margin-top: 2px;
}
.ptype-card .ptype-content { flex: 1; min-width: 0; }
.ptype-card .ptype-pill {
    display: inline-block; font-size: 9px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    padding: 1px 7px; border-radius: 20px; margin-bottom: 5px;
}
.ptype-card .ptype-title {
    font-size: 12px; font-weight: 700; color: #2d3748;
    display: block; margin-bottom: 3px; line-height: 1.3;
}
.ptype-card .ptype-desc {
    font-size: 11px; color: #718096; line-height: 1.4; display: block;
}
.ptype-card .ptype-check {
    position: absolute; top: 10px; right: 10px;
    width: 18px; height: 18px; border-radius: 50%;
    border: 2px solid #e4e9f2; background: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; color: transparent; transition: all 0.18s; flex-shrink: 0;
}

/* Sampling */
.ptype-card.card-sampling.ptype-active { border-color: #ed8936; background: #fff8f0; }
.ptype-card.card-sampling.ptype-active::before { background: #ed8936; }
.ptype-card.card-sampling.ptype-active .ptype-title { color: #c05621; }
.ptype-card.card-sampling .ptype-pill { background: #feebc8; color: #c05621; }
.ptype-card.card-sampling.ptype-active .ptype-check { background: #ed8936; border-color: #ed8936; color: #fff; }

/* Approved */
.ptype-card.card-approved.ptype-active { border-color: #38a169; background: #f0fff4; }
.ptype-card.card-approved.ptype-active::before { background: #38a169; }
.ptype-card.card-approved.ptype-active .ptype-title { color: #276749; }
.ptype-card.card-approved .ptype-pill { background: #c6f6d5; color: #276749; }
.ptype-card.card-approved.ptype-active .ptype-check { background: #38a169; border-color: #38a169; color: #fff; }

/* Generation */
.ptype-card.card-generation.ptype-active { border-color: #805ad5; background: #faf5ff; }
.ptype-card.card-generation.ptype-active::before { background: #805ad5; }
.ptype-card.card-generation.ptype-active .ptype-title { color: #553c9a; }
.ptype-card.card-generation .ptype-pill { background: #e9d8fd; color: #553c9a; }
.ptype-card.card-generation.ptype-active .ptype-check { background: #805ad5; border-color: #805ad5; color: #fff; }

/* ═══════════════════════════════════════════
   NOTICES
═══════════════════════════════════════════ */
.pf-notice {
    display: none; border-radius: 6px; padding: 10px 14px;
    margin-top: 12px; gap: 10px; align-items: flex-start; font-size: 12px;
}
.pf-notice.pf-visible { display: flex; }
.pf-notice i { font-size: 13px; margin-top: 1px; flex-shrink: 0; }
.pf-notice p { margin: 0; line-height: 1.5; }
.pf-notice strong { font-weight: 700; }

.pf-notice-warning { background: #fffbeb; border: 1px solid #f6d860; border-left: 3px solid #ed8936; }
.pf-notice-warning i, .pf-notice-warning strong { color: #c05621; }
.pf-notice-warning p { color: #7b4f00; }

.pf-notice-success { background: #f0fff4; border: 1px solid #9ae6b4; border-left: 3px solid #38a169; }
.pf-notice-success i, .pf-notice-success strong { color: #276749; }
.pf-notice-success p { color: #22543d; }

.pf-notice-purple { background: #faf5ff; border: 1px solid #d6bcfa; border-left: 3px solid #805ad5; }
.pf-notice-purple i, .pf-notice-purple strong { color: #553c9a; }
.pf-notice-purple p { color: #44337a; }

/* ═══════════════════════════════════════════
   OLD PRODUCT BOX
═══════════════════════════════════════════ */
#oldProductBox {
    background: #faf5ff; border: 2px dashed #d6bcfa;
    border-radius: 8px; padding: 16px 18px; display: none; margin-top: 12px;
}
#oldProductBox .opb-title {
    font-size: 11px; font-weight: 700; color: #553c9a;
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
}

/* ═══════════════════════════════════════════
   FORM FIELDS
═══════════════════════════════════════════ */
.pf-form-group { margin-bottom: 16px; }
.pf-form-group > label {
    font-size: 11px; font-weight: 700; color: #4a5568;
    text-transform: uppercase; letter-spacing: 0.4px;
    margin-bottom: 6px; display: block;
}
.pf-form-group label .req { color: #e53e3e; margin-left: 2px; }
.pf-form-group label .opt {
    font-size: 10px; color: #a0aec0; font-weight: 400;
    text-transform: none; letter-spacing: 0; margin-left: 3px;
}
.pf-form-group .form-control {
    height: 36px; border: 1.5px solid #e2e8f0 !important;
    border-radius: 6px !important; font-size: 13px; color: #2d3748;
    padding: 0 11px; transition: border-color 0.18s, box-shadow 0.18s;
    box-shadow: none !important; background: #fff;
}
.pf-form-group .form-control:focus {
    border-color: #3598dc !important;
    box-shadow: 0 0 0 3px rgba(53,152,220,0.1) !important;
}
.pf-form-group textarea.form-control {
    height: auto; min-height: 72px; padding: 8px 11px; resize: vertical;
}
.pf-error {
    font-size: 11px; color: #e53e3e; margin-top: 4px;
    display: none; font-weight: 600;
}
.pf-error.visible { display: block; }
.pf-hint { font-size: 11px; color: #a0aec0; margin-top: 3px; }

.pf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; }
.pf-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0 20px; }

/* ═══════════════════════════════════════════
   STEP DIVIDER
═══════════════════════════════════════════ */
.pf-step-label {
    display: flex; align-items: center; gap: 8px; margin: 18px 0 14px;
}
.pf-step-line  { flex: 1; height: 1px; background: #eef1f7; }
.pf-step-text  {
    font-size: 10px; font-weight: 700; color: #a0aec0;
    text-transform: uppercase; letter-spacing: 0.7px; white-space: nowrap;
}

/* ═══════════════════════════════════════════
   STATUS TOGGLE
═══════════════════════════════════════════ */
.pf-status-toggle { display: flex; gap: 8px; max-width: 280px; }
.pf-status-btn {
    flex: 1; border: 2px solid #e2e8f0; border-radius: 7px;
    padding: 9px 14px; cursor: pointer; transition: all 0.18s;
    display: flex; align-items: center; gap: 7px; background: #fff;
}
.pf-status-btn input[type="radio"] { display: none; }
.pf-status-dot {
    width: 9px; height: 9px; border-radius: 50%;
    border: 2px solid #e2e8f0; flex-shrink: 0; transition: all 0.18s;
}
.pf-status-label { font-size: 13px; font-weight: 600; color: #718096; }
.pf-status-btn.is-active   { border-color: #38a169; background: #f0fff4; }
.pf-status-btn.is-active   .pf-status-dot  { background: #38a169; border-color: #38a169; }
.pf-status-btn.is-active   .pf-status-label { color: #276749; }
.pf-status-btn.is-inactive { border-color: #cbd5e0; background: #f7fafc; }
.pf-status-btn.is-inactive .pf-status-dot  { background: #a0aec0; border-color: #a0aec0; }
.pf-status-btn.is-inactive .pf-status-label { color: #718096; }

/* ═══════════════════════════════════════════
   EDIT MODE BADGE
═══════════════════════════════════════════ */
.pf-type-badge {
    display: inline-flex; align-items: center; gap: 7px;
    border-radius: 7px; padding: 8px 14px; font-size: 12px; font-weight: 600; border: 1.5px solid;
}
.pf-type-badge.badge-sampling   { background: #fff8f0; border-color: #fbd38d; color: #c05621; }
.pf-type-badge.badge-approved   { background: #f0fff4; border-color: #9ae6b4; color: #276749; }
.pf-type-badge.badge-generation { background: #faf5ff; border-color: #d6bcfa; color: #553c9a; }

.pf-upgrade-toggle {
    margin-top: 10px; padding: 10px 14px;
    background: #f7fafc; border: 1.5px dashed #bee3f8; border-radius: 7px;
    display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.18s;
}
.pf-upgrade-toggle:hover { border-color: #3598dc; background: #ebf8ff; }
.pf-upgrade-toggle input { cursor: pointer; }
.pf-upgrade-toggle span { font-size: 12px; font-weight: 600; color: #2b6cb0; }

/* ═══════════════════════════════════════════
   STICKY ACTIONS BAR
═══════════════════════════════════════════ */
.pf-actions-bar {
    background: #fff; border-radius: 8px; border: 1px solid #e4e9f2;
    box-shadow: 0 -2px 14px rgba(0,0,0,0.06);
    padding: 14px 22px; display: flex; align-items: center;
    justify-content: space-between; position: sticky; bottom: 14px; z-index: 99;
}
.pf-actions-left { display: flex; align-items: center; gap: 8px; }
.pf-save-hint    { font-size: 11px; color: #a0aec0; }

.btn-pf-save {
    background: linear-gradient(135deg,#1a6fa8 0%,#3598dc 100%) !important;
    border: none !important; border-radius: 7px !important;
    padding: 9px 24px !important; font-size: 13px !important; font-weight: 700 !important;
    color: #fff !important; letter-spacing: 0.2px;
    box-shadow: 0 3px 12px rgba(53,152,220,0.30) !important;
    transition: all 0.18s !important; display: inline-flex; align-items: center; gap: 6px;
}
.btn-pf-save:hover { transform: translateY(-1px) !important; box-shadow: 0 5px 16px rgba(53,152,220,0.4) !important; }

.btn-pf-cancel {
    background: #fff !important; border: 1.5px solid #e2e8f0 !important;
    border-radius: 7px !important; padding: 8px 18px !important;
    font-size: 13px !important; font-weight: 600 !important; color: #718096 !important;
    transition: all 0.18s !important; display: inline-flex; align-items: center; gap: 6px;
}
.btn-pf-cancel:hover { border-color: #a0aec0 !important; color: #4a5568 !important; background: #f7fafc !important; }
</style>

<div class="page-content-wrapper">
<div class="page-content">

    {{-- PAGE HEADER --}}
    <div class="pf-page-header">
        <div class="pf-header-left">
            <div class="pf-header-icon"><i class="fa fa-{{ $isEditMode ? 'pencil' : 'plus' }}"></i></div>
            <div>
                <div class="pf-header-title">{{ $title }}</div>
                <div class="pf-header-sub">{{ $isEditMode ? 'Update product information' : 'Register a new product in the system' }}</div>
            </div>
        </div>
        <div class="pf-breadcrumb">
            <a href="{{ url('admin/dashboard') }}"><i class="fa fa-home"></i></a>
            <span class="bc-sep"><i class="fa fa-chevron-right"></i></span>
            <a href="{{ url('admin/products') }}">Products</a>
            <span class="bc-sep"><i class="fa fa-chevron-right"></i></span>
            <a href="#" style="color:rgba(255,255,255,0.88);">{{ $isEditMode ? 'Edit' : 'Add' }}</a>
        </div>
    </div>

    <form id="ProductForm" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
        <input type="hidden" name="productid" value="{{ $productdata['id'] ?? '' }}">

        {{-- ══ CARD 1: PRODUCT TYPE ══ --}}
        <div class="pf-card">
            <div class="pf-card-header">
                <div class="pf-section-icon icon-blue"><i class="fa fa-tag"></i></div>
                <div>
                    <div class="pf-section-title">Product Classification</div>
                    <div class="pf-section-sub">Define what kind of product this is</div>
                </div>
            </div>
            <div class="pf-card-body">

                @if($showTypeSelector)
                    {{-- ADD MODE or SAMPLING EDIT MODE: show all 3 type cards --}}
                    <div class="pf-form-group" style="margin-bottom:0;">
                        <label>Select Product Type <span class="req">*</span></label>
                        <div class="ptype-grid">

                            <label class="ptype-card card-sampling {{ $isSampling ? 'ptype-active' : (!$isEditMode ? 'ptype-active' : '') }}" id="card-sampling">
                                <input type="radio" name="product_status_type" value="sampling"
                                    {{ ($isSampling || !$isEditMode) ? 'checked' : '' }}>
                                <div class="ptype-check"><i class="fa fa-check"></i></div>
                                <span class="ptype-icon-wrap">🧪</span>
                                <div class="ptype-content">
                                    <span class="ptype-pill">Quick Entry</span>
                                    <span class="ptype-title">New Product — Sampling Stage</span>
                                    <span class="ptype-desc">Basic info only. Not available for orders yet.</span>
                                </div>
                            </label>

                            <label class="ptype-card card-approved" id="card-approved">
                                <input type="radio" name="product_status_type" value="approved">
                                <div class="ptype-check"><i class="fa fa-check"></i></div>
                                <span class="ptype-icon-wrap">✅</span>
                                <div class="ptype-content">
                                    <span class="ptype-pill">Full Entry</span>
                                    <span class="ptype-title">New Product — Approved</span>
                                    <span class="ptype-desc">Full specs. Ready for orders and production.</span>
                                </div>
                            </label>

                            <label class="ptype-card card-generation" id="card-generation">
                                <input type="radio" name="product_status_type" value="generation">
                                <div class="ptype-check"><i class="fa fa-check"></i></div>
                                <span class="ptype-icon-wrap">🔄</span>
                                <div class="ptype-content">
                                    <span class="ptype-pill">New Generation</span>
                                    <span class="ptype-title">New Generation of Existing Product</span>
                                    <span class="ptype-desc">Old product will be marked Discontinued.</span>
                                </div>
                            </label>

                        </div>
                        <p id="Product-product_status_type" class="pf-error"></p>
                    </div>

                @elseif($isGeneration)
                    {{-- GENERATION EDIT: locked badge --}}
                    @php $parentProd = \App\Product::find($productdata['parent_product_id'] ?? 0); @endphp
                    <div class="pf-form-group" style="margin-bottom:0;">
                        <label>Product Type</label>
                        <div>
                            <span class="pf-type-badge badge-generation">
                                <i class="fa fa-code-fork"></i>
                                New Generation
                                @if(!empty($productdata['version'])) &nbsp;·&nbsp; <strong>{{ $productdata['version'] }}</strong> @endif
                                @if($parentProd) &nbsp;·&nbsp; of {{ $parentProd->product_name }} @endif
                            </span>
                        </div>
                    </div>
                    <input type="hidden" name="product_status_type" value="generation">

                @else
                    {{-- APPROVED EDIT: locked badge --}}
                    <div class="pf-form-group" style="margin-bottom:0;">
                        <label>Product Type</label>
                        <div>
                            <span class="pf-type-badge badge-approved">
                                <i class="fa fa-check-circle"></i> New Product — Approved
                                @if(!empty($productdata['version'])) &nbsp;·&nbsp; <strong>{{ $productdata['version'] }}</strong> @endif
                            </span>
                        </div>
                    </div>
                    <input type="hidden" name="product_status_type" value="approved">
                @endif

                {{-- Notices --}}
                <div id="samplingNotice" class="pf-notice pf-notice-warning {{ ($isSampling || !$isEditMode) ? 'pf-visible' : '' }}">
                    <i class="fa fa-info-circle"></i>
                    <p><strong>Sampling Stage</strong> — only basic fields required. Not visible for Dealer Orders, Customer Orders or Production until upgraded.</p>
                </div>
                <div id="upgradeNotice" class="pf-notice pf-notice-success">
                    <i class="fa fa-arrow-circle-up"></i>
                    <p><strong>Upgrading to Approved</strong> — all fields below are now active. Please fill them before saving.</p>
                </div>
                <div id="generationNotice" class="pf-notice pf-notice-purple">
                    <i class="fa fa-code-fork"></i>
                    <p>Select the existing product below. Name and code will be copied. Old product will be marked <strong>Discontinued</strong> on save.</p>
                </div>

                {{-- Old Product Selector (add mode OR sampling edit choosing generation) --}}
                @if($showTypeSelector)
                <div id="oldProductBox">
                    <div class="opb-title"><i class="fa fa-cube"></i> Select Existing Product</div>
                    <div class="pf-form-group" style="margin-bottom:0;">
                        <label>Existing Product <span class="req">*</span></label>
                        <select class="form-control select2" name="old_product_id" style="width:100%;" disabled>
                            <option value="">— Search and select —</option>
                            @foreach(\App\Product::where('status',1)->orderBy('product_name')->get() as $p)
                                <option value="{{ $p->id }}"
                                    data-product_code="{{ $p->product_code }}"
                                    data-product_name="{{ e($p->product_name) }}"
                                    data-physical_form="{{ $p->physical_form }}"
                                    data-product_detail_id="{{ $p->product_detail_id }}"
                                    data-short_description="{{ e($p->short_description) }}"
                                    data-description="{{ e($p->description) }}"
                                    data-suggested_dosage="{{ e($p->suggested_dosage) }}"
                                    data-packing_type_id="{{ $p->packing_type_id }}"
                                    data-additional_packing_type_id="{{ $p->additional_packing_type_id }}"
                                    data-standard_fill_size="{{ $p->standard_fill_size }}"
                                    data-packing_size_id="{{ $p->packing_size_id }}"
                                    data-label_id="{{ $p->label_id }}"
                                    data-shelf_life="{{ $p->shelf_life }}"
                                >{{ $p->product_name }} ({{ $p->product_code }})@if($p->version) · {{ $p->version }}@endif</option>
                            @endforeach
                        </select>
                        <p id="Product-old_product_id" class="pf-error"></p>
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- ══ CARD 2: BASIC INFO ══ --}}
        <div class="pf-card">
            <div class="pf-card-header">
                <div class="pf-section-icon icon-teal"><i class="fa fa-info-circle"></i></div>
                <div>
                    <div class="pf-section-title">Basic Information</div>
                    <div class="pf-section-sub">Core identity — always required</div>
                </div>
            </div>
            <div class="pf-card-body">

                <div class="pf-grid-2">
                    <div class="pf-form-group">
                        <label>Product Vertical <span class="req">*</span></label>
                        <select class="form-control select2" name="is_trader_product" style="width:100%;">
                            @foreach(product_types() as $pkey => $protype)
                                <option value="{{ $pkey }}"
                                    @if(empty($productdata)) @if($pkey==0) selected @endif
                                    @else @if($productdata['is_trader_product']==$pkey) selected @endif
                                    @endif>{{ $protype }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pf-form-group">
                        <label>Physical Form <span class="req">*</span></label>
                        <select class="form-control" name="physical_form">
                            @foreach(physical_forms() as $form => $physicalForm)
                                <option value="{{ $physicalForm }}"
                                    @if(!empty($productdata) && $productdata['physical_form']==$physicalForm) selected @endif>
                                    {{ $physicalForm }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Product Name always present. Product Code row hidden in sampling mode --}}
                <div class="pf-form-group">
                    <label>Product Name <span class="req">*</span></label>
                    <input type="text" placeholder="Full product name" name="product_name"
                           class="form-control" value="{{ $productdata['product_name'] ?? '' }}">
                    <p id="Product-product_name" class="pf-error"></p>
                </div>

                <div class="pf-form-group" id="productCodeRow">
                    <label>Product Code <span class="req">*</span></label>
                    <input type="text" placeholder="e.g. GW-001" name="product_code"
                           class="form-control" value="{{ $productdata['product_code'] ?? '' }}">
                    <p id="Product-product_code" class="pf-error"></p>
                </div>

                <div class="pf-form-group">
                    <label>Product Category <span class="req">*</span></label>
                    <div class="pf-grid-2">
                        <div>
                            <select class="form-control select2" name="product_detail_id" style="width:100%;">
                                <option value="">— Please Select —</option>
                                @foreach(product_details_with_levels() as $prodetail)
                                    <option data-level="{{ $prodetail['level'] }}" value="{{ $prodetail['id'] }}"
                                        {{ (!empty($productdata) && $productdata['product_detail_id']==$prodetail['id']) ? 'selected' : '' }}>
                                        {{ $prodetail['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p id="Product-product_detail_id" class="pf-error"></p>
                        </div>
                        <div style="padding-top:8px;">
                            <span id="ShowLevel" style="font-size:12px; color:#805ad5; font-weight:600;"></span>
                        </div>
                    </div>
                </div>

                <div class="pf-form-group">
                    <label>Short Description <span class="req">*</span></label>
                    <textarea placeholder="Brief summary (1–2 sentences)..." class="form-control"
                              name="short_description" rows="2">{{ $productdata['short_description'] ?? '' }}</textarea>
                    <p id="Product-short_description" class="pf-error"></p>
                </div>

            </div>
        </div>

        {{-- ══ CARD 3: FULL DETAILS ══ --}}
        <div class="pf-card" id="fullFieldsCard">
            <div class="pf-card-header">
                <div class="pf-section-icon icon-purple"><i class="fa fa-file-text-o"></i></div>
                <div>
                    <div class="pf-section-title">Product Details</div>
                    <div class="pf-section-sub">Full specifications — required for approved products</div>
                </div>
            </div>
            <div class="pf-card-body">

                <div class="pf-step-label" style="margin-top:0;">
                    <span class="pf-step-text">Descriptions</span>
                    <div class="pf-step-line"></div>
                </div>

                <div class="pf-form-group otherProType">
                    <label>Long Description <span class="opt">(optional)</span></label>
                    <textarea placeholder="Detailed product description..." class="form-control"
                              name="description" rows="3">{{ $productdata['description'] ?? '' }}</textarea>
                </div>

                <div class="pf-form-group otherProType">
                    <label>Suggested Dosage <span class="req">*</span></label>
                    <textarea placeholder="Recommended usage and dosage instructions..." class="form-control"
                              name="suggested_dosage" rows="2">{{ $productdata['suggested_dosage'] ?? '' }}</textarea>
                    <p id="Product-suggested_dosage" class="pf-error"></p>
                </div>

                <div class="pf-step-label">
                    <span class="pf-step-text">Packing &amp; Sizing</span>
                    <div class="pf-step-line"></div>
                </div>

                @php $packing_types = \App\PackingType::packing_types(); @endphp
                <div class="pf-grid-2">
                    <div class="pf-form-group">
                        <label>Standard Packing Type <span class="req">*</span></label>
                        <select class="form-control select2" name="packing_type_id" style="width:100%;">
                            <option value="">— Please Select —</option>
                            @foreach($packing_types as $typeInfo)
                                <option value="{{ $typeInfo['id'] }}"
                                    {{ (!empty($productdata) && $productdata['packing_type_id']==$typeInfo['id']) ? 'selected' : '' }}>
                                    {{ $typeInfo['name'] }}</option>
                            @endforeach
                        </select>
                        <p id="Product-packing_type_id" class="pf-error"></p>
                    </div>
                    <div class="pf-form-group">
                        <label>Additional Packing Type <span class="opt">(if any)</span></label>
                        <select class="form-control select2" name="additional_packing_type_id" style="width:100%;">
                            <option value="">— Please Select —</option>
                            @foreach($packing_types as $typeInfo)
                                @if($typeInfo['additional_packing']==1)
                                <option value="{{ $typeInfo['id'] }}"
                                    {{ (!empty($productdata) && $productdata['additional_packing_type_id']==$typeInfo['id']) ? 'selected' : '' }}>
                                    {{ $typeInfo['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pf-grid-3">
                    <div class="pf-form-group">
                        <label>Standard Fill Per Packing <span class="req">*</span></label>
                        <input type="number" placeholder="e.g. 500" name="standard_fill_size"
                               class="form-control" value="{{ $productdata['standard_fill_size'] ?? '' }}">
                        <p id="Product-standard_fill_size" class="pf-error"></p>
                    </div>
                    <div class="pf-form-group">
                        <label>Standard Order Size <span class="req">*</span></label>
                        @php $packing_sizes = \App\PackingSize::order_sizes(); @endphp
                        <select class="form-control select2" name="packing_size_id" style="width:100%;">
                            <option value="">— Please Select —</option>
                            @foreach($packing_sizes as $sizeInfo)
                                <option value="{{ $sizeInfo['id'] }}"
                                    {{ (!empty($productdata) && $productdata['packing_size_id']==$sizeInfo['id']) ? 'selected' : '' }}>
                                    {{ $sizeInfo['size'] }} Kg</option>
                            @endforeach
                        </select>
                        <p id="Product-packing_size_id" class="pf-error"></p>
                        @if(isset($productdata['packing_size_id']))
                            <p class="pf-hint">Order Size Id: {{ $productdata['packing_size_id'] }}</p>
                        @endif
                    </div>
                    <div class="pf-form-group">
                        <label>Shelf Life <span class="req">*</span> <span class="opt">months</span></label>
                        <input type="text" placeholder="e.g. 24" name="shelf_life"
                               class="form-control" value="{{ $productdata['shelf_life'] ?? '' }}">
                        <p id="Product-shelf_life" class="pf-error"></p>
                    </div>
                </div>

                <div class="pf-grid-2">
                    <div class="pf-form-group">
                        <label>Select Label</label>
                        <select class="form-control select2" name="label_id" style="width:100%;">
                            @foreach($labels as $labelInfo)
                                <option value="{{ $labelInfo['id'] }}"
                                    {{ (!empty($productdata) && $productdata['label_id']==$labelInfo['id']) ? 'selected' : '' }}>
                                    {{ $labelInfo['label_type'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div></div>
                </div>

                <div class="pf-step-label">
                    <span class="pf-step-text">Lab &amp; Recipe</span>
                    <div class="pf-step-line"></div>
                </div>

                <div class="pf-grid-2">
                    <div class="pf-form-group otherProType">
                        <label>Recipe No. <span class="req">*</span></label>
                        <input type="text" placeholder="e.g. LAB-2024-001" name="lab_recipe_number"
                               class="form-control" value="{{ $productdata['lab_recipe_number'] ?? '' }}">
                        <p id="Product-lab_recipe_number" class="pf-error"></p>
                    </div>
                    <div class="pf-form-group otherProType">
                        <label>Product Introduced On</label>
                        <input type="date" name="product_introduced_on" class="form-control"
                               value="{{ $productdata['product_introduced_on'] ?? date('Y-m-d') }}">
                        <p id="Product-product_introduced_on" class="pf-error"></p>
                    </div>
                </div>

                <div class="pf-step-label">
                    <span class="pf-step-text">Raw Materials</span>
                    <div class="pf-step-line"></div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="inherit_type" value="Inhouse">
                        <div id="InheritDiv">@include('admin.products.product-inhouse')</div>
                        <p id="Product-raw_materials" class="pf-error"></p>
                    </div>
                </div>

                <div class="pf-step-label">
                    <span class="pf-step-text">Additional Info</span>
                    <div class="pf-step-line"></div>
                </div>

                <div class="pf-form-group otherProType">
                    <label>Search Keywords <span class="opt">(optional)</span></label>
                    <div class="row"><div class="col-md-6">
                        <input type="text" placeholder="e.g. softener, textile..." name="keywords"
                               class="form-control" value="{{ $productdata['keywords'] ?? '' }}">
                    </div></div>
                </div>

                <div class="pf-form-group">
                    <label>Remarks <span class="opt">(optional)</span></label>
                    <div class="row"><div class="col-md-6">
                        <textarea placeholder="Internal notes..." class="form-control"
                                  name="remarks" rows="2">{{ $productdata['remarks'] ?? '' }}</textarea>
                    </div></div>
                </div>

            </div>
        </div>

        {{-- ══ CARD 4: STATUS ══ --}}
        <div class="pf-card">
            <div class="pf-card-header">
                <div class="pf-section-icon icon-green"><i class="fa fa-toggle-on"></i></div>
                <div>
                    <div class="pf-section-title">Product Status</div>
                    <div class="pf-section-sub">Control visibility and availability</div>
                </div>
            </div>
            <div class="pf-card-body">
                <div class="pf-status-toggle">
                    <label class="pf-status-btn {{ (!$isEditMode || ($productdata['status'] ?? 1)==1) ? 'is-active' : '' }}" id="btn-active">
                        <input type="radio" name="status" value="1"
                            @if(!empty($productdata) && $productdata['status']==1) checked
                            @elseif(empty($productdata)) checked @endif>
                        <span class="pf-status-dot"></span>
                        <span class="pf-status-label">Active</span>
                    </label>
                    <label class="pf-status-btn {{ (!empty($productdata) && $productdata['status']==0) ? 'is-inactive' : '' }}" id="btn-inactive">
                        <input type="radio" name="status" value="0"
                            @if(!empty($productdata) && $productdata['status']==0) checked @endif>
                        <span class="pf-status-dot"></span>
                        <span class="pf-status-label">Inactive</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ══ ACTIONS BAR ══ --}}
        <div class="pf-actions-bar">
            <div class="pf-actions-left">
                <a href="{{ url('admin/products') }}" class="btn-pf-cancel">
                    <i class="fa fa-arrow-left"></i> Cancel
                </a>
                <span class="pf-save-hint">All required fields must be filled before saving</span>
            </div>
            <button type="submit" class="btn-pf-save">
                <i class="fa fa-save"></i>
                {{ $isEditMode ? 'Save Changes' : 'Add Product' }}
            </button>
        </div>

    </form>
</div>
</div>

<script type="text/javascript">
$(document).ready(function () {

    /* ── Initial state ── */
    @if($isEditMode && !$isSampling)
        showFullMode();
    @else
        showSamplingMode();
    @endif

    /* ══ TYPE CARD SELECT (add mode + sampling edit mode) ══ */
    $(document).on('change', '[name=product_status_type]', function () {
        var val = $(this).val();
        $('.ptype-card').removeClass('ptype-active');
        $(this).closest('.ptype-card').addClass('ptype-active');
        $('#samplingNotice, #upgradeNotice, #generationNotice').removeClass('pf-visible');
        $('#oldProductBox').slideUp(180);

        if (val === 'sampling') {
            showSamplingMode();
            $('#samplingNotice').addClass('pf-visible');
        } else if (val === 'approved') {
            showFullMode();
            $('#upgradeNotice').addClass('pf-visible');
        } else if (val === 'generation') {
            showFullMode();
            $('#generationNotice').addClass('pf-visible');
            $('#oldProductBox').slideDown(200, function () {
                $('[name=old_product_id]').prop('disabled', false).select2('destroy').select2({ width: '100%' });
            });
        }
    });

    /* ══ AUTO-FILL OLD PRODUCT ══ */
    $(document).on('change', '[name=old_product_id]', function () {
        var opt = $(this).find('option:selected');
        if (!opt.val()) return;
        $('[name=product_code]').val(opt.data('product_code'));
        $('[name=product_name]').val(opt.data('product_name'));
        $('[name=short_description]').val(opt.data('short_description'));
        $('[name=description]').val(opt.data('description'));
        $('[name=suggested_dosage]').val(opt.data('suggested_dosage'));
        $('[name=standard_fill_size]').val(opt.data('standard_fill_size'));
        $('[name=shelf_life]').val(opt.data('shelf_life'));
        $('[name=physical_form]').val(opt.data('physical_form'));
        $('[name=product_detail_id]').val(opt.data('product_detail_id')).trigger('change');
        $('[name=packing_type_id]').val(opt.data('packing_type_id')).trigger('change');
        $('[name=additional_packing_type_id]').val(opt.data('additional_packing_type_id')).trigger('change');
        $('[name=packing_size_id]').val(opt.data('packing_size_id')).trigger('change');
        $('[name=label_id]').val(opt.data('label_id')).trigger('change');
        $('.select2').select2({ width: '100%' });
    });

    /* ══ PRODUCT VERTICAL ══ */
    $(document).on('change', '[name=is_trader_product]', function () {
        if ($(this).val() === "0") { $('.otherProType').show(); }
        else { $('.otherProType').hide(); }
    });
    $('[name=is_trader_product]').trigger('change');

    /* ══ CATEGORY LEVEL ══ */
    $(document).on('change', '[name=product_detail_id]', function () {
        $('#ShowLevel').text($(this).find(':selected').attr('data-level') || '');
    });
    $('[name=product_detail_id]').trigger('change');

    /* ══ STATUS RADIO VISUAL ══ */
    $(document).on('change', '[name=status]', function () {
        $('.pf-status-btn').removeClass('is-active is-inactive');
        if ($(this).val() == '1') { $('#btn-active').addClass('is-active'); }
        else { $('#btn-inactive').addClass('is-inactive'); }
    });

    /* ══ CODE → NAME SYNC ══ */
    @if(!$isEditMode)
    $(document).on('keyup', '[name=product_code]', function () {
        if ($('[name=product_status_type]:checked').val() !== 'generation') {
            $('[name=product_name]').val($(this).val());
        }
    });
    @endif

    /* ══ INHERIT TYPE ══ */
    $(document).on('change', '[name=inherit_type]', function () {
        $('.loadingDiv').show();
        $.ajax({
            url: '/admin/get-product-inherit-layout',
            data: { inherit_type: $(this).val() }, type: 'POST',
            success: function (resp) { $('.loadingDiv').hide(); $('#InheritDiv').html(resp.view); refreshSelect2(); }
        });
    });

    /* ══ RAW MATERIALS ══ */
    $(document).on('change', '.getRawMaterial', function () {
        var $num = $(this).closest('tr').find("td:eq(1) input[type=number]");
        $(this).val() == "" ? $num.prop("readonly", true).val('') : $num.prop("readonly", false);
    });
    $(document).on('click', '#addMoreRawMaterial', function () {
        $('.loadingDiv').show();
        $.ajax({
            url: '/admin/add-more-raw-material', type: 'GET',
            success: function (resp) { $('.loadingDiv').hide(); $('#AppendRawMaterials').append(resp.view); refreshSelect2(); }
        });
    });
    $(document).on('click', 'button.removeRow', function () {
        if (confirm("Remove this raw material?")) { $(this).closest('tr').remove(); }
        return false;
    });

    /* ══ FORM SUBMIT ══ */
    $("#ProductForm").submit(function (e) {
        e.preventDefault();
        $('.pf-error').removeClass('visible').html('');

        // Re-enable disabled fields so FormData captures them
        var $disabled = $(this).find(':input:disabled').prop('disabled', false);

        $('.loadingDiv').show();
        var formdata = new FormData(this);

        // Re-disable immediately after FormData is built
        $disabled.prop('disabled', true);

        $.ajax({
            url: '/admin/save-product', type: 'POST',
            data: formdata, processData: false, contentType: false,
            success: function (data) {
                $('.loadingDiv').hide();
                if (!data.status) {
                    $.each(data.errors, function (i, error) {
                        var $err = $('#Product-' + i);
                        $err.html(error).addClass('visible');
                        setTimeout(function () { $err.removeClass('visible').html(''); }, 6000);
                    });
                    var $first = $('.pf-error.visible').first();
                    if ($first.length) {
                        $('html,body').animate({ scrollTop: $first.offset().top - 160 }, 600);
                    }
                } else {
                    window.location.href = data.url;
                }
            }
        });
    });

    /* ══ RM COST CALCULATIONS ══ */
    $(document).on('keyup', '.getPercentage', function () {
        $("input:checkbox[name=calculate_rm_cost]").prop("checked", false);
        $('#CalculationDiv').hide();
        $('[name=packing_cost],[name=product_cost],[name=formulation_cost]').val('');
        $('#ProductCostVal').text('');
        $('[name=company_mark_up],[name=dealer_price],[name=market_price],[name=dealer_markup],[name=dp_calculation_cost]').val('');
        $('#DealerPriceVal,#MarketPriceVal').text('');
    });
    $(document).on('change', '[name=calculate_rm_cost]', function () {
        if ($(this).is(':checked')) {
            $('.loadingDiv').show();
            $.ajax({
                data: $("#ProductSearchRow :input").serializeArray(), dataType: "json",
                url: '/admin/calculate-rm-cost', type: 'POST',
                success: function (resp) {
                    $('.loadingDiv').hide();
                    if (!resp.status) { alert(resp.message); $('[name=calculate_rm_cost]').prop('checked', false); }
                    else { $('#CalculationDiv').show(); $('[name=rm_cost]').val(resp.rm_cost); $('#RMCOSTVal').text('Rs. ' + resp.rm_cost); }
                }
            });
        } else {
            $('#CalculationDiv').hide();
            $('[name=packing_cost],[name=product_cost],[name=formulation_cost]').val('');
            $('[name=company_mark_up],[name=dealer_price],[name=market_price],[name=dealer_markup],[name=dp_calculation_cost]').val('');
            $('#ProductCostVal,#DealerPriceVal,#MarketPriceVal').text('');
        }
    });
    $(document).on('keyup', '[name=packing_cost]', function () {
        var fc = $('[name=formulation_cost]').val();
        if (fc > 0) {
            var pc = parseInt(fc) + parseInt($('[name=rm_cost]').val()) + parseInt($(this).val());
            if (!isNaN(pc)) { $('[name=product_cost]').val(pc); $('#ProductCostVal').text('Rs. ' + pc); updateInhousePricings(); }
        } else { alert('Please enter correct Formulation Cost'); }
    });
    $(document).on('keyup', '[name=company_mark_up]', function () {
        var dpc = $('[name=dp_calculation_cost]').val();
        if (dpc > 0) {
            var dp = parseInt(dpc) / (1 - ($(this).val() / 100));
            if (!isNaN(dp)) { $('[name=dealer_price]').val(Math.round(dp)); $('#DealerPriceVal').text('Rs. ' + Math.round(dp)); }
            $('[name=market_price],[name=dealer_markup]').val(''); $('#MarketPriceVal').text('');
        } else { alert('Please enter correct DP Calculation Cost'); }
    });
    $(document).on('keyup', '[name=dealer_markup]', function () {
        var dp = $('[name=dealer_price]').val();
        if (dp > 0) {
            var mp = (parseInt(dp) + 5) / (1 - ($(this).val() / 100));
            if (!isNaN(mp) && $(this).val() > 0) { $('[name=market_price]').val(Math.round(mp)); $('#MarketPriceVal').text('Rs. ' + Math.round(mp)); }
        } else { alert('Please enter correct Dealer Price'); }
    });

});

/* ══ SHOW/HIDE HELPERS ══ */
function showSamplingMode() {
    // Hide full details card and product code field only
    $('#fullFieldsCard').hide();
    $('#productCodeRow').hide();
    // Disable hidden inputs so browser skips native validation
    $('#fullFieldsCard :input').prop('disabled', true);
    $('#productCodeRow :input').prop('disabled', true);
    // product_name is always visible and always enabled
    $('[name=product_name]').prop('disabled', false);
}

function showFullMode() {
    $('#fullFieldsCard').show();
    $('#productCodeRow').show();
    // Re-enable all inputs
    $('#fullFieldsCard :input').prop('disabled', false);
    $('#productCodeRow :input').prop('disabled', false);
    $('[name=product_name]').prop('disabled', false);
}

function updateInhousePricings() {
    var fc = $('[name=formulation_cost]').val();
    var pc = $('[name=packing_cost]').val();
    var rc = $('[name=rm_cost]').val();
    var cost = parseInt(rc) + parseInt(fc) + parseInt(pc);
    $('[name=dp_calculation_cost]').val(cost);
    var dp = parseInt(cost) / (1 - ($('[name=company_mark_up]').val() / 100));
    if (!isNaN(dp)) { $('[name=dealer_price]').val(Math.round(dp)); $('#DealerPriceVal').text('Rs. ' + Math.round(dp)); }
}
</script>

@endsection