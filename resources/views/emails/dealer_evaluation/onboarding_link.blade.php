<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complete Your Onboarding — Greenwave</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f4f6f8">
<tr>
<td align="center" style="padding:30px 16px;">

  <table width="580" cellpadding="0" cellspacing="0" border="0" style="width:580px;max-width:580px;background:#ffffff;border-radius:8px;overflow:hidden;">

    {{-- ═══ HEADER ═══ --}}
    <tr>
      <td bgcolor="#1a7f3c" style="background-color:#1a7f3c;padding:28px 40px;text-align:center;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td align="center" style="padding-bottom:18px;">
              <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td bgcolor="#ffffff" style="background:#ffffff;padding:8px 22px;border-radius:8px;">
                    <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="140" height="auto" style="display:block;border:0;outline:none;">
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center">
              <p style="margin:0 0 6px 0;font-size:20px;font-weight:bold;color:#ffffff;font-family:Arial,Helvetica,sans-serif;">Partner Onboarding</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    {{-- ═══ BODY ═══ --}}
    <tr>
      <td bgcolor="#ffffff" style="background-color:#ffffff;padding:28px 32px 0 32px;">
        {{-- Greeting box --}}
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-left:4px solid #1a7f3c;background-color:#f1f8e9;">
          <tr>
            <td style="padding:16px 18px;">
              <p style="margin:0 0 6px 0;font-size:14px;font-weight:bold;color:#1b5e20;font-family:Arial,Helvetica,sans-serif;">Hello {{ $dealer['business_name'] ?? 'Partner' }},</p>
              <p style="margin:0;font-size:13px;color:#33691e;line-height:1.8;font-family:Arial,Helvetica,sans-serif;">
                We are excited to proceed with your registration. Please click the button below to access your secure onboarding portal and complete your business profile.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    {{-- ═══ LINK BOX ═══ --}}
    <tr>
      <td bgcolor="#ffffff" style="background-color:#ffffff;padding:32px 32px 16px 32px;text-align:center;">
        <a href="{{ $link }}" style="display:inline-block;background-color:#1a7f3c;color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none;padding:14px 32px;border-radius:6px;font-family:Arial,Helvetica,sans-serif;">
          Complete Onboarding
        </a>
        <p style="margin:16px 0 0 0;font-size:12px;color:#777777;font-family:Arial,Helvetica,sans-serif;">
          This link is strictly confidential and expires on <strong style="color:#c62828;">{{ $expires_at }}</strong>.
        </p>
      </td>
    </tr>

    {{-- ═══ FOOTER ═══ --}}
    <tr>
      <td bgcolor="#1a7f3c" style="background-color:#1a7f3c;padding:22px 32px;text-align:center;">
        <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 14px auto;">
          <tr>
            <td bgcolor="#ffffff" style="background:#ffffff;padding:6px 18px;border-radius:6px;">
              <img src="https://g2app.in/public/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="110" height="auto" style="display:block;border:0;">
            </td>
          </tr>
        </table>
        <p style="margin:0 0 8px 0;font-size:12px;color:rgba(255,255,255,0.75);line-height:1.8;font-family:Arial,Helvetica,sans-serif;">
          For any queries, please contact the Greenwave Sales Team.
        </p>
        <p style="margin:0;font-size:10px;color:rgba(255,255,255,0.45);font-family:Arial,Helvetica,sans-serif;">
          Automated message from Greenwave Partner Management System. Do not reply.
        </p>
      </td>
    </tr>

  </table>
</td>
</tr>
</table>

</body>
</html>