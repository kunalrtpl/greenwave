<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>New Purchase Order - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #e8f5e9;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        img { border: 0; display: block; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    </style>
</head>
<body style="background-color: #e8f5e9; margin: 0; padding: 0;">

@php
    // Dynamic header text
    $sourceText = 'New Purchase Order';
    if ($po->action == 'dealer')             $sourceText = 'Dealer Placed a Purchase Order';
    elseif ($po->action == 'dealer_customer') $sourceText = 'Dealer Placed a Customer Order';
    elseif ($po->action == 'customer')        $sourceText = 'Customer Placed a Purchase Order';

    // Dynamic PO detail URL
    $poUrl = null;
    if ($po->action == 'dealer') {
        $poUrl = 'https://g2app.in/admin/dealer-purchase-order-detail/' . $po->id;
    } elseif ($po->action == 'customer') {
        $poUrl = 'https://g2app.in/admin/direct-customer-purchase-order-detail/' . $po->id;
    }

    // Action badge color
    $actionColor = '#2e7d32';
    if ($po->action == 'dealer_customer') $actionColor = '#1565c0';
    elseif ($po->action == 'customer')    $actionColor = '#6a1b9a';
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #e8f5e9;">
    <tr>
        <td align="center" style="padding: 36px 16px;">

            <table width="680" cellpadding="0" cellspacing="0" border="0"
                   style="max-width: 680px; width: 100%; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,100,0,0.14);">

                <!-- ══════════════════════════════════════ -->
                <!-- HEADER                                -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td align="center"
                        style="background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%);
                               padding: 40px 32px 32px;">

                        <!-- Logo in white pill -->
                        <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto 28px;">
                            <tr>
                                <td align="center"
                                    style="background: #ffffff; border-radius: 14px;
                                           padding: 12px 28px;
                                           box-shadow: 0 4px 18px rgba(0,0,0,0.18);">
                                    <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg"
                                         alt="Greenwave" width="180"
                                         style="max-width: 180px; display: block; margin: 0 auto;">
                                </td>
                            </tr>
                        </table>

                        <!-- Divider -->
                        <div style="width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 22px; border-radius: 2px;"></div>

                        <!-- Bell Icon -->
                        <div style="width: 64px; height: 64px;
                                    background: rgba(255,255,255,0.15);
                                    border: 2px solid rgba(255,255,255,0.5);
                                    border-radius: 50%;
                                    margin: 0 auto 18px;
                                    text-align: center;
                                    line-height: 60px;
                                    font-size: 30px;">
                            🔔
                        </div>

                        <div style="font-size: 23px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 10px;">
                            {{ $sourceText }}
                        </div>
                        <div style="font-size: 13px; color: rgba(255,255,255,0.6);">
                            {{ \Carbon\Carbon::parse($po->created_at)->format('d F Y, h:i A') }}
                        </div>
                    </td>
                </tr>

                <!-- PO Reference Ribbon -->
                <tr>
                    <td style="background: #43a047; padding: 12px 32px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="font-size: 13px; color: #fff; font-weight: 600;">
                                    ● &nbsp;PO Reference: <strong>{{ $po->po_ref_no_string }}</strong>
                                </td>
                                <td align="right">
                                    <span style="display: inline-block; background: rgba(255,255,255,0.22);
                                                 border: 1px solid rgba(255,255,255,0.4);
                                                 border-radius: 20px; padding: 3px 14px;
                                                 font-size: 11px; font-weight: 700; color: #fff;
                                                 text-transform: uppercase; letter-spacing: 0.5px;">
                                        {{ strtoupper(str_replace('_', ' ', $po->action)) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- ADMIN GREETING                        -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 28px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background: #f1f8e9; border: 1px solid #c5e1a5;
                                      border-left: 5px solid #558b2f; border-radius: 10px;">
                            <tr>
                                <td style="padding: 20px 22px;">
                                    <div style="font-size: 15px; color: #33691e; font-weight: 700; margin-bottom: 8px;">
                                        Dear Admin,
                                    </div>
                                    <div style="font-size: 13px; color: #558b2f; line-height: 1.8;">
                                        A new Purchase Order has been received and requires your attention.
                                        Please review the details below and take the necessary action from your admin dashboard.
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- ORDER META INFO                       -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 20px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden;">

                            <tr style="background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%);">
                                <td style="padding: 12px 16px; font-size: 11px; font-weight: 700;
                                           color: #fff; text-transform: uppercase; letter-spacing: 0.8px;">
                                    ◉ &nbsp;Order Information
                                </td>
                            </tr>

                            <!-- PO ID -->
                            <tr style="border-bottom: 1px solid #f1f8e9;">
                                <td style="padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600;
                                           text-transform: uppercase; width: 32%; background: #f9fbe7;">PO Number</td>
                                <td style="padding: 11px 16px; font-size: 13px; color: #1b5e20; font-weight: 800;">
                                    {{ $po->po_ref_no_string }}
                                </td>
                            </tr>

                            <!-- Placed On -->
                            <tr style="border-bottom: 1px solid #f1f8e9;">
                                <td style="padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600;
                                           text-transform: uppercase; background: #f9fbe7;">Placed On</td>
                                <td style="padding: 11px 16px; font-size: 13px; color: #33691e;">
                                    {{ \Carbon\Carbon::parse($po->created_at)->format('d M Y, h:i A') }}
                                </td>
                            </tr>

                            <!-- Order Type -->
                            <tr style="border-bottom: 1px solid #f1f8e9;">
                                <td style="padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600;
                                           text-transform: uppercase; background: #f9fbe7;">Order Type</td>
                                <td style="padding: 11px 16px;">
                                    <span style="display: inline-block; background: {{ $actionColor }};
                                                 color: #fff; padding: 3px 12px; border-radius: 20px;
                                                 font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                        {{ strtoupper(str_replace('_', ' ', $po->action)) }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Dealer -->
                            <tr style="border-bottom: 1px solid #f1f8e9;">
                                <td style="padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600;
                                           text-transform: uppercase; background: #f9fbe7;">Dealer</td>
                                <td style="padding: 11px 16px; font-size: 13px; color: #33691e; font-weight: 600;">
                                    {{ $po->dealer->business_name ?? ($po->dealer->name ?? 'N/A') }}
                                    @if(!empty($po->dealer->mobile))
                                        <span style="color: #9ca3af; font-size: 12px; font-weight: 400; margin-left: 8px;">
                                            {{ $po->dealer->mobile }}
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Customer (only if exists) -->
                            @if(isset($po->customer->name))
                            <tr style="border-bottom: 1px solid #f1f8e9;">
                                <td style="padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600;
                                           text-transform: uppercase; background: #f9fbe7;">Customer</td>
                                <td style="padding: 11px 16px; font-size: 13px; color: #33691e; font-weight: 600;">
                                    {{ $po->customer->name }}
                                </td>
                            </tr>
                            @endif

                            <!-- Remarks -->
                            @if(!empty($po->remarks))
                            <tr>
                                <td style="padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600;
                                           text-transform: uppercase; background: #f9fbe7;">Remarks</td>
                                <td style="padding: 11px 16px; font-size: 13px; color: #6b7280; font-style: italic;">
                                    {{ $po->remarks }}
                                </td>
                            </tr>
                            @endif

                        </table>
                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- ORDER ITEMS TABLE (with pricing+MOQ)  -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 24px 32px 0;">

                        <!-- Section Label -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
                            <tr>
                                <td>
                                    <span style="display: inline-block; width: 4px; height: 16px;
                                                 background: #43a047; border-radius: 2px;
                                                 vertical-align: middle; margin-right: 10px;"></span>
                                    <span style="font-size: 13px; font-weight: 700; color: #2e7d32;
                                                 text-transform: uppercase; letter-spacing: 0.8px;
                                                 vertical-align: middle;">Order Items</span>
                                </td>
                                <td align="right">
                                    <span style="font-size: 11px; color: #81c784; font-weight: 600;">
                                        {{ $po->orderitems->count() }} product(s)
                                        @if($po->is_mini_pack_order == 1) &nbsp;·&nbsp; Mini Pack @endif
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <!-- Table -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="border: 1px solid #c8e6c9; border-radius: 12px; overflow: hidden; font-size: 12px;">

                            <!-- Header -->
                            <tr style="background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);">
                                <td style="padding: 12px 12px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px; width: 26px;">#</td>
                                <td style="padding: 12px 12px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px;">Product</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Pack</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">MOQ</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Ordered Qty</td>

                            </tr>

                            @foreach($po->orderitems as $idx => $item)
                            @php
                                $moq      = !empty($item->product->moq) ? $item->product->moq : 0;
                                $isMini   = $po->is_mini_pack_order == 1;
                                $belowMoq = !$isMini && $moq > 0 && $item->qty < $moq;
                            @endphp
                            <tr style="background: {{ $loop->even ? '#f9fbe7' : '#ffffff' }};
                                       border-bottom: 1px solid #e8f5e9;">

                                <!-- # -->
                                <td style="padding: 13px 12px; color: #a5d6a7; font-size: 11px; font-weight: 700;">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- Product -->
                                <td style="padding: 13px 12px;">
                                    <div style="font-size: 12px; font-weight: 700; color: #1b5e20;">
                                        {{ $item->product->product_name ?? '—' }}
                                    </div>
                                    @if(!empty($item->product->product_code))
                                        <div style="font-size: 10px; color: #a5d6a7; margin-top: 2px;">
                                            {{ $item->product->product_code }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Pack Size -->
                                <td style="padding: 13px 8px; text-align: center; color: #558b2f; font-size: 11px; font-weight: 600;">
                                    @if($isMini)
                                        {{ $item->mini_pack_size ?? '—' }}
                                    @else
                                        {{ isset($item->packingsize->size) ? $item->packingsize->size . ' kg' : '—' }}
                                    @endif
                                </td>

                                <!-- MOQ -->
                                <td style="padding: 13px 8px; text-align: center;">
                                    @if($isMini)
                                        <span style="font-size: 11px; color: #9ca3af;">N/A</span>
                                    @elseif($moq > 0)
                                        <span style="font-size: 13px; font-weight: 700; color: #558b2f;">{{ $moq }}</span>
                                        <span style="font-size: 10px; color: #a5d6a7;"> kg</span>
                                    @else
                                        <span style="color: #9ca3af;">—</span>
                                    @endif
                                </td>

                                <!-- Ordered Qty -->
                                <td style="padding: 13px 8px; text-align: center;">
                                    <span style="font-size: 15px; font-weight: 800;
                                                 color: {{ $belowMoq ? '#c62828' : '#2e7d32' }};">
                                        {{ $item->qty }}
                                    </span>
                                    <span style="font-size: 10px; color: #a5d6a7;"> kg</span>
                                    @if($belowMoq)
                                        <div style="margin-top: 4px;">
                                            <span style="font-size: 9px; color: #c62828; font-weight: 700;
                                                         background: #ffebee; padding: 2px 6px;
                                                         border-radius: 3px; display: inline-block;
                                                         border: 1px solid #ffcdd2;">⚠ Below MOQ</span>
                                        </div>
                                    @endif
                                </td>

                            </tr>
                            @endforeach

                            <!-- Totals Footer -->
                            <tr style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); border-top: 2px solid #c8e6c9;">
                                <td colspan="3" style="padding: 13px 12px; font-size: 11px;
                                                       color: #558b2f; font-weight: 700; text-transform: uppercase;">
                                    Total Products: {{ $po->orderitems->count() }}
                                </td>
                                <td colspan="2" style="padding: 13px 12px; text-align: center;
                                                       font-size: 14px; color: #1b5e20; font-weight: 800;">
                                    Total Qty: {{ $po->orderitems->sum('qty') }} kg
                                </td>
                            </tr>

                        </table>

                        <!-- MOQ legend -->
                        @if(!$po->is_mini_pack_order)
                        <!-- <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 10px;">
                            <tr>
                                <td style="font-size: 11px; color: #9ca3af; padding: 0 4px;">
                                    <span style="color: #c62828; font-weight: 700;">⚠</span>
                                    Items in red are below the Minimum Order Quantity (MOQ).
                                </td>
                            </tr>
                        </table>
                        @endif -->

                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- REVIEW / APPROVE BUTTON               -->
                <!-- (only for dealer and customer)        -->
                <!-- ══════════════════════════════════════ -->
                @if($poUrl)
                <tr>
                    <td style="padding: 24px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background: #fff9e6; border: 1px solid #fde68a;
                                      border-left: 5px solid #f59e0b; border-radius: 10px;">
                            <tr>
                                <td style="padding: 20px 22px;">
                                    <div style="font-size: 14px; font-weight: 700; color: #92400e; margin-bottom: 6px;">
                                        ⚡ Action Required
                                    </div>
                                    <div style="font-size: 13px; color: #78350f; line-height: 1.7; margin-bottom: 16px;">
                                        This order is awaiting your review and approval. Click the button below to open the PO detail page and take action.
                                    </div>
                                    <table cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td align="center"
                                                style="background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
                                                       border-radius: 8px;
                                                       box-shadow: 0 4px 14px rgba(27,94,32,0.35);">
                                                <a href="{{ $poUrl }}"
                                                   style="display: inline-block; padding: 13px 32px;
                                                          font-size: 14px; font-weight: 700; color: #ffffff;
                                                          text-decoration: none; letter-spacing: 0.3px;">
                                                    🔍 &nbsp;Review &amp; Approve PO
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif

                <!-- ══════════════════════════════════════ -->
                <!-- FOOTER                                -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 28px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 100%);
                                      border-radius: 12px;">
                            <tr>
                                <td style="padding: 28px 32px; text-align: center;">
                                    <table cellpadding="0" cellspacing="0" border="0"
                                           align="center" style="margin: 0 auto 18px;">
                                        <tr>
                                            <td align="center"
                                                style="background: #ffffff; border-radius: 10px;
                                                       padding: 8px 20px;
                                                       box-shadow: 0 3px 12px rgba(0,0,0,0.15);">
                                                <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg"
                                                     alt="Greenwave" width="130"
                                                     style="max-width: 130px; display: block; margin: 0 auto;">
                                            </td>
                                        </tr>
                                    </table>
                                    <div style="width: 40px; height: 1px; background: rgba(255,255,255,0.25); margin: 0 auto 16px;"></div>
                                    <div style="font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.8; margin-bottom: 12px;">
                                        Greenwave System — Admin Notification<br>
                                        Please log in to the admin dashboard to manage orders.
                                    </div>
                                    <div style="font-size: 11px; color: rgba(255,255,255,0.4); letter-spacing: 0.3px;">
                                        This is an automated email from Greenwave System. Please do not reply.
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Bottom spacer -->
                <tr><td style="height: 32px; background: #ffffff;"></td></tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>