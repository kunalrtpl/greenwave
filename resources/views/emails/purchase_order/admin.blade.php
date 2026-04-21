<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>New Purchase Order - Admin</title>
    <style>

        /* ── Reset ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        img { border: 0; display: block; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }

        /* ── Page ── */
        body, .bg-page    { background-color: #e8f5e9; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        .wrapper          { max-width: 680px; width: 100%; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,100,0,0.14); }

        /* ── Header ── */
        .header           { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%); padding: 40px 32px 32px; text-align: center; }
        .logo-pill        { display: inline-block; background: #ffffff; border-radius: 14px; padding: 12px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.18); margin-bottom: 28px; }
        .header-divider   { width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 22px; border-radius: 2px; }
        .bell-icon        { width: 64px; height: 64px; background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.5); border-radius: 50%; margin: 0 auto 18px; text-align: center; line-height: 60px; font-size: 30px; }
        .header-title     { font-size: 23px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 10px; }
        .header-date      { font-size: 13px; color: rgba(255,255,255,0.6); }

        /* ── PO Ribbon ── */
        .ribbon           { background: #43a047; padding: 12px 32px; }
        .ribbon-ref       { font-size: 13px; color: #fff; font-weight: 600; }
        .ribbon-badge     { display: inline-block; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); border-radius: 20px; padding: 3px 14px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Body Sections ── */
        .section          { padding: 24px 32px 0; }

        /* ── Greeting Box ── */
        .greeting-box     { background: #f1f8e9; border: 1px solid #c5e1a5; border-left: 5px solid #558b2f; border-radius: 10px; padding: 20px 22px; }
        .greeting-name    { font-size: 15px; color: #33691e; font-weight: 700; margin-bottom: 8px; }
        .greeting-body    { font-size: 13px; color: #558b2f; line-height: 1.8; }

        /* ── Order Info Table ── */
        .info-table               { width: 100%; border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden; }
        .info-table-header        { background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%); }
        .info-table-header td     { padding: 14px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.8px; text-align: center; }
        .info-row-label           { padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600; text-transform: uppercase; width: 32%; background: #f9fbe7; }
        .info-row-value           { padding: 11px 16px; font-size: 13px; color: #1b5e20; font-weight: 800; }
        .info-row-value-normal    { padding: 11px 16px; font-size: 13px; color: #33691e; }
        .info-row-value-muted     { padding: 11px 16px; font-size: 13px; color: #6b7280; font-style: italic; }
        .info-row-border          { border-bottom: 1px solid #f1f8e9; }
        .action-badge             { display: inline-block; color: #fff; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .dealer-mobile            { color: #9ca3af; font-size: 12px; font-weight: 400; margin-left: 8px; }

        /* ── Items Table ── */
        .section-label            { font-size: 13px; font-weight: 700; color: #2e7d32; text-transform: uppercase; letter-spacing: 0.8px; vertical-align: middle; }
        .section-label-bar        { display: inline-block; width: 4px; height: 16px; background: #43a047; border-radius: 2px; vertical-align: middle; margin-right: 10px; }
        .section-count            { font-size: 11px; color: #81c784; font-weight: 600; }
        .items-table              { width: 100%; border: 1px solid #c8e6c9; border-radius: 12px; overflow: hidden; font-size: 12px; }
        .items-header             { background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); }
        .items-header td          { padding: 12px 12px; color: #fff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .items-header .center     { text-align: center; }
        .item-row-even            { background: #f9fbe7; border-bottom: 1px solid #e8f5e9; }
        .item-row-odd             { background: #ffffff; border-bottom: 1px solid #e8f5e9; }
        .item-num                 { padding: 13px 12px; color: #a5d6a7; font-size: 11px; font-weight: 700; }
        .item-product             { padding: 13px 12px; }
        .item-product-name        { font-size: 12px; font-weight: 700; color: #1b5e20; }
        .item-pack-size           { font-size: 11px; color: #81c784; font-weight: 500; margin-top: 3px; }
        .item-moq                 { padding: 13px 8px; text-align: center; }
        .item-moq-val             { font-size: 13px; font-weight: 700; color: #558b2f; }
        .item-moq-unit            { font-size: 10px; color: #a5d6a7; }
        .item-moq-na              { font-size: 11px; color: #9ca3af; }
        .item-qty                 { padding: 13px 8px; text-align: center; }
        .item-qty-val-ok          { font-size: 15px; font-weight: 800; color: #2e7d32; }
        .item-qty-val-bad         { font-size: 15px; font-weight: 800; color: #c62828; }
        .item-qty-unit            { font-size: 10px; color: #a5d6a7; }
        .moq-warning              { margin-top: 4px; }
        .moq-warning span         { font-size: 9px; color: #c62828; font-weight: 700; background: #ffebee; padding: 2px 6px; border-radius: 3px; display: inline-block; border: 1px solid #ffcdd2; }
        .items-footer             { background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border-top: 2px solid #c8e6c9; }
        .items-footer-left        { padding: 13px 12px; font-size: 11px; color: #558b2f; font-weight: 700; text-transform: uppercase; }
        .items-footer-right       { padding: 13px 12px; text-align: center; font-size: 14px; color: #1b5e20; font-weight: 800; }

        /* ── Action Box ── */
        .action-box               { background: #fff9e6; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; border-radius: 10px; padding: 20px 22px; }
        .action-box-title         { font-size: 14px; font-weight: 700; color: #92400e; margin-bottom: 6px; }
        .action-box-body          { font-size: 13px; color: #78350f; line-height: 1.7; margin-bottom: 16px; }
        .action-btn               { display: inline-block; background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%); border-radius: 8px; box-shadow: 0 4px 14px rgba(27,94,32,0.35); }
        .action-btn a             { display: inline-block; padding: 13px 32px; font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; letter-spacing: 0.3px; }

        /* ── Footer ── */
        .footer                   { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 100%); border-radius: 12px; padding: 28px 32px; text-align: center; }
        .footer-logo-wrap         { display: inline-block; background: #ffffff; border-radius: 10px; padding: 8px 20px; box-shadow: 0 3px 12px rgba(0,0,0,0.15); margin-bottom: 18px; }
        .footer-divider           { width: 40px; height: 1px; background: rgba(255,255,255,0.25); margin: 0 auto 16px; }
        .footer-text              { font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.8; margin-bottom: 12px; }
        .footer-note              { font-size: 11px; color: rgba(255,255,255,0.4); letter-spacing: 0.3px; }

    </style>
</head>
<body>

@php
    $sourceText = 'New Purchase Order';
    if ($po->action == 'dealer')              $sourceText = 'Dealer Placed a Purchase Order';
    elseif ($po->action == 'dealer_customer') $sourceText = 'Dealer Placed a Customer Order';
    elseif ($po->action == 'customer')        $sourceText = 'Customer Placed a Purchase Order';

    $poUrl = null;
    if ($po->action == 'dealer') {
        $poUrl = 'https://g2app.in/admin/dealer-purchase-order-detail/' . $po->id;
    } elseif ($po->action == 'customer') {
        $poUrl = 'https://g2app.in/admin/direct-customer-purchase-order-detail/' . $po->id;
    }

    $actionColor = '#2e7d32';
    if ($po->action == 'dealer_customer') $actionColor = '#1565c0';
    elseif ($po->action == 'customer')    $actionColor = '#6a1b9a';
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="bg-page">
<tr>
<td align="center" style="padding: 36px 16px;">

    <table cellpadding="0" cellspacing="0" border="0" class="wrapper">

        {{-- ══════════════════════════════════ --}}
        {{-- HEADER                            --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="header">
                <div class="logo-pill">
                    <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="180">
                </div>
                <div class="header-divider"></div>
                <!-- <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto 18px;">
                    <tr>
                        <td align="center">
                            <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.5); border-radius: 50%; text-align: center; line-height: 64px; font-size: 30px; margin: 0 auto;">
                                🔔
                            </div>
                        </td>
                    </tr>
                </table> -->
                <div class="header-title">{{ $sourceText }}</div>
                <div class="header-date">{{ \Carbon\Carbon::parse($po->created_at)->format('d F Y, h:i A') }}</div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- PO RIBBON                         --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="ribbon">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="ribbon-ref">● &nbsp;PO Reference: <strong>{{ $po->po_ref_no_string }}</strong></td>
                        <td align="right">
                            <span class="ribbon-badge">{{ strtoupper(str_replace('_', ' ', $po->action)) }}</span>
                        </td>
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
                    <div class="greeting-name">Dear Admin,</div>
                    <div class="greeting-body">
                        A new Purchase Order has been received and requires your attention.
                        Please review the details below and take the necessary action from your admin dashboard.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- ORDER INFO                        --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <table class="info-table" cellpadding="0" cellspacing="0" border="0">

                    {{-- Full-width green header --}}
                    <tr class="info-table-header">
                        <td colspan="2"><div class="info-table-header td">◉ &nbsp;Order Information</div></td>
                    </tr>

                    {{-- PO Number --}}
                    <tr class="info-row-border">
                        <td class="info-row-label">PO Number</td>
                        <td class="info-row-value">{{ $po->po_ref_no_string }}</td>
                    </tr>

                    {{-- Placed On --}}
                    <tr class="info-row-border">
                        <td class="info-row-label">Placed On</td>
                        <td class="info-row-value-normal">{{ \Carbon\Carbon::parse($po->created_at)->format('d M Y, h:i A') }}</td>
                    </tr>

                    {{-- Order Type --}}
                    <tr class="info-row-border">
                        <td class="info-row-label">Order Type</td>
                        <td style="padding: 11px 16px;">
                            <span class="action-badge" style="background: {{ $actionColor }};">
                                {{ strtoupper(str_replace('_', ' ', $po->action)) }}
                            </span>
                        </td>
                    </tr>

                    {{-- Dealer --}}
                    <tr class="info-row-border">
                        <td class="info-row-label">Dealer</td>
                        <td class="info-row-value">
                            {{ $po->dealer->business_name ?? ($po->dealer->name ?? 'N/A') }}
                            @if(!empty($po->dealer->mobile))
                                <span class="dealer-mobile">{{ $po->dealer->mobile }}</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Customer (only if exists) --}}
                    @if(isset($po->customer->name))
                    <tr class="info-row-border">
                        <td class="info-row-label">Customer</td>
                        <td class="info-row-value">{{ $po->customer->name }}</td>
                    </tr>
                    @endif

                    {{-- Remarks --}}
                    @if(!empty($po->remarks))
                    <tr>
                        <td class="info-row-label">Remarks</td>
                        <td class="info-row-value-muted">{{ $po->remarks }}</td>
                    </tr>
                    @endif

                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- ORDER ITEMS                       --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">

                {{-- Section Label --}}
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
                    <tr>
                        <td>
                            <span class="section-label-bar"></span>
                            <span class="section-label">Order Items</span>
                        </td>
                        <td align="right">
                            <span class="section-count">
                                {{ $po->orderitems->count() }} product(s)
                                @if($po->is_mini_pack_order == 1) &nbsp;·&nbsp; Mini Pack @endif
                            </span>
                        </td>
                    </tr>
                </table>

                {{-- Items Table --}}
                <table class="items-table" cellpadding="0" cellspacing="0" border="0">

                    {{-- Header — 4 columns --}}
                    <tr class="items-header">
                        <td style="width: 28px;">#</td>
                        <td>Product</td>
                        <td class="center">MOQ</td>
                        <td class="center">Ordered Qty</td>
                    </tr>

                    @foreach($po->orderitems as $idx => $item)
                    @php
                        $moq      = !empty($item->product->moq) ? $item->product->moq : 0;
                        $isMini   = $po->is_mini_pack_order == 1;
                        $belowMoq = !$isMini && $moq > 0 && $item->qty < $moq;

                        if ($isMini) {
                            $packLabel = $item->mini_pack_size ?? null;
                        } else {
                            $packLabel = isset($item->packingsize->size) ? $item->packingsize->size . ' kg' : null;
                        }
                    @endphp

                    <tr class="{{ $loop->even ? 'item-row-even' : 'item-row-odd' }}">

                        {{-- # --}}
                        <td class="item-num">{{ $idx + 1 }}</td>

                        {{-- Product + Pack Size below --}}
                        <td class="item-product">
                            <div class="item-product-name">{{ $item->product->product_name ?? '—' }}</div>
                            @if($packLabel)
                                <div class="item-pack-size">{{ $packLabel }}</div>
                            @endif
                        </td>

                        {{-- MOQ --}}
                        <td class="item-moq">
                            @if($isMini)
                                <span class="item-moq-na">N/A</span>
                            @elseif($moq > 0)
                                <span class="item-moq-val">{{ $moq }}</span>
                                <span class="item-moq-unit"> kg</span>
                            @else
                                <span class="item-moq-na">—</span>
                            @endif
                        </td>

                        {{-- Ordered Qty --}}
                        <td class="item-qty">
                            <span class="{{ $belowMoq ? 'item-qty-val-bad' : 'item-qty-val-ok' }}">{{ $item->qty }}</span>
                            <span class="item-qty-unit"> kg</span>
                            @if($belowMoq)
                                <div class="moq-warning">
                                    <span>⚠ Below MOQ</span>
                                </div>
                            @endif
                        </td>

                    </tr>
                    @endforeach

                    {{-- Footer --}}
                    <tr class="items-footer">
                        <td colspan="2" class="items-footer-left">
                            Total Products: {{ $po->orderitems->count() }}
                        </td>
                        <td colspan="2" class="items-footer-right">
                            Total Qty: {{ $po->orderitems->sum('qty') }} kg
                        </td>
                    </tr>

                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- ACTION BUTTON (dealer / customer) --}}
        {{-- ══════════════════════════════════ --}}
        @if($poUrl)
        <tr>
            <td class="section">
                <div class="action-box">
                    <div class="action-box-title">⚡ Action Required</div>
                    <div class="action-box-body">
                        This order is awaiting your review and approval.
                        Click the button below to open the PO detail page and take action.
                    </div>
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td class="action-btn">
                                <a href="{{ $poUrl }}">🔍 &nbsp;Review &amp; Approve PO</a>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        @endif

        {{-- ══════════════════════════════════ --}}
        {{-- FOOTER                            --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section" style="padding-bottom: 32px;">
                <div class="footer">
                    <div class="footer-logo-wrap">
                        <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="130">
                    </div>
                    <div class="footer-divider"></div>
                    <div class="footer-text">
                        Greenwave System — Admin Notification<br>
                        Please log in to the admin dashboard to manage orders.
                    </div>
                    <div class="footer-note">This is an automated email. Please do not reply.</div>
                </div>
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>