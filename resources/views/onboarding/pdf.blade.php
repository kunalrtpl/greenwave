<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Channel Partner Onboarding Form</title>
<style>
    /* mPDF-friendly CSS — avoid flex/grid; use tables + simple block layout */
    @page { margin: 18px 16px; }
    body  { font-family: 'DejaVu Sans', Arial, sans-serif; color: #1b3a2b; font-size: 11px; line-height: 1.5; }

    .doc-header        { background: #1b5e20; color: #ffffff; padding: 18px 20px; border-radius: 8px; }
    .doc-title         { font-size: 18px; font-weight: bold; margin: 0; }
    .doc-sub           { font-size: 11px; color: #c8e6c9; margin-top: 4px; }
    .doc-meta          { font-size: 10px; color: #a5d6a7; margin-top: 8px; }

    .type-pill         { display: inline-block; padding: 3px 12px; border-radius: 12px; font-size: 10px;
                         font-weight: bold; color: #ffffff; text-transform: uppercase; letter-spacing: .5px; }

    .section-title     { font-size: 12px; font-weight: bold; color: #1b5e20; text-transform: uppercase;
                         letter-spacing: .6px; border-bottom: 2px solid #43a047; padding-bottom: 5px;
                         margin: 22px 0 10px; }

    table.info         { width: 100%; border-collapse: collapse; border: 1px solid #c8e6c9; }
    table.info td      { padding: 8px 12px; border-bottom: 1px solid #e8f5e9; vertical-align: top; }
    table.info tr:last-child td { border-bottom: none; }
    td.label           { width: 34%; background: #f1f8e9; color: #558b2f; font-weight: bold;
                         font-size: 10px; text-transform: uppercase; letter-spacing: .3px; }
    td.value           { color: #1b3a2b; font-size: 11px; }
    td.value strong    { color: #1b5e20; }

    .docs td           { padding: 8px 12px; border-bottom: 1px solid #e8f5e9; font-size: 11px; }
    .ok                { color: #2e7d32; font-weight: bold; }
    .no                { color: #9e9e9e; }

    .declaration       { margin-top: 22px; background: #f1f8e9; border: 1px solid #c5e1a5;
                         border-left: 4px solid #558b2f; border-radius: 6px; padding: 12px 14px;
                         font-size: 10px; color: #33691e; line-height: 1.6; }

    .footer            { margin-top: 26px; text-align: center; font-size: 9px; color: #9e9e9e;
                         border-top: 1px solid #e0e0e0; padding-top: 10px; }
</style>
</head>
<body>

@php
    // dealer may arrive as model or array
    $d           = is_array($dealer ?? null) ? (object) $dealer : ($dealer ?? null);
    $isSub       = $isSubDealer ?? (($d->dealer_type ?? '') === 'sub dealer');
    $pTypeLabel  = $isSub ? 'Sub Dealer' : 'Primary Dealer';
    $pTypeColor  = $isSub ? '#d2691e' : '#6a1b9a';
    $bizName     = $d->business_name ?? ($d->name ?? 'N/A');
    $shortName   = $d->short_name ?? null;
    $contactName = $d->name ?? '—';
    $designation = $d->designation ?? null;
    $city        = $d->city ?? '—';
    $ownerMobile = $d->owner_mobile ?? null;
    $emailAddr   = $d->email ?? null;
    $terr        = $territory ?? '—';
    $submitted   = !empty($d->onboarding_submitted_at)
                    ? \Carbon\Carbon::parse($d->onboarding_submitted_at)->format('d M Y, h:i A')
                    : \Carbon\Carbon::now()->format('d M Y, h:i A');

    $docs = [
        'GST Certificate'  => $d->doc_gst_certificate ?? null,
        'PAN Card'         => $d->doc_pan_card         ?? null,
        'Cancelled Cheque' => $d->doc_cancelled_cheque ?? null,
        'Visiting Card'    => $d->doc_visiting_card    ?? null,
    ];
@endphp

{{-- ════════════ HEADER ════════════ --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td>
            <div class="doc-header">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td>
                            <p class="doc-title">Channel Partner Onboarding Form</p>
                            <div class="doc-sub">Greenwave System</div>
                            <div class="doc-meta">Submitted on {{ $submitted }}</div>
                        </td>
                        <td align="right" valign="top">
                            <span class="type-pill" style="background: {{ $pTypeColor }};">{{ $pTypeLabel }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- ════════════ PARTNER DETAILS ════════════ --}}
<div class="section-title">Partner Details</div>
<table class="info" cellpadding="0" cellspacing="0">
    <tr>
        <td class="label">Business Name</td>
        <td class="value"><strong>{{ $bizName }}</strong>@if($shortName) ({{ $shortName }})@endif</td>
    </tr>
    <tr>
        <td class="label">Partner Type</td>
        <td class="value">{{ $pTypeLabel }}</td>
    </tr>
    @if($isSub && !empty($linkedDealer))
    <tr>
        <td class="label">Parent Dealer</td>
        <td class="value">{{ $linkedDealer }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">Contact Person</td>
        <td class="value">{{ $contactName }}@if($designation) — {{ $designation }}@endif</td>
    </tr>
    <tr>
        <td class="label">City</td>
        <td class="value">{{ $city }}</td>
    </tr>
    @if($ownerMobile)
    <tr>
        <td class="label">Mobile</td>
        <td class="value">{{ $ownerMobile }}</td>
    </tr>
    @endif
    @if($emailAddr)
    <tr>
        <td class="label">Email</td>
        <td class="value">{{ $emailAddr }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">Territory</td>
        <td class="value">{{ $terr ?: '—' }}</td>
    </tr>
</table>

{{-- ════════════ BUSINESS & TAX (primary only) ════════════ --}}
@if(!$isSub)
<div class="section-title">Business &amp; Tax</div>
<table class="info" cellpadding="0" cellspacing="0">
    <tr>
        <td class="label">Constitution</td>
        <td class="value">{{ $d->business_constitution ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">GST No.</td>
        <td class="value"><strong>{{ $d->gst_no ?? '—' }}</strong></td>
    </tr>
    <tr>
        <td class="label">PAN No.</td>
        <td class="value"><strong>{{ $d->pan_no ?? '—' }}</strong></td>
    </tr>
    <tr>
        <td class="label">Billing Address</td>
        <td class="value">{{ $d->billing_address ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Shipping Address</td>
        <td class="value">{{ $d->shipping_address ?? '—' }}</td>
    </tr>
</table>

{{-- ════════════ ACCOUNTS CONTACT ════════════ --}}
@if(!empty($d->accounts_contact_person) || !empty($d->accounts_mobile) || !empty($d->accounts_email))
<div class="section-title">Accounts Contact</div>
<table class="info" cellpadding="0" cellspacing="0">
    @if(!empty($d->accounts_contact_person))
    <tr><td class="label">Contact Person</td><td class="value">{{ $d->accounts_contact_person }}</td></tr>
    @endif
    @if(!empty($d->accounts_mobile))
    <tr><td class="label">Mobile</td><td class="value">{{ $d->accounts_mobile }}</td></tr>
    @endif
    @if(!empty($d->accounts_email))
    <tr><td class="label">Email</td><td class="value">{{ $d->accounts_email }}</td></tr>
    @endif
</table>
@endif

{{-- ════════════ BANK DETAILS ════════════ --}}
<div class="section-title">Bank Details</div>
<table class="info" cellpadding="0" cellspacing="0">
    <tr>
        <td class="label">Bank Name</td>
        <td class="value">{{ $d->bank_name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Account Name</td>
        <td class="value">{{ $d->bank_account_name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Account No.</td>
        <td class="value">{{ $d->bank_account_number ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">IFSC</td>
        <td class="value">{{ $d->bank_ifsc ?? '—' }}</td>
    </tr>
</table>

{{-- ════════════ DOCUMENTS ════════════ --}}
<div class="section-title">Uploaded Documents</div>
<table class="info docs" cellpadding="0" cellspacing="0">
    @foreach($docs as $label => $path)
    <tr>
        <td class="label">{{ $label }}</td>
        <td class="value">
            @if(!empty($path))
                <span class="ok">&#10003; Uploaded</span>
            @else
                <span class="no">&mdash; Not provided</span>
            @endif
        </td>
    </tr>
    @endforeach
</table>
@endif

{{-- ════════════ DECLARATION ════════════ --}}
<div class="declaration">
    <strong>Declaration:</strong>
    The partner has confirmed that the information provided above is true and correct to the best of
    their knowledge, and accepted the onboarding terms
    @if(!empty($d->declaration_accepted_at))
        on {{ \Carbon\Carbon::parse($d->declaration_accepted_at)->format('d M Y, h:i A') }}.
    @else
        at the time of submission.
    @endif
</div>

{{-- ════════════ FOOTER ════════════ --}}
<div class="footer">
    This document was generated automatically by the Greenwave System.<br>
    {{ $bizName }} &middot; {{ $pTypeLabel }} &middot; Generated {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}
</div>

</body>
</html>