@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    .page-content { padding-bottom: 40px !important; }

    .portlet.light.bordered {
        border-radius: 6px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.08);
        border: 1px solid #dde3ec;
    }

    /* ── Summary bar ── */
    .summary-bar { display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
    .sum-card {
        background:#f4f6fa; border:1px solid #dde3ec; border-radius:6px;
        padding:8px 16px; font-size:12px; color:#5a6a85;
        display:flex; align-items:center; gap:6px;
    }
    .sum-card strong { font-size:15px; color:#2d3748; }
    .sum-card .dot   { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
    .dot-total { background:#3598dc; }
    .dot-notl  { background:#e53e3e; }
    .dot-nomsds{ background:#ed8936; }

    /* ── Filter strip ── */
    .filter-strip {
        display:flex; align-items:flex-end; gap:14px; flex-wrap:wrap;
        background:#f4f6fa; border:1px solid #dde3ec; border-radius:6px;
        padding:14px 18px; margin-bottom:18px;
    }
    .filter-strip .fg { display:flex; flex-direction:column; gap:4px; }
    .filter-strip label {
        font-size:11px; font-weight:700; color:#5a6a85;
        text-transform:uppercase; letter-spacing:0.5px; margin:0;
    }
    .filter-strip select,
    .filter-strip input[type="text"] {
        height:34px; border:1px solid #c8d0dc; border-radius:4px !important;
        font-size:13px; color:#2d3748; background:#fff; padding:0 10px;
    }
    .filter-strip select { min-width:190px; }
    .filter-strip input[type="text"] { width:180px; }
    .filter-result { margin-left:auto; font-size:12px; color:#718096; padding-bottom:4px; white-space:nowrap; }
    .filter-result strong { color:#3598dc; }

    /* ── Table ── */
    .doc-table { width:100%; border-collapse:collapse; font-size:12px; }

    .doc-table thead tr th {
        background:#eef1f7; color:#4a5568; font-weight:700;
        font-size:10px; text-transform:uppercase; letter-spacing:0.55px;
        padding:9px 10px; border:1px solid #d5dbe8; white-space:nowrap; text-align:left;
    }
    .doc-table thead tr th:first-child,
    .doc-table tbody td:first-child {
        width:36px !important; min-width:36px !important; max-width:36px !important;
        padding:8px 4px !important; text-align:center !important;
        color:#a0aec0; font-size:11px;
    }
    .doc-table tbody td {
        padding:8px 10px; border:1px solid #e4e9f2;
        vertical-align:middle; color:#2d3748; background:#fff;
    }
    .doc-table tbody tr:hover td { background:#fafcff; }
    .doc-table tbody tr:nth-child(even) td { background:#fafbfd; }
    .doc-table tbody tr:nth-child(even):hover td { background:#f0f5ff; }
    .doc-table tbody tr.is-dirty td,
    .doc-table tbody tr.is-dirty:nth-child(even) td { background:#fffde7 !important; }

    @keyframes rowFlash { 0%{background:#c6f6d5;} 100%{background:#fff;} }
    .row-saved td { animation:rowFlash 1.6s ease forwards; }

    /* ── Product name ── */
    .prod-name { font-weight:600; color:#2d3748; font-size:12px; display:block; }
    .prod-code { font-size:10px; color:#a0aec0; display:block; margin-top:1px; }

    /* ── Doc cell ── */
    .doc-cell { display:flex; flex-direction:column; gap:5px; }
    .file-input-wrap input[type="file"] {
        font-size:11px; border:1px solid #e2e8f0; border-radius:4px !important;
        padding:2px 5px; background:#fff; height:28px; width:100%;
        transition:border-color 0.18s;
    }
    .file-input-wrap input[type="file"].changed { border-color:#f6ad55 !important; background:#fffdf5; }

    .doc-links { display:flex; align-items:center; gap:5px; flex-wrap:wrap; }
    .doc-link-view {
        font-size:10px; color:#3598dc; font-weight:600;
        text-decoration:none; display:inline-flex; align-items:center; gap:3px;
    }
    .doc-link-view:hover { text-decoration:underline; }
    .doc-divider { color:#cbd5e0; font-size:10px; }
    .btn-doc-delete {
        font-size:9px; padding:2px 7px; border-radius:4px !important;
        color:#e53e3e; background:#fff5f5; border:1px solid #feb2b2;
        font-weight:600; cursor:pointer; transition:all 0.15s; line-height:1.4;
    }
    .btn-doc-delete:hover { background:#fed7d7; }
    .no-doc-badge { display:inline-flex; align-items:center; gap:3px; font-size:10px; color:#a0aec0; font-style:italic; }

    /* ── Certification cell ── */
    .cert-cell { display:flex; flex-direction:column; gap:4px; align-items:flex-start; }

    /* Yes/No radio group */
    .yn-group { display:flex; gap:8px; align-items:center; }
    .yn-group label {
        display:inline-flex; align-items:center; gap:3px;
        font-size:11px; font-weight:600; color:#4a5568;
        cursor:pointer; margin:0; white-space:nowrap;
    }
    .yn-group input[type="radio"] { cursor:pointer; accent-color:#3598dc; }
    .yn-group input[type="radio"].changed { accent-color:#f6ad55; }

    /* Badge display */
    .cert-yes { display:inline-block; font-size:10px; padding:2px 8px; border-radius:10px !important; font-weight:700; background:#e6fffa; color:#276749; border:1px solid #9ae6b4; }
    .cert-no  { display:inline-block; font-size:10px; padding:2px 8px; border-radius:10px !important; font-weight:700; background:#fff5f5; color:#c53030; border:1px solid #feb2b2; }

    /* ZDHC PID input */
    .pid-input {
        border:1px solid #e2e8f0; border-radius:4px !important;
        padding:3px 7px; font-size:11px; color:#2d3748;
        background:#fff; height:26px; width:110px;
        transition:border-color 0.18s;
    }
    .pid-input:focus { outline:none; border-color:#3598dc; }
    .pid-input.changed { border-color:#f6ad55 !important; background:#fffdf5; }
    .pid-label { font-size:9px; color:#718096; font-weight:600; text-transform:uppercase; letter-spacing:0.3px; }

    /* ── Update button ── */
    .btn-update {
        padding:5px 12px; font-size:10px; font-weight:700;
        border-radius:4px !important; text-transform:uppercase;
        letter-spacing:0.4px; transition:all 0.18s;
        display:block; margin:0 auto; text-align:center; white-space:nowrap;
    }
    .btn-update:disabled { opacity:0.3; cursor:not-allowed; pointer-events:none; }
    .btn-update:not(:disabled):hover { transform:translateY(-1px); box-shadow:0 3px 8px rgba(53,152,220,0.30); }
    .btn-update.saving { opacity:0.6; pointer-events:none; }

    /* ── Row upload overlay loader ── */
    .row-uploading td { opacity: 0.4; pointer-events: none; transition: opacity 0.2s; }
    .row-uploading .row-upload-overlay {
        display: none; /* overlay div not used - we use the fixed badge instead */
    }
    /* Fixed upload badge shown when any row is saving */
    #upload-progress-badge {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9998;
        background: #fff;
        border: 1px solid #bee3f8;
        border-radius: 10px;
        padding: 18px 32px;
        font-size: 14px;
        font-weight: 700;
        color: #2b6cb0;
        box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        text-align: center;
        min-width: 220px;
    }
    #upload-progress-badge i { font-size: 22px; display: block; margin-bottom: 8px; color: #3598dc; }
    #upload-progress-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.08);
        z-index: 9997;
    }

    /* ── Trader badge ── */
    .trader-badge {
        display:inline-block; font-size:9px; padding:1px 6px;
        border-radius:10px !important; background:#fffbeb; color:#975a16;
        border:1px solid #f6e05e; font-weight:700; margin-left:4px;
    }

    /* ── Toast ── */
    #toast-wrap { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px; pointer-events:none; }
    .toast-item { padding:10px 18px; border-radius:6px; font-size:13px; font-weight:600; box-shadow:0 4px 14px rgba(0,0,0,0.12); pointer-events:auto; opacity:0; transition:opacity 0.28s; border:1px solid transparent; }
    .toast-success { background:#c6f6d5; color:#276749; border-color:#9ae6b4; }
    .toast-danger  { background:#fed7d7; color:#9b2c2c; border-color:#fc8181; }

    #empty-row td { text-align:center; padding:30px; color:#a0aec0; font-style:italic; border:1px solid #e4e9f2; }
    .alert { border-radius:6px !important; }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">

            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-file-text-o font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">{{ $title }}</span>
                </div>
            </div>

            <div class="portlet-body">

                @if(Session::has('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-check-circle"></i> {{ Session::get('success') }}
                    </div>
                @endif

                {{-- Summary --}}
                @php
                    $noTl   = $products->filter(fn($p) => empty($p->technical_literature))->count();
                    $noMsds = $products->filter(fn($p) => empty($p->msds))->count();
                @endphp
                <div class="summary-bar">
                    <div class="sum-card"><span class="dot dot-total"></span>Total Active &nbsp;<strong>{{ $products->count() }}</strong></div>
                    <div class="sum-card"><span class="dot dot-notl"></span>No Tech. Literature &nbsp;<strong style="color:#e53e3e;">{{ $noTl }}</strong></div>
                    <div class="sum-card"><span class="dot dot-nomsds"></span>No MSDS &nbsp;<strong style="color:#ed8936;">{{ $noMsds }}</strong></div>
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
                        <label><i class="fa fa-filter"></i> &nbsp;Document Status</label>
                        <select id="filter-doc-status">
                            <option value="">— All —</option>
                            <option value="no_tl">Missing Tech. Literature</option>
                            <option value="no_msds">Missing MSDS</option>
                            <option value="no_both">Missing Both</option>
                            <option value="complete">Both Uploaded</option>
                        </select>
                    </div>
                    <div class="fg">
                        <label><i class="fa fa-search"></i> &nbsp;Search</label>
                        <input type="text" id="filter-search" placeholder="Name or code...">
                    </div>
                    <div class="fg">
                        <label>GOTS</label>
                        <select id="filter-gots" style="min-width:100px;">
                            <option value="">— All —</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="fg">
                        <label>ZDHC</label>
                        <select id="filter-zdhc" style="min-width:100px;">
                            <option value="">— All —</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="fg">
                        <label>Oekotex</label>
                        <select id="filter-oeko" style="min-width:100px;">
                            <option value="">— All —</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="filter-result">
                        Showing <strong id="visible-count">{{ $products->count() }}</strong>
                        of {{ $products->count() }} products
                    </div>
                </div>

                {{-- Table --}}
                <div style="overflow-x:auto;">
                <table class="doc-table" id="doc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="width:16%;">Product Name</th>
                            <th style="width:18%;">Technical Literature</th>
                            <th style="width:18%;">MSDS</th>
                            <th style="width:10%; text-align:center;">GOTS</th>
                            <th style="width:14%; text-align:center;">ZDHC</th>
                            <th style="width:10%; text-align:center;">Oekotex</th>
                            <th style="width:8%; text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="doc-tbody">

                    @foreach($products as $index => $product)
                    <tr class="product-row"
                        id="row-{{ $product->id }}"
                        data-product-id="{{ $product->id }}"
                        data-has-tl="{{ !empty($product->technical_literature) ? 1 : 0 }}"
                        data-has-msds="{{ !empty($product->msds) ? 1 : 0 }}"
                        data-is-trader="{{ $product->is_trader_product }}"
                        data-orig-gots="{{ $product->gots_certification }}"
                        data-orig-zdhc="{{ $product->zdhc_certification }}"
                        data-orig-pid="{{ $product->zdhc_pid }}"
                        data-orig-oeko="{{ $product->oekotex_certified }}"
                        data-gots="{{ $product->gots_certification }}"
                        data-zdhc="{{ $product->zdhc_certification }}"
                        data-oeko="{{ $product->oekotex_certified }}"
                        style="position:relative;">

                        <td>{{ $index + 1 }}</td>

                        {{-- Product Name --}}
                        <td>
                            <span class="prod-name">
                                {{ $product->product_name }}
                                @if($product->is_trader_product)
                                    <span class="trader-badge">Trader</span>
                                @endif
                            </span>
                            <span class="prod-code">{{ $product->product_code }}</span>
                        </td>

                        {{-- Technical Literature --}}
                        <td>
                            <div class="doc-cell">
                                <div class="file-input-wrap">
                                    <input type="file" class="field-tl" accept=".pdf,.doc,.docx" data-product-id="{{ $product->id }}">
                                </div>
                                <div class="doc-links" id="tl-links-{{ $product->id }}">
                                    @if(!empty($product->technical_literature))
                                        <a href="{{ url('images/ProductDocuments/' . $product->technical_literature) }}" target="_blank" class="doc-link-view"><i class="fa fa-eye"></i> View</a>
                                        <span class="doc-divider">|</span>
                                        <button type="button" class="btn-doc-delete" data-product-id="{{ $product->id }}" data-field="technical_literature"><i class="fa fa-trash"></i> Del</button>
                                    @else
                                        <span class="no-doc-badge"><i class="fa fa-times-circle" style="color:#fc8181;"></i> None</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- MSDS --}}
                        <td>
                            <div class="doc-cell">
                                <div class="file-input-wrap">
                                    <input type="file" class="field-msds" accept=".pdf,.doc,.docx" data-product-id="{{ $product->id }}">
                                </div>
                                <div class="doc-links" id="msds-links-{{ $product->id }}">
                                    @if(!empty($product->msds))
                                        <a href="{{ url('images/ProductDocuments/' . $product->msds) }}" target="_blank" class="doc-link-view"><i class="fa fa-eye"></i> View</a>
                                        <span class="doc-divider">|</span>
                                        <button type="button" class="btn-doc-delete" data-product-id="{{ $product->id }}" data-field="msds"><i class="fa fa-trash"></i> Del</button>
                                    @else
                                        <span class="no-doc-badge"><i class="fa fa-times-circle" style="color:#fc8181;"></i> None</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- GOTS Certified --}}
                        <td style="text-align:center;">
                            <div class="cert-cell" style="align-items:center;">
                                <div class="yn-group">
                                    <label>
                                        <input type="radio" class="field-gots" name="gots_{{ $product->id }}"
                                               value="Yes" data-product-id="{{ $product->id }}"
                                               {{ $product->gots_certification === 'Yes' ? 'checked' : '' }}>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" class="field-gots" name="gots_{{ $product->id }}"
                                               value="No" data-product-id="{{ $product->id }}"
                                               {{ ($product->gots_certification !== 'Yes') ? 'checked' : '' }}>
                                        No
                                    </label>
                                </div>
                            </div>
                        </td>

                        {{-- ZDHC Certified + PID in same cell --}}
                        <td style="text-align:center;">
                            <div class="cert-cell" style="align-items:center;">
                                <div class="yn-group">
                                    <label>
                                        <input type="radio" class="field-zdhc" name="zdhc_{{ $product->id }}"
                                               value="Yes" data-product-id="{{ $product->id }}"
                                               {{ $product->zdhc_certification === 'Yes' ? 'checked' : '' }}>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" class="field-zdhc" name="zdhc_{{ $product->id }}"
                                               value="No" data-product-id="{{ $product->id }}"
                                               {{ ($product->zdhc_certification !== 'Yes') ? 'checked' : '' }}>
                                        No
                                    </label>
                                </div>
                                {{-- PID shown only when ZDHC = Yes --}}
                                <div class="pid-wrap" id="pid-wrap-{{ $product->id }}"
                                     style="{{ $product->zdhc_certification === 'Yes' ? '' : 'display:none;' }} margin-top:5px; width:100%;">
                                    <div class="pid-label" style="text-align:left;">PID No. <span style="color:#e53e3e;">*</span></div>
                                    <input type="text"
                                           class="pid-input field-pid"
                                           data-product-id="{{ $product->id }}"
                                           value="{{ $product->zdhc_pid ?? '' }}"
                                           placeholder="e.g. P945FO47"
                                           style="width:100%;">
                                </div>
                            </div>
                        </td>

                        {{-- Oekotex Certified --}}
                        <td style="text-align:center;">
                            <div class="cert-cell" style="align-items:center;">
                                <div class="yn-group">
                                    <label>
                                        <input type="radio" class="field-oeko" name="oeko_{{ $product->id }}"
                                               value="Yes" data-product-id="{{ $product->id }}"
                                               {{ $product->oekotex_certified === 'Yes' ? 'checked' : '' }}>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" class="field-oeko" name="oeko_{{ $product->id }}"
                                               value="No" data-product-id="{{ $product->id }}"
                                               {{ ($product->oekotex_certified !== 'Yes') ? 'checked' : '' }}>
                                        No
                                    </label>
                                </div>
                            </div>
                        </td>

                        {{-- Action --}}
                        <td>
                            <button type="button"
                                    class="btn btn-primary btn-xs btn-update"
                                    data-product-id="{{ $product->id }}"
                                    disabled>
                                <i class="fa fa-save"></i> Save
                            </button>
                        </td>

                    </tr>
                    @endforeach

                    <tr id="empty-row" style="display:none;">
                        <td colspan="8">
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

<div id="upload-progress-backdrop"></div>
<div id="upload-progress-badge">
    <i class="fa fa-cloud-upload fa-spin"></i>
    Uploading document...<br>
    <small style="font-weight:400; color:#718096; font-size:11px;">Please wait</small>
</div>
<div id="toast-wrap"></div>

<script>
$(document).ready(function () {

    /* ════════════════════════════════════════════════════════
       1. DIRTY TRACKING
    ════════════════════════════════════════════════════════ */
    function checkDirty($row) {
        var pid = $row.data('product-id');

        var hasTlFile   = $row.find('.field-tl').get(0)   && $row.find('.field-tl').get(0).files.length   > 0;
        var hasMsdsFile = $row.find('.field-msds').get(0) && $row.find('.field-msds').get(0).files.length > 0;

        var curGots = $row.find('.field-gots:checked').val() || '';
        var curZdhc = $row.find('.field-zdhc:checked').val() || '';
        var curPid  = $row.find('.field-pid').val().trim();
        var curOeko = $row.find('.field-oeko:checked').val() || '';

        var origGots = $row.data('orig-gots') || '';
        var origZdhc = $row.data('orig-zdhc') || '';
        var origPid  = ($row.data('orig-pid') || '') + '';
        var origOeko = $row.data('orig-oeko') || '';

        var certChanged = (curGots !== origGots) || (curZdhc !== origZdhc)
                       || (curPid  !== origPid)  || (curOeko !== origOeko);

        var dirty = hasTlFile || hasMsdsFile || certChanged;

        $row.toggleClass('is-dirty', dirty);
        $row.find('.field-tl').toggleClass('changed',   hasTlFile);
        $row.find('.field-msds').toggleClass('changed', hasMsdsFile);
        $row.find('.field-pid').toggleClass('changed',  curPid !== origPid);
        $row.find('.btn-update').prop('disabled', !dirty);
    }

    /* File inputs */
    $(document).on('change', '.field-tl, .field-msds', function () {
        checkDirty($('#row-' + $(this).data('product-id')));
    });

    /* Radio buttons */
    $(document).on('change', '.field-gots, .field-zdhc, .field-oeko', function () {
        var pid  = $(this).data('product-id');
        var $row = $('#row-' + pid);

        // Show/hide PID wrap based on ZDHC value
        if ($(this).hasClass('field-zdhc')) {
            var zdhcVal = $row.find('.field-zdhc:checked').val();
            if (zdhcVal === 'Yes') {
                $('#pid-wrap-' + pid).slideDown(150);
            } else {
                $('#pid-wrap-' + pid).slideUp(150);
                $row.find('.field-pid').val('').removeClass('changed');
            }
        }
        checkDirty($row);
    });

    /* PID text input */
    $(document).on('input', '.field-pid', function () {
        checkDirty($('#row-' + $(this).data('product-id')));
    });

    /* ════════════════════════════════════════════════════════
       2. SAVE — AJAX multipart per row
    ════════════════════════════════════════════════════════ */
    $(document).on('click', '.btn-update:not(:disabled)', function () {
        var $btn = $(this);
        var pid  = $btn.data('product-id');
        var $row = $('#row-' + pid);

        // Validate: PID mandatory when ZDHC = Yes
        var zdhcVal = $row.find('.field-zdhc:checked').val();
        var $pidInput = $row.find('.field-pid');
        if (zdhcVal === 'Yes' && $pidInput.val().trim() === '') {
            $pidInput.css({ 'border-color': '#e53e3e', 'background': '#fff5f5' }).focus();
            setTimeout(function() {
                $pidInput.css({ 'border-color': '', 'background': '' });
            }, 3000);
            alert('ZDHC PID Number is mandatory when ZDHC is Yes.');
            return false;
        }

        var tlInput   = $row.find('.field-tl').get(0);
        var msdsInput = $row.find('.field-msds').get(0);

        var formData = new FormData();
        formData.append('_token',            '{{ csrf_token() }}');
        formData.append('gots_certification', $row.find('.field-gots:checked').val() || 'No');
        formData.append('zdhc_certification', $row.find('.field-zdhc:checked').val() || 'No');
        formData.append('zdhc_pid',           $row.find('.field-pid').val().trim());
        formData.append('oekotex_certified',  $row.find('.field-oeko:checked').val() || 'No');

        if (tlInput   && tlInput.files.length   > 0) formData.append('technical_literature', tlInput.files[0]);
        if (msdsInput && msdsInput.files.length > 0) formData.append('msds', msdsInput.files[0]);

        $btn.addClass('saving').html('<i class="fa fa-save"></i>');

        // Dim the row and show centred upload badge
        $row.addClass('row-uploading');
        $('#upload-progress-backdrop, #upload-progress-badge').fadeIn(150);

        $.ajax({
            url:         '/admin/product-documents/upload/' + pid,
            method:      'POST',
            data:        formData,
            processData: false,
            contentType: false,
            success: function (resp) {
                if (!resp.success) { showToast('danger', resp.message); return; }

                // Update TL links
                if (resp.files && resp.files.technical_literature) {
                    var f = resp.files.technical_literature;
                    $row.data('has-tl', 1);
                    $('#tl-links-' + pid).html(
                        '<a href="' + f.view_url + '" target="_blank" class="doc-link-view"><i class="fa fa-eye"></i> View</a>' +
                        '<span class="doc-divider">|</span>' +
                        '<button type="button" class="btn-doc-delete" data-product-id="' + pid + '" data-field="technical_literature"><i class="fa fa-trash"></i> Del</button>'
                    );
                    $row.find('.field-tl').val('');
                }

                // Update MSDS links
                if (resp.files && resp.files.msds) {
                    var f = resp.files.msds;
                    $row.data('has-msds', 1);
                    $('#msds-links-' + pid).html(
                        '<a href="' + f.view_url + '" target="_blank" class="doc-link-view"><i class="fa fa-eye"></i> View</a>' +
                        '<span class="doc-divider">|</span>' +
                        '<button type="button" class="btn-doc-delete" data-product-id="' + pid + '" data-field="msds"><i class="fa fa-trash"></i> Del</button>'
                    );
                    $row.find('.field-msds').val('');
                }

                // Update stored originals for certs so dirty check resets
                $row.data('orig-gots', resp.gots_certification || 'No');
                $row.data('orig-zdhc', resp.zdhc_certification || 'No');
                $row.data('orig-pid',  resp.zdhc_pid           || '');
                $row.data('orig-oeko', resp.oekotex_certified  || 'No');

                // Update filter data attrs too
                $row.data('gots', resp.gots_certification || 'No');
                $row.data('zdhc', resp.zdhc_certification || 'No');
                $row.data('oeko', resp.oekotex_certified  || 'No');

                $row.removeClass('is-dirty');
                $row.find('.field-tl, .field-msds, .field-pid').removeClass('changed');
                $row.addClass('row-saved');
                setTimeout(function () { $row.removeClass('row-saved'); }, 1700);

                showToast('success', '<i class="fa fa-check-circle"></i> &nbsp;' + resp.message);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Save failed.';
                showToast('danger', '<i class="fa fa-times-circle"></i> &nbsp;' + msg);
            },
            complete: function () {
                // Remove row dimming and hide upload badge
                $row.removeClass('row-uploading');
                $('#upload-progress-backdrop, #upload-progress-badge').fadeOut(150);
                $btn.removeClass('saving').html('<i class="fa fa-save"></i> Save');
                checkDirty($row);
            }
        });
    });

    /* ════════════════════════════════════════════════════════
       3. DELETE document
    ════════════════════════════════════════════════════════ */
    $(document).on('click', '.btn-doc-delete', function () {
        if (!confirm('Delete this document? This cannot be undone.')) return;

        var $btn  = $(this);
        var pid   = $btn.data('product-id');
        var field = $btn.data('field');
        var $row  = $('#row-' + pid);

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url:    '/admin/product-documents/delete/' + pid + '/' + field,
            method: 'POST',
            data:   { _token: '{{ csrf_token() }}' },
            success: function (resp) {
                if (!resp.success) { showToast('danger', resp.message); return; }

                var linkId = (field === 'technical_literature') ? 'tl-links-' + pid : 'msds-links-' + pid;
                $('#' + linkId).html('<span class="no-doc-badge"><i class="fa fa-times-circle" style="color:#fc8181;"></i> None</span>');

                if (field === 'technical_literature') $row.data('has-tl', 0);
                else                                  $row.data('has-msds', 0);

                showToast('success', '<i class="fa fa-check-circle"></i> &nbsp;Document deleted.');
            },
            error: function () { showToast('danger', 'Delete failed. Please try again.'); },
            complete: function () { $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Del'); }
        });
    });

    /* ════════════════════════════════════════════════════════
       4. FILTERS
    ════════════════════════════════════════════════════════ */
    function applyFilters() {
        var prodId    = $('#filter-product').val();
        var docStatus = $('#filter-doc-status').val();
        var search    = $('#filter-search').val().toLowerCase().trim();
        var gotsFilter = $('#filter-gots').val();
        var zdhcFilter = $('#filter-zdhc').val();
        var oekoFilter = $('#filter-oeko').val();
        var visible   = 0;

        $('#doc-tbody .product-row').each(function () {
            var $r    = $(this);
            var hasTl = $r.data('has-tl')  == 1;
            var hasMd = $r.data('has-msds') == 1;

            if (prodId    && $r.data('product-id') + '' !== prodId) { $r.hide(); return; }
            if (docStatus === 'no_tl'    && hasTl)                  { $r.hide(); return; }
            if (docStatus === 'no_msds'  && hasMd)                  { $r.hide(); return; }
            if (docStatus === 'no_both'  && (hasTl || hasMd))       { $r.hide(); return; }
            if (docStatus === 'complete' && (!hasTl || !hasMd))     { $r.hide(); return; }
            if (gotsFilter && $r.data('gots') !== gotsFilter)       { $r.hide(); return; }
            if (zdhcFilter && $r.data('zdhc') !== zdhcFilter)       { $r.hide(); return; }
            if (oekoFilter && $r.data('oeko') !== oekoFilter)       { $r.hide(); return; }

            if (search) {
                var name = $r.find('.prod-name').text().toLowerCase();
                var code = $r.find('.prod-code').text().toLowerCase();
                if (name.indexOf(search) === -1 && code.indexOf(search) === -1) { $r.hide(); return; }
            }

            $r.show(); visible++;
        });

        $('#visible-count').text(visible);
        $('#empty-row').toggle(visible === 0);
    }

    $('#filter-product, #filter-doc-status, #filter-gots, #filter-zdhc, #filter-oeko').on('change', applyFilters);
    $('#filter-search').on('input', applyFilters);

    /* ════════════════════════════════════════════════════════
       5. TOAST
    ════════════════════════════════════════════════════════ */
    function showToast(type, html) {
        var $t = $('<div class="toast-item toast-' + type + '"></div>').html(html);
        $('#toast-wrap').append($t);
        setTimeout(function () { $t.css('opacity', 1); }, 10);
        setTimeout(function () { $t.css('opacity', 0); setTimeout(function () { $t.remove(); }, 320); }, 3500);
    }

});
</script>
@endsection