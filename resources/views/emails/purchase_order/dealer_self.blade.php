<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Purchase Order Created</title>
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
        .logo-pill      { display: inline-block; background: #ffffff; border-radius: 14px; padding: 12px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.18); margin-bottom: 28px; }
        .header-divider { width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 24px; border-radius: 2px; }
        .header-title   { font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 8px; }
        .header-date    { font-size: 13px; color: rgba(255,255,255,0.6); }

        /* ── Ribbon ── */
        .ribbon         { background: #43a047; padding: 12px 32px; }
        .ribbon-ref     { font-size: 13px; color: #fff; font-weight: 600; }
        .ribbon-status  { font-size: 12px; color: rgba(255,255,255,0.85); }

        /* ── Body Sections ── */
        .section        { padding: 24px 32px 0; }

        /* ── Greeting ── */
        .greeting-box   { background: #f1f8e9; border: 1px solid #c5e1a5; border-left: 5px solid #558b2f; border-radius: 10px; padding: 20px 22px; }
        .greeting-name  { font-size: 15px; color: #33691e; font-weight: 700; margin-bottom: 8px; }
        .greeting-body  { font-size: 13px; color: #558b2f; line-height: 1.8; }

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
        .item-moq               { padding: 14px 14px; text-align: center; }
        .item-moq-val           { font-size: 14px; font-weight: 700; color: #558b2f; }
        .item-moq-unit          { font-size: 11px; color: #a5d6a7; margin-left: 2px; }
        .item-moq-na            { font-size: 12px; color: #9ca3af; }
        .item-qty               { padding: 14px 14px; text-align: center; }
        .item-qty-val-ok        { font-size: 18px; font-weight: 800; color: #2e7d32; }
        .item-qty-val-bad       { font-size: 18px; font-weight: 800; color: #c62828; }
        .item-qty-unit          { font-size: 11px; color: #a5d6a7; margin-left: 2px; }
        .moq-warning            { margin-top: 6px; }
        .moq-warning span       { font-size: 10px; color: #c62828; font-weight: 700; background: #ffebee; padding: 3px 8px; border-radius: 4px; display: inline-block; border: 1px solid #ffcdd2; }
        .items-footer           { background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border-top: 2px solid #c8e6c9; }
        .items-footer-left      { padding: 13px 14px; font-size: 12px; color: #558b2f; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
        .items-footer-right     { padding: 13px 14px; text-align: center; font-size: 14px; color: #1b5e20; font-weight: 800; }

        /* ── What Happens Next ── */
        .next-box           { background: #f9fbe7; border: 1px solid #c8e6c9; border-radius: 12px; overflow: hidden; }
        .next-header        { background: #dcedc8; padding: 13px 20px; border-bottom: 1px solid #c5e1a5; text-align: center; }
        .next-header span   { font-size: 12px; font-weight: 700; color: #33691e; text-transform: uppercase; letter-spacing: 0.8px; }
        .next-body          { padding: 20px 16px; }
        .next-card          { background: #fff; border: 1px solid #c8e6c9; border-radius: 10px; padding: 18px 10px; text-align: center; }
        .next-card-icon     { font-size: 28px; margin-bottom: 10px; }
        .next-card-title    { font-size: 12px; font-weight: 700; color: #2e7d32; margin-bottom: 4px; }
        .next-card-body     { font-size: 11px; color: #a5d6a7; line-height: 1.5; }

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

                {{-- Divider --}}
                <div class="header-divider"></div>

                {{-- Check Circle — using display:table for reliable centering --}}
                <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto 18px;">
                    <tr>
                        <td align="center" style="padding: 0;">
                            <div style="width: 68px; height: 68px; border-radius: 34px; background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.55); display: table; margin: 0 auto;">
                                <div style="display: table-cell; vertical-align: middle; text-align: center; font-size: 32px; color: #ffffff;">
                                    ✓
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="header-title">Order Created Successfully</div>
                <div class="header-date">{{ \Carbon\Carbon::parse($po->created_at)->format('d F Y, h:i A') }}</div>

            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- RIBBON                            --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="ribbon">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="ribbon-ref">● &nbsp;PO Reference: <strong>{{ $po->po_ref_no_string }}</strong></td>
                        <td align="right" class="ribbon-status">Status: <strong>{{ ucwords($po->po_status ?? 'Pending') }}</strong></td>
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
                    <div class="greeting-name">Dear {{ $po->dealer->name ?? ($po->dealer->business_name ?? 'Valued Dealer') }},</div>
                    <div class="greeting-body">
                        Your Purchase Order has been successfully received and is now under review by our team.
                        Here is a summary of the items you ordered. We will notify you as soon as it is processed.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- ORDER ITEMS                       --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">

                {{-- Label Row --}}
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

                {{-- Items Table — 4 columns, pack size below product name --}}
                <table class="items-table" cellpadding="0" cellspacing="0" border="0">

                    <tr class="items-header">
                        <td style="width: 30px;">#</td>
                        <td>Product</td>
                        <td class="center">MOQ</td>
                        <td class="center">Ordered Qty</td>
                    </tr>

                    @foreach($po->orderitems as $idx => $item)
                    @php
                        $moq        = !empty($item->product->moq) ? $item->product->moq : 0;
                        $isMiniPack = $po->is_mini_pack_order == 1;
                        $belowMoq   = !$isMiniPack && $moq > 0 && $item->qty < $moq;

                        if ($isMiniPack) {
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
                            @if($isMiniPack)
                                <span class="item-moq-na">N/A</span>
                            @else
                                <span class="item-moq-val">{{ $moq > 0 ? $moq : '—' }}</span>
                                @if($moq > 0)
                                    <span class="item-moq-unit">kg</span>
                                @endif
                            @endif
                        </td>

                        {{-- Ordered Qty --}}
                        <td class="item-qty">
                            <span class="{{ $belowMoq ? 'item-qty-val-bad' : 'item-qty-val-ok' }}">{{ $item->qty }}</span>
                            <span class="item-qty-unit">kg</span>
                            @if($belowMoq)
                                <div class="moq-warning"><span>⚠ Below MOQ</span></div>
                            @endif
                        </td>

                    </tr>
                    @endforeach

                    {{-- Footer --}}
                    <tr class="items-footer">
                        <td colspan="2" class="items-footer-left">Total Products: {{ $po->orderitems->count() }}</td>
                        <td colspan="2" class="items-footer-right">Total Qty: {{ $po->orderitems->sum('qty') }} kg</td>
                    </tr>

                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- WHAT HAPPENS NEXT                 --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <div class="next-box">
                    <div class="next-header">
                        <span>📋 &nbsp;What Happens Next?</span>
                    </div>
                    <div class="next-body">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td valign="top" style="width: 33%; padding-right: 8px;">
                                    <div class="next-card">
                                        <div class="next-card-icon">📥</div>
                                        <div class="next-card-title">Received</div>
                                        <div class="next-card-body">PO logged in our system</div>
                                    </div>
                                </td>
                                <td valign="top" style="width: 33%; padding-right: 8px;">
                                    <div class="next-card">
                                        <div class="next-card-icon">🔍</div>
                                        <div class="next-card-title">Under Review</div>
                                        <div class="next-card-body">Team verifies quantities</div>
                                    </div>
                                </td>
                                <td valign="top" style="width: 33%;">
                                    <div class="next-card">
                                        <div class="next-card-icon">🚚</div>
                                        <div class="next-card-title">Dispatched</div>
                                        <div class="next-card-body">You'll be notified on dispatch</div>
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
            <td class="section" style="padding-bottom: 32px;">
                <div class="footer">
                    <div class="footer-logo-wrap">
                        <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="130">
                    </div>
                    <div class="footer-divider"></div>
                    <div class="footer-text">
                        For any queries, please contact your account manager<br>
                        or your nearest Greenwave office.
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