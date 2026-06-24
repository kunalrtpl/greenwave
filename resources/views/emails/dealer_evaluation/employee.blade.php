<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Channel Partner Evaluation</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  img { border: 0; display: block; }
  table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }

  body, .bg-page { background-color: #e8f5e9; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust: 100%; }
  .wrapper { max-width: 660px; width: 100%; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,100,0,0.14); }

  /* ── Header ── */
  .header-new    { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 55%, #388e3c 100%); padding: 40px 32px 32px; text-align: center; }
  .header-update { background: linear-gradient(160deg, #78350f 0%, #b45309 55%, #d97706 100%); padding: 40px 32px 32px; text-align: center; }
  .header-divider { width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 24px; border-radius: 2px; }
  .header-title  { font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 8px; }
  .header-sub    { font-size: 13px; color: rgba(255,255,255,0.65); margin-top: 6px; }

  /* ── Ribbon ── */
  .ribbon-new    { background: #43a047; padding: 12px 32px; }
  .ribbon-update { background: #c2770a; padding: 12px 32px; }
  .ribbon-text   { font-size: 13px; color: #fff; font-weight: 600; }
  .ribbon-badge-new    { display: inline-block; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.45); border-radius: 20px; padding: 3px 14px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }
  .ribbon-badge-update { display: inline-block; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.45); border-radius: 20px; padding: 3px 14px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

  /* ── Greeting Box ── */
  .section { padding: 24px 32px 0; }
  .greeting-box-new    { background: #f1f8e9; border: 1px solid #c5e1a5; border-left: 5px solid #558b2f; border-radius: 10px; padding: 20px 22px; }
  .greeting-box-update { background: #fff7ed; border: 1px solid #fed7aa; border-left: 5px solid #c2770a; border-radius: 10px; padding: 20px 22px; }
  .greeting-name-new    { font-size: 15px; color: #33691e; font-weight: 700; margin-bottom: 8px; }
  .greeting-name-update { font-size: 15px; color: #92400e; font-weight: 700; margin-bottom: 8px; }
  .greeting-body-new    { font-size: 13px; color: #558b2f; line-height: 1.8; }
  .greeting-body-update { font-size: 13px; color: #b45309; line-height: 1.8; }

  /* ── Info Table ── */
  .info-table { width: 100%; border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden; }
  .info-table-header-new    { background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%); }
  .info-table-header-update { background: linear-gradient(135deg, #b45309 0%, #d97706 100%); }
  .info-table-header-new td, .info-table-header-update td { padding: 12px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.8px; }
  .info-label { padding: 11px 16px; font-size: 11px; color: #558b2f; font-weight: 600; text-transform: uppercase; width: 38%; background: #f9fbe7; border-bottom: 1px solid #f1f8e9; }
  .info-value { padding: 11px 16px; font-size: 13px; color: #1b5e20; font-weight: 600; border-bottom: 1px solid #f1f8e9; }
  .info-label-last { padding: 11px 16px; font-size: 11px; color: #558b2f; font-weight: 600; text-transform: uppercase; width: 38%; background: #f9fbe7; }
  .info-value-last { padding: 11px 16px; font-size: 13px; color: #1b5e20; font-weight: 600; }

  /* ── Badge ── */
  .badge-new    { display: inline-block; background: #e8f5e9; color: #1a7f3c; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #a5d6a7; }
  .badge-update { display: inline-block; background: #fef3c7; color: #b45309; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #fcd34d; }
  .badge-dark   { display: inline-block; background: #1b5e20; color: #fff; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; }

  /* ── Section Label ── */
  .section-label-bar { display: inline-block; width: 4px; height: 16px; background: #43a047; border-radius: 2px; vertical-align: middle; margin-right: 10px; }
  .section-label-bar-update { display: inline-block; width: 4px; height: 16px; background: #c2770a; border-radius: 2px; vertical-align: middle; margin-right: 10px; }
  .section-label-text { font-size: 12px; font-weight: 700; color: #2e7d32; text-transform: uppercase; letter-spacing: 0.8px; vertical-align: middle; }
  .section-label-text-update { font-size: 12px; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.8px; vertical-align: middle; }

  /* ── Note Box ── */
  .note-new    { background: #f1f8e9; border: 1px solid #c5e1a5; border-left: 5px solid #43a047; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #33691e; line-height: 1.8; }
  .note-update { background: #fff7ed; border: 1px solid #fed7aa; border-left: 5px solid #c2770a; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #92400e; line-height: 1.8; }

  /* ── Footer ── */
  .footer { background: linear-gradient(160deg, #1b5e20 0%, #2e7d32 100%); border-radius: 12px; padding: 28px 32px; text-align: center; }
  .footer-logo-wrap { display: inline-block; background: #ffffff; border-radius: 10px; padding: 8px 20px; box-shadow: 0 3px 12px rgba(0,0,0,0.15); margin-bottom: 18px; }
  .footer-divider { width: 40px; height: 1px; background: rgba(255,255,255,0.25); margin: 0 auto 16px; }
  .footer-text { font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.8; margin-bottom: 12px; }
  .footer-note { font-size: 11px; color: rgba(255,255,255,0.4); letter-spacing: 0.3px; }
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
    <td class="{{ ($is_new ?? true) ? 'header-new' : 'header-update' }}">

      {{-- Logo --}}
      <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto 28px;">
        <tr>
          <td align="center" style="background: #ffffff; border-radius: 14px; padding: 12px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.18);">
            <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="160">
          </td>
        </tr>
      </table>

      <div class="header-divider"></div>

      {{-- Icon --}}
      <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 0 auto 18px;">
        <tr>
          <td align="center" style="font-size: 48px; line-height: 1;">
            {{ ($is_new ?? true) ? '🤝' : '✏️' }}
          </td>
        </tr>
      </table>

      <div class="header-title">
        @if($is_new ?? true)
          New Prospective Channel Partner
        @else
          Channel Partner Evaluation Updated
        @endif
      </div>
      <div class="header-sub">{{ $submittedAt }}</div>

    </td>
  </tr>

  {{-- ══════════════════════════════════ --}}
  {{-- RIBBON                            --}}
  {{-- ══════════════════════════════════ --}}
  <tr>
    <td class="{{ ($is_new ?? true) ? 'ribbon-new' : 'ribbon-update' }}">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="ribbon-text">
            {{ ($is_new ?? true) ? '🤝' : '✏️' }}
            &nbsp;
            {{ ($is_new ?? true) ? 'Firm: ' : 'Updated Firm: ' }}
            <strong>{{ $dealer['business_name'] }}</strong>
            &mdash; {{ $dealer['city'] }}
          </td>
          <td align="right">
            @if($is_new ?? true)
              <span class="ribbon-badge-new">NEW SUBMISSION</span>
            @else
              <span class="ribbon-badge-update">UPDATED</span>
            @endif
          </td>
        </tr>
      </table>
    </td>
  </tr>

  {{-- ══════════════════════════════════ --}}
  {{-- GREETING                          --}}
  {{-- ══════════════════════════════════ --}}
  <tr>
    <td class="section" style="padding-top: 28px;">
      <div class="{{ ($is_new ?? true) ? 'greeting-box-new' : 'greeting-box-update' }}">
        <div class="{{ ($is_new ?? true) ? 'greeting-name-new' : 'greeting-name-update' }}">
          Dear {{ $employee['name'] ?? 'Team' }},
        </div>
        <div class="{{ ($is_new ?? true) ? 'greeting-body-new' : 'greeting-body-update' }}">
          @if($is_new ?? true)
            Your <strong>Prospective Channel Partner Evaluation</strong> for
            <strong>{{ $dealer['business_name'] }}</strong> has been <strong>successfully submitted</strong>.
            Our team will review the evaluation and get back to you with the next steps.
          @else
            Your <strong>updated Channel Partner Evaluation</strong> for
            <strong>{{ $dealer['business_name'] }}</strong> has been <strong>successfully saved</strong>.
            The revised form is attached as PDF for your reference.
            Our team will review the updated information and revert accordingly.
          @endif
        </div>
      </div>
    </td>
  </tr>

  {{-- ══════════════════════════════════ --}}
  {{-- DEALER INFORMATION                --}}
  {{-- ══════════════════════════════════ --}}
  <tr>
    <td class="section" style="padding-top: 22px;">

      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
        <tr>
          <td>
            <span class="{{ ($is_new ?? true) ? 'section-label-bar' : 'section-label-bar-update' }}"></span>
            <span class="{{ ($is_new ?? true) ? 'section-label-text' : 'section-label-text-update' }}">Dealer Information</span>
          </td>
        </tr>
      </table>

      <table class="info-table" cellpadding="0" cellspacing="0" border="0">
        <tr class="{{ ($is_new ?? true) ? 'info-table-header-new' : 'info-table-header-update' }}">
          <td colspan="2">🏢 &nbsp;Basic Details</td>
        </tr>
        <tr>
          <td class="info-label">Firm Name</td>
          <td class="info-value"><strong>{{ $dealer['business_name'] }}</strong></td>
        </tr>
        <tr>
          <td class="info-label">Contact Person</td>
          <td class="info-value">{{ $dealer['name'] ?? '—' }}</td>
        </tr>
        <tr>
          <td class="info-label">Mobile</td>
          <td class="info-value">{{ $dealer['owner_mobile'] }}</td>
        </tr>
        <tr>
          <td class="info-label">Email</td>
          <td class="info-value">{{ $dealer['email'] ?: '—' }}</td>
        </tr>
        <tr>
          <td class="info-label">City</td>
          <td class="info-value">{{ $dealer['city'] }}</td>
        </tr>
        <tr>
          <td class="info-label">Source of Lead</td>
          <td class="info-value">
            <span class="{{ ($is_new ?? true) ? 'badge-new' : 'badge-update' }}">
              {{ $dealer['source_of_lead'] ?? '—' }}
            </span>
          </td>
        </tr>
        <tr>
          <td class="info-label-last">{{ ($is_new ?? true) ? 'Submitted By' : 'Updated By' }}</td>
          <td class="info-value-last">{{ $submittedBy['name'] ?? '—' }}</td>
        </tr>
      </table>

    </td>
  </tr>

  {{-- ══════════════════════════════════ --}}
  {{-- SUBMISSION DETAILS                --}}
  {{-- ══════════════════════════════════ --}}
  <tr>
    <td class="section" style="padding-top: 22px;">

      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
        <tr>
          <td>
            <span class="{{ ($is_new ?? true) ? 'section-label-bar' : 'section-label-bar-update' }}"></span>
            <span class="{{ ($is_new ?? true) ? 'section-label-text' : 'section-label-text-update' }}">Submission Details</span>
          </td>
        </tr>
      </table>

      <table class="info-table" cellpadding="0" cellspacing="0" border="0">
        <tr class="{{ ($is_new ?? true) ? 'info-table-header-new' : 'info-table-header-update' }}">
          <td colspan="2">📋 &nbsp;{{ ($is_new ?? true) ? 'Submission Info' : 'Update Info' }}</td>
        </tr>
        <tr>
          <td class="info-label">{{ ($is_new ?? true) ? 'Submitted By' : 'Updated By' }}</td>
          <td class="info-value">{{ $submittedBy['name'] ?? '—' }}</td>
        </tr>
        <tr>
          <td class="info-label-last">{{ ($is_new ?? true) ? 'Submitted At' : 'Updated At' }}</td>
          <td class="info-value-last">
            <span class="badge-dark">{{ $submittedAt }}</span>
          </td>
        </tr>
      </table>

    </td>
  </tr>

  {{-- ══════════════════════════════════ --}}
  {{-- NOTE BOX                          --}}
  {{-- ══════════════════════════════════ --}}
  <tr>
    <td class="section" style="padding-top: 22px; padding-bottom: 28px;">
      @if($is_new ?? true)
        <div class="note-new">
          📎 &nbsp;The complete evaluation form is attached as PDF for your records.
          If you need to make any changes, you can update the evaluation from the app.
          For any queries, feel free to reach out to the Greenwave Team.
        </div>
      @else
        <div class="note-update">
          📎 &nbsp;The updated evaluation form is attached as PDF for your records.
          If further changes are needed, you can continue editing from the app.
          For any queries, feel free to reach out to the Greenwave Team.
        </div>
      @endif
    </td>
  </tr>

  {{-- ══════════════════════════════════ --}}
  {{-- FOOTER                            --}}
  {{-- ══════════════════════════════════ --}}
  <tr>
    <td style="padding: 0 32px 32px;">
      <div class="footer">
        <div class="footer-logo-wrap">
          <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="130">
        </div>
        <div class="footer-divider"></div>
        <div class="footer-text">
          For any queries regarding this evaluation, please contact the Greenwave Team.
        </div>
        <div class="footer-note">This is an automated message from Greenwave Partner Management System. Please do not reply.</div>
      </div>
    </td>
  </tr>

</table>
</td>
</tr>
</table>

</body>
</html>