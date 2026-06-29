<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #1e293b;
    background: #ffffff;
    line-height: 1.5;
}

/* ═══════════════════════════════════════
   HEADER
═══════════════════════════════════════ */
.hdr-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
}
.hdr-left  { vertical-align: middle; text-align: left; }
.hdr-right { vertical-align: middle; text-align: right; }

.logo-img { width: 150px; height: auto; }

.hdr-doc-type {
    font-size: 13px;
    font-weight: bold;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 4px;
}
.hdr-date {
    font-size: 8px;
    color: #64748b;
    margin-top: 2px;
}
.hdr-pill {
    display: inline-block;
    background: #1a7f3c;
    color: #ffffff;
    border-radius: 3px;
    padding: 2px 9px;
    font-size: 8px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: .6px;
    margin-top: 5px;
}
.hdr-pill.sub { background: #d2691e; }

/* ═══════════════════════════════════════
   SECTION HEADER BAR
═══════════════════════════════════════ */
.section-bar {
    background: #1a7f3c;
    color: #ffffff;
    font-size: 8px;
    font-weight: bold;
    padding: 7px 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* ═══════════════════════════════════════
   DATA ROWS
═══════════════════════════════════════ */
.main-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
    /* prevents mPDF from breaking the section across multiple pages */
    page-break-inside: avoid;
}

.label-cell {
    width: 40%;
    padding: 8px 12px;
    border-bottom: 1px solid #e2e8f0;
    color: #475569;
    font-weight: bold;
    font-size: 8px;
    vertical-align: top;
    background: #f8fafc;
}

.value-cell {
    padding: 8px 12px;
    border-bottom: 1px solid #e2e8f0;
    color: #1e293b;
    font-size: 8.5px;
    vertical-align: top;
    background: #ffffff;
}

.firm-name {
    font-weight: bold;
    color: #0f172a;
}

/* ═══════════════════════════════════════
   CHIPS / STATUS
═══════════════════════════════════════ */
.chip-lead {
    display: inline-block;
    background: #e8f5e9;
    color: #1a7f3c;
    border: 1px solid #a5d6a7;
    border-radius: 3px;
    padding: 2px 8px;
    font-size: 8px;
    font-weight: bold;
}

.doc-ok {
    color: #1a7f3c;
    font-weight: bold;
    font-size: 8.5px;
}
.doc-no {
    color: #94a3b8;
    font-style: italic;
    font-size: 8px;
}

.not-provided {
    color: #94a3b8;
    font-style: italic;
    font-size: 8px;
}

/* ═══════════════════════════════════════
   DECLARATION
═══════════════════════════════════════ */
.declaration {
    margin-top: 4px;
    margin-bottom: 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #1a7f3c;
    padding: 9px 12px;
    font-size: 8px;
    color: #475569;
    line-height: 1.6;
    page-break-inside: avoid;
}
.declaration strong { color: #334155; }

/* ═══════════════════════════════════════
   FOOTER
═══════════════════════════════════════ */
.footer-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-top: 1px solid #cbd5e1;
    padding-top: 6px;
}
.footer-left  { font-size: 7.5px; font-weight: bold; color: #334155; }
.footer-mid   { font-size: 7px; color: #64748b; text-align: center; }
.footer-right { font-size: 7px; color: #64748b; text-align: right; }
</style>
</head>
<body>

@php
    // dealer may arrive as model or array
    $d           = is_array($dealer ?? null) ? (object) $dealer : ($dealer ?? null);
    $isSub       = $isSubDealer ?? (($d->dealer_type ?? '') === 'sub dealer');
    $pTypeLabel  = $isSub ? 'Sub Dealer' : 'Primary Dealer';
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

{{-- ── HEADER ── --}}
<table class="hdr-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="hdr-left">
            <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" class="logo-img" />
        </td>
        <td class="hdr-right">
            <div class="hdr-doc-type">Channel Partner Onboarding</div>
            <div class="hdr-date"><strong>Submitted On:</strong> {{ $submitted }}</div>
            <span class="hdr-pill {{ $isSub ? 'sub' : '' }}">{{ $pTypeLabel }}</span>
        </td>
    </tr>
</table>

{{-- ── SECTION A — PARTNER DETAILS ── --}}
<table class="main-table" cellspacing="0" cellpadding="0">
    <tr>
        <td colspan="2" class="section-bar">Section A &ndash; Partner Details</td>
    </tr>
    <tr>
        <td class="label-cell">Business Name</td>
        <td class="value-cell firm-name">{{ $bizName }}@if($shortName) ({{ $shortName }})@endif</td>
    </tr>
    <tr>
        <td class="label-cell">Partner Type</td>
        <td class="value-cell">{{ $pTypeLabel }}</td>
    </tr>
    @if($isSub && !empty($linkedDealer))
    <tr>
        <td class="label-cell">Parent Dealer</td>
        <td class="value-cell">{{ $linkedDealer }}</td>
    </tr>
    @endif
    <tr>
        <td class="label-cell">Contact Person</td>
        <td class="value-cell">{{ $contactName }}@if($designation) &mdash; {{ $designation }}@endif</td>
    </tr>
    <tr>
        <td class="label-cell">Mobile Number</td>
        <td class="value-cell">{{ $ownerMobile ?: '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">Email</td>
        <td class="value-cell">{{ $emailAddr ?: '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">City</td>
        <td class="value-cell">{{ $city }}</td>
    </tr>
    <tr>
        <td class="label-cell">Territory Covered</td>
        <td class="value-cell">
            <span class="chip-lead">{{ $terr ?: '—' }}</span>
        </td>
    </tr>
</table>

{{-- ── SECTION B — BUSINESS & TAX (primary only) ── --}}
@if(!$isSub)
<table class="main-table" cellspacing="0" cellpadding="0">
    <tr>
        <td colspan="2" class="section-bar">Section B &ndash; Business &amp; Tax</td>
    </tr>
    <tr>
        <td class="label-cell">Business Constitution</td>
        <td class="value-cell">{{ $d->business_constitution ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">GST No.</td>
        <td class="value-cell firm-name">{{ $d->gst_no ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">PAN No.</td>
        <td class="value-cell firm-name">{{ $d->pan_no ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">Billing Address</td>
        <td class="value-cell">{{ $d->billing_address ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">Shipping Address</td>
        <td class="value-cell">{{ $d->shipping_address ?? '—' }}</td>
    </tr>
</table>

{{-- ── SECTION C — ACCOUNTS CONTACT ── --}}
@if(!empty($d->accounts_contact_person) || !empty($d->accounts_mobile) || !empty($d->accounts_email))
<table class="main-table" cellspacing="0" cellpadding="0">
    <tr>
        <td colspan="2" class="section-bar">Section C &ndash; Accounts Contact</td>
    </tr>
    @if(!empty($d->accounts_contact_person))
    <tr>
        <td class="label-cell">Contact Person</td>
        <td class="value-cell">{{ $d->accounts_contact_person }}</td>
    </tr>
    @endif
    @if(!empty($d->accounts_mobile))
    <tr>
        <td class="label-cell">Mobile</td>
        <td class="value-cell">{{ $d->accounts_mobile }}</td>
    </tr>
    @endif
    @if(!empty($d->accounts_email))
    <tr>
        <td class="label-cell">Email</td>
        <td class="value-cell">{{ $d->accounts_email }}</td>
    </tr>
    @endif
</table>
@endif

{{-- ── SECTION D — BANK DETAILS ── --}}
<table class="main-table" cellspacing="0" cellpadding="0">
    <tr>
        <td colspan="2" class="section-bar">Section D &ndash; Bank Details</td>
    </tr>
    <tr>
        <td class="label-cell">Bank Name</td>
        <td class="value-cell">{{ $d->bank_name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">Account Name</td>
        <td class="value-cell">{{ $d->bank_account_name ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">Account No.</td>
        <td class="value-cell">{{ $d->bank_account_number ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label-cell">IFSC</td>
        <td class="value-cell">{{ $d->bank_ifsc ?? '—' }}</td>
    </tr>
</table>

{{-- ── SECTION E — UPLOADED DOCUMENTS ── --}}
<table class="main-table" cellspacing="0" cellpadding="0">
    <tr>
        <td colspan="2" class="section-bar">Section E &ndash; Uploaded Documents</td>
    </tr>
    @foreach($docs as $label => $path)
    <tr>
        <td class="label-cell">{{ $label }}</td>
        <td class="value-cell">
            @if(!empty($path))
                <span class="doc-ok">&#10003; Uploaded</span>
            @else
                <span class="doc-no">&mdash; Not provided</span>
            @endif
        </td>
    </tr>
    @endforeach
</table>
@endif

{{-- ── DECLARATION ── --}}
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

{{-- ── FOOTER ── --}}
<table class="footer-table" cellspacing="0" cellpadding="0">
    <tr>
        <td class="footer-left">Greenwave &bull; Channel Partner Onboarding</td>
        <td class="footer-mid">Confidential &mdash; Internal Use Only</td>
        <td class="footer-right">{{ $submitted }}</td>
    </tr>
</table>

</body>
</html>