<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Purchase Order Approved</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #e8f5e9;
            margin: 0; padding: 0;
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

            <table width="700" cellpadding="0" cellspacing="0" border="0"
                   style="max-width: 700px; width: 100%; background: #ffffff;
                          border-radius: 18px; overflow: hidden;
                          box-shadow: 0 8px 40px rgba(0,100,0,0.14);">

                <!-- ══════════════════════════════════════ -->
                <!-- HEADER                                -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td align="center"
                        style="background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%);
                               padding: 40px 32px 32px;">

                        <!-- Centered Logo in white pill -->
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
                        <div style="width: 48px; height: 2px; background: rgba(255,255,255,0.3);
                                    margin: 0 auto 24px; border-radius: 2px;"></div>

                        <!-- Approved Checkmark -->
                        <div style="width: 68px; height: 68px;
                                    background: #10b981;
                                    border: 3px solid rgba(255,255,255,0.6);
                                    border-radius: 50%;
                                    margin: 0 auto 18px;
                                    text-align: center;
                                    line-height: 62px;
                                    font-size: 32px;
                                    color: #ffffff;
                                    box-shadow: 0 4px 20px rgba(16,185,129,0.4);">
                            ✓
                        </div>

                        <div style="font-size: 26px; font-weight: 700; color: #ffffff;
                                    letter-spacing: 0.3px; margin-bottom: 8px;">
                            Your Order Has Been Approved!
                        </div>
                        <div style="font-size: 13px; color: rgba(255,255,255,0.65);">
                            {{ \Carbon\Carbon::now()->format('d F Y, h:i A') }}
                        </div>
                    </td>
                </tr>

                <!-- Approved Status Ribbon -->
                <tr>
                    <td style="background: #10b981; padding: 12px 32px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="font-size: 13px; color: #fff; font-weight: 600;">
                                    ✓ &nbsp;PO Reference: <strong>{{ $po->po_ref_no_string }}</strong>
                                </td>
                                <td align="right">
                                    <span style="display: inline-block;
                                                 background: rgba(255,255,255,0.25);
                                                 border: 1px solid rgba(255,255,255,0.5);
                                                 border-radius: 20px; padding: 3px 16px;
                                                 font-size: 11px; font-weight: 700;
                                                 color: #fff; text-transform: uppercase;
                                                 letter-spacing: 0.8px;">
                                        APPROVED
                                    </span>
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
                               style="background: #f0fdf4; border: 1px solid #bbf7d0;
                                      border-left: 5px solid #10b981; border-radius: 10px;">
                            <tr>
                                <td style="padding: 20px 22px;">
                                    <div style="font-size: 15px; color: #065f46; font-weight: 700; margin-bottom: 8px;">
                                        Dear {{ $po->dealer->business_name ?? ($po->dealer->name ?? 'Valued Dealer') }},
                                    </div>
                                    <div style="font-size: 13px; color: #047857; line-height: 1.8;">
                                        Great news! Your Purchase Order has been <strong>reviewed and approved</strong> by our team.
                                        Please find the complete order summary below including approved quantities and pricing details.
                                        Our team will begin processing your order shortly.
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- PO SUMMARY INFO                       -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 20px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden;">
                            <tr style="background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%);">
                                <td style="padding: 12px 16px; font-size: 11px; font-weight: 700;
                                           color: #fff; text-transform: uppercase; letter-spacing: 0.8px;">
                                    ◉ &nbsp;Order Summary
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                        <tr style="border-bottom: 1px solid #f1f8e9;">
                                            <td style="padding: 11px 16px; font-size: 11px; color: #81c784;
                                                       font-weight: 600; text-transform: uppercase; width: 30%; background: #f9fbe7;">PO Number</td>
                                            <td style="padding: 11px 16px; font-size: 14px; color: #1b5e20; font-weight: 800;">
                                                {{ $po->po_ref_no_string }}
                                            </td>
                                            <td style="padding: 11px 16px; font-size: 11px; color: #81c784;
                                                       font-weight: 600; text-transform: uppercase; width: 25%; background: #f9fbe7;">Order Date</td>
                                            <td style="padding: 11px 16px; font-size: 13px; color: #33691e;">
                                                {{ \Carbon\Carbon::parse($po->created_at)->format('d M Y') }}
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #f1f8e9;">
                                            <td style="padding: 11px 16px; font-size: 11px; color: #81c784;
                                                       font-weight: 600; text-transform: uppercase; background: #f9fbe7;">Approved On</td>
                                            <td style="padding: 11px 16px; font-size: 13px; color: #33691e;">
                                                {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}
                                            </td>
                                            <td style="padding: 11px 16px; font-size: 11px; color: #81c784;
                                                       font-weight: 600; text-transform: uppercase; background: #f9fbe7;">Mode</td>
                                            <td style="padding: 11px 16px; font-size: 13px; color: #33691e;">
                                                {{ $po->mode ?: '—' }}
                                            </td>
                                        </tr>
                                        @if(!empty($po->remarks))
                                        <tr>
                                            <td style="padding: 11px 16px; font-size: 11px; color: #81c784;
                                                       font-weight: 600; text-transform: uppercase; background: #f9fbe7;">Remarks</td>
                                            <td colspan="3" style="padding: 11px 16px; font-size: 13px;
                                                                    color: #6b7280; font-style: italic;">
                                                {{ $po->remarks }}
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- APPROVED PRODUCTS TABLE               -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 24px 32px 0;">

                        <!-- Section Label -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
                            <tr>
                                <td>
                                    <span style="display: inline-block; width: 4px; height: 16px;
                                                 background: #10b981; border-radius: 2px;
                                                 vertical-align: middle; margin-right: 10px;"></span>
                                    <span style="font-size: 13px; font-weight: 700; color: #065f46;
                                                 text-transform: uppercase; letter-spacing: 0.8px;
                                                 vertical-align: middle;">Approved Order Items</span>
                                </td>
                                <td align="right">
                                    <span style="font-size: 11px; color: #6ee7b7; font-weight: 600;">
                                        {{ $po->orderitems->count() }} product(s)
                                        @if($po->is_mini_pack_order == 1) &nbsp;·&nbsp; Mini Pack @endif
                                    </span>
                                </td>
                            </tr>
                        </table>

                        <!-- Products Table -->
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="border: 1px solid #6ee7b7; border-radius: 12px;
                                      overflow: hidden; font-size: 12px;">

                            <!-- Header -->
                            <tr style="background: linear-gradient(135deg, #065f46 0%, #059669 100%);">
                                <td style="padding: 12px 10px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px; width: 24px;">#</td>
                                <td style="padding: 12px 10px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px;">Product</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px; text-align: center;">Pack</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px; text-align: center;">Dealer Price</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px; text-align: center;">Disc.</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px; text-align: center;">Net Price</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px; text-align: center;">Ord. Qty</td>
                                <td style="padding: 12px 8px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px; text-align: center;">Appr. Qty</td>
                                <td style="padding: 12px 10px; color: #fff; font-size: 10px; font-weight: 700;
                                           text-transform: uppercase; letter-spacing: 0.4px; text-align: right;">Value</td>
                            </tr>

                            @foreach($po->orderitems as $idx => $item)
                            @php
                                $totalDisc  = $item->dealer_qty_discount + $item->dealer_special_discount + $item->dealer_basic_discount;
                                $subTotal   = $item->actual_qty * $item->net_price;
                                $isMini     = $po->is_mini_pack_order == 1;
                                $qtyReduced = $item->actual_qty < $item->qty;
                            @endphp
                            <tr style="background: {{ $loop->even ? '#f0fdf4' : '#ffffff' }};
                                       border-bottom: 1px solid #d1fae5;">

                                <!-- # -->
                                <td style="padding: 13px 10px; color: #6ee7b7; font-size: 11px; font-weight: 700;">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- Product -->
                                <td style="padding: 13px 10px;">
                                    <div style="font-size: 12px; font-weight: 700; color: #065f46;">
                                        {{ $item->product->product_name ?? '—' }}
                                    </div>
                                    @if(!empty($item->comments))
                                        <div style="font-size: 10px; color: #9ca3af; margin-top: 3px;
                                                    font-style: italic; line-height: 1.4;">
                                            💬 {{ $item->comments }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Pack Size -->
                                <td style="padding: 13px 8px; text-align: center;
                                           color: #059669; font-size: 11px; font-weight: 600;">
                                    @if($isMini)
                                        {{ $item->mini_pack_size ?? '—' }}
                                    @else
                                        {{ isset($item->packingsize->size) ? $item->packingsize->size . ' kg' : '—' }}
                                    @endif
                                </td>

                                <!-- Dealer Price -->
                                <td style="padding: 13px 8px; text-align: center;">
                                    <span style="font-size: 12px; font-weight: 700; color: #374151;">
                                        Rs. {{ number_format($item->product_price, 2) }}
                                    </span>
                                </td>

                                <!-- Discounts breakdown -->
                                <td style="padding: 13px 8px; text-align: center;">
                                    <table cellpadding="0" cellspacing="0" border="0" align="center"
                                           style="font-size: 10px; line-height: 1.6;">
                                        <tr>
                                            <td style="color: #9ca3af; padding-right: 4px;">Qty</td>
                                            <td style="color: #c2410c; font-weight: 700;">{{ $item->dealer_qty_discount }}%</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #9ca3af; padding-right: 4px;">Spl</td>
                                            <td style="color: #c2410c; font-weight: 700;">{{ $item->dealer_special_discount }}%</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #9ca3af; padding-right: 4px;">Basic</td>
                                            <td style="color: #c2410c; font-weight: 700;">{{ $item->dealer_basic_discount }}%</td>
                                        </tr>
                                        <tr style="border-top: 1px solid #fde8d8;">
                                            <td style="color: #9ca3af; font-weight: 700; padding-right: 4px; padding-top: 2px;">Total</td>
                                            <td style="color: #9a3412; font-weight: 800; padding-top: 2px;">{{ $totalDisc }}%</td>
                                        </tr>
                                    </table>
                                    @if($po->is_mini_pack_order == 1 && !empty($item->additional_charges) && $item->additional_charges > 0)
                                        <div style="margin-top: 4px; font-size: 10px; color: #7c3aed; font-weight: 600;">
                                            +Rs.{{ $item->additional_charges }} charges
                                        </div>
                                    @endif
                                </td>

                                <!-- Net Price -->
                                <td style="padding: 13px 8px; text-align: center;">
                                    <div style="background: #ecfdf5; border: 1px solid #6ee7b7;
                                                border-radius: 6px; padding: 5px 8px; display: inline-block;">
                                        <span style="font-size: 12px; font-weight: 800; color: #065f46;">
                                            Rs. {{ number_format($item->net_price, 2) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Ordered Qty -->
                                <td style="padding: 13px 8px; text-align: center;">
                                    <span style="font-size: 13px; font-weight: 700;
                                                 color: {{ $qtyReduced ? '#9ca3af' : '#374151' }};
                                                 text-decoration: {{ $qtyReduced ? 'line-through' : 'none' }};">
                                        {{ $item->qty }}
                                    </span>
                                    <span style="font-size: 10px; color: #9ca3af;"> kg</span>
                                </td>

                                <!-- Approved Qty -->
                                <td style="padding: 13px 8px; text-align: center;">
                                    <div style="background: {{ $qtyReduced ? '#fff7ed' : '#ecfdf5' }};
                                                border: 1px solid {{ $qtyReduced ? '#fed7aa' : '#6ee7b7' }};
                                                border-radius: 6px; padding: 5px 8px; display: inline-block;">
                                        <span style="font-size: 14px; font-weight: 800;
                                                     color: {{ $qtyReduced ? '#c2410c' : '#065f46' }};">
                                            {{ $item->actual_qty }}
                                        </span>
                                        <span style="font-size: 10px; color: #9ca3af;"> kg</span>
                                    </div>
                                    @if($qtyReduced)
                                        <div style="margin-top: 4px;">
                                            <span style="font-size: 9px; color: #c2410c; font-weight: 700;
                                                         background: #fff7ed; border: 1px solid #fed7aa;
                                                         padding: 2px 6px; border-radius: 3px;">
                                                Qty Reduced
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Subtotal -->
                                <td style="padding: 13px 10px; text-align: right;">
                                    <span style="font-size: 13px; font-weight: 800; color: #065f46;">
                                        Rs. {{ number_format($subTotal, 2) }}
                                    </span>
                                </td>

                            </tr>
                            @endforeach

                        </table>
                    </td>
                </tr>

                <!-- ══════════════════════════════════════ -->
                <!-- ORDER TOTALS                          -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 16px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="50%">&nbsp;</td>
                                <td width="50%">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                           style="border: 1px solid #6ee7b7; border-radius: 10px; overflow: hidden;">
                                        <tr style="border-bottom: 1px solid #d1fae5;">
                                            <td style="padding: 11px 16px; font-size: 12px; color: #6b7280;
                                                       background: #f0fdf4;">Subtotal</td>
                                            <td style="padding: 11px 16px; font-size: 13px; font-weight: 700;
                                                       color: #065f46; text-align: right;">
                                                Rs. {{ number_format($po->price, 2) }}
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px solid #d1fae5;">
                                            <td style="padding: 11px 16px; font-size: 12px; color: #92400e;
                                                       background: #fffbeb;">
                                                GST ({{ $po->gst_per }}%)
                                            </td>
                                            <td style="padding: 11px 16px; font-size: 13px; font-weight: 700;
                                                       color: #92400e; text-align: right;">
                                                + Rs. {{ number_format($po->gst, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"
                                                style="background: linear-gradient(135deg, #065f46 0%, #059669 100%);
                                                       padding: 15px 16px;">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td style="font-size: 12px; color: rgba(255,255,255,0.75);
                                                                   font-weight: 600; text-transform: uppercase;
                                                                   letter-spacing: 0.5px;">Grand Total</td>
                                                        <td style="font-size: 20px; font-weight: 800;
                                                                   color: #fff; text-align: right;">
                                                            Rs. {{ number_format($po->grand_total, 2) }}
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
                <!-- QTY REDUCED NOTE (if any item reduced) -->
                <!-- ══════════════════════════════════════ -->
                @if($po->orderitems->where('actual_qty', '<', $po->orderitems->first()->qty ?? 0)->count() > 0 || $po->orderitems->contains(fn($i) => $i->actual_qty < $i->qty))
                <tr>
                    <td style="padding: 16px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background: #fff7ed; border: 1px solid #fed7aa;
                                      border-left: 5px solid #f59e0b; border-radius: 10px;">
                            <tr>
                                <td style="padding: 16px 20px;">
                                    <div style="font-size: 13px; font-weight: 700; color: #92400e; margin-bottom: 5px;">
                                        ⚠ Please Note — Quantity Adjustment
                                    </div>
                                    <div style="font-size: 12px; color: #78350f; line-height: 1.7;">
                                        One or more items in your order have been adjusted to a lower approved quantity
                                        than originally requested. Items marked <strong>"Qty Reduced"</strong> reflect these changes.
                                        Please review and contact your account manager if you have any questions.
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif

                <!-- ══════════════════════════════════════ -->
                <!-- WHAT HAPPENS NEXT                     -->
                <!-- ══════════════════════════════════════ -->
                <tr>
                    <td style="padding: 24px 32px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background: #f0fdf4; border: 1px solid #bbf7d0;
                                      border-radius: 12px; overflow: hidden;">
                            <tr>
                                <td style="background: #d1fae5; padding: 13px 20px;
                                           border-bottom: 1px solid #a7f3d0; text-align: center;">
                                    <span style="font-size: 12px; font-weight: 700; color: #065f46;
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
                                                       style="background: #fff; border: 1px solid #bbf7d0; border-radius: 10px;">
                                                    <tr>
                                                        <td style="padding: 18px 10px; text-align: center;">
                                                            <div style="font-size: 28px; margin-bottom: 10px;">✅</div>
                                                            <div style="font-size: 12px; font-weight: 700; color: #065f46; margin-bottom: 4px;">Approved</div>
                                                            <div style="font-size: 11px; color: #6ee7b7; line-height: 1.5;">Order confirmed by our team</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td valign="top" style="padding-right: 8px; width: 33%;">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                                       style="background: #fff; border: 1px solid #bbf7d0; border-radius: 10px;">
                                                    <tr>
                                                        <td style="padding: 18px 10px; text-align: center;">
                                                            <div style="font-size: 28px; margin-bottom: 10px;">📦</div>
                                                            <div style="font-size: 12px; font-weight: 700; color: #065f46; margin-bottom: 4px;">Processing</div>
                                                            <div style="font-size: 11px; color: #6ee7b7; line-height: 1.5;">Order being prepared for dispatch</div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td valign="top" style="width: 33%;">
                                                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                                       style="background: #fff; border: 1px solid #bbf7d0; border-radius: 10px;">
                                                    <tr>
                                                        <td style="padding: 18px 10px; text-align: center;">
                                                            <div style="font-size: 28px; margin-bottom: 10px;">🚚</div>
                                                            <div style="font-size: 12px; font-weight: 700; color: #065f46; margin-bottom: 4px;">Dispatched</div>
                                                            <div style="font-size: 11px; color: #6ee7b7; line-height: 1.5;">You'll be notified when shipped</div>
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
                                        For any queries regarding your approved order, please contact us.<br>
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