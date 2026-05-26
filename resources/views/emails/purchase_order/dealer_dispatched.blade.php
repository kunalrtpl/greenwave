<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dispatch Info</title>
    <style>

        /* ── Reset ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        img { border: 0; display: block; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }

        /* ── Page ── */
        body, .bg-page  { background-color: #e8f5e9; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        .wrapper        { max-width: 660px; width: 100%; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,100,0,0.14); }

        /* ── Header ── */
        .header         { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%); padding: 40px 32px 32px; text-align: center; }
        .header-divider { width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 24px; border-radius: 2px; }
        .header-title   { font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 8px; }
        .header-date    { font-size: 13px; color: rgba(255,255,255,0.6); }

        /* ── Ribbon ── */
        .ribbon         { background: #43a047; padding: 12px 32px; }
        .ribbon-ref     { font-size: 13px; color: #fff; font-weight: 600; }
        .ribbon-badge   { display: inline-block; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.45); border-radius: 20px; padding: 3px 14px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Body Sections ── */
        .section        { padding: 24px 32px 0; }

        /* ── Greeting ── */
        .greeting-box   { background: #f1f8e9; border: 1px solid #c5e1a5; border-left: 5px solid #558b2f; border-radius: 10px; padding: 20px 22px; }
        .greeting-name  { font-size: 15px; color: #33691e; font-weight: 700; margin-bottom: 8px; }
        .greeting-body  { font-size: 13px; color: #558b2f; line-height: 1.8; }

        /* ── Transport Info Box ── */
        .transport-table        { width: 100%; border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden; }
        .transport-header       { background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%); }
        .transport-header td    { padding: 12px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.8px; }
        .transport-label        { padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600; text-transform: uppercase; width: 35%; background: #f9fbe7; }
        .transport-value        { padding: 11px 16px; font-size: 13px; color: #1b5e20; font-weight: 700; }
        .transport-row-border   { border-bottom: 1px solid #f1f8e9; }

        /* ── Section Label ── */
        .section-label-bar  { display: inline-block; width: 4px; height: 16px; background: #43a047; border-radius: 2px; vertical-align: middle; margin-right: 10px; }
        .section-label      { font-size: 13px; font-weight: 700; color: #2e7d32; text-transform: uppercase; letter-spacing: 0.8px; vertical-align: middle; }
        .section-count      { font-size: 11px; color: #81c784; font-weight: 600; }

        /* ── Items Table ── */
        .items-table            { width: 100%; border: 1px solid #c8e6c9; border-radius: 12px; overflow: hidden; font-size: 13px; }
        .items-header           { background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%); }
        .items-header td        { padding: 13px 14px; color: #fff; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .items-header .center   { text-align: center; }
        .item-row-even          { background: #f9fbe7; border-bottom: 1px solid #e8f5e9; }
        .item-row-odd           { background: #ffffff; border-bottom: 1px solid #e8f5e9; }
        .item-num               { padding: 14px 14px; color: #a5d6a7; font-size: 12px; font-weight: 700; }
        .item-product           { padding: 14px 14px; }
        .item-product-name      { font-size: 13px; font-weight: 700; color: #1b5e20; }
        .item-pack-size         { font-size: 11px; color: #81c784; font-weight: 500; margin-top: 3px; }
        .item-qty               { padding: 14px 14px; text-align: center; }
        .item-qty-val           { font-size: 18px; font-weight: 800; color: #2e7d32; }
        .item-qty-unit          { font-size: 11px; color: #a5d6a7; margin-left: 2px; }
        .items-footer           { background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border-top: 2px solid #c8e6c9; }
        .items-footer-left      { padding: 13px 14px; font-size: 12px; color: #558b2f; font-weight: 700; text-transform: uppercase; }
        .items-footer-right     { padding: 13px 14px; text-align: center; font-size: 14px; color: #1b5e20; font-weight: 800; }

        /* ── Footer ── */
        .footer             { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 100%); border-radius: 12px; padding: 28px 32px; text-align: center; }
        .footer-logo-wrap   { display: inline-block; background: #ffffff; border-radius: 10px; padding: 8px 20px; box-shadow: 0 3px 12px rgba(0,0,0,0.15); margin-bottom: 18px; }
        .footer-divider     { width: 40px; height: 1px; background: rgba(255,255,255,0.25); margin: 0 auto 16px; }
        .footer-text        { font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.8; margin-bottom: 12px; }
        .footer-note        { font-size: 11px; color: rgba(255,255,255,0.4); letter-spacing: 0.3px; }

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

                {{-- Plain truck emoji centered — no circle, no box --}}
                <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto 18px;">
                    <tr>
                        <td align="center" style="font-size: 52px; line-height: 1; padding: 0;">
                            🚚
                        </td>
                    </tr>
                </table>

                <div class="header-title">Your Material Has Been Dispatched!</div>
                <div class="header-date">{{ \Carbon\Carbon::parse($dispatch_date)->format('d F Y') }}</div>

            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- RIBBON                            --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="ribbon">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <!-- <td class="ribbon-ref">🚚 &nbsp;PO Reference: <strong>{{ $po->po_ref_no_string }}</strong></td> -->
                        <td align="right"><span class="ribbon-badge">DISPATCHED</span></td>
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
                    <div class="greeting-name">Dear {{ $dealer->business_name ?? ($dealer->name ?? 'Valued Dealer') }},</div>
                    <div class="greeting-body">
                        Great news! Your material has been <strong>dispatched</strong> and is now on its way to you.
                        Please find the shipment and product details below.
                        Kindly arrange to receive the delivery at your end.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- TRANSPORT DETAILS                 --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <table class="transport-table" cellpadding="0" cellspacing="0" border="0">
                    <tr class="transport-header">
                        <td colspan="2">🚛 &nbsp;Shipment Details</td>
                    </tr>
                    <tr class="transport-row-border">
                        <td class="transport-label">Transporter</td>
                        <td class="transport-value">{{ $transport_name }}</td>
                    </tr>
                    <tr class="transport-row-border">
                        <td class="transport-label">LR Number</td>
                        <td class="transport-value">{{ $lr_no }}</td>
                    </tr>
                    <tr>
                        <td class="transport-label">Dispatch Date</td>
                        <td class="transport-value">{{ \Carbon\Carbon::parse($dispatch_date)->format('d M Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- DISPATCHED ITEMS                  --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">

                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
                    <tr>
                        <td>
                            <span class="section-label-bar"></span>
                            <span class="section-label">Dispatched Items</span>
                        </td>
                        <td align="right">
                            <span class="section-count">{{ count($dispatched_items) }} product(s)</span>
                        </td>
                    </tr>
                </table>

                <table class="items-table" cellpadding="0" cellspacing="0" border="0">

                    <tr class="items-header">
                        <td style="width: 30px;">#</td>
                        <td>Product</td>
                        <td class="center">Dispatched Qty</td>
                    </tr>

                    @foreach($dispatched_items as $idx => $item)
                    <tr class="{{ $idx % 2 == 0 ? 'item-row-odd' : 'item-row-even' }}">

                        <td class="item-num">{{ $idx + 1 }}</td>

                        <td class="item-product">
                            <div class="item-product-name">{{ $item['product_name'] }}</div>
                            @if(!empty($item['pack_size']))
                                <div class="item-pack-size">{{ $item['pack_size'] }}</div>
                            @endif
                        </td>

                        <td class="item-qty">
                            <span class="item-qty-val">{{ $item['qty'] }}</span>
                            <span class="item-qty-unit">kg</span>
                        </td>

                    </tr>
                    @endforeach

                    <tr class="items-footer">
                        <td colspan="2" class="items-footer-left">Total Products: {{ count($dispatched_items) }}</td>
                        <td class="items-footer-right">Total: {{ array_sum(array_column($dispatched_items, 'qty')) }} kg</td>
                    </tr>

                </table>
            </td>
        </tr>

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
                        For any queries regarding your shipment, please contact Greenwave Office Team.
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