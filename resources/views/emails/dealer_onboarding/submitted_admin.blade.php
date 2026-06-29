<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Channel Partner Onboarding Submitted</title>
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
        .logo-pill        { display: inline-block; background: #ffffff; border-radius: 14px; padding: 12px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.18); margin-bottom: 26px; }
        .header-divider   { width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 20px; border-radius: 2px; }
        .header-title     { font-size: 23px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 10px; }
        .header-date      { font-size: 13px; color: rgba(255,255,255,0.6); }

        /* ── Ribbon ── */
        .ribbon           { background: #43a047; padding: 12px 32px; }
        .ribbon-ref       { font-size: 13px; color: #fff; font-weight: 600; }
        .ribbon-badge     { display: inline-block; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); border-radius: 20px; padding: 3px 14px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Sections ── */
        .section          { padding: 24px 32px 0; }

        /* ── Greeting ── */
        .greeting-box     { background: #f1f8e9; border: 1px solid #c5e1a5; border-left: 5px solid #558b2f; border-radius: 10px; padding: 20px 22px; }
        .greeting-name    { font-size: 15px; color: #33691e; font-weight: 700; margin-bottom: 8px; }
        .greeting-body    { font-size: 13px; color: #558b2f; line-height: 1.8; }

        /* ── Partner type pill ── */
        .ptype-pill       { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; }

        /* ── Info table ── */
        .info-table               { width: 100%; border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden; }
        .info-table-header        { background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%); }
        .info-table-header-cell   { padding: 14px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.8px; }
        .info-row-label           { padding: 11px 16px; font-size: 11px; color: #81c784; font-weight: 600; text-transform: uppercase; width: 36%; background: #f9fbe7; vertical-align: top; }
        .info-row-value           { padding: 11px 16px; font-size: 13px; color: #1b5e20; font-weight: 800; }
        .info-row-value-normal    { padding: 11px 16px; font-size: 13px; color: #33691e; }
        .info-row-value-muted     { padding: 11px 16px; font-size: 13px; color: #6b7280; font-style: italic; }
        .info-row-border          { border-bottom: 1px solid #f1f8e9; }
        .dealer-mobile            { color: #9ca3af; font-size: 12px; font-weight: 400; margin-left: 8px; }

        /* ── Section label ── */
        .section-label            { font-size: 13px; font-weight: 700; color: #2e7d32; text-transform: uppercase; letter-spacing: 0.8px; vertical-align: middle; }
        .section-label-bar        { display: inline-block; width: 4px; height: 16px; background: #43a047; border-radius: 2px; vertical-align: middle; margin-right: 10px; }

        /* ── Documents checklist ── */
        .docs-table               { width: 100%; border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden; }
        .docs-row                 { border-bottom: 1px solid #f1f8e9; }
        .docs-row td              { padding: 11px 16px; font-size: 13px; }
        .docs-name                { color: #33691e; font-weight: 600; }
        .docs-status-ok           { color: #2e7d32; font-weight: 700; font-size: 12px; }
        .docs-status-no           { color: #9ca3af; font-weight: 600; font-size: 12px; }

        /* ── Attachment note ── */
        .attach-box               { background: #fff9e6; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; border-radius: 10px; padding: 16px 20px; }
        .attach-box-text          { font-size: 13px; color: #78350f; line-height: 1.7; }

        /* ── Action box ── */
        .action-box               { background: #f1f8e9; border: 1px solid #c5e1a5; border-radius: 10px; padding: 20px 22px; text-align: center; }
        .action-box-body          { font-size: 13px; color: #558b2f; line-height: 1.7; margin-bottom: 16px; }
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
    // Normalise inputs (dealer may be array or object depending on caller)
    $d            = is_array($dealer ?? null) ? (object) $dealer : ($dealer ?? null);
    $isSub         = $isSubDealer ?? false;
    $pTypeLabel   = $partnerType ?? ($isSub ? 'Sub Dealer' : 'Primary Dealer');
    $pTypeColor   = $isSub ? '#d2691e' : '#6a1b9a';
    $bizName      = $d->business_name ?? ($d->name ?? 'N/A');
    $shortName    = $d->short_name ?? null;
    $contactName  = $d->name ?? '—';
    $designation  = $d->designation ?? null;
    $city         = $d->city ?? '—';
    $ownerMobile  = $d->owner_mobile ?? null;
    $emailAddr    = $d->email ?? null;
    $terr         = $territory ?? '—';
    $when         = $submittedAt ?? \Carbon\Carbon::now()->format('d M Y, h:i A');
    $panel        = $panelUrl ?? null;

    // Document checklist (only meaningful for primary dealers)
    $docs = [
        'GST Certificate'  => $d->doc_gst_certificate  ?? null,
        'PAN Card'         => $d->doc_pan_card           ?? null,
        'Cancelled Cheque' => $d->doc_cancelled_cheque  ?? null,
        'Visiting Card'    => $d->doc_visiting_card     ?? null,
    ];
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
                <div class="header-divider"></div>
                <div class="header-title">Onboarding Form Submitted</div>
                <div class="header-date">{{ $when }}</div>
            </td>
        </tr>

        {{-- ════════════ RIBBON ════════════ --}}
        <tr>
            <td class="ribbon">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="ribbon-ref">●&nbsp; Channel Partner: <strong>{{ $bizName }}</strong></td>
                        <td align="right">
                            <span class="ribbon-badge">{{ strtoupper($pTypeLabel) }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ════════════ GREETING ════════════ --}}
        <tr>
            <td class="section">
                <div class="greeting-box">
                    <div class="greeting-name">Dear Admin,</div>
                    <div class="greeting-body">
                        <strong>{{ $bizName }}</strong>
                        has completed and submitted their onboarding form.
                        The full submission is attached to this email as a PDF.
                        Please review the details in the admin panel and proceed to confirm the partner.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ════════════ PARTNER DETAILS ════════════ --}}
        <tr>
            <td class="section">
                <table class="info-table" cellpadding="0" cellspacing="0" border="0">
                    <tr class="info-table-header">
                        <td colspan="2" class="info-table-header-cell">◉&nbsp; Partner Details</td>
                    </tr>

                    <tr class="info-row-border">
                        <td class="info-row-label">Business Name</td>
                        <td class="info-row-value">
                            {{ $bizName }}
                        </td>
                    </tr>

                    <tr class="info-row-border">
                        <td class="info-row-label">Partner Type</td>
                        <td style="padding: 11px 16px;">
                            <span class="ptype-pill" style="background: {{ $pTypeColor }};">{{ $pTypeLabel }}</span>
                        </td>
                    </tr>

                    @if($isSub && !empty($linkedDealer))
                    <tr class="info-row-border">
                        <td class="info-row-label">Parent Dealer</td>
                        <td class="info-row-value-normal">{{ $linkedDealer }}</td>
                    </tr>
                    @endif

                    <tr class="info-row-border">
                        <td class="info-row-label">Contact Person</td>
                        <td class="info-row-value-normal">
                            {{ $contactName }}@if($designation) <span class="dealer-mobile">{{ $designation }}</span>@endif
                        </td>
                    </tr>

                    <tr class="info-row-border">
                        <td class="info-row-label">City</td>
                        <td class="info-row-value-normal">{{ $city }}</td>
                    </tr>

                    @if($ownerMobile)
                    <tr class="info-row-border">
                        <td class="info-row-label">Mobile</td>
                        <td class="info-row-value-normal">{{ $ownerMobile }}</td>
                    </tr>
                    @endif

                    @if($emailAddr)
                    <tr class="info-row-border">
                        <td class="info-row-label">Email</td>
                        <td class="info-row-value-normal">{{ $emailAddr }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td class="info-row-label">Territory</td>
                        <td class="info-row-value-normal">{{ $terr ?: '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ════════════ BANK & TAX (primary only) ════════════ --}}
        @if(!$isSub)
        <tr>
            <td class="section">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
                    <tr>
                        <td>
                            <span class="section-label-bar"></span>
                            <span class="section-label">Business &amp; Bank</span>
                        </td>
                    </tr>
                </table>

                <table class="info-table" cellpadding="0" cellspacing="0" border="0">
                    @if(!empty($d->business_constitution))
                    <tr class="info-row-border">
                        <td class="info-row-label">Constitution</td>
                        <td class="info-row-value-normal">{{ $d->business_constitution }}</td>
                    </tr>
                    @endif
                    @if(!empty($d->gst_no))
                    <tr class="info-row-border">
                        <td class="info-row-label">GST No.</td>
                        <td class="info-row-value">{{ $d->gst_no }}</td>
                    </tr>
                    @endif
                    @if(!empty($d->pan_no))
                    <tr class="info-row-border">
                        <td class="info-row-label">PAN No.</td>
                        <td class="info-row-value">{{ $d->pan_no }}</td>
                    </tr>
                    @endif
                    @if(!empty($d->bank_name))
                    <tr class="info-row-border">
                        <td class="info-row-label">Bank</td>
                        <td class="info-row-value-normal">{{ $d->bank_name }}</td>
                    </tr>
                    @endif
                    @if(!empty($d->bank_account_number))
                    <tr class="info-row-border">
                        <td class="info-row-label">Account No.</td>
                        <td class="info-row-value-normal">{{ $d->bank_account_number }}</td>
                    </tr>
                    @endif
                    @if(!empty($d->bank_ifsc))
                    <tr>
                        <td class="info-row-label">IFSC</td>
                        <td class="info-row-value-normal">{{ $d->bank_ifsc }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>

        {{-- ════════════ DOCUMENTS CHECKLIST ════════════ --}}
        <tr>
            <td class="section">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
                    <tr>
                        <td>
                            <span class="section-label-bar"></span>
                            <span class="section-label">Uploaded Documents</span>
                        </td>
                    </tr>
                </table>

                <table class="docs-table" cellpadding="0" cellspacing="0" border="0">
                    @foreach($docs as $label => $path)
                    <tr class="docs-row">
                        <td class="docs-name">{{ $label }}</td>
                        <td align="right">
                            @if(!empty($path))
                                <span class="docs-status-ok">✓ Uploaded</span>
                            @else
                                <span class="docs-status-no">— Not provided</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        @endif

        {{-- ════════════ ATTACHMENT NOTE ════════════ --}}
        <tr>
            <td class="section">
                <div class="attach-box">
                    <div class="attach-box-text">
                        📎 &nbsp;<strong>The complete onboarding form is attached as a PDF</strong>
                        ({{ $pdfFileName ?? 'onboarding-form.pdf' }}) for your records.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ════════════ ACTION ════════════ --}}
        @if($panel)
        <tr>
            <td class="section">
                <div class="action-box">
                    <div class="action-box-body">
                        Open the channel partner in the admin panel to review the submission and confirm.
                    </div>
                    <!-- Fixed alignment using inline style margin: 0 auto for compatibility -->
                    <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto;">
                        <tr>
                            <td class="action-btn">
                                <a href="{{ $panel }}" style="display:inline-block;background-color:#1a7f3c;color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none;padding:14px 32px;border-radius:6px;font-family:Arial,Helvetica,sans-serif;">
                                    Review in Admin Panel
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        @endif

        {{-- ════════════ FOOTER ════════════ --}}
        <tr>
            <td class="section" style="padding-bottom: 32px;">
                <div class="footer">
                    <div class="footer-logo-wrap">
                        <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="130">
                    </div>
                    <div class="footer-divider"></div>
                    <div class="footer-text">
                        Greenwave System — Admin Notification<br>
                        Please log in to the admin dashboard to manage channel partners.
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