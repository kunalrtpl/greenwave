<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Weekly Work Report</title>
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
        .logo-pill        { display: inline-block; background: #ffffff; border-radius: 14px; padding: 12px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.18); margin-bottom: 28px; }
        .header-divider   { width: 48px; height: 2px; background: rgba(255,255,255,0.3); margin: 0 auto 22px; border-radius: 2px; }
        .header-title     { font-size: 23px; font-weight: 700; color: #ffffff; letter-spacing: 0.3px; margin-bottom: 10px; }
        .header-date      { font-size: 13px; color: rgba(255,255,255,0.6); }

        /* ── Ribbon ── */
        .ribbon           { background: #43a047; padding: 12px 32px; }
        .ribbon-ref       { font-size: 13px; color: #fff; font-weight: 600; }
        .ribbon-badge     { display: inline-block; background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); border-radius: 20px; padding: 3px 14px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── Sections ── */
        .section          { padding: 24px 32px 0; }

        /* ── Greeting Box ── */
        .greeting-box     { background: #f1f8e9; border: 1px solid #c5e1a5; border-left: 5px solid #558b2f; border-radius: 10px; padding: 20px 22px; }
        .greeting-name    { font-size: 15px; color: #33691e; font-weight: 700; margin-bottom: 8px; }
        .greeting-body    { font-size: 13px; color: #558b2f; line-height: 1.8; }

        /* ── No-activity Box ── */
        .noact-box        { background: #fff9e6; border: 1px solid #fde68a; border-left: 5px solid #f59e0b; border-radius: 10px; padding: 20px 22px; }
        .noact-title      { font-size: 14px; font-weight: 700; color: #92400e; margin-bottom: 6px; }
        .noact-body       { font-size: 13px; color: #78350f; line-height: 1.7; }

        /* ── Week at a Glance Table ── */
        .info-table               { width: 100%; border: 1px solid #c8e6c9; border-radius: 10px; overflow: hidden; }
        .info-table-header        { background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%); }
        .info-table-header td     { padding: 14px 16px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.8px; text-align: center; }
        .glance-cell              { width: 25%; text-align: center; padding: 18px 8px 16px; background: #f9fbe7; border-right: 1px solid #e8f5e9; }
        .glance-cell-last         { border-right: none; }
        .glance-num               { font-size: 26px; font-weight: 800; color: #1b5e20; line-height: 1; }
        .glance-sub               { font-size: 14px; font-weight: 700; color: #81c784; }
        .glance-lbl               { font-size: 10px; font-weight: 700; color: #81c784; text-transform: uppercase; letter-spacing: 0.6px; margin-top: 6px; }

        /* ── Attachment Box ── */
        .attach-box               { background: #f1f8e9; border: 1px solid #c5e1a5; border-radius: 10px; padding: 18px 22px; }
        .attach-title             { font-size: 13px; font-weight: 700; color: #33691e; margin-bottom: 5px; }
        .attach-body              { font-size: 12px; color: #558b2f; line-height: 1.7; }

        /* ── Sign off ── */
        .signoff                  { font-size: 13px; color: #33691e; line-height: 1.8; }

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
    $hasActivity = isset($hasActivity) ? $hasActivity : true;
    $counts      = isset($counts) ? $counts : ['tasks' => 0, 'visits' => 0, 'work_notes' => 0, 'active_days' => 0, 'upcoming_tasks' => 0];
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="bg-page">
<tr>
<td align="center" style="padding: 36px 16px;">

    <table cellpadding="0" cellspacing="0" border="0" class="wrapper">

        {{-- ══════════════════════════════════ --}}
        {{-- HEADER                             --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="header">
                <div class="logo-pill">
                    <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="180">
                </div>
                <div class="header-divider"></div>
                <div class="header-title">Weekly Work Report</div>
                <div class="header-date">Week of {{ $weekRangeDisplay }}</div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- RIBBON                             --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="ribbon">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="ribbon-ref">● &nbsp;Employee: <strong>{{ $employee['name'] }}</strong></td>
                        <td align="right">
                            <span class="ribbon-badge">This Week: {{ $upWeekDisplay }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- GREETING                           --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <div class="greeting-box">
                    <div class="greeting-name">Hi {{ $employee['name'] }},</div>
                    <div class="greeting-body">
                        @if($hasActivity)
                            Here's your <strong>Weekly Work Report for {{ $weekRangeDisplay }}</strong>, attached
                            with this email. It brings together your entire week — scheduled tasks, customer
                            visits, and other key developments, organised day by day — along with your
                            <strong>upcoming tasks for this week ({{ $upWeekDisplay }})</strong>, so you can
                            review the week gone by and plan the one ahead with complete clarity.
                        @else
                            Please find attached your <strong>Weekly Work Report for {{ $weekRangeDisplay }}</strong>,
                            along with your <strong>upcoming tasks for this week ({{ $upWeekDisplay }})</strong>.
                            Take a moment to review your plan and make every visit count.
                        @endif
                    </div>
                </div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- NO ACTIVITY NOTICE                 --}}
        {{-- ══════════════════════════════════ --}}
        @if(!$hasActivity)
        <tr>
            <td class="section">
                <div class="noact-box">
                    <div class="noact-title">⚠ No work activity recorded</div>
                    <div class="noact-body">
                        No scheduled tasks, customer visits, or other developments were found against your
                        account for the week of <strong>{{ $weekRangeDisplay }}</strong>. If you did work
                        during this week, please make sure your DVRs and work notes are entered in the
                        system, or reach out to your reporting manager.
                    </div>
                </div>
            </td>
        </tr>
        @endif

        {{-- ══════════════════════════════════ --}}
        {{-- WEEK AT A GLANCE                   --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <table class="info-table" cellpadding="0" cellspacing="0" border="0">
                    <tr class="info-table-header">
                        <td colspan="4">◉ &nbsp;Your Week at a Glance</td>
                    </tr>
                    <tr>
                        <td class="glance-cell">
                            <div class="glance-num">{{ $counts['tasks'] }}</div>
                            <div class="glance-lbl">Scheduled Tasks</div>
                        </td>
                        <td class="glance-cell">
                            <div class="glance-num">{{ $counts['visits'] }}</div>
                            <div class="glance-lbl">Customer Visits</div>
                        </td>
                        <td class="glance-cell">
                            <div class="glance-num">{{ $counts['work_notes'] }}</div>
                            <div class="glance-lbl">Other Developments</div>
                        </td>
                        <td class="glance-cell glance-cell-last">
                            <div class="glance-num">{{ $counts['active_days'] }}<span class="glance-sub">/7</span></div>
                            <div class="glance-lbl">Active Days</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- ATTACHMENT NOTE                    --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <div class="attach-box">
                    <div class="attach-title">📎 Your detailed weekly report is attached (PDF)</div>
                    <div class="attach-body">
                        The attachment covers your complete week day by day — every visit sheet with
                        contacts met, products discussed, visit details and next plans — plus work notes
                        and this week's task list.
                    </div>
                </div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- SIGN OFF                           --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section">
                <div class="signoff">
                    @if($counts['upcoming_tasks'] > 0)
                        You have <strong>{{ $counts['upcoming_tasks'] }} {{ $counts['upcoming_tasks'] == 1 ? 'task' : 'tasks' }}</strong>
                        lined up for this week — wishing you a focused and productive week ahead. Good luck!
                    @else
                        Wishing you a focused and productive week ahead. Good luck!
                    @endif
                    <br><br>
                    Best regards,<br>
                    <strong>Team Greenwave</strong>
                </div>
            </td>
        </tr>

        {{-- ══════════════════════════════════ --}}
        {{-- FOOTER                             --}}
        {{-- ══════════════════════════════════ --}}
        <tr>
            <td class="section" style="padding-bottom: 32px;">
                <div class="footer">
                    <div class="footer-logo-wrap">
                        <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" width="130">
                    </div>
                    <div class="footer-divider"></div>
                    <div class="footer-text">
                        Greenwave System — Weekly Work Report<br>
                        Generated automatically from your DVRs, work notes and task scheduler.
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
