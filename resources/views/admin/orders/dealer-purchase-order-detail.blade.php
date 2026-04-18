@extends('layouts.adminLayout.backendLayout')
@section('content')

<style>
    /* ===== PO DETAIL PAGE STYLES ===== */
    .po-page { font-family: 'Open Sans', sans-serif; }

    /* Header Banner */
    .po-header-banner {
        background: linear-gradient(135deg, #1b2a4a 0%, #243b6e 60%, #1e4d8c 100%);
        border-radius: 8px;
        padding: 24px 30px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(27,42,74,0.25);
        position: relative;
        overflow: hidden;
    }
    .po-header-banner::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .po-header-banner::after {
        content: '';
        position: absolute;
        bottom: -60px; right: 60px;
        width: 240px; height: 240px;
        background: rgba(255,255,255,0.03);
        border-radius: 50%;
    }
    .po-header-banner h2 {
        margin: 0 0 6px;
        color: #fff;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    .po-header-banner .po-meta {
        color: rgba(255,255,255,0.65);
        font-size: 13px;
        margin: 0;
    }
    .po-status-pill {
        display: inline-block;
        padding: 7px 22px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .po-status-pill.pending   { background: #f59e0b; color: #fff; }
    .po-status-pill.approved  { background: #10b981; color: #fff; }
    .po-status-pill.rejected  { background: #ef4444; color: #fff; }
    .po-status-pill.on-hold   { background: #6b7280; color: #fff; }
    .po-status-pill.completed { background: #3b82f6; color: #fff; }

    /* Info Cards */
    .info-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e8ecf0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .info-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e8ecf0;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .info-card .card-header i {
        color: #3b82f6;
        font-size: 16px;
    }
    .info-card .card-header h4 {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #374151;
    }
    .info-card .card-body { padding: 0; }
    .info-row {
        display: flex;
        align-items: center;
        padding: 11px 20px;
        border-bottom: 1px solid #f3f4f6;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row:nth-child(even) { background: #fafbfc; }
    .info-label {
        width: 42%;
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        flex-shrink: 0;
    }
    .info-label i { color: #9ca3af; margin-right: 6px; }
    .info-value {
        color: #1f2937;
        font-size: 13px;
        font-weight: 500;
        flex: 1;
    }

    /* Product Cards */
    .product-card {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        margin-bottom: 20px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: box-shadow 0.2s ease;
    }
    .product-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .product-card-header {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        padding: 13px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .product-card-header .product-name {
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.2px;
    }
    .product-card-header .product-meta {
        color: rgba(255,255,255,0.65);
        font-size: 12px;
    }
    .product-card-body {
        display: flex;
        flex-wrap: nowrap;
    }
    .pc-section {
        padding: 18px 20px;
        border-right: 1px solid #f0f0f0;
        flex: 1;
        min-width: 0;
    }
    .pc-section:last-child { border-right: none; }
    .pc-section-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #9ca3af;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f3f4f6;
    }

    /* Editable Input Styles */
    .po-input {
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        padding: 7px 10px;
        font-size: 13px;
        color: #1f2937;
        width: 100%;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #fff;
        box-sizing: border-box;
    }
    .po-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
    }
    .po-input.price-input {
        border-color: #3b82f6;
        font-weight: 700;
        color: #1b2a4a;
        font-size: 14px;
    }
    .po-input.qty-input {
        border-color: #10b981;
        font-weight: 700;
        font-size: 15px;
    }
    .po-input.price-input:focus { border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,0.12); }
    .po-input.qty-input:focus   { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.12); }

    .field-label {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 5px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .field-group { margin-bottom: 13px; }

    /* Value Display (read-only) */
    .value-display {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
    }
    .value-display.price { color: #1b2a4a; }
    .value-display.green { color: #059669; }

    /* Net Price Box */
    .net-price-box {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 1px solid #6ee7b7;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 14px;
        text-align: center;
    }
    .net-price-box .net-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #059669;
        margin-bottom: 3px;
    }
    .net-price-box .net-amount {
        font-size: 20px;
        font-weight: 800;
        color: #065f46;
    }

    /* Total Discount Badge */
    .disc-total-badge {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 6px;
        padding: 8px 12px;
        text-align: center;
        margin-top: 10px;
    }
    .disc-total-badge .disc-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9a3412;
    }
    .disc-total-badge .disc-amount {
        font-size: 16px;
        font-weight: 800;
        color: #c2410c;
    }

    /* Subtotal Box */
    .subtotal-box {
        background: linear-gradient(135deg, #1b2a4a 0%, #243b6e 100%);
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        margin-bottom: 16px;
    }
    .subtotal-box .sub-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: rgba(255,255,255,0.6);
        margin-bottom: 4px;
    }
    .subtotal-box .sub-amount {
        font-size: 18px;
        font-weight: 800;
        color: #fff;
    }

    /* Status Radio Buttons */
    .status-radio-group { margin-top: 4px; }
    .status-radio-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 6px;
        margin-bottom: 4px;
        cursor: pointer;
        transition: background 0.15s;
    }
    .status-radio-item:hover { background: #f9fafb; }
    .status-radio-item input[type="radio"] { margin: 0; cursor: pointer; }
    .status-radio-item.on-hold-opt { color: #92400e; font-size: 13px; font-weight: 500; }
    .status-radio-item.cancel-opt  { color: #991b1b; font-size: 13px; font-weight: 500; }
    .status-radio-item.urgent-opt  { color: #065f46; font-size: 13px; font-weight: 500; }

    .on-hold-date-wrap {
        margin: 4px 0 8px 28px;
    }

    /* Clear button */
    .btn-clear-status {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        border-radius: 5px;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
        margin-top: 6px;
    }
    .btn-clear-status:hover { background: #fecaca; border-color: #f87171; }

    /* Below MOQ Warning */
    .moq-warning {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        border-radius: 5px;
        padding: 5px 10px;
        font-size: 11px;
        color: #dc2626;
        font-weight: 600;
        margin-top: 6px;
    }

    /* Order Totals Footer */
    .order-totals-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 20px 24px;
        margin-top: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .totals-table { width: 100%; }
    .totals-table td { padding: 8px 0; border: none; }
    .totals-table .t-label { color: #6b7280; font-size: 14px; }
    .totals-table .t-value { text-align: right; font-size: 14px; font-weight: 600; }
    .totals-table .grand-row td {
        padding-top: 14px;
        border-top: 2px solid #e5e7eb;
        font-size: 18px;
        font-weight: 800;
        color: #1b2a4a;
    }
    .totals-table .grand-row .t-value { font-size: 22px; }

    /* Submit Button */
    .btn-submit-approve {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 13px 32px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(5,150,105,0.3);
        letter-spacing: 0.3px;
    }
    .btn-submit-approve:hover {
        background: linear-gradient(135deg, #047857 0%, #059669 100%);
        box-shadow: 0 6px 20px rgba(5,150,105,0.4);
        transform: translateY(-1px);
    }

    /* Label/Badge Chips */
    .chip {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        vertical-align: middle;
        margin-left: 4px;
    }
    .chip-linked  { background: #d1fae5; color: #065f46; }
    .chip-unlinked { background: #fee2e2; color: #991b1b; }
    .chip-count   { background: #dbeafe; color: #1e40af; }

    /* Invoice Section */
    .invoice-portlet {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        margin-top: 20px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .invoice-portlet .portlet-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        padding: 12px 20px;
    }
    .invoice-portlet .portlet-header h4 {
        margin: 0;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
    }

    /* Update Status Modal Styling */
    .po-modal .modal-header {
        background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
        border-radius: 4px 4px 0 0;
        padding: 16px 20px;
    }
    .po-modal .modal-header .modal-title { color: #fff; font-size: 15px; font-weight: 700; }
    .po-modal .modal-header .close { color: #fff; opacity: 0.8; text-shadow: none; }
    .po-modal .modal-header .close:hover { opacity: 1; }

    /* Comments textarea */
    .po-textarea {
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 12px;
        color: #374151;
        width: 100%;
        resize: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
        font-family: inherit;
    }
    .po-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .product-card-body { flex-wrap: wrap; }
        .pc-section { min-width: 50%; border-right: none; border-bottom: 1px solid #f0f0f0; }
        .pc-section:last-child { border-bottom: none; }
    }
    @media (max-width: 768px) {
        .pc-section { min-width: 100%; }
        .po-header-banner { padding: 16px; }
    }
</style>

<div class="page-content-wrapper po-page">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-head">
            <div class="page-title">
                <h1 style="font-size: 20px; font-weight: 700; color: #1f2937;">
                    Purchase Orders
                    <small style="font-size: 13px; color: #9ca3af; font-weight: 400; margin-left: 8px;">/ Order Detail</small>
                </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb" style="margin-bottom: 20px;">
            <li>
                <a href="{!! url('admin/dashboard') !!}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/dealer-orders') }}">Purchase Orders</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>Order #{{$poDetail['id']}}</li>
        </ul>

        <!-- Flash Messages -->
        @if(Session::has('flash_message_error'))
        <div role="alert" class="alert alert-danger alert-dismissible fade in" style="border-radius: 8px; border: none; box-shadow: 0 2px 10px rgba(239,68,68,0.15);">
            <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button>
            <i class="fa fa-exclamation-triangle"></i> <strong>Error!</strong> {!! session('flash_message_error') !!}
        </div>
        @endif
        @if(Session::has('flash_message_success'))
        <div role="alert" class="alert alert-success alert-dismissible fade in" style="border-radius: 8px; border: none; box-shadow: 0 2px 10px rgba(16,185,129,0.15);">
            <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button>
            <i class="fa fa-check-circle"></i> <strong>Success!</strong> {!! session('flash_message_success') !!}
        </div>
        @endif

        <div class="row">
            <div class="col-md-12">

                <!-- ===== ORDER HEADER BANNER ===== -->
                <div class="po-header-banner">
                    <div class="row">
                        <div class="col-sm-8">
                            <h2><i class="fa fa-file-text-o" style="margin-right:10px; opacity:0.8;"></i>Order #{{$poDetail['id']}}</h2>
                            <p class="po-meta">
                                <i class="fa fa-clock-o" style="margin-right:5px;"></i>
                                {{ date('d F Y, h:i A', strtotime($poDetail['created_at'])) }}
                            </p>
                        </div>
                        <div class="col-sm-4 text-right" style="padding-top: 8px;">
                            @php
                                $statusSlug = strtolower(str_replace(' ', '-', $poDetail['po_status']));
                            @endphp
                            <span class="po-status-pill {{ $statusSlug }}">
                                <i class="fa
                                    @if($poDetail['po_status']=='approved') fa-check-circle
                                    @elseif($poDetail['po_status']=='pending') fa-clock-o
                                    @elseif($poDetail['po_status']=='rejected') fa-times-circle
                                    @elseif($poDetail['po_status']=='on hold') fa-pause-circle
                                    @else fa-info-circle
                                    @endif"
                                style="margin-right: 6px;"></i>
                                {{ucwords($poDetail['po_status'])}}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ===== DEALER + PO DETAILS ===== -->
                <div class="row">
                    <!-- Dealer Details -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="card-header">
                                <i class="fa fa-user-circle-o"></i>
                                <h4>Dealer Details</h4>
                            </div>
                            <div class="card-body">
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-building-o"></i>Business</div>
                                    <div class="info-value" style="font-weight: 700; color: #1b2a4a;">{{$poDetail['business_name']}}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-phone"></i>Mobile</div>
                                    <div class="info-value">{{$poDetail['dealer_mobile']}}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-envelope-o"></i>Email</div>
                                    <div class="info-value">{{$poDetail['dealer_email']}}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-info-circle"></i>Status</div>
                                    <div class="info-value">
                                        @if($poDetail['po_status'] =="pending" || $poDetail['po_status'] =="on hold")
                                            <span class="po-status-pill {{ $statusSlug }}" style="font-size:11px; padding: 4px 14px;">
                                                {{ucwords($poDetail['po_status'])}}
                                            </span>
                                            &nbsp;
                                            <a href="javascript:;" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#DealerPOstatusModal" style="border-radius: 4px; font-size: 11px;">
                                                <i class="fa fa-pencil"></i> Update
                                            </a>
                                        @elseif($poDetail['po_status'] =="completed")
                                            @if(empty($poDetail['saleinvoices']))
                                                @if(!empty($poDetail['adjust_items']))
                                                    <span class="po-status-pill" style="background:#6366f1; font-size:11px; padding:4px 14px;">Adjusted</span>
                                                @elseif(!empty($poDetail['cancel_items']))
                                                    <span class="po-status-pill rejected" style="font-size:11px; padding:4px 14px;">Cancelled</span>
                                                @endif
                                            @else
                                                @if(!empty($poDetail['adjust_items']))
                                                    <span class="po-status-pill" style="background:#f59e0b; font-size:11px; padding:4px 14px;">Partially Adjusted</span>
                                                @elseif(!empty($poDetail['cancel_items']))
                                                    <span class="po-status-pill" style="background:#f59e0b; font-size:11px; padding:4px 14px;">Partially Cancelled</span>
                                                @endif
                                            @endif
                                        @else
                                            <span class="po-status-pill {{ $statusSlug }}" style="font-size:11px; padding:4px 14px;">{{ucwords($poDetail['po_status'])}}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($poDetail['po_status'] =="rejected" || $poDetail['po_status'] =="on hold")
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-comment-o"></i>Reason</div>
                                    <div class="info-value" style="color: #dc2626; font-size: 12px;">{{$poDetail['reason']}}</div>
                                </div>
                                @endif
                            </div>
                            @if($poDetail['po_status'] =="approved")
                                @if(empty($poDetail['adjust_cancel_items']))
                                <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e8ecf0;">
                                    <a data-status="adjustment" class="btn btn-sm btn-primary poAdjustment" href="javascript:;" style="margin-right: 8px; border-radius: 6px;">
                                        <i class="fa fa-sliders"></i> Adjust Order
                                    </a>
                                    <a data-status="cancel" class="btn btn-sm btn-danger poAdjustment" href="javascript:;" style="border-radius: 6px;">
                                        <i class="fa fa-times"></i> Cancel Order
                                    </a>
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- PO Details -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="card-header">
                                <i class="fa fa-file-text-o"></i>
                                <h4>PO Details</h4>
                            </div>
                            <div class="card-body">
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-hashtag"></i>PO ID</div>
                                    <div class="info-value" style="font-weight: 800; color: #1b2a4a; font-size: 16px;">{{$poDetail['id']}}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-barcode"></i>PO Number</div>
                                    <div class="info-value">{{$poDetail['customer_purchase_order_no']}}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-exchange"></i>Mode</div>
                                    <div class="info-value">{{$poDetail['mode']}}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="fa fa-sticky-note-o"></i>Remarks</div>
                                    <div class="info-value">{{$poDetail['remarks']}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Adjustment List Include -->
                @include('admin.orders.po-adjustment-list')

                <!-- ===== PO PRODUCTS ===== -->
                <div class="info-card" style="margin-bottom: 24px;">
                    <div class="card-header" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-bottom: 1px solid #bbf7d0;">
                        <i class="fa fa-cubes" style="color: #059669;"></i>
                        <h4 style="color: #065f46;">PO Products</h4>
                        @if($poDetail['po_edited']=="no" && $poDetail['po_status'] !="rejected")
                            <span style="margin-left: auto; font-size: 11px; color: #059669; font-weight: 600;">
                                <i class="fa fa-pencil-square-o"></i> Edit Mode — Changes calculate live
                            </span>
                        @else
                            <span style="margin-left: auto; font-size: 11px; color: #6b7280; font-weight: 600;">
                                <i class="fa fa-lock"></i> Read Only — Order Approved
                            </span>
                        @endif
                    </div>
                    <div class="card-body" style="padding: 20px;">

                        <form method="post" action="{{url('/admin/update-dealer-po-qty')}}" id="poProductsForm">
                            @csrf
                            <input type="hidden" name="purchase_order_id" value="{{$poDetail['id']}}">
                            @if($poDetail['po_edited']=="no" && $poDetail['po_status'] !="rejected")
                                <input type="hidden" name="dealer_id" value="{{$poDetail['dealer_id']}}">
                            @endif

                            @foreach($poDetail['orderitems'] as $key => $orderItemInfo)
                            @php
                                $idx = $loop->index;
                                $totalDisc = $orderItemInfo['dealer_qty_discount'] + $orderItemInfo['dealer_special_discount'] + $orderItemInfo['dealer_basic_discount'];
                                $subTotal  = $orderItemInfo['actual_qty'] * $orderItemInfo['net_price'];
                                $belowMoq  = !$poDetail['is_mini_pack_order'] && $orderItemInfo['qty'] < $orderItemInfo['product']['moq'];
                            @endphp

                            <div class="product-card">
                                <!-- Product Card Header -->
                                <div class="product-card-header">
                                    <div>
                                        <span class="product-name">
                                            <i class="fa fa-cube" style="margin-right: 8px; opacity: 0.7;"></i>
                                            {{$orderItemInfo['product']['product_name']}}
                                        </span>
                                        <!-- Linked Badge -->
                                        @if(in_array($orderItemInfo['product']['id'],$linkedProducts))
                                            <span class="chip chip-linked">Linked</span>
                                        @else
                                            @if($poDetail['po_edited']=="no")
                                                <span id="NotLinked-{{$orderItemInfo['id']}}">
                                                    <a data-itemid="{{$orderItemInfo['id']}}"
                                                       data-productid="{{$orderItemInfo['product_id']}}"
                                                       data-dealerid="{{$poDetail['dealer_id']}}"
                                                       href="javascript:;" class="linkDealerProduct">
                                                        <span class="chip chip-unlinked">Not Linked</span>
                                                    </a>
                                                </span>
                                            @else
                                                <span class="chip chip-unlinked">Not Linked</span>
                                            @endif
                                        @endif
                                        @if(isset($productCounts[$orderItemInfo['product_id']]))
                                            <span class="chip chip-count">{{$productCounts[$orderItemInfo['product_id']]}}</span>
                                        @endif
                                    </div>
                                    <div class="product-meta">
                                        @if($poDetail['is_mini_pack_order'] == 1)
                                            <i class="fa fa-cube" style="margin-right: 4px;"></i> Pack: {{$orderItemInfo['mini_pack_size']}}
                                        @elseif(isset($orderItemInfo['packingsize']['size']))
                                            <i class="fa fa-cube" style="margin-right: 4px;"></i> Pack: {{$orderItemInfo['packingsize']['size']}} kg
                                        @endif
                                    </div>
                                </div>

                                <!-- Product Card Body: 4 Sections -->
                                <div class="product-card-body">

                                    <!-- SECTION 1: Pricing -->
                                    <div class="pc-section" style="flex: 1.1;">
                                        <div class="pc-section-title"><i class="fa fa-rupee"></i> Pricing</div>

                                        <!-- Dealer Price -->
                                        <div class="field-group">
                                            <label class="field-label">Dealer Price (Rs.)</label>
                                            @if($poDetail['po_edited']=="no")
                                                <input type="number"
                                                       class="po-input price-input dealer-price-input"
                                                       name="dealer_prices[]"
                                                       id="dealer-price-{{$idx}}"
                                                       data-index="{{$idx}}"
                                                       value="{{$orderItemInfo['product_price']}}"
                                                       step="0.01" min="0">
                                            @else
                                                <input type="hidden" name="dealer_prices[]" value="{{$orderItemInfo['product_price']}}">
                                                <div class="value-display price">Rs. {{$orderItemInfo['product_price']}}</div>
                                            @endif
                                        </div>

                                        <!-- Net Price -->
                                        <div class="net-price-box">
                                            <div class="net-label">Net Price</div>
                                            <div class="net-amount">
                                                Rs. <span id="net-price-{{$idx}}">{{number_format($orderItemInfo['net_price'],2)}}</span>
                                            </div>
                                        </div>

                                        @if(!empty($orderItemInfo['product']['qty_discounts']))
                                            <a href="javascript:;" class="btn btn-xs btn-info fetchProductQtyDiscounts"
                                               data-product_id="{{$orderItemInfo['product_id']}}"
                                               data-dealer_id="{{$poDetail['dealer_id']}}"
                                               style="border-radius: 4px; font-size: 11px;">
                                                <i class="fa fa-tag"></i> Qty Discounts
                                            </a>
                                        @endif
                                    </div>

                                    <!-- SECTION 2: Discounts -->
                                    <div class="pc-section" style="flex: 1.2;">
                                        <div class="pc-section-title"><i class="fa fa-percent"></i> Discounts & Charges</div>

                                        @if($poDetail['po_edited']=="no")
                                            <!-- Editable Discounts -->
                                            <div class="field-group">
                                                <label class="field-label">Basic Discount (%)</label>
                                                <input type="number" class="po-input discount-input"
                                                       name="basic_discounts[]"
                                                       id="disc-basic-{{$idx}}"
                                                       data-index="{{$idx}}" data-type="basic"
                                                       value="{{$orderItemInfo['dealer_basic_discount']}}"
                                                       step="0.01" min="0" max="100">
                                            </div>
                                            
                                            <div class="field-group">
                                                <label class="field-label">Special Discount (%)</label>
                                                <input type="number" class="po-input discount-input"
                                                       name="special_discounts[]"
                                                       id="disc-spec-{{$idx}}"
                                                       data-index="{{$idx}}" data-type="special"
                                                       value="{{$orderItemInfo['dealer_special_discount']}}"
                                                       step="0.01" min="0" max="100">
                                            </div>
                                            <div class="field-group">
                                                <label class="field-label">Qty Discount (%)</label>
                                                <input type="number" class="po-input discount-input"
                                                       name="qty_discounts[]"
                                                       id="disc-qty-{{$idx}}"
                                                       data-index="{{$idx}}" data-type="qty"
                                                       value="{{$orderItemInfo['dealer_qty_discount']}}"
                                                       step="0.01" min="0" max="100">
                                            </div>
                                            
                                        @else
                                            <!-- Read-only Discounts -->
                                            <input type="hidden" name="qty_discounts[]"     value="{{$orderItemInfo['dealer_qty_discount']}}">
                                            <input type="hidden" name="special_discounts[]" value="{{$orderItemInfo['dealer_special_discount']}}">
                                            <input type="hidden" name="basic_discounts[]"   value="{{$orderItemInfo['dealer_basic_discount']}}">
                                            <table style="width:100%; font-size:12px; margin-bottom: 8px;">
                                                <tr><td style="padding:4px 0; color:#6b7280;">Qty Disc.</td>    <td style="text-align:right; font-weight:700; color:#374151;">{{$orderItemInfo['dealer_qty_discount']}}%</td></tr>
                                                <tr><td style="padding:4px 0; color:#6b7280;">Special Disc.</td><td style="text-align:right; font-weight:700; color:#374151;">{{$orderItemInfo['dealer_special_discount']}}%</td></tr>
                                                <tr><td style="padding:4px 0; color:#6b7280;">Basic Disc.</td>  <td style="text-align:right; font-weight:700; color:#374151;">{{$orderItemInfo['dealer_basic_discount']}}%</td></tr>
                                            </table>
                                        @endif

                                        <!-- Total Discount Display -->
                                        <div class="disc-total-badge">
                                            <div class="disc-label">Total Discount</div>
                                            <div class="disc-amount" id="total-disc-{{$idx}}">{{$totalDisc}}%</div>
                                        </div>

                                        <!-- Additional Charges (mini pack) -->
                                        @if($poDetail['is_mini_pack_order'] == 1)
                                            <div style="margin-top: 10px; background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 6px; padding: 8px 12px; font-size: 12px;">
                                                <span style="color: #7c3aed; font-weight: 600;">Add. Charges:</span>
                                                <span style="color: #5b21b6; font-weight: 700; float: right;">
                                                    Rs. {{!empty($orderItemInfo['additional_charges']) ? $orderItemInfo['additional_charges'] : 0}}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- SECTION 3: Quantities -->
                                    <div class="pc-section" style="flex: 1.2;">
                                        <div class="pc-section-title"><i class="fa fa-balance-scale"></i> Quantities</div>

                                        <!-- Ordered Qty -->
                                        <div class="field-group">
                                            <label class="field-label">Ordered Qty</label>
                                            @if($poDetail['is_mini_pack_order'] == 1)
                                                <div class="value-display">{{$orderItemInfo['qty']}} <small style="font-size:12px; color:#6b7280;">kg</small></div>
                                            @else
                                                <div class="value-display @if($belowMoq) price @endif" style="@if($belowMoq) color:#dc2626; @endif">
                                                    {{$orderItemInfo['qty']}} <small style="font-size:12px; color:#6b7280;">kg</small>
                                                </div>
                                                <div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
                                                    MOQ: <strong style="color: #6b7280;">{{!empty($orderItemInfo['product']['moq']) ? $orderItemInfo['product']['moq'] : 0}} kg</strong>
                                                </div>
                                                @if($belowMoq)
                                                    <div class="moq-warning"><i class="fa fa-exclamation-triangle"></i> Below MOQ</div>
                                                @endif
                                            @endif
                                        </div>

                                        <!-- Approved Qty -->
                                        <div class="field-group">
                                            <label class="field-label">Approved Qty</label>
                                            @if($poDetail['po_edited']=="no")
                                                @if(in_array($orderItemInfo['product']['id'],$linkedProducts))
                                                    <input type="hidden" name="product_links[]" value="1">
                                                @else
                                                    <input type="hidden" id="ProLink-{{$orderItemInfo['id']}}" name="product_links[]" value="0">
                                                @endif
                                                <input type="hidden" name="item_ids[]" value="{{$orderItemInfo['id']}}">
                                                <input class="po-input qty-input approved-qty-input"
                                                       type="number"
                                                       name="actual_qtys[]"
                                                       id="approved-qty-{{$idx}}"
                                                       data-index="{{$idx}}"
                                                       value="{{$orderItemInfo['actual_qty']}}"
                                                       required>
                                            @else
                                                <div class="value-display green">{{$orderItemInfo['actual_qty']}} <small style="font-size:12px; color:#6b7280;">kg</small></div>
                                                @if(!empty($orderItemInfo['comments']))
                                                    <div style="margin-top: 8px; background: #f9fafb; border-radius: 6px; padding: 8px 10px; font-size: 12px; color: #6b7280; border: 1px solid #e5e7eb;">
                                                        <i class="fa fa-comment-o" style="margin-right: 4px;"></i>{{$orderItemInfo['comments']}}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                        <!-- Comments -->
                                        @if($poDetail['po_edited']=="no")
                                        <div class="field-group">
                                            <label class="field-label">Comments</label>
                                            <textarea class="po-textarea" name="comments[]" rows="2"
                                                      placeholder="Optional notes...">{{$orderItemInfo['comments']}}</textarea>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- SECTION 4: Status & Subtotal -->
                                    <div class="pc-section" style="flex: 1;">
                                        <div class="pc-section-title"><i class="fa fa-signal"></i> Status & Total</div>

                                        <!-- Subtotal -->
                                        <div class="subtotal-box">
                                            <div class="sub-label">Subtotal</div>
                                            <div class="sub-amount">
                                                Rs. <span id="subtotal-{{$idx}}">{{number_format($subTotal, 2)}}</span>
                                            </div>
                                        </div>

                                        <!-- Item Status Radios -->
                                        <div class="status-radio-group">
                                            <?php $statuses = ['On Hold', 'Cancel', 'Urgent']; ?>
                                            @foreach($statuses as $skey => $status)
                                                <label class="status-radio-item
                                                    @if($status=='On Hold') on-hold-opt
                                                    @elseif($status=='Cancel') cancel-opt
                                                    @else urgent-opt @endif">
                                                    <input data-orderitemid="{{$orderItemInfo['id']}}"
                                                           class="urgentOrderItem"
                                                           type="radio"
                                                           name="orderitemstatus[{{$orderItemInfo['id']}}]"
                                                           id="{{$orderItemInfo['id']}}{{$skey}}"
                                                           value="{{$status}}"
                                                           @if($orderItemInfo['item_action'] == $status) checked @endif>
                                                    <i class="fa
                                                        @if($status=='On Hold') fa-pause-circle
                                                        @elseif($status=='Cancel') fa-times-circle
                                                        @else fa-bolt @endif"></i>
                                                    {{$status}}
                                                </label>

                                                @if($status == 'On Hold')
                                                    <div class="on-hold-date-wrap onHoldDateBox"
                                                         id="onHoldDateBox-{{$orderItemInfo['id']}}"
                                                         style="display: {{ $orderItemInfo['item_action'] == 'On Hold' ? 'block' : 'none' }};">
                                                        <input id="onHoldDate-{{ $orderItemInfo['id'] }}"
                                                               type="date"
                                                               class="po-input onHoldDate"
                                                               style="font-size: 12px; padding: 5px 8px;"
                                                               data-orderitemid="{{$orderItemInfo['id']}}"
                                                               value="{{$orderItemInfo['on_hold_until'] ?? ''}}">
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <button type="button"
                                                class="btn-clear-status clearItemStatus"
                                                data-orderitemid="{{$orderItemInfo['id']}}">
                                            <i class="fa fa-times"></i> Clear Status
                                        </button>
                                    </div>

                                </div>{{-- end product-card-body --}}
                            </div>{{-- end product-card --}}
                            @endforeach

                            <!-- ===== ORDER TOTALS ===== -->
                            <div class="order-totals-card">
                                <div class="row">
                                    <div class="col-md-5 col-md-offset-7">
                                        <table class="totals-table">
                                            <tr>
                                                <td class="t-label">Subtotal</td>
                                                <td class="t-value">Rs. {{$poDetail['price']}}</td>
                                            </tr>
                                            <tr>
                                                <td class="t-label" style="color: #92400e;">GST (+)</td>
                                                <td class="t-value" style="color: #92400e;">Rs. {{$poDetail['gst']}}</td>
                                            </tr>
                                            <tr class="grand-row">
                                                <td class="t-label">Grand Total</td>
                                                <td class="t-value">Rs. {{$poDetail['grand_total']}}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            @if($poDetail['po_edited']=="no" && $poDetail['po_status'] !="rejected")
                            <div style="text-align: right; margin-top: 24px;">
                                <button class="btn-submit-approve" type="button" id="btnReviewApprove">
                                    <i class="fa fa-eye" style="margin-right: 8px;"></i>
                                    Review &amp; Approve Order
                                </button>
                            </div>
                            @endif

                        </form>

                    </div>{{-- card-body --}}
                </div>{{-- info-card --}}

                <!-- ===== SALE INVOICES ===== -->
                @if(!empty($poDetail['sale_invoices']))
                    @foreach($poDetail['sale_invoices'] as $skey => $saleInvoice)

                    <!-- Invoice Details -->
                    <div class="invoice-portlet">
                        <div class="portlet-header">
                            <h4><i class="fa fa-file-text-o" style="margin-right: 8px;"></i>Sale Invoice {{++$skey}} Details</h4>
                        </div>
                        <div style="padding: 20px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <table style="width: 100%; font-size: 13px;">
                                        <tr>
                                            <td style="padding: 7px 0; color: #6b7280; font-weight: 600; width: 45%;">Sale Invoice ID</td>
                                            <td style="padding: 7px 0; font-weight: 700; color: #1b2a4a;">{{$saleInvoice['id']}}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 7px 0; color: #6b7280; font-weight: 600;">Invoice No.</td>
                                            <td style="padding: 7px 0;">{{$saleInvoice['dealer_invoice_no']}}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 7px 0; color: #6b7280; font-weight: 600;">Sale Invoice Date</td>
                                            <td style="padding: 7px 0;">
                                                @if($saleInvoice['sale_invoice_date'] !="0000-00-00"){{$saleInvoice['sale_invoice_date']}}@endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 7px 0; color: #6b7280; font-weight: 600;">Transport Name</td>
                                            <td style="padding: 7px 0;">{{$saleInvoice['transport_name']}}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table style="width: 100%; font-size: 13px;">
                                        <tr>
                                            <td style="padding: 7px 0; color: #6b7280; font-weight: 600; width: 45%;">LR No.</td>
                                            <td style="padding: 7px 0;">{{$saleInvoice['lr_no']}}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 7px 0; color: #6b7280; font-weight: 600;">Dispatch Date</td>
                                            <td style="padding: 7px 0;">
                                                @if($saleInvoice['dispatch_date'] !="0000-00-00"){{$saleInvoice['dispatch_date']}}@endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 7px 0; color: #6b7280; font-weight: 600;">Delivered</td>
                                            <td style="padding: 7px 0;">
                                                @if($saleInvoice['is_delivered'])
                                                    <span style="background: #d1fae5; color: #065f46; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">Yes</span>
                                                @else
                                                    <span style="background: #f3f4f6; color: #6b7280; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if(!empty($saleInvoice['payment_term_type']))
                                        <tr>
                                            <td style="padding: 7px 0; color: #6b7280; font-weight: 600;">Payment Term</td>
                                            <td style="padding: 7px 0;">
                                                {{$saleInvoice['payment_term_type']}} — {{$saleInvoice['payment_term']}}
                                                <span style="color: #059669;">({{$saleInvoice['payment_discount_per']}}%)</span>
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Products -->
                    <div class="invoice-portlet">
                        <div class="portlet-header">
                            <h4><i class="fa fa-list-ul" style="margin-right: 8px;"></i>Sale Invoice {{$skey}} Products</h4>
                        </div>
                        <div style="padding: 16px 20px;">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-striped" style="font-size: 13px;">
                                    <thead>
                                        <tr style="background: #374151; color: #fff;">
                                            <th style="background: #374151; color: #fff; border-color: #4b5563; font-weight: 600;">Product Name</th>
                                            <th style="background: #374151; color: #fff; border-color: #4b5563; font-weight: 600;">Product Code</th>
                                            <th style="background: #374151; color: #fff; border-color: #4b5563; font-weight: 600;">HSN Code</th>
                                            <th style="background: #374151; color: #fff; border-color: #4b5563; font-weight: 600;">Dealer Price</th>
                                            <th style="background: #374151; color: #fff; border-color: #4b5563; font-weight: 600;">Qty</th>
                                            <th style="background: #374151; color: #fff; border-color: #4b5563; font-weight: 600;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($saleInvoice['invoice_items'] as $key => $saleInvoiceInfo)
                                        <tr>
                                            <td>{{$saleInvoiceInfo['productinfo']['product_name']}}</td>
                                            <td>{{$saleInvoiceInfo['productinfo']['product_code']}}</td>
                                            <td>{{$saleInvoiceInfo['productinfo']['hsn_code']}}</td>
                                            <td>Rs. {{$saleInvoiceInfo['purchase_order_item']['product_price']}}</td>
                                            <td>{{$saleInvoiceInfo['qty']}}</td>
                                            <?php $siSubTotal = $saleInvoiceInfo['qty'] * $saleInvoiceInfo['price']; ?>
                                            <td>Rs. {{$siSubTotal}}</td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td colspan="6" style="text-align: right; font-weight: 700; background: #f8fafc;">
                                                Subtotal: Rs. {{$saleInvoice['price']}}
                                            </td>
                                        </tr>
                                        @if($saleInvoice['payment_term_type']=="On Bill")
                                        <tr>
                                            <td colspan="6" style="text-align: right; color: #dc2626; background: #fef2f2;">
                                                Payment Discount (−): Rs. {{$saleInvoice['payment_discount']}}
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td colspan="6" style="text-align: right; color: #92400e; background: #fffbeb;">
                                                GST (+): Rs. {{$saleInvoice['gst']}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" style="text-align: right; font-weight: 800; font-size: 15px; color: #065f46; background: #ecfdf5;">
                                                Grand Total: Rs. {{$saleInvoice['grand_total']}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @endforeach
                @endif

            </div>{{-- col-md-12 --}}
        </div>{{-- row --}}

    </div>{{-- page-content --}}
</div>{{-- page-content-wrapper --}}

<!-- ===== UPDATE STATUS MODAL ===== -->
<div class="modal fade po-modal" id="DealerPOstatusModal" tabindex="-1" role="dialog" aria-labelledby="DealerPOstatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="DealerPOstatusModalLabel">
                    <i class="fa fa-edit" style="margin-right: 8px;"></i>Update PO Status
                </h5>
            </div>
            <form action="{{url('/admin/update-dealer-po-status')}}" method="post">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <input type="hidden" name="purchase_order_id" value="{{$poDetail['id']}}">
                    <div class="form-group">
                        <label class="field-label">Status <span style="color:#dc2626;">*</span></label>
                        <select class="form-control" name="po_status" required style="border-radius: 6px; border: 1.5px solid #d1d5db; font-size: 13px;">
                            <option value="">Please Select</option>
                            <option value="rejected">Rejected</option>
                            <option value="on hold">On Hold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="field-label">Reason <span style="color:#dc2626;">*</span></label>
                        <select class="form-control" name="reason" required style="border-radius: 6px; border: 1.5px solid #d1d5db; font-size: 13px;">
                            <option value="">Please Select</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="field-label">Comments</label>
                        <textarea class="po-textarea" name="comments" rows="3" placeholder="Add any additional comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e5e7eb; border-radius: 0 0 8px 8px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 6px; font-weight: 600;">
                        <i class="fa fa-check" style="margin-right: 5px;"></i>Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<span id="AppendAdjustModal"></span>
<span id="AppendProductQtyDiscountModal"></span>

<!-- ══════════════════════════════════════════════════ -->
<!-- APPROVAL CONFIRMATION MODAL                       -->
<!-- ══════════════════════════════════════════════════ -->
<div class="modal fade" id="ApprovalConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 16px 60px rgba(0,0,0,0.18); overflow: hidden;">

            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #1b2a4a 0%, #243b6e 100%); padding: 20px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; background: rgba(255,255,255,0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-eye" style="color: #fff; font-size: 16px;"></i>
                    </div>
                    <div>
                        <div style="color: #fff; font-size: 15px; font-weight: 700;">Review Before Approving</div>
                        <div style="color: rgba(255,255,255,0.55); font-size: 12px; margin-top: 2px;">Please verify the changes below before submitting</div>
                    </div>
                </div>
                <button type="button" data-dismiss="modal" style="background: none; border: none; color: rgba(255,255,255,0.7); font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 24px; max-height: 65vh; overflow-y: auto; background: #f8fafc;">

                <!-- Changes Summary will be injected here by JS -->
                <div id="approvalChangesBody"></div>

                <!-- No Changes Notice -->
                <div id="noChangesNotice" style="display: none; text-align: center; padding: 30px;">
                    <i class="fa fa-check-circle" style="font-size: 48px; color: #10b981; display: block; margin-bottom: 12px;"></i>
                    <div style="font-size: 15px; font-weight: 700; color: #1f2937; margin-bottom: 6px;">No Changes Detected</div>
                    <div style="font-size: 13px; color: #6b7280;">All values are at their original amounts. Proceed to approve?</div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="background: #fff; padding: 16px 24px; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-size: 12px; color: #9ca3af;">
                    <i class="fa fa-info-circle" style="margin-right: 4px;"></i>
                    This action will approve the order and notify the dealer via email.
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" data-dismiss="modal"
                            style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db;
                                   border-radius: 8px; padding: 10px 22px; font-size: 13px;
                                   font-weight: 600; cursor: pointer; transition: all 0.15s;">
                        <i class="fa fa-times" style="margin-right: 6px;"></i> Cancel
                    </button>
                    <button type="button" id="btnConfirmApprove"
                            style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);
                                   color: #fff; border: none; border-radius: 8px;
                                   padding: 10px 26px; font-size: 13px; font-weight: 700;
                                   cursor: pointer; box-shadow: 0 4px 14px rgba(5,150,105,0.3);
                                   transition: all 0.2s;">
                        <i class="fa fa-check-circle" style="margin-right: 6px;"></i> Confirm &amp; Approve
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════ -->
<!-- FULL-SCREEN LOADER OVERLAY                        -->
<!-- ══════════════════════════════════════════════════ -->
<div id="poApprovalLoader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
     background: rgba(27,42,74,0.88); z-index: 99999; flex-direction: column;
     align-items: center; justify-content: center;">
    <div style="text-align: center;">
        <!-- Spinner Ring -->
        <div style="width: 72px; height: 72px; border: 5px solid rgba(255,255,255,0.15);
                    border-top-color: #10b981; border-radius: 50%; margin: 0 auto 24px;
                    animation: poSpin 0.85s linear infinite;"></div>
        <div style="font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 8px;">
            Approving Order...
        </div>
        <div style="font-size: 13px; color: rgba(255,255,255,0.55);">
            Please wait — do not close or refresh this page
        </div>
    </div>
</div>

<style>
    @keyframes poSpin {
        to { transform: rotate(360deg); }
    }
    /* Change summary table in modal */
    .change-summary-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 16px; }
    .change-summary-table thead th {
        background: #1b2a4a; color: #fff; padding: 10px 14px;
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; text-align: left;
    }
    .change-summary-table tbody tr:nth-child(even) { background: #f9fafb; }
    .change-summary-table tbody tr:hover { background: #f0f4ff; }
    .change-summary-table td { padding: 10px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .change-pill-old { display: inline-block; background: #fee2e2; color: #991b1b; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .change-pill-new { display: inline-block; background: #d1fae5; color: #065f46; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .change-arrow { color: #9ca3af; margin: 0 6px; font-size: 12px; }
    .section-divider { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #3b82f6; margin: 18px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #dbeafe; }
    .no-change-row td { color: #9ca3af; font-style: italic; }
    .qty-reduced-badge { display: inline-block; background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; border-radius: 4px; padding: 1px 8px; font-size: 10px; font-weight: 700; margin-left: 6px; }
    .qty-increased-badge { display: inline-block; background: #f0fdf4; color: #059669; border: 1px solid #6ee7b7; border-radius: 4px; padding: 1px 8px; font-size: 10px; font-weight: 700; margin-left: 6px; }
</style>

<script type="text/javascript">

    // =============================================
    // ORIGINAL VALUES — stored on page load
    // Used to detect what admin changed
    // =============================================
    var originalValues = {};

    $(document).ready(function () {
        // Snapshot originals from each editable field
        $('.dealer-price-input, .discount-input, .approved-qty-input').each(function () {
            var id = $(this).attr('id');
            originalValues[id] = parseFloat($(this).val()) || 0;
        });
    });

    // =============================================
    // REAL-TIME PRICING CALCULATION
    // Net Price = Dealer Price * (1 - TotalDiscount/100)
    // Subtotal  = Net Price * Approved Qty
    // =============================================
    function recalcItem(idx) {
        var dealerPrice = parseFloat($('#dealer-price-' + idx).val()) || 0;
        var qtyDisc     = parseFloat($('#disc-qty-'    + idx).val()) || 0;
        var specDisc    = parseFloat($('#disc-spec-'   + idx).val()) || 0;
        var basicDisc   = parseFloat($('#disc-basic-'  + idx).val()) || 0;
        var approvedQty = parseFloat($('#approved-qty-'+ idx).val()) || 0;

        var totalDisc = qtyDisc + specDisc + basicDisc;
        var netPrice  = dealerPrice * (1 - totalDisc / 100);
        var subtotal  = netPrice * approvedQty;

        $('#net-price-'  + idx).text(netPrice.toFixed(2));
        $('#total-disc-' + idx).text(totalDisc.toFixed(2) + '%');
        $('#subtotal-'   + idx).text(subtotal.toFixed(2));
    }

    // Trigger on any pricing input change
    $(document).on('input change', '.dealer-price-input, .discount-input, .approved-qty-input', function () {
        var idx = $(this).data('index');
        recalcItem(idx);

        // Clear red error highlight when user starts correcting the field
        if ($(this).hasClass('dealer-price-input')) {
            var val = parseFloat($(this).val());
            if (val > 0) {
                $(this).css({ 'border-color': '#3b82f6', 'box-shadow': '' });
                $('#poValidationError').remove();
            }
        }
        if ($(this).hasClass('approved-qty-input')) {
            if ($(this).val() !== '') {
                $(this).css({ 'border-color': '#10b981', 'box-shadow': '' });
                $('#poValidationError').remove();
            }
        }
    });

    // =============================================
    // ITEM STATUS — URGENT / ON HOLD / CANCEL
    // =============================================
    $(document).on('change', '.urgentOrderItem', function () {
        var orderitemid = $(this).data('orderitemid');
        var value = $(this).val();

        $.ajax({
            data: { value: value, orderitemid: orderitemid, _token: '{{ csrf_token() }}' },
            url: '/admin/mark-urgent-po-item',
            type: 'post',
            success: function (resp) { /* toast or log */ }
        });

        if (value === 'On Hold') {
            $('#onHoldDateBox-' + orderitemid).show();
        } else {
            $('#onHoldDateBox-' + orderitemid).hide();
        }
    });

    // On Hold date change
    $(document).on('change', '.onHoldDate', function () {
        var orderitemid = $(this).data('orderitemid');
        var date = $(this).val();
        if (!date) return;
        $.ajax({
            data: { orderitemid: orderitemid, on_hold_until: date, _token: '{{ csrf_token() }}' },
            url: '/admin/mark-po-item-on-hold-date',
            type: 'post',
            success: function (resp) { /* success handler */ }
        });
    });

    // =============================================
    // CLEAR ITEM STATUS
    // =============================================
    $(document).on('click', '.clearItemStatus', function () {
        var orderitemid = $(this).data('orderitemid');
        $.ajax({
            data: { value: '', orderitemid: orderitemid, _token: '{{ csrf_token() }}' },
            url: '/admin/mark-urgent-po-item',
            type: 'post',
            success: function () {
                $('#' + orderitemid + '0').prop('checked', false);
                $('#' + orderitemid + '1').prop('checked', false);
                $('#' + orderitemid + '2').prop('checked', false);
                $('#onHoldDateBox-' + orderitemid).hide();
                $('#onHoldDate-'    + orderitemid).val('');
            }
        });
    });

    // =============================================
    // LINK DEALER PRODUCT
    // =============================================
    $(document).on('click', '.linkDealerProduct', function () {
        if (confirm('Are you sure you want to link this product?')) {
            var dealerid  = $(this).data('dealerid');
            var productid = $(this).data('productid');
            var itemid    = $(this).data('itemid');
            $.ajax({
                data: { dealerid: dealerid, productid: productid },
                type: 'POST',
                url: '/admin/link-dealer-product',
                success: function (resp) {
                    if (resp.status) {
                        $('#ProLink-' + itemid).val(1);
                        $('#NotLinked-' + itemid).html('<span class="chip chip-linked">Linked</span>');
                    }
                }
            });
        }
    });

    // =============================================
    // PO ADJUSTMENT MODAL
    // =============================================
    $(document).on('click', '.poAdjustment', function () {
        var status = $(this).data('status');
        $.ajax({
            data: { status: status, po_id: '{{$poDetail['id']}}' },
            url: '/admin/open-po-adjust-modal',
            type: 'post',
            success: function (resp) {
                $('#AppendAdjustModal').html(resp.view);
                $('#PoAdjustModal').modal('show');
            }
        });
    });

    // =============================================
    // QTY DISCOUNTS MODAL
    // =============================================
    $(document).on('click', '.fetchProductQtyDiscounts', function () {
        var productId = $(this).data('product_id');
        var dealerId  = $(this).data('dealer_id');
        $.ajax({
            data: { productId: productId, dealerId: dealerId },
            url: '/admin/fetch-product-qty-discounts',
            type: 'post',
            success: function (resp) {
                $('#AppendProductQtyDiscountModal').html(resp.view);
                $('#QtyDiscountModal').modal('show');
            }
        });
    });

    // =============================================
    // STATUS MODAL — REASON OPTIONS
    // =============================================
    $(document).on('change', '[name=po_status]', function () {
        var status = $(this).val();
        var $reason = $('[name=reason]');
        $reason.html('<option value="">Please Select</option>');
        if (status === 'rejected') {
            $reason.append(
                '<option value="Order declined due to unavailablity of material">Order declined due to unavailablity of material</option>' +
                '<option value="Order declined as the Product has been stopped">Order declined as the Product has been stopped</option>' +
                '<option value="Other">Other</option>'
            );
        } else if (status === 'on hold') {
            $reason.append(
                '<option value="Order has been put on hold pending due payment">Order has been put on hold pending due payment</option>' +
                '<option value="Order has been put on hold as material availability/ price can not be confirmed right now">Order has been put on hold as material availability/ price can not be confirmed right now</option>' +
                '<option value="Other">Other</option>'
            );
        }
    });

    // =============================================
    // REVIEW BUTTON — Validate then build modal
    // =============================================
    $('#btnReviewApprove').on('click', function () {

        // ── VALIDATION ───────────────────────────────
        var errors        = [];
        var firstError    = null;

        $('.dealer-price-input').each(function (i) {
            var val         = parseFloat($(this).val());
            var productName = $('.product-card').eq(i).find('.product-name').text().trim();

            if (!$(this).val() || isNaN(val) || val <= 0) {
                errors.push('• <strong>' + productName + '</strong> — Dealer Price cannot be 0 or empty.');
                if (!firstError) firstError = this;

                // Highlight the field
                $(this).css({
                    'border-color' : '#ef4444',
                    'box-shadow'   : '0 0 0 3px rgba(239,68,68,0.15)'
                });
            } else {
                $(this).css({ 'border-color': '#3b82f6', 'box-shadow': '' });
            }
        });

        $('.approved-qty-input').each(function (i) {
            var val         = $(this).val();
            var productName = $('.product-card').eq(i).find('.product-name').text().trim();

            if (val === '' || val === null || val === undefined) {
                errors.push('• <strong>' + productName + '</strong> — Approved Qty is required (enter 0 if none).');
                if (!firstError) firstError = this;

                $(this).css({
                    'border-color' : '#ef4444',
                    'box-shadow'   : '0 0 0 3px rgba(239,68,68,0.15)'
                });
            } else {
                $(this).css({ 'border-color': '#10b981', 'box-shadow': '' });
            }
        });

        if (errors.length > 0) {
            // Show inline error banner above the button
            var existingErr = $('#poValidationError');
            if (existingErr.length) existingErr.remove();

            var errHtml = '<div id="poValidationError" style="'
                + 'background: #fef2f2; border: 1px solid #fca5a5; border-left: 4px solid #ef4444;'
                + 'border-radius: 8px; padding: 14px 18px; margin-top: 16px; margin-bottom: 4px;">'
                + '<div style="font-size: 13px; font-weight: 700; color: #dc2626; margin-bottom: 8px;">'
                + '<i class="fa fa-exclamation-triangle" style="margin-right: 6px;"></i>'
                + 'Please fix the following before proceeding:</div>'
                + '<div style="font-size: 13px; color: #b91c1c; line-height: 1.8;">'
                + errors.join('<br>') + '</div></div>';

            $(this).closest('div').before(errHtml);

            // Scroll to first error field
            if (firstError) {
                $('html, body').animate({
                    scrollTop: $(firstError).offset().top - 120
                }, 400);
                $(firstError).focus();
            }
            return; // stop — do not open modal
        }

        // Clear any previous error banner if validation passes
        $('#poValidationError').remove();

        // ── BUILD MODAL ──────────────────────────────
        var html        = '';
        var hasChanges  = false;
        var totalItems  = 0;

        // Count how many product cards exist
        $('.product-card').each(function (ci) {
            totalItems = ci + 1;
        });

        for (var idx = 0; idx < totalItems; idx++) {
            var productName = $('.product-card').eq(idx).find('.product-name').text().trim();

            var curPrice    = parseFloat($('#dealer-price-' + idx).val())   || 0;
            var curQtyDisc  = parseFloat($('#disc-qty-'     + idx).val())   || 0;
            var curSpecDisc = parseFloat($('#disc-spec-'    + idx).val())   || 0;
            var curBasicDisc= parseFloat($('#disc-basic-'   + idx).val())   || 0;
            var curQty      = parseFloat($('#approved-qty-' + idx).val())   || 0;

            var origPrice    = originalValues['dealer-price-' + idx]   || 0;
            var origQtyDisc  = originalValues['disc-qty-'     + idx]   || 0;
            var origSpecDisc = originalValues['disc-spec-'    + idx]   || 0;
            var origBasicDisc= originalValues['disc-basic-'   + idx]   || 0;
            var origQty      = originalValues['approved-qty-' + idx]   || 0;

            var itemChanges = [];

            if (curPrice !== origPrice) {
                itemChanges.push({
                    field : 'Dealer Price',
                    old   : 'Rs. ' + origPrice.toFixed(2),
                    new_  : 'Rs. ' + curPrice.toFixed(2),
                    badge : null
                });
            }
            if (curQtyDisc !== origQtyDisc) {
                itemChanges.push({ field: 'Qty Discount',     old: origQtyDisc  + '%', new_: curQtyDisc   + '%', badge: null });
            }
            if (curSpecDisc !== origSpecDisc) {
                itemChanges.push({ field: 'Special Discount', old: origSpecDisc + '%', new_: curSpecDisc  + '%', badge: null });
            }
            if (curBasicDisc !== origBasicDisc) {
                itemChanges.push({ field: 'Basic Discount',   old: origBasicDisc+ '%', new_: curBasicDisc + '%', badge: null });
            }
            if (curQty !== origQty) {
                var badge = curQty < origQty
                    ? '<span class="qty-reduced-badge">Reduced</span>'
                    : '<span class="qty-increased-badge">Increased</span>';
                itemChanges.push({ field: 'Approved Qty', old: origQty + ' kg', new_: curQty + ' kg', badge: badge });
            }

            // Calculate new net price for display
            var totalDisc   = curQtyDisc + curSpecDisc + curBasicDisc;
            var newNetPrice = curPrice * (1 - totalDisc / 100);
            var newSubtotal = newNetPrice * curQty;

            html += '<div class="section-divider"><i class="fa fa-cube" style="margin-right:6px;"></i>' + productName + '</div>';

            if (itemChanges.length > 0) {
                hasChanges = true;
                html += '<table class="change-summary-table"><thead><tr>';
                html += '<th>Field</th><th>Original Value</th><th></th><th>New Value</th>';
                html += '</tr></thead><tbody>';

                $.each(itemChanges, function (i, c) {
                    html += '<tr>';
                    html += '<td style="font-weight:600; color:#374151;">' + c.field + '</td>';
                    html += '<td><span class="change-pill-old">' + c.old + '</span></td>';
                    html += '<td class="change-arrow"><i class="fa fa-arrow-right"></i></td>';
                    html += '<td><span class="change-pill-new">' + c.new_ + '</span>' + (c.badge || '') + '</td>';
                    html += '</tr>';
                });

                // Always show resulting Net Price & Subtotal after changes
                html += '<tr style="background:#f0fdf4;">';
                html += '<td style="font-weight:700; color:#065f46; font-size:11px; text-transform:uppercase; letter-spacing:0.4px;">Resulting Net Price</td>';
                html += '<td colspan="3" style="font-weight:800; color:#065f46;">Rs. ' + newNetPrice.toFixed(2) + ' &nbsp;·&nbsp; Subtotal: Rs. ' + newSubtotal.toFixed(2) + '</td>';
                html += '</tr>';

                html += '</tbody></table>';
            } else {
                html += '<p style="font-size:12px; color:#9ca3af; padding: 6px 2px; font-style:italic;">'
                      + '<i class="fa fa-check" style="color:#10b981; margin-right:5px;"></i>'
                      + 'No changes — original values kept.</p>';
            }
        }

        $('#approvalChangesBody').html(html);
        $('#noChangesNotice').toggle(!hasChanges && totalItems === 0);
        $('#ApprovalConfirmModal').modal('show');
    });

    // =============================================
    // CONFIRM APPROVE — Submit form + show loader
    // =============================================
    $('#btnConfirmApprove').on('click', function () {
        // Disable button immediately to prevent double-click
        $(this).prop('disabled', true)
               .html('<i class="fa fa-spinner fa-spin" style="margin-right:6px;"></i> Submitting...');

        // Close modal
        $('#ApprovalConfirmModal').modal('hide');

        // Show full-screen loader
        $('#poApprovalLoader').css('display', 'flex');

        // Submit the form
        $('#poProductsForm').submit();
    });

</script>
@stop