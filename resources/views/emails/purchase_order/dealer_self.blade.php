<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Purchase Order Created</title>
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

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #e8f5e9;">
    <tr>
        <td align="center" style="padding: 36px 16px;">

            <table width="660" cellpadding="0" cellspacing="0" border="0"
                   style="max-width: 660px; width: 100%; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,100,0,0.14);">

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
                        <div style="width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 24px; border-radius: 2px;"></div>

                        <!-- Check Circle -->
                        <div style="width: 64px; height: 64px;
                                    background: rgba(255,255,255,0.15);
                                    border: 2px solid rgba(255,255,255,0.5);
                                    border-radius: 50%;
                                    margin: 0 auto 18px;
                                    text-align: center;
                                    line-height: 60px;
                                    font-size: 30px;
                                    color: #ffffff;">✓</div>

                        <div style="font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 8px;">
                            Order Created Successfully
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
                                <td align="right" style="font-size: 12px; color: rgba(255,255,255,0.85);">
                                    Status: <strong>{{ ucwords($po->po_status ?? 'Pending') }}</strong>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- GREETING                              -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 28px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background: #f1f8e9; border: 1px solid #c5e1a5;
                                      border-left: 5px solid #558b2f; border-radius: 10px;">
                            <tr>
                                <td style="padding: 20px 22px;">
                                    <div style="font-size: 15px; color: #33691e; font-weight: 700; margin-bottom: 8px;">
                                        Dear {{ $po->dealer->name ?? ($po->dealer->business_name ?? 'Valued Dealer') }},
                                    </div>
                                    <div style="font-size: 13px; color: #558b2f; line-height: 1.8;">
                                        Your Purchase Order has been successfully received and is now under review by our team.
                                        Here is a summary of the items you ordered. We will notify you as soon as it is processed.
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- ORDER ITEMS TABLE                     -->
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

                        <!-- Products Table -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="border: 1px solid #c8e6c9; border-radius: 12px; overflow: hidden; font-size: 13px;">

                            <!-- ── TABLE HEADER ── -->
                            <tr style="background: linear-gradient(135deg, #2e7d32 0%, #43a047 100%);">
                                <td style="padding: 13px 14px; color: #fff; font-size: 11px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px; width: 30px;">#</td>
                                <td style="padding: 13px 14px; color: #fff; font-size: 11px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px;">Product Name</td>
                                <td style="padding: 13px 14px; color: #fff; font-size: 11px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Pack Size</td>
                                <td style="padding: 13px 14px; color: #fff; font-size: 11px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">MOQ</td>
                                <td style="padding: 13px 14px; color: #fff; font-size: 11px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Ordered Qty</td>
                            </tr>

                            <!-- ── PRODUCT ROWS ── -->
                            @foreach($po->orderitems as $idx => $item)
                            @php
                                $moq        = !empty($item->product->moq) ? $item->product->moq : 0;
                                $isMiniPack = $po->is_mini_pack_order == 1;
                                $belowMoq   = !$isMiniPack && $moq > 0 && $item->qty < $moq;
                            @endphp
                            <tr style="background: {{ $loop->even ? '#f9fbe7' : '#ffffff' }};
                                       border-bottom: 1px solid #e8f5e9;">

                                <!-- # -->
                                <td style="padding: 14px 14px; color: #a5d6a7; font-size: 12px; font-weight: 700;">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- Product Name -->
                                <td style="padding: 14px 14px;">
                                    <div style="font-size: 13px; font-weight: 700; color: #1b5e20;">
                                        {{ $item->product->product_name ?? '—' }}
                                    </div>
                                </td>

                                <!-- Pack Size -->
                                <td style="padding: 14px 14px; text-align: center;
                                           color: #558b2f; font-size: 12px; font-weight: 600;">
                                    @if($isMiniPack)
                                        {{ $item->mini_pack_size ?? '—' }}
                                    @else
                                        {{ isset($item->packingsize->size) ? $item->packingsize->size . ' kg' : '—' }}
                                    @endif
                                </td>

                                <!-- MOQ -->
                                <td style="padding: 14px 14px; text-align: center;">
                                    @if($isMiniPack)
                                        <span style="font-size: 12px; color: #9ca3af;">N/A</span>
                                    @else
                                        <span style="font-size: 14px; font-weight: 700; color: #558b2f;">
                                            {{ $moq > 0 ? $moq : '—' }}
                                        </span>
                                        @if($moq > 0)
                                            <span style="font-size: 11px; color: #a5d6a7; margin-left: 2px;">kg</span>
                                        @endif
                                    @endif
                                </td>

                                <!-- Ordered Qty -->
                                <td style="padding: 14px 14px; text-align: center;">
                                    <span style="font-size: 18px; font-weight: 800;
                                                 color: {{ $belowMoq ? '#c62828' : '#2e7d32' }};">
                                        {{ $item->qty }}
                                    </span>
                                    <span style="font-size: 11px; color: #a5d6a7; margin-left: 2px;">kg</span>

                                    @if($belowMoq)
                                        <div style="margin-top: 6px;">
                                            <span style="font-size: 10px; color: #c62828; font-weight: 700;
                                                         background: #ffebee; padding: 3px 8px;
                                                         border-radius: 4px; display: inline-block;
                                                         border: 1px solid #ffcdd2;">
                                                ⚠ Below MOQ
                                            </span>
                                        </div>
                                    @endif
                                </td>

                            </tr>
                            @endforeach

                            <!-- ── FOOTER TOTALS ROW ── -->
                            <tr style="background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
                                       border-top: 2px solid #c8e6c9;">
                                <td colspan="3" style="padding: 13px 14px; font-size: 12px;
                                                       color: #558b2f; font-weight: 700;
                                                       text-transform: uppercase; letter-spacing: 0.4px;">
                                    Total Products: {{ $po->orderitems->count() }}
                                </td>
                                <td colspan="2" style="padding: 13px 14px; text-align: center;
                                                       font-size: 14px; color: #1b5e20; font-weight: 800;">
                                    Total Qty: {{ $po->orderitems->sum('qty') }} kg
                                </td>
                            </tr>

                        </table>

                        <!-- MOQ legend note -->
                        @if(!$po->is_mini_pack_order)
                        <!-- <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 10px;">
                            <tr>
                                <td style="font-size: 11px; color: #9ca3af; padding: 0 4px;">
                                    <span style="color: #c62828; font-weight: 700;">⚠</span>
                                    Items highlighted in red are below the Minimum Order Quantity (MOQ). Please review before confirmation.
                                </td>
                            </tr>
                        </table> -->
                        @endif

                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- WHAT HAPPENS NEXT                     -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 24px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background: #f9fbe7; border: 1px solid #c8e6c9;
                                      border-radius: 12px; overflow: hidden;">
                            <tr>
                                <td style="background: #dcedc8; padding: 13px 20px;
                                           border-bottom: 1px solid #c5e1a5; text-align: center;">
                                    <span style="font-size: 12px; font-weight: 700; color: #33691e;
                                                 text-transform: uppercase; letter-spacing: 0.8px;">
                                        📋 &nbsp;What Happens Next?
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 16px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td valign="top" style="padding-right: 8px; width: 33%;">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                                       style="background: #fff; border: 1px solid #c8e6c9; border-radius: 10px;">
                                                    <tr>
                                                        <td style="padding: 18px 10px; text-align: center;">
                                                            <div style="font-size: 28px; margin-bottom: 10px;">📥</div>
                                                            <div style="font-size: 12px; font-weight: 700; color: #2e7d32; margin-bottom: 4px;">Received</div>
                                                            <div style="font-size: 11px; color: #a5d6a7; line-height: 1.5;">PO logged in our system</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td valign="top" style="padding-right: 8px; width: 33%;">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                                       style="background: #fff; border: 1px solid #c8e6c9; border-radius: 10px;">
                                                    <tr>
                                                        <td style="padding: 18px 10px; text-align: center;">
                                                            <div style="font-size: 28px; margin-bottom: 10px;">🔍</div>
                                                            <div style="font-size: 12px; font-weight: 700; color: #2e7d32; margin-bottom: 4px;">Under Review</div>
                                                            <div style="font-size: 11px; color: #a5d6a7; line-height: 1.5;">Team verifies quantities</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td valign="top" style="width: 33%;">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                                       style="background: #fff; border: 1px solid #c8e6c9; border-radius: 10px;">
                                                    <tr>
                                                        <td style="padding: 18px 10px; text-align: center;">
                                                            <div style="font-size: 28px; margin-bottom: 10px;">🚚</div>
                                                            <div style="font-size: 12px; font-weight: 700; color: #2e7d32; margin-bottom: 4px;">Dispatched</div>
                                                            <div style="font-size: 11px; color: #a5d6a7; line-height: 1.5;">You'll be notified on dispatch</div>
                                                        </td>
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
                                        For any queries, please contact your account manager<br>
                                        or your nearest Greenwave office.
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