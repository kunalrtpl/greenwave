<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>App Manual - {{ $user->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #2d2d2d;
            background: #ffffff;
        }

        /* =====================================================
           PAGE MARGINS — reserve space for running header/footer
        ===================================================== */
        @page {
            margin-top: 85px;    /* Increased slightly to ensure clearance */
            margin-bottom: 50px; 
        }

        /* =====================================================
           RUNNING HEADER — Fixed position for all pages
        ===================================================== */
        .running-header {
            position: fixed;
            top: -72px;           /* Adjusted to sit comfortably in the margin */
            left: 0;
            right: 0;
            height: 40px;
            background-color: #f0faf5;
            border-bottom: 2px solid #c5ead8;
            padding: 0 30px;
        }

        .rh-inner {
            width: 100%;
            border-collapse: collapse;
            height: 40px;
        }

        .rh-inner td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .rh-logo img  { height: 24px; width: auto; }
        .rh-title     { font-size: 10px; font-weight: bold; color: #1a7a4a; padding-left: 10px; }
        .rh-continued { font-size: 9px; color: #888888; text-align: right; }

        /* =====================================================
           RUNNING FOOTER — every page
        ===================================================== */
        .running-footer {
            position: fixed;
            bottom: -38px;
            left: 0;
            right: 0;
            height: 36px;
            background-color: #1a7a4a;
            text-align: center;
            padding-top: 10px;
        }

        .running-footer span {
            font-size: 9px;
            color: #a8e8c4;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .running-footer .dot { color: #7de0ad; margin: 0 6px; }

        /* =====================================================
           PAGE 1 HEADER ELEMENTS
        ===================================================== */
        .logo-bar {
            text-align: center;
            padding: 16px 40px 14px 40px;
            border-bottom: 2px solid #e0f2ea;
            background: #ffffff;
            position: relative; /* Sits over the fixed header on page 1 */
            z-index: 10;
        }

        .logo-bar img { height: 46px; width: auto; }

        .header {
            background-color: #1a7a4a;
            padding: 26px 40px 24px 40px;
        }

        .header h1 {
            font-size: 22px;
            color: #ffffff;
            font-weight: bold;
            letter-spacing: 1.2px;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 10px;
            color: #a8e8c4;
        }

        .accent-bar {
            background-color: #25a865;
            height: 5px;
        }

        .user-strip {
            background-color: rgba(255,255,255,0.12);
            border-left: 4px solid #7de0ad;
            padding: 12px 18px;
            margin-top: 18px;
        }

        .user-strip .label {
            font-size: 8px;
            color: #7de0ad;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }

        .user-strip .name  { font-size: 15px; font-weight: bold; color: #ffffff; }
        .user-strip .email { font-size: 10px; color: #c5f0dc; margin-top: 2px; }

        .section-wrap {
            padding: 24px 40px 10px 40px;
        }

        .section-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #1a7a4a;
            margin-bottom: 4px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
        }

        .section-divider {
            height: 2px;
            background-color: #e0f2ea;
            margin-top: 10px;
        }

        .summary-badge {
            background-color: #1a7a4a;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 3px 12px;
            border-radius: 12px;
            float: right;
            margin-top: -2px;
        }

        /* =====================================================
           TIMELINE & FEATURE TABLES
        ===================================================== */
        .timeline {
            padding: 30px 40px 10px 40px; /* Buffer for content start */
        }

        .feature-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            margin-top: 20px; /* Added margin to push item 6 away from page top */
        }

        .col-left {
            width: 50px;
            vertical-align: top;
            padding: 0;
        }

        .col-right {
            vertical-align: top;
            padding: 0 0 18px 0;
        }

        .step-circle {
            width: 34px;
            height: 34px;
            background-color: #1a7a4a;
            border-radius: 4px;
            text-align: center;
            padding-top: 7px;
            font-size: 13px;
            font-weight: bold;
            color: #ffffff;
            margin: 0 auto;
        }

        .connector-cell {
            text-align: center;
            padding-top: 0;
        }

        .connector-line {
            width: 2px;
            background-color: #b8dfc9;
            margin: 0 auto;
        }

        .feature-card {
            background-color: #f7fdf9;
            border: 1px solid #c8e8d5;
            border-left: 4px solid #1a7a4a;
            border-radius: 3px;
            padding: 13px 18px;
        }

        .feature-name {
            font-size: 13px;
            font-weight: bold;
            color: #1a7a4a;
            margin-bottom: 7px;
        }

        .feature-tag {
            background-color: #dff4ea;
            color: #137a3e;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 10px;
            margin-left: 8px;
        }

        .feature-desc {
            font-size: 11.5px;
            color: #4a4a4a;
            line-height: 1.75;
        }

        .no-roles {
            padding: 40px;
            text-align: center;
            color: #aaaaaa;
        }
    </style>
</head>
<body>

    <!-- RUNNING HEADER -->
    <div class="running-header">
        <table class="rh-inner">
            <tr>
                <td class="rh-logo" style="width:140px;">
                    <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave" />
                </td>
                <td class="rh-title">
                    App Feature Manual &nbsp;&mdash;&nbsp; {{ $user->name }}
                </td>
                <td class="rh-continued" style="width:90px;">
                    Continued&hellip;
                </td>
            </tr>
        </table>
    </div>

    <!-- RUNNING FOOTER -->
    <div class="running-footer">
        <span>
            Greenwave
            <span class="dot">&bull;</span>
            Confidential
            <span class="dot">&bull;</span>
            {{ date('Y') }}
        </span>
    </div>

    <!-- PAGE 1 ONLY HEADER -->
    <div class="logo-bar">
        <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave Logo" />
    </div>

    <div class="header">
        <h1>App Feature Manual</h1>
        <div class="subtitle">Personalised guide to your enabled features &nbsp;&bull;&nbsp; Generated {{ date('d M Y, h:i A') }}</div>

        <div class="user-strip">
            <div class="label">Prepared For</div>
            <div class="name">{{ $user->name }}</div>
            @if(!empty($user->email))
                <div class="email">{{ $user->email }}</div>
            @endif
        </div>
    </div>
    <div class="accent-bar"></div>

    <div class="section-wrap">
        <div class="section-label">Feature Guide</div>
        <div class="section-title">
            Assigned App Features
            @if($roles->count() > 0)
                <span class="summary-badge">{{ $roles->count() }} Feature{{ $roles->count() > 1 ? 's' : '' }}</span>
            @endif
        </div>
        <div class="section-divider"></div>
    </div>

    <!-- TIMELINE LIST -->
    <div class="timeline">
        @if($roles->count() > 0)
            @foreach($roles as $index => $role)
                @php $isLast = $loop->last; @endphp

                <table class="feature-table">
                    <tr>
                        <td class="col-left">
                            <div class="step-circle">{{ $index + 1 }}</div>
                            @if(!$isLast)
                                <div class="connector-cell">
                                    <div class="connector-line" style="height:66px;"></div>
                                </div>
                            @endif
                        </td>

                        <td class="col-right">
                            <div class="feature-card">
                                <div class="feature-name">
                                    {{ $role->name_app }}
                                </div>
                                <div class="feature-desc">
                                    {{ $role->description ?? 'No description available for this feature.' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            @endforeach
        @else
            <div class="no-roles">No app features are currently assigned to this user.</div>
        @endif
    </div>

</body>
</html>