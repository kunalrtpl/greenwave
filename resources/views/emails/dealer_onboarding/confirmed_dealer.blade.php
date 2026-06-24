<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome Aboard — Channel Partner Confirmed</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        img { border: 0; display: block; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }

        body, .bg-page    { background-color: #e8f5e9; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        .wrapper          { max-width: 680px; width: 100%; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,100,0,0.14); }

        /* Header */
        .header           { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%); padding: 42px 32px 34px; text-align: center; }
        .logo-pill        { display: inline-block; background: #ffffff; border-radius: 14px; padding: 12px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.18); margin-bottom: 24px; }
        .check-badge      { width: 70px; height: 70px; background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.55); border-radius: 50%; margin: 0 auto 16px; text-align: center; line-height: 66px; font-size: 34px; color: #ffffff; }
        .header-title     { font-size: 24px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 8px; }
        .header-sub       { font-size: 14px; color: rgba(255,255,255,0.85); }
        .header-date      { font-size: 12px; color: rgba(255,255,255,0.55); margin-top: 8px; }

        /* Ribbon */
        .ribbon           { background: #43a047; padding: 12px 32px; text-align: center; }
        .ribbon-badge     { display: inline-block; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); border-radius: 20px; padding: 4px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

        .section          { padding: 24px 32px 0; }

        /* Greeting */
        .greeting-box     { background: #f1f8e9; border: 1px solid #c5e1a5; border-left: 5px solid #558b2f; border-radius: 10px; padding: 22px 24px; }
        .greeting-name    { font-size: 16px; color: #33691e; font-weight: 700; margin-bottom: 8px; }
        .greeting-body    { font-size: 13.5px; color: #558b2f; line-height: 1.8; }

        /* Status pills */
        .ptype-pill       { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; }

        /* Info table */
        .info-table               { width: 100%; border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden; }
        .info-table-header        { background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%); }
        .info-table-header-cell   { padding: 14px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.8px; }
        .info-row-label           { padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600; text-transform: uppercase; width: 38%; background: #f9fbe7; }
        .info-row-value           { padding: 11px 16px; font-size: 13px; color: #1b5e20; font-weight: 800; }
        .info-row-value-normal    { padding: 11px 16px; font-size: 13px; color: #33691e; }
        .info-row-border          { border-bottom: 1px solid #f1f8e9; }

        /* Next steps */
        .steps-box        { background: #fff9e6; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; border-radius: 10px; padding: 18px 22px; }
        .steps-title      { font-size: 14px; font-weight: 700; color: #92400e; margin-bottom: 10px; }
        .steps-list       { font-size: 13px; color: #78350f; line-height: 1.9; }

        /* CTA */
        .cta-box          { text-align: center; padding: 4px 0 0; }
        .cta-btn          { display: inline-block; background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%); border-radius: 8px; box-shadow: 0 4px 14px rgba(27,94,32,0.35); }
        .cta-btn a        { display: inline-block; padding: 14px 36px; font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; letter-spacing: 0.3px; }

        /* Footer */
        .footer                   { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 100%); border-radius: 12px; padding: 28px 32px; text-align: center; }
        .footer-logo-wrap         { display: inline-block; background: #ffffff; border-radius: 10px; padding: 8px 20px; box-shadow: 0 3px 12px rgba(0,0,0,0.15); margin-bottom: 18px; }
        .footer-divider           { width: 40px; height: 1px; background: rgba(255,255,255,0.25); margin: 0 auto 16px; }
        .footer-text              { font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.8; margin-bottom: 12px; }
        .footer-note              { font-size: 11px; color: rgba(255,255,255,0.4); letter-spacing: 0.3px; }
    </style>
</head>
<body>

@php
    $d           = is_array($dealer ?? null) ? (object) $dealer : ($dealer ?? null);
    $isSub       = $isSubDealer ?? false;
    $pTypeLabel  = $partnerType ?? ($isSub ? 'Sub Dealer' : 'Primary Dealer');
    $pTypeColor  = $isSub ? '#d2691e' : '#6a1b9a';
    $bizName     = $d->business_name ?? ($d->name ?? 'Partner');
    $contactName = $d->name ?? 'Partner';
    $city        = $d->city ?? '—';
    $terr        = $territory ?? '—';
    $when        = $confirmedAt ?? \Carbon\Carbon::now()->format('d M Y, h:i A');
    $login       = $loginUrl ?? null;
    $status      = $cpStatus ?? null;  // provisional | authorized | null
    $statusLabel = $status ? ucfirst($status) . ' Channel Partner' : null;
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="bg-page">
<tr>
<td align="center" style="padding: 36px 16px;">

    <table cellpadding="0" cellspacing="0" border="0" class="wrapper">

        {{-- ════════════ HEADER ════════════ --}}
        <tr>
            <td class="header">
                <div class="logo-pill">
                    <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="180">
                </div>
                <div class="check-badge">&#10003;</div>
                <div class="header-title">Welcome Aboard!</div>
                <div class="header-sub">You are now a confirmed Greenwave Channel Partner</div>
                <div class="header-date">{{ $when }}</div>
            </td>
        </tr>

        {{-- ════════════ RIBBON ════════════ --}}
        <tr>
            <td class="ribbon">
                <span class="ribbon-badge">{{ strtoupper($pTypeLabel) }}@if($statusLabel) &nbsp;·&nbsp; {{ strtoupper($status) }} @endif</span>
            </td>
        </tr>

        {{-- ════════════ GREETING ════════════ --}}
        <tr>
            <td class="section">
                <div class="greeting-box">
                    <div class="greeting-name">Dear {{ $contactName }},</div>
                    <div class="greeting-body">
                        Congratulations! We're delighted to welcome <strong>{{ $bizName }}</strong> as
                        @if($isSub)
                            a confirmed <strong>Sub Dealer</strong>@if(!empty($linkedDealer)) under <strong>{{ $linkedDealer }}</strong>@endif
                        @else
                            a confirmed <strong>Primary Dealer</strong>@if($statusLabel) with <strong>{{ $statusLabel }}</strong> status @endif
                        @endif
                        in the Greenwave network. Your onboarding is complete and your account is now active.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ════════════ PARTNERSHIP DETAILS ════════════ --}}
        <tr>
            <td class="section">
                <table class="info-table" cellpadding="0" cellspacing="0" border="0">
                    <tr class="info-table-header">
                        <td colspan="2" class="info-table-header-cell">◉&nbsp; Your Partnership</td>
                    </tr>
                    <tr class="info-row-border">
                        <td class="info-row-label">Business Name</td>
                        <td class="info-row-value">{{ $bizName }}</td>
                    </tr>
                    <tr class="info-row-border">
                        <td class="info-row-label">Partner Type</td>
                        <td style="padding: 11px 16px;">
                            <span class="ptype-pill" style="background: {{ $pTypeColor }};">{{ $pTypeLabel }}</span>
                        </td>
                    </tr>
                    @if(!$isSub && $statusLabel)
                    <tr class="info-row-border">
                        <td class="info-row-label">Status</td>
                        <td class="info-row-value-normal">{{ $statusLabel }}</td>
                    </tr>
                    @endif
                    @if($isSub && !empty($linkedDealer))
                    <tr class="info-row-border">
                        <td class="info-row-label">Parent Dealer</td>
                        <td class="info-row-value-normal">{{ $linkedDealer }}</td>
                    </tr>
                    @endif
                    <tr class="info-row-border">
                        <td class="info-row-label">City</td>
                        <td class="info-row-value-normal">{{ $city }}</td>
                    </tr>
                    <tr>
                        <td class="info-row-label">Territory</td>
                        <td class="info-row-value-normal">{{ $terr ?: '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ════════════ NEXT STEPS ════════════ --}}
        <tr>
            <td class="section">
                <div class="steps-box">
                    <div class="steps-title">What's next?</div>
                    <div class="steps-list">
                        1.&nbsp; Log in to your Greenwave dealer account using your registered mobile number.<br>
                        2.&nbsp; Complete OTP &amp; PIN verification to secure your account.<br>
                        3.&nbsp; Browse the catalogue and start placing your purchase orders.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ════════════ FOOTER ════════════ --}}
        <tr>
            <td class="section" style="padding-bottom: 32px; padding-top: 24px;">
                <div class="footer">
                    <div class="footer-logo-wrap">
                        <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="130">
                    </div>
                    <div class="footer-divider"></div>
                    <div class="footer-text">
                        Greenwave System — Channel Partner Network<br>
                        We're excited to grow together. For any help, reach out to your Greenwave representative.
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