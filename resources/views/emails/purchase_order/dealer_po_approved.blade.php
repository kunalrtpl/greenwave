<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Purchase Order Approved</title>
    <style>

        /* ── Reset ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        img { border: 0; display: block; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }

        /* ── Page ── */
        body, .bg-page      { background-color: #e8f5e9; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        .wrapper            { max-width: 700px; width: 100%; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,100,0,0.14); }

        /* ── Header ── */
        .header             { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%); padding: 40px 32px 32px; text-align: center; }
        .header-divider     { width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 24px; border-radius: 2px; }
        .header-title       { font-size: 26px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 8px; }
        .header-date        { font-size: 13px; color: rgba(255,255,255,0.65); }

        /* ── Ribbon ── */
        .ribbon             { background: #10b981; padding: 12px 32px; }
        .ribbon-ref         { font-size: 13px; color: #fff; font-weight: 600; }
        .ribbon-badge       { display: inline-block; background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.5); border-radius: 20px; padding: 3px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.8px; }

        /* ── Sections ── */
        .section            { padding: 24px 32px 0; }
        .section-sm         { padding: 16px 32px 0; }
        .section-last       { padding: 28px 32px 0; }

        /* ── Greeting ── */
        .greeting-box       { background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 5px solid #10b981; border-radius: 10px; padding: 20px 22px; }
        .greeting-name      { font-size: 15px; color: #065f46; font-weight: 700; margin-bottom: 8px; }
        .greeting-body      { font-size: 13px; color: #047857; line-height: 1.8; }

        /* ── Order Summary Info ── */
        .summary-table          { width: 100%; border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden; }
        .summary-header         { background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%); }
        .summary-header td      { padding: 12px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.8px; }
        .summary-label          { padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600; text-transform: uppercase; width: 25%; background: #f9fbe7; }
        .summary-value          { padding: 11px 16px; font-size: 13px; color: #33691e; }
        .summary-value-bold     { padding: 11px 16px; font-size: 14px; color: #1b5e20; font-weight: 800; }
        .summary-row-border     { border-bottom: 1px solid #f1f8e9; }

        /* ── Section Label ── */
        .section-label-bar  { display: inline-block; width: 4px; height: 16px; background: #10b981; border-radius: 2px; vertical-align: middle; margin-right: 10px; }
        .section-label      { font-size: 13px; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 0.8px; vertical-align: middle; }
        .section-count      { font-size: 11px; color: #6ee7b7; font-weight: 600; }

        /* ── Items Table ── */
        .items-table            { width: 100%; border: 1px solid #6ee7b7; border-radius: 12px; overflow: hidden; font-size: 12px; }
        .items-header           { background: linear-gradient(135deg, #065f46 0%, #059669 100%); }
        .items-header td        { padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
        .items-header .center   { text-align: center; }
        .items-header .right    { text-align: right; }
        .item-row-even          { background: #f0fdf4; border-bottom: 1px solid #d1fae5; }
        .item-row-odd           { background: #ffffff; border-bottom: 1px solid #d1fae5; }

        /* product cell */
        .item-num               { padding: 13px 8px; color: #6ee7b7; font-size: 11px; font-weight: 700; }
        .item-product           { padding: 13px 8px; }
        .item-product-name      { font-size: 12px; font-weight: 700; color: #065f46; }
        .item-pack-size         { font-size: 11px; color: #6ee7b7; font-weight: 500; margin-top: 3px; }
        .item-comment           { font-size: 10px; color: #9ca3af; margin-top: 3px; font-style: italic; line-height: 1.4; }

        /* qty cells */
        .item-center            { padding: 13px 8px; text-align: center; }
        .item-right             { padding: 13px 8px; text-align: right; }

        .ord-qty-val            { font-size: 13px; font-weight: 700; color: #9ca3af; text-decoration: line-through; }
        .ord-qty-val-same       { font-size: 13px; font-weight: 700; color: #374151; }
        .qty-unit               { font-size: 10px; color: #9ca3af; }

        .appr-pill-ok           { background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 6px; padding: 4px 8px; display: inline-block; }
        .appr-pill-reduced      { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px; padding: 4px 8px; display: inline-block; }
        .appr-qty-ok            { font-size: 14px; font-weight: 800; color: #065f46; }
        .appr-qty-reduced       { font-size: 14px; font-weight: 800; color: #c2410c; }
        .qty-reduced-badge      { margin-top: 4px; }
        .qty-reduced-badge span { font-size: 9px; color: #c2410c; font-weight: 700; background: #fff7ed; border: 1px solid #fed7aa; padding: 2px 6px; border-radius: 3px; display: inline-block; }

        .dealer-price           { font-size: 12px; font-weight: 700; color: #374151; }

        /* discount cell */
        .disc-label             { font-size: 10px; color: #9ca3af; padding-right: 3px; }
        .disc-val               { font-size: 10px; color: #c2410c; font-weight: 700; }
        .disc-total-label       { font-size: 10px; color: #9ca3af; font-weight: 700; padding-right: 3px; padding-top: 2px; border-top: 1px solid #fde8d8; }
        .disc-total-val         { font-size: 10px; color: #9a3412; font-weight: 800; padding-top: 2px; border-top: 1px solid #fde8d8; }
        .mini-charge            { margin-top: 4px; font-size: 10px; color: #7c3aed; font-weight: 600; }

        .net-pill               { background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 6px; padding: 5px 8px; display: inline-block; }
        .net-price-val          { font-size: 12px; font-weight: 800; color: #065f46; }

        .subtotal-val           { font-size: 13px; font-weight: 800; color: #065f46; }

        /* ── Totals Box ── */
        .totals-table           { width: 100%; border: 1px solid #6ee7b7; border-radius: 10px; overflow: hidden; }
        .totals-row-border      { border-bottom: 1px solid #d1fae5; }
        .totals-label           { padding: 11px 16px; font-size: 12px; color: #6b7280; background: #f0fdf4; }
        .totals-label-warn      { padding: 11px 16px; font-size: 12px; color: #92400e; background: #fffbeb; }
        .totals-value           { padding: 11px 16px; font-size: 13px; font-weight: 700; color: #065f46; text-align: right; }
        .totals-value-warn      { padding: 11px 16px; font-size: 13px; font-weight: 700; color: #92400e; text-align: right; }
        .totals-grand           { background: linear-gradient(135deg, #065f46 0%, #059669 100%); padding: 15px 16px; }
        .totals-grand-label     { font-size: 12px; color: rgba(255,255,255,0.75); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .totals-grand-value     { font-size: 20px; font-weight: 800; color: #fff; text-align: right; }

        /* ── Qty Reduced Note ── */
        .qty-note-box           { background: #fff7ed; border: 1px solid #fed7aa; border-left: 5px solid #f59e0b; border-radius: 10px; padding: 16px 20px; }
        .qty-note-title         { font-size: 13px; font-weight: 700; color: #92400e; margin-bottom: 5px; }
        .qty-note-body          { font-size: 12px; color: #78350f; line-height: 1.7; }

        /* ── What Happens Next ── */
        .next-box               { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; overflow: hidden; }
        .next-header            { background: #d1fae5; padding: 13px 20px; border-bottom: 1px solid #a7f3d0; text-align: center; }
        .next-header span       { font-size: 12px; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 0.8px; }
        .next-body              { padding: 20px 16px; }
        .next-card              { background: #fff; border: 1px solid #bbf7d0; border-radius: 10px; padding: 18px 10px; text-align: center; }
        .next-card-icon         { font-size: 28px; margin-bottom: 10px; }
        .next-card-title        { font-size: 12px; font-weight: 700; color: #065f46; margin-bottom: 4px; }
        .next-card-body         { font-size: 11px; color: #6ee7b7; line-height: 1.5; }

        /* ── Footer ── */
        .footer                 { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 100%); border-radius: 12px; padding: 28px 32px; text-align: center; }
        .footer-logo-wrap       { display: inline-block; background: #ffffff; border-radius: 10px; padding: 8px 20px; box-shadow: 0 3px 12px rgba(0,0,0,0.15); margin-bottom: 18px; }
        .footer-divider         { width: 40px; height: 1px; background: rgba(255,255,255,0.25); margin: 0 auto 16px; }
        .footer-text            { font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.8; margin-bottom: 12px; }
        .footer-note            { font-size: 11px; color: rgba(255,255,255,0.4); letter-spacing: 0.3px; }

    </style>
</head>
<body>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="bg-page">
<tr>
<td align="center" style="padding: 36px 16px;">

    <table cellpadding="0" cellspacing="0" border="0" class="wrapper">

        {{-- ══════════════════════════════════ --}}
        {{-- HEADER                            --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="header">

                {{-- Logo --}}
                <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto 28px;">
                    <tr>
                        <td align="center" style="background: #ffffff; border-radius: 14px; padding: 12px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.18);">
                            <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="180">
                        </td>
                    </tr>
                </table>

                <div class="header-divider"></div>

                {{-- Approved Circle --}}
                <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto 18px;">
                    <tr>
                        <td align="center" style="padding: 0;">
                            <div style="width: 68px; height: 68px; border-radius: 34px; background: #10b981; border: 3px solid rgba(255,255,255,0.6); box-shadow: 0 4px 20px rgba(16,185,129,0.4); display: table; margin: 0 auto;">
                                <div style="display: table-cell; vertical-align: middle; text-align: center; font-size: 32px; color: #ffffff;">
                                    ✓
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="header-title">Your Order Has Been Approved!</div>
                <div class="header-date">{{ \Carbon\Carbon::now()->format('d F Y, h:i A') }}</div>

            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- RIBBON                            --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="ribbon">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="ribbon-ref">✓ &nbsp;PO Reference: <strong>{{ $po->po_ref_no_string }}</strong></td>
                        <td align="right"><span class="ribbon-badge">APPROVED</span></td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- GREETING                          --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <div class="greeting-box">
                    <div class="greeting-name">Dear {{ $po->dealer->business_name ?? ($po->dealer->name ?? 'Valued Dealer') }},</div>
                    <div class="greeting-body">
                        Great news! Your Purchase Order has been <strong>reviewed and approved</strong> by our team.
                        Please find the complete order summary below including approved quantities and pricing details.
                        Our team will begin processing your order shortly.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- ORDER SUMMARY INFO                --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <table class="summary-table" cellpadding="0" cellspacing="0" border="0">
                    <tr class="summary-header">
                        <td colspan="4">◉ &nbsp;Order Summary</td>
                    </tr>
                    <tr class="summary-row-border">
                        <td class="summary-label">PO Number</td>
                        <td class="summary-value-bold">{{ $po->po_ref_no_string }}</td>
                        <td class="summary-label">Order Date</td>
                        <td class="summary-value">{{ \Carbon\Carbon::parse($po->created_at)->format('d M Y') }}</td>
                    </tr>
                    <tr class="summary-row-border">
                        <td class="summary-label">Approved On</td>
                        <td class="summary-value">{{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}</td>
                        <td class="summary-label">Mode</td>
                        <td class="summary-value">{{ $po->mode ?: '—' }}</td>
                    </tr>
                    @if(!empty($po->remarks))
                    <tr>
                        <td class="summary-label">Remarks</td>
                        <td colspan="3" style="padding: 11px 16px; font-size: 13px; color: #6b7280; font-style: italic;">{{ $po->remarks }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════════════════════ --}}
        {{-- APPROVED ITEMS TABLE                              --}}
        {{-- Columns: # | Product (pack) | Ord.Qty | Appr.Qty  --}}
        {{--           | Dealer Price | Disc | Net Price | Value --}}
        {{-- ══════════════════════════════════════════════════ --}}
        <tr>
            <td class="section">

                {{-- Label Row --}}
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
                    <tr>
                        <td>
                            <span class="section-label-bar"></span>
                            <span class="section-label">Approved Order Items</span>
                        </td>
                        <td align="right">
                            <span class="section-count">
                                {{ $po->orderitems->count() }} product(s)
                                @if($po->is_mini_pack_order == 1) &nbsp;·&nbsp; Mini Pack @endif
                            </span>
                        </td>
                    </tr>
                </table>

                <table class="items-table" cellpadding="0" cellspacing="0" border="0">

                    {{-- Header --}}
                    <tr class="items-header">
                        <td style="width: 22px; padding: 12px 8px;">#</td>
                        <td style="padding: 12px 8px;">Product</td>
                        <td class="center">Ord. Qty</td>
                        <td class="center">Appr. Qty</td>
                        <td class="center">Dealer Price</td>
                        <td class="center">Disc</td>
                        <td class="center">Net Price</td>
                        <td class="right" style="padding-right: 10px;">Value</td>
                    </tr>

                    @foreach($po->orderitems as $idx => $item)
                    @php
                        $bd         = (float) $item->dealer_basic_discount;
                        $sd         = (float) $item->dealer_special_discount;
                        $qd         = (float) $item->dealer_qty_discount;
                        $totalDisc  = $bd + $sd + $qd;
                        $subTotal   = $item->actual_qty * $item->net_price;
                        $isMini     = $po->is_mini_pack_order == 1;
                        $qtyReduced = $item->actual_qty < $item->qty;

                        if ($isMini) {
                            $packLabel = $item->mini_pack_size ?? null;
                        } else {
                            $packLabel = isset($item->packingsize->size) ? $item->packingsize->size . ' kg' : null;
                        }
                    @endphp

                    <tr class="{{ $loop->even ? 'item-row-even' : 'item-row-odd' }}">

                        {{-- # --}}
                        <td class="item-num">{{ $idx + 1 }}</td>

                        {{-- Product + pack size below --}}
                        <td class="item-product">
                            <div class="item-product-name">{{ $item->product->product_name ?? '—' }}</div>
                            @if($packLabel)
                                <div class="item-pack-size">{{ $packLabel }}</div>
                            @endif
                            @if(!empty($item->comments))
                                <div class="item-comment">💬 {{ $item->comments }}</div>
                            @endif
                        </td>

                        {{-- Ordered Qty — strikethrough if reduced --}}
                        <td class="item-center">
                            <span class="{{ $qtyReduced ? 'ord-qty-val' : 'ord-qty-val-same' }}">{{ $item->qty }}</span>
                            <span class="qty-unit"> kg</span>
                        </td>

                        {{-- Approved Qty --}}
                        <td class="item-center">
                            <div class="{{ $qtyReduced ? 'appr-pill-reduced' : 'appr-pill-ok' }}">
                                <span class="{{ $qtyReduced ? 'appr-qty-reduced' : 'appr-qty-ok' }}">{{ $item->actual_qty }}</span>
                                <span class="qty-unit"> kg</span>
                            </div>
                            @if($qtyReduced)
                                <div class="qty-reduced-badge"><span>Qty Reduced</span></div>
                            @endif
                        </td>

                        {{-- Dealer Price --}}
                        <td class="item-center">
                            <span class="dealer-price">Rs. {{ number_format($item->product_price, 2) }}</span>
                        </td>

                        {{-- Discounts — only show non-zero --}}
                        <td class="item-center">
                            @if($bd > 0 || $sd > 0 || $qd > 0)
                                <table cellpadding="0" cellspacing="0" border="0" align="center">
                                    @if($bd > 0)
                                    <tr>
                                        <td class="disc-label">BD</td>
                                        <td class="disc-val">{{ $bd }}%</td>
                                    </tr>
                                    @endif
                                    @if($sd > 0)
                                    <tr>
                                        <td class="disc-label">SD</td>
                                        <td class="disc-val">{{ $sd }}%</td>
                                    </tr>
                                    @endif
                                    @if($qd > 0)
                                    <tr>
                                        <td class="disc-label">QD</td>
                                        <td class="disc-val">{{ $qd }}%</td>
                                    </tr>
                                    @endif
                                    @if(($bd > 0 ? 1 : 0) + ($sd > 0 ? 1 : 0) + ($qd > 0 ? 1 : 0) > 1)
                                    <tr>
                                        <td class="disc-total-label">Total</td>
                                        <td class="disc-total-val">{{ $totalDisc }}%</td>
                                    </tr>
                                    @endif
                                </table>
                                @if($isMini && !empty($item->additional_charges) && $item->additional_charges > 0)
                                    <div class="mini-charge">+Rs.{{ $item->additional_charges }} charges</div>
                                @endif
                            @else
                                <span style="font-size: 11px; color: #9ca3af;">—</span>
                            @endif
                        </td>

                        {{-- Net Price --}}
                        <td class="item-center">
                            <div class="net-pill">
                                <span class="net-price-val">Rs. {{ number_format($item->net_price, 2) }}</span>
                            </div>
                        </td>

                        {{-- Value --}}
                        <td class="item-right">
                            <span class="subtotal-val">Rs. {{ number_format($subTotal, 2) }}</span>
                        </td>

                    </tr>
                    @endforeach

                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- ORDER TOTALS                      --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section-sm">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="50%">&nbsp;</td>
                        <td width="50%">
                            <table class="totals-table" cellpadding="0" cellspacing="0" border="0">
                                <tr class="totals-row-border">
                                    <td class="totals-label">Subtotal</td>
                                    <td class="totals-value">Rs. {{ number_format($po->price, 2) }}</td>
                                </tr>
                                <tr class="totals-row-border">
                                    <td class="totals-label-warn">GST ({{ $po->gst_per }}%)</td>
                                    <td class="totals-value-warn">+ Rs. {{ number_format($po->gst, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="totals-grand">
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td class="totals-grand-label">Grand Total</td>
                                                <td class="totals-grand-value">Rs. {{ number_format($po->grand_total, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- QTY REDUCED NOTE                  --}}
        {{-- ══════════════════════════════════ --}}
        @if($po->orderitems->contains(function($i) { return $i->actual_qty < $i->qty; }))
        <tr>
            <td class="section-sm">
                <div class="qty-note-box">
                    <div class="qty-note-title">⚠ Please Note — Quantity Adjustment</div>
                    <div class="qty-note-body">
                        One or more items in your order have been adjusted to a lower approved quantity
                        than originally requested. Items marked <strong>"Qty Reduced"</strong> reflect these changes.
                        Please review and contact your account manager if you have any questions.
                    </div>
                </div>
            </td>
        </tr>
        @endif

        {{-- ══════════════════════════════════ --}}
        {{-- WHAT HAPPENS NEXT                 --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <div class="next-box">
                    <div class="next-header"><span>📋 &nbsp;What Happens Next?</span></div>
                    <div class="next-body">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td valign="top" style="width: 33%; padding-right: 8px;">
                                    <div class="next-card">
                                        <div class="next-card-icon">✅</div>
                                        <div class="next-card-title">Approved</div>
                                        <div class="next-card-body">Order confirmed by our team</div>
                                    </div>
                                </td>
                                <td valign="top" style="width: 33%; padding-right: 8px;">
                                    <div class="next-card">
                                        <div class="next-card-icon">📦</div>
                                        <div class="next-card-title">Processing</div>
                                        <div class="next-card-body">Order being prepared for dispatch</div>
                                    </div>
                                </td>
                                <td valign="top" style="width: 33%;">
                                    <div class="next-card">
                                        <div class="next-card-icon">🚚</div>
                                        <div class="next-card-title">Dispatched</div>
                                        <div class="next-card-body">You'll be notified when shipped</div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- FOOTER                            --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section-last" style="padding-bottom: 32px;">
                <div class="footer">
                    <div class="footer-logo-wrap">
                        <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="130">
                    </div>
                    <div class="footer-divider"></div>
                    <div class="footer-text">
                        For any queries regarding your approved order, please contact us.
                    </div>
                    <div class="footer-note">This is an automated email from Greenwave System. Please do not reply.</div>
                </div>
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>