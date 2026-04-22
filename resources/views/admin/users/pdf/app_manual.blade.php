<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>App Manual - {{ $user->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #2d2d2d;
            background: #ffffff;
        }

        /* ── Header ── */
        .header {
            background-color: #1a7a4a;
            padding: 28px 35px;
            margin-bottom: 0;
        }

        .header h1 {
            font-size: 22px;
            color: #ffffff;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #a8e8c4;
            margin-top: 3px;
        }

        /* ── Green accent bar below header ── */
        .accent-bar {
            background-color: #25a865;
            height: 6px;
            width: 100%;
        }

        /* ── User Card ── */
        .user-card {
            background-color: #f4faf7;
            border-left: 5px solid #1a7a4a;
            padding: 16px 20px;
            margin: 25px 35px 25px 35px;
        }

        .user-card .label {
            font-size: 9px;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 5px;
        }

        .user-card .name {
            font-size: 17px;
            font-weight: bold;
            color: #1a7a4a;
        }

        .user-card .email {
            font-size: 11px;
            color: #666666;
            margin-top: 3px;
        }

        /* ── Section Title ── */
        .section-title-wrap {
            padding: 0 35px;
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1a7a4a;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1a7a4a;
        }

        /* ── Table ── */
        .table-wrapper {
            padding: 0 35px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #1a7a4a;
        }

        table thead tr {
            background-color: #1a7a4a;
        }

        table thead th {
            padding: 11px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-right: 1px solid #25a865;
        }

        table thead th:last-child {
            border-right: none;
        }

        table tbody tr.even {
            background-color: #f4faf7;
        }

        table tbody tr.odd {
            background-color: #ffffff;
        }

        table tbody td {
            padding: 11px 14px;
            vertical-align: top;
            font-size: 12px;
            color: #333333;
            border-bottom: 1px solid #c8e6d8;
            border-right: 1px solid #c8e6d8;
        }

        table tbody td:last-child {
            border-right: none;
        }

        table tbody td.sr {
            width: 35px;
            color: #999999;
            font-size: 11px;
            text-align: center;
        }

        table tbody td.feature-name {
            width: 200px;
            font-weight: bold;
            color: #1a7a4a;
        }

        table tbody td.description {
            color: #555555;
            line-height: 1.6;
        }

        /* ── No Roles ── */
        .no-roles {
            padding: 30px;
            text-align: center;
            color: #999999;
            font-size: 13px;
        }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 36px;
            background-color: #1a7a4a;
            text-align: center;
            padding-top: 10px;
        }

        .footer span {
            font-size: 10px;
            color: #a8e8c4;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header">
        <h1>App Feature Manual</h1>
        <div class="subtitle">Your personalised guide to enabled app features &nbsp;&bull;&nbsp; Generated on {{ date('d M Y, h:i A') }}</div>
    </div>
    <div class="accent-bar"></div>

    {{-- ── User Info Card ── --}}
    <div class="user-card">
        <div class="label">Prepared For</div>
        <div class="name">{{ $user->name }}</div>
        @if(!empty($user->email))
            <div class="email">{{ $user->email }}</div>
        @endif
    </div>

    {{-- ── Section Title ── --}}
    <div class="section-title-wrap">
        <div class="section-title">Assigned App Features</div>
    </div>

    {{-- ── Table ── --}}
    <div class="table-wrapper">
        @if($roles->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width:35px;">#</th>
                        <th style="width:200px;">Feature Name</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $index => $role)
                        <tr class="{{ $index % 2 == 0 ? 'odd' : 'even' }}">
                            <td class="sr">{{ $index + 1 }}</td>
                            <td class="feature-name">{{ $role->name_app }}</td>
                            <td class="description">{{ $role->description ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-roles">No app features are currently assigned to this user.</div>
        @endif
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        <span>Greenwave &bull; Confidential &bull; {{ date('Y') }}</span>
    </div>

</body>
</html>