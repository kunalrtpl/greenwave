<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your OTP — Greenwave</title>
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
              <p style="margin:0 0 6px 0;font-size:20px;font-weight:bold;color:#ffffff;font-family:Arial,Helvetica,sans-serif;">One-Time Password (OTP)</p>
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
              <p style="margin:0 0 6px 0;font-size:14px;font-weight:bold;color:#1b5e20;font-family:Arial,Helvetica,sans-serif;">Hello,</p>
              <p style="margin:0;font-size:13px;color:#33691e;line-height:1.8;font-family:Arial,Helvetica,sans-serif;">
                To create your business profile in our system, a One-Time Password has been generated to verify your email
                <strong>{{ $identifier }}</strong>.
              </p>
            </td>
          </tr>
        </table>

      </td>
    </tr>

    {{-- ═══ OTP BOX ═══ --}}
    <tr>
      <td bgcolor="#ffffff" style="background-color:#ffffff;padding:24px 32px 8px 32px;">

        <p style="margin:0 0 10px 0;font-size:10px;font-weight:bold;color:#888888;text-transform:uppercase;letter-spacing:1px;text-align:center;font-family:Arial,Helvetica,sans-serif;">Your One-Time Password</p>

        <table width="100%" cellpadding="0" cellspacing="0" border="2" style="border:2px dashed #1a7f3c;border-radius:8px;background-color:#f1f8e9;">
          <tr>
            <td style="padding:28px 20px;text-align:center;background-color:#f1f8e9;">
              <p style="margin:0;font-size:44px;font-weight:900;color:#1a7f3c;letter-spacing:14px;font-family:'Courier New',Courier,monospace;line-height:1;">{{ $otp }}</p>
              <p style="margin:12px 0 0 0;font-size:12px;color:#777777;font-family:Arial,Helvetica,sans-serif;">
                This OTP expires in <strong style="color:#c62828;">{{ $expiresIn }}</strong> from the time of this email.
              </p>
            </td>
          </tr>
        </table>

      </td>
    </tr>

    {{-- ═══ SHARE INSTRUCTION ═══ --}}
    <tr>
      <td bgcolor="#ffffff" style="background-color:#ffffff;padding:16px 32px 0 32px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fff8e1;border-left:4px solid #f9a825;">
          <tr>
            <td style="padding:14px 18px;">
              <p style="margin:0 0 6px 0;font-size:11px;font-weight:bold;color:#b45309;text-transform:uppercase;letter-spacing:0.5px;font-family:Arial,Helvetica,sans-serif;">Action Required </p>
              <p style="margin:0;font-size:13px;color:#78350f;line-height:1.7;font-family:Arial,Helvetica,sans-serif;">
                Please share this OTP with the <strong>Greenwave Representative assisting you.</strong>
                <br><br>
                <strong>Do not share this OTP with anyone else.</strong>
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    {{-- ═══ SECURITY NOTICE ═══ --}}
    <tr>
      <td bgcolor="#ffffff" style="background-color:#ffffff;padding:16px 32px 28px 32px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fef2f2;border-left:4px solid #dc2626;">
          <tr>
            <td style="padding:14px 18px;">
              <p style="margin:0 0 6px 0;font-size:11px;font-weight:bold;color:#dc2626;text-transform:uppercase;letter-spacing:0.5px;font-family:Arial,Helvetica,sans-serif;">Security Notice</p>
              <p style="margin:0;font-size:12px;color:#7f1d1d;line-height:1.7;font-family:Arial,Helvetica,sans-serif;">
                Greenwave will <strong>never</strong> ask for your OTP via phone call, WhatsApp, or any other channel.
                This OTP is valid for <strong>single use only</strong> and will expire in {{ $expiresIn }}.
              </p>
            </td>
          </tr>
        </table>
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
  {{-- End wrapper --}}

</td>
</tr>
</table>

</body>
</html>