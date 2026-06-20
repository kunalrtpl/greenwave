<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
    /* ── BASE RESET ── */
    @page {
        margin: 16mm 14mm 16mm 14mm;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10.5px;
        color: #1e293b;
        background: #ffffff;
        line-height: 1.6;
    }

    /* ── HEADER ── */
    .hdr-wrap { width: 100%; margin-bottom: 12px; }
    .hdr-logo-cell { vertical-align: middle; text-align: left; width: 45%; }
    .hdr-info-cell { vertical-align: middle; text-align: right; width: 55%; }
    .logo-img { width: 170px; height: auto; }
    .hdr-title {
        font-size: 20px;
        font-weight: bold;
        color: #0f172a;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .hdr-sub { font-size: 10.5px; color: #64748b; margin-top: 5px; display: block; }
    .rule { border: none; border-top: 3px solid #0f172a; margin-bottom: 25px; }

    /* ── LAYOUT SECTIONS ── */
    .section-container { width: 100%; margin-bottom: 25px; }
    .block-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .block-td { vertical-align: top; width: 50%; }
    .padding-right { padding-right: 20px; }
    .padding-left { padding-left: 20px; }

    /* ── SECTION TITLES ── */
    .sec-title {
        color: #0f172a;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding-bottom: 6px;
        border-bottom: 2px solid #cbd5e1;
        margin-bottom: 12px;
    }

    /* ── DATA TABLES ── */
    .fl { width: 100%; border-collapse: collapse; }
    .fl td { padding: 6.5px 0; vertical-align: top; font-size: 10.5px; border-bottom: 1px solid #f1f5f9; }
    .fl tr:last-child td { border-bottom: none; }
    .fl .lbl { color: #64748b; width: 38%; font-size: 10px; }
    .fl .sep { color: #94a3b8; width: 5%; text-align: left; }
    .fl .val { color: #0f172a; width: 57%; }

    /* ── COMPETITOR CONTAINER ── */
    .comp-container {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-top: 4px solid #6d28d9;
        background: #ffffff;
        margin-top: 5px;
        margin-bottom: 20px;
    }
    .comp-title {
        background: #f5f3ff;
        color: #5b21b6;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 14px;
        border-bottom: 1px solid #e9d5ff;
    }
    .comp-body { padding: 15px; }
    
    /* COMPETITOR PRICE GRID */
    .comp-price { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .comp-price th {
        background: #f8fafc;
        padding: 10px;
        font-size: 10px;
        font-weight: bold;
        color: #475569;
        text-transform: uppercase;
        border: 1px solid #cbd5e1;
        text-align: center;
    }
    .comp-price td { padding: 12px; font-size: 13px; border: 1px solid #cbd5e1; text-align: center; }
    .comp-price td.strong { font-weight: bold; color: #0f172a; }

    /* ── MISC STYLES ── */
    .ta-field { background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; font-size: 10px; color: #334155; margin-top: 6px; border-radius: 4px; }
    .note-box { background: #fef9c3; border: 1px solid #fde68a; padding: 10px 14px; font-size: 10px; color: #92400e; }
    .badge-yes { color: #7c3aed; font-weight: bold; text-transform: uppercase; }
    .badge-no { color: #64748b; font-weight: bold; text-transform: uppercase; }

    /* ── PRODUCT CARD ── */
    .prod-card { border: 1px solid #cbd5e1; border-top: 4px solid #1e293b; margin-bottom: 18px; background: #ffffff; }
    .prod-head { background: #f8fafc; color: #1e293b; font-size: 12px; font-weight: bold; text-transform: uppercase; padding: 10px 14px; border-bottom: 1px solid #cbd5e1; }
    .prod-body { padding: 15px; }
    .prod-name-lg { font-size: 14px; font-weight: bold; color: #0f172a; }
    .prod-code-sm { font-size: 11px; color: #64748b; font-weight: normal; margin-left: 5px; }

    /* ── PAGE BREAK & FOOTER ── */
    .pagebreak { page-break-before: always; }
    .footer-rule { border: none; border-top: 1px solid #cbd5e1; margin-top: 20px; }
    .foot-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .foot-table td { font-size: 9px; color: #64748b; vertical-align: middle; }
</style>
</head>
<body>

{{-- ════════════════════════════════════════════════
     PAGE 1 — HEADER
════════════════════════════════════════════════ --}}
<table class="hdr-wrap">
    <tr>
        <td class="hdr-logo-cell">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-info-cell">
            <h1 class="hdr-title">Free Sample Request</h1>
            <span class="hdr-sub">Ref No: <strong>{{ $sampling->sample_ref_no_string }}</strong> &nbsp;&bull;&nbsp; {{ now()->format('d M Y, h:i A') }}</span>
        </td>
    </tr>
</table>
<hr class="rule" />

{{-- ════════════════════════════════════════════════
     SECTION 1 — SAMPLE INFO & CUSTOMER DETAILS
════════════════════════════════════════════════ --}}
<div class="section-container">
    <table class="block-table">
        <tr>
            <!-- LEFT COLUMN: REQUEST INFO -->
            <td class="block-td padding-right">
                <div class="sec-title">Sample Request Info</div>
                <table class="fl">
                    <tr><td class="lbl">Request ID</td><td class="sep">:</td><td class="val">{{ $sampling->id }}</td></tr>
                    <tr><td class="lbl">Ref No</td><td class="sep">:</td><td class="val"><strong>{{ $sampling->sample_ref_no_string }}</strong></td></tr>
                    <tr><td class="lbl">Request Type</td><td class="sep">:</td><td class="val">{{ $sampling->request_type }}</td></tr>
                    <tr><td class="lbl">Sample Type</td><td class="sep">:</td><td class="val">{{ ucfirst($sampling->sample_type) }}</td></tr>
                    <tr><td class="lbl">Req. Through</td><td class="sep">:</td><td class="val">{{ $sampling->required_through ?: '—' }}</td></tr>
                    <tr><td class="lbl">Sampling Date</td><td class="sep">:</td><td class="val">{{ \Carbon\Carbon::parse($sampling->sampling_date)->format('d M Y') }}</td></tr>
                    <tr><td class="lbl">Status</td><td class="sep">:</td><td class="val"><strong>{{ ucfirst($sampling->sample_status) }}</strong></td></tr>
                    <tr><td class="lbl">Fin. Year</td><td class="sep">:</td><td class="val">{{ $sampling->financial_year }}</td></tr>
                    <tr><td class="lbl">Created At</td><td class="sep">:</td><td class="val">{{ $sampling->created_at ? $sampling->created_at->format('d M Y, h:i A') : '—' }}</td></tr>
                </table>
            </td>

            <!-- RIGHT COLUMN: CUSTOMER DETAILS -->
            <td class="block-td padding-left" style="border-left: 1px solid #e2e8f0;">
                <div class="sec-title">Customer Details</div>
                @if($sampling->customer)
                    <table class="fl">
                        <tr><td class="lbl">Name</td><td class="sep">:</td><td class="val"><strong>{{ $sampling->customer->name }}</strong></td></tr>
                        <tr><td class="lbl">Contact</td><td class="sep">:</td><td class="val">{{ $sampling->customer->contact_person_name }}</td></tr>
                        <tr><td class="lbl">Mobile</td><td class="sep">:</td><td class="val">{{ $sampling->customer->mobile }}</td></tr>
                        <tr><td class="lbl">Address</td><td class="sep">:</td><td class="val">{{ $sampling->customer->address }}</td></tr>
                        <tr><td class="lbl">Biz Model</td><td class="sep">:</td><td class="val">{{ $sampling->customer->business_model }}</td></tr>
                        <tr>
                            <td class="lbl">Specific Customer</td>
                            <td class="sep">:</td>
                            <td class="val">
                                @if(isset($sampling->is_specific_customer) && $sampling->is_specific_customer)
                                    <span class="badge-yes">Yes</span>
                                @else
                                    <span class="badge-no">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="note-box">No customer linked to this request.</div>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ════════════════════════════════════════════════
     SECTION 2 — EXECUTIVE DETAILS & DISPATCH DETAILS
════════════════════════════════════════════════ --}}
<div class="section-container">
    <table class="block-table">
        <tr>
            <!-- LEFT COLUMN: EXECUTIVE DETAILS & PURPOSE -->
            <td class="block-td padding-right">
                <div class="sec-title">Executive Details</div>
                <table class="fl" style="margin-bottom: 20px;">
                    <tr><td class="lbl">Name</td><td class="sep">:</td><td class="val"><strong>{{ $sampling->user->name ?? '—' }}</strong></td></tr>
                    <tr><td class="lbl">Email</td><td class="sep">:</td><td class="val">{{ $sampling->user->email ?? '—' }}</td></tr>
                    <tr><td class="lbl">Mobile</td><td class="sep">:</td><td class="val">{{ $sampling->user->mobile ?? '—' }}</td></tr>
                </table>

                <div class="sec-title">Purpose / Reason</div>
                @if($sampling->purpose_reason_for_request)
                    <div style="font-size:10.5px; color:#1e293b; line-height:1.6; padding: 4px 0;">{{ $sampling->purpose_reason_for_request }}</div>
                @else
                    <span style="color:#94a3b8; font-style:italic; display:block; padding: 4px 0;">Not specified</span>
                @endif
                @if($sampling->application_plan)
                    <div style="margin-top:10px; font-size:9px; color:#64748b; font-weight:bold; text-transform:uppercase;">Application Plan</div>
                    <div class="ta-field">{{ $sampling->application_plan }}</div>
                @endif
            </td>

            <!-- RIGHT COLUMN: DISPATCH DETAILS -->
            <td class="block-td padding-left" style="border-left: 1px solid #e2e8f0;">
                <div class="sec-title">Dispatch Details</div>
                <table class="fl">
                    <tr><td class="lbl">Dispatch To</td><td class="sep">:</td><td class="val"><strong>{{ $sampling->dispatch_to ?: '—' }}</strong></td></tr>
                    <tr>
                        <td class="lbl">Address</td>
                        <td class="sep">:</td>
                        <td class="val" style="white-space:pre-line; line-height: 1.6;">{{ $sampling->dispatch_address ?: '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

{{-- ════════════════════════════════════════════════
     SECTION 3 — COMPETITOR INFORMATION
════════════════════════════════════════════════ --}}
<div class="section-container">
    @if($sampling->target_competitor)
    <div class="comp-container">
        <div class="comp-title">★ Competitor Information &mdash; Targeted</div>
        <div class="comp-body">
            <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
                <tr>
                    <td style="vertical-align:top; width:33.33%;">
                        <table class="fl">
                            <tr><td class="lbl" style="width:45%;">Targeted</td><td class="sep">:</td><td class="val"><span class="badge-yes">Yes</span></td></tr>
                            <tr><td class="lbl" style="width:45%;">Category</td><td class="sep">:</td><td class="val"><strong>{{ $sampling->competitor_category ?: '—' }}</strong></td></tr>
                        </table>
                    </td>
                    <td style="vertical-align:top; width:33.33%; padding-left: 12px;">
                        <table class="fl">
                            <tr><td class="lbl" style="width:40%;">Product</td><td class="sep">:</td><td class="val"><strong>{{ $sampling->competitor_product_name ?: '—' }}</strong></td></tr>
                            <tr><td class="lbl" style="width:40%;">Brand</td><td class="sep">:</td><td class="val">{{ $sampling->competitor_make ?: '—' }}</td></tr>
                        </table>
                    </td>
                    <td style="vertical-align:top; width:33.33%; padding-left: 12px;">
                        <table class="fl">
                            <tr><td class="lbl" style="width:45%;">Dosage</td><td class="sep">:</td><td class="val">{{ $sampling->competitor_dosage ?: '—' }}</td></tr>
                            <tr><td class="lbl" style="width:45%;">Monthly Req.</td><td class="sep">:</td><td class="val"><strong>{{ !is_null($sampling->competitor_monthly_requirement_kg) ? number_format((float)$sampling->competitor_monthly_requirement_kg, 2).' kg' : '—' }}</strong></td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="comp-price">
                <thead>
                    <tr>
                        <th>Dealer Price</th>
                        <th>Customer Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="strong">&#8377; {{ !is_null($sampling->competitor_dealer_price) ? number_format((float)$sampling->competitor_dealer_price, 2) : '0.00' }}</td>
                        <td class="strong">&#8377; {{ !is_null($sampling->competitor_customer_price) ? number_format((float)$sampling->competitor_customer_price, 2) : '0.00' }}</td>
                    </tr>
                </tbody>
            </table>

            @if($sampling->competitor_specialty || $sampling->competitor_application_details || $sampling->competitor_expectation)
            <table style="width:100%; border-collapse:collapse; margin-top:15px; table-layout:fixed;">
                <tr>
                    @if($sampling->competitor_specialty)
                    <td style="vertical-align:top; padding-right:10px;">
                        <div style="font-size:9px; color:#64748b; font-weight:bold; text-transform:uppercase;">Specialty</div>
                        <div class="ta-field">{{ $sampling->competitor_specialty }}</div>
                    </td>
                    @endif
                    @if($sampling->competitor_application_details)
                    <td style="vertical-align:top; padding-right:10px;">
                        <div style="font-size:9px; color:#64748b; font-weight:bold; text-transform:uppercase;">Application Details</div>
                        <div class="ta-field">{{ $sampling->competitor_application_details }}</div>
                    </td>
                    @endif
                    @if($sampling->competitor_expectation)
                    <td style="vertical-align:top;">
                        <div style="font-size:9px; color:#64748b; font-weight:bold; text-transform:uppercase;">Expectation</div>
                        <div class="ta-field">{{ $sampling->competitor_expectation }}</div>
                    </td>
                    @endif
                </tr>
            </table>
            @endif
        </div>
    </div>
    @else
    <div style="margin-top: 15px;">
        <div class="note-box" style="background: #f8fafc; border-color: #cbd5e1; color: #475569;">
            No competitor targeted information listed for this request.
        </div>
    </div>
    @endif
</div>

{{-- PAGE 1 FOOTER --}}
<div style="position: absolute; bottom: 0; width: 100%;">
    <hr class="footer-rule" />
    <table class="foot-table">
        <tr>
            <td style="text-align: left; font-weight: bold;">Greenwave &bull; Free Sample Request</td>
            <td style="text-align: center; color: #94a3b8;">Confidential &mdash; Internal Use Only</td>
            <td style="text-align: right;">Page 1 of 2</td>
        </tr>
    </table>
</div>


{{-- ════════════════════════════════════════════════
     PAGE 2 — PRODUCT DETAILS
════════════════════════════════════════════════ --}}
<div class="pagebreak">

<table class="hdr-wrap">
    <tr>
        <td class="hdr-logo-cell">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-info-cell">
            <h1 class="hdr-title">Product Details</h1>
            <span class="hdr-sub">Ref No: <strong>{{ $sampling->sample_ref_no_string }}</strong> &nbsp;&bull;&nbsp; {{ now()->format('d M Y, h:i A') }}</span>
        </td>
    </tr>
</table>
<hr class="rule" />

@php
    $userItems = $sampling->sampleitems->where('requested_from', 'user')->values();
    $total     = $userItems->count();
@endphp

@if($total === 0)
    <div class="note-box">No products were added to this sample request.</div>
@else
    {{-- Summary Strip --}}
    <div style="background:#f8fafc; border:1px solid #cbd5e1; padding:12px 16px; margin-bottom:25px; font-size:11px; color:#1e293b; border-left: 4px solid #1e293b;">
        Requested Summary: <strong>{{ $total }}</strong> product{{ $total > 1 ? 's' : '' }} &mdash; 
        Total Qty: <strong>{{ $userItems->sum('qty') }} kg</strong> &nbsp;&bull;&nbsp; 
        Total Packs: <strong>{{ $userItems->sum('no_of_packs') }}</strong>
    </div>

    {{-- Product Cards Structure --}}
    @foreach($userItems as $idx => $item)
    <div class="prod-card">
        <div class="prod-head">Product {{ $idx + 1 }}</div>
        <div class="prod-body">
            <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
                <tr>
                    <td style="vertical-align:top; width:55%; padding-right:15px; border-right:1px solid #e2e8f0;">
                        <div style="margin-bottom:6px;">
                            <span class="prod-name-lg">{{ $item->requested_product->product_name ?? '—' }}</span>
                            @if(!empty($item->requested_product->product_code ?? ''))
                                <span class="prod-code-sm">({{ $item->requested_product->product_code }})</span>
                            @endif
                        </div>
                        @if($item->remarks)
                            <div style="font-size:10px; color:#475569; margin-top:8px; background:#f8fafc; padding:8px; border:1px dashed #cbd5e1; border-radius: 4px;">
                                <strong>Remarks:</strong> {{ $item->remarks }}
                            </div>
                        @endif
                    </td>
                    <td style="vertical-align:top; width:45%; padding-left:15px;">
                        <table class="fl">
                            <tr><td class="lbl">Pack Size</td><td class="sep">:</td><td class="val"><strong>{{ $item->pack_size ?: '—' }}</strong></td></tr>
                            <tr><td class="lbl">No. of Packs</td><td class="sep">:</td><td class="val"><strong>{{ $item->no_of_packs ?: '—' }}</strong></td></tr>
                            <tr><td class="lbl">Required Qty</td><td class="sep">:</td><td class="val" style="color:#b91c1c; font-weight:bold; font-size: 11.5px;">{{ $item->qty }} kg</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach
@endif

{{-- PAGE 2 FOOTER --}}
<div style="position: absolute; bottom: 0; width: 100%;">
    <hr class="footer-rule" />
    <table class="foot-table">
        <tr>
            <td style="text-align: left; font-weight: bold;">Greenwave &bull; Free Sample Request</td>
            <td style="text-align: center; color: #94a3b8;">Confidential &mdash; Internal Use Only</td>
            <td style="text-align: right;">Page 2 of 2</td>
        </tr>
    </table>
</div>

</div>

</body>
</html>