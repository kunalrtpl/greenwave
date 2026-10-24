{{--
   Shared shell for the product update emails sent from Product Pricing
   (discontinued, available again, not available, back in stock, price
   revised). A copy of the sampling layout so both families look alike.

   Email clients ignore most modern CSS, so this stays on tables, inline-ish
   styles and web-safe fonts, in the same green as the purchase-order mails.

   Sections a child view fills in:
     @section('title')     browser/tab title
     @section('heading')   the line in the green header
     @section('intro')     the greeting paragraph
     @section('content')   the body
--}}
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <title>@yield('title', 'Product Update')</title>
   <style>
      * { margin:0; padding:0; box-sizing:border-box; }
      img { border:0; display:block; }
      table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }

      body, .bg-page { background-color:#e8f5e9; font-family:Arial, Helvetica, sans-serif; -webkit-text-size-adjust:100%; }
      .wrapper { max-width:680px; width:100%; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 8px 40px rgba(0,100,0,0.14); }

      /* Solid colour first: clients that drop the gradient still get green,
         so the white text on top never lands on a white background. */
      .header { background-color:#1b5e20; background:linear-gradient(160deg,#1b5e20 0%,#2e7d32 55%,#388e3c 100%); padding:30px 32px 26px; text-align:center; }
      .logo-pill { display:inline-block; background:#ffffff; border-radius:14px; padding:10px 24px; margin-bottom:20px; }
      .header-divider { width:48px; height:2px; background-color:rgba(255,255,255,.35); margin:0 auto 18px; }
      .header-title { font-size:22px; font-weight:700; color:#ffffff !important; letter-spacing:.3px; margin-bottom:8px; }
      .header-date { font-size:12.5px; color:#d7eBd8 !important; }

      .ribbon { background-color:#43a047; padding:12px 32px; }
      .ribbon-ref { font-size:13px; color:#ffffff !important; font-weight:700; }
      .ribbon-badge { display:inline-block; background-color:#2e7d32; border:1px solid #81c784; border-radius:20px; padding:3px 14px; font-size:11px; font-weight:700; color:#ffffff !important; text-transform:uppercase; letter-spacing:.5px; }

      .section { padding:24px 32px 0; }
      .section-last { padding-bottom:28px; }

      .greeting-box { background:#f1f8e9; border:1px solid #c5e1a5; border-left:5px solid #558b2f; border-radius:10px; padding:18px 20px; }
      .greeting-name { font-size:15px; color:#33691e; font-weight:700; margin-bottom:6px; }
      .greeting-body { font-size:13px; color:#558b2f; line-height:1.7; }

      .section-label { font-size:12.5px; font-weight:700; color:#2e7d32; text-transform:uppercase; letter-spacing:.8px; padding-bottom:10px; }

      .info-table { width:100%; border:1px solid #c8e6c9; border-radius:10px; overflow:hidden; }
      .info-row-label { padding:10px 16px; font-size:11px; color:#7cb342; font-weight:700; text-transform:uppercase; width:34%; background:#f9fbe7; border-bottom:1px solid #f1f8e9; }
      .info-row-value { padding:10px 16px; font-size:13px; color:#1b5e20; border-bottom:1px solid #f1f8e9; }

      .items-table { width:100%; border:1px solid #c8e6c9; border-radius:10px; overflow:hidden; font-size:12px; }
      .items-header { background-color:#2e7d32; }
      .items-header td { padding:11px 12px; color:#ffffff !important; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
      .items-table td { padding:11px 12px; border-bottom:1px solid #e8f5e9; color:#33691e; }
      .items-table tr:last-child td { border-bottom:none; }
      .item-name { font-size:12.5px; font-weight:700; color:#1b5e20; }
      .item-code { font-size:10px; color:#9ccc65; margin-top:2px; }
      .center { text-align:center; }
      .right { text-align:right; }

      .note { background:#fffde7; border:1px solid #fff59d; border-radius:8px; padding:12px 14px; font-size:12px; color:#827717; line-height:1.6; }

      /* Action button. Clients that drop the gradient keep the solid green,
         and the padding lives on the <a> so it stays clickable everywhere. */
      .action-box { background:#fff9e6; border:1px solid #fde68a; border-left:5px solid #f59e0b; border-radius:10px; padding:18px 20px; text-align:center; }
      .action-box-body { font-size:13px; color:#78350f; line-height:1.7; padding-bottom:14px; }
      .action-btn { background-color:#1b5e20; background:linear-gradient(135deg,#1b5e20 0%,#2e7d32 100%); border-radius:8px; }
      .action-btn a { display:inline-block; padding:13px 30px; font-size:14px; font-weight:700; color:#ffffff !important; text-decoration:none; letter-spacing:.3px; }

      .footer { background:#f1f8e9; padding:22px 32px; text-align:center; border-top:1px solid #dcedc8; }
      .footer-text { font-size:11px; color:#9e9e9e; line-height:1.7; }
      .notice { background:#fff4f2; border:1px solid #ffccbc; border-left:5px solid #e64a19; border-radius:10px; padding:16px 18px; }
      .notice-title { font-size:12px; font-weight:700; color:#bf360c; text-transform:uppercase; letter-spacing:.6px; padding-bottom:6px; }
      .notice-body { font-size:13px; color:#5d4037; line-height:1.7; }
      .alt-box { background:#e8f5e9; border:1px solid #a5d6a7; border-radius:10px; padding:14px 18px; }
      .alt-name { font-size:15px; font-weight:700; color:#1b5e20; }
      .alt-code { font-size:11px; color:#66bb6a; padding-top:2px; }
      .steps td { font-size:13px; color:#33691e; line-height:1.6; padding:5px 0; vertical-align:top; }
      .step-dot { width:22px; color:#43a047; font-weight:700; }
      .good { background:#f1f8e9; border:1px solid #c5e1a5; border-left:5px solid #43a047; border-radius:10px; padding:16px 18px; }
      .good .notice-title { color:#2e7d32; }
      .good .notice-body { color:#33691e; }
      .price-grid td { padding:14px 10px; text-align:center; vertical-align:middle; }
      .price-cap { font-size:10.5px; font-weight:700; color:#7cb342; text-transform:uppercase; letter-spacing:.6px; padding-bottom:6px; }
      .price-old { font-size:18px; color:#9e9e9e; text-decoration:line-through; font-weight:700; }
      .price-new { font-size:24px; color:#1b5e20; font-weight:800; }
      .badge-red { display:inline-block; background-color:#c62828; border:1px solid #ef9a9a; border-radius:20px; padding:3px 14px; font-size:11px; font-weight:700; color:#ffffff !important; text-transform:uppercase; letter-spacing:.5px; }
   </style>
</head>
<body class="bg-page" style="margin:0; padding:0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="bg-page">
   <tr>
      <td align="center" style="padding:26px 12px;">
         <table role="presentation" cellpadding="0" cellspacing="0" class="wrapper">

            <tr>
               <td class="header" bgcolor="#1b5e20" style="background-color:#1b5e20; padding:30px 32px 26px; text-align:center;">
                  <div class="logo-pill" style="display:inline-block; background:#ffffff; border-radius:14px; padding:10px 24px; margin-bottom:20px;">
                     <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="170" style="display:block; border:0;">
                  </div>
                  <div class="header-divider" style="width:48px; height:2px; background-color:#5a9e62; margin:0 auto 18px;"></div>
                  <div class="header-title" style="font-size:22px; font-weight:700; color:#ffffff; margin-bottom:8px;">@yield('heading', 'Product Update')</div>
                  <div class="header-date" style="font-size:12.5px; color:#d7ebd8;">{{ date('d M Y, h:i A') }}</div>
               </td>
            </tr>

            @hasSection('ribbon')
            <tr>
               <td class="ribbon" bgcolor="#43a047" style="background-color:#43a047; padding:12px 32px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                     <tr>
                        <td class="ribbon-ref" style="font-size:13px; color:#ffffff; font-weight:700;">@yield('ribbon')</td>
                        <td class="right" style="text-align:right;">@yield('ribbon_badge')</td>
                     </tr>
                  </table>
               </td>
            </tr>
            @endif

            <tr>
               <td class="section">
                  <div class="greeting-box">
                     <div class="greeting-name">@yield('greeting', 'Hello,')</div>
                     <div class="greeting-body">@yield('intro')</div>
                  </div>
               </td>
            </tr>

            @yield('content')

            {{-- An optional button that opens the admin panel on the screen
                 where this email can be acted on. --}}
            @hasSection('cta')
            <tr>
               <td class="section section-last">
                  <div class="action-box" style="background:#fff9e6; border:1px solid #fde68a; border-left:5px solid #f59e0b; border-radius:10px; padding:18px 20px; text-align:center;">
                     <div class="action-box-body" style="font-size:13px; color:#78350f; line-height:1.7; padding-bottom:14px;">@yield('cta_text')</div>
                     <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                        <tr>
                           <td class="action-btn" bgcolor="#1b5e20" style="background-color:#1b5e20; border-radius:8px;">
                              @yield('cta')
                           </td>
                        </tr>
                     </table>
                  </div>
               </td>
            </tr>
            @endif

            <tr>
               <td class="footer" bgcolor="#f1f8e9" style="background-color:#f1f8e9; padding:22px 32px; text-align:center; border-top:1px solid #dcedc8;">
                  <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="120" style="display:inline-block; border:0; margin-bottom:12px;">
                  <div class="footer-text" style="font-size:11px; color:#9e9e9e; line-height:1.7;">
                     This is an automated message from Greenwave.<br>
                     Please do not reply to this email.
                  </div>
               </td>
            </tr>

         </table>
      </td>
   </tr>
</table>
</body>
</html>
