<!DOCTYPE html>
<html>
<head>
<style>
    body{
        font-family: Arial, sans-serif;
        font-size: 13px;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .container {
        padding: 12px 22px;
    }

    .logo-container {
        text-align: center;
        margin-bottom: 8px;
    }

    .logo-container img {
        width: 190px;
        margin-bottom: 5px;
    }

    .title-name {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        margin-top: 0px;
        margin-bottom: 5px;
    }

    .period-text {
        text-align: center;
        font-size: 12px;
        color: #666;
        margin-bottom: 6px;
    }

    .generated {
        text-align: right;
        font-size: 12px;
        margin-bottom: 8px;
        color: #888;
    }

    table{
        width:100%;
        border-collapse: collapse;
        margin-top: 8px;
        text-align: center;
    }

    th,td{
        border:1px solid #ccc;
        padding:6px 5px;
        vertical-align: middle !important;
        font-size: 12px;
        line-height: 1.3;
        text-align: center;
    }

    th{
        background:#f2f2f2;
        font-size: 13px;
        font-weight: bold;
    }

    .present     { background:#d6f5d6; }
    .absent      { background:#ffcccc; }
    .leave       { background:#fff0b3; }
    .holiday     { background:#e6e6e6; }

    .late-text {
        color:#ff0000;
        font-weight:bold;
        font-size: 12px;
    }

    small{
        font-size: 11px;
        color:#666;
    }

    .footer-note {
        text-align:center;
        margin-top: 18px;
        font-size: 11px;
        color: #555;
    }
    .month-title {
        text-align: center;
        font-size: 16px;       /* smaller than name (20px), bigger than period (12px) */
        font-weight: 600;
        margin-top: -3px;
        margin-bottom: 6px;
        color: #444;
    }
</style>
</head>

<body>

<div class="container">

    <div class="logo-container">
        <img src="{{ public_path('images/greenwave-logo-1-275-sl.jpg') }}" alt="Greenwave Logo">
    </div>
    
    <div class="title-name">
        {{ $user->name }}
    </div>
    <div class="month-title">
        {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
    </div>
    @php
        $startDate = \Carbon\Carbon::create($year, $month, 1);

        // If it's the current month, use today as end date
        if ($year == date('Y') && $month == date('m')) {
            $endDate = \Carbon\Carbon::today();
        } else {
            $endDate = $startDate->copy()->endOfMonth();
        }
    @endphp

    <div class="period-text">
        Period:
        {{ $startDate->format('d M Y') }} –
        {{ $endDate->format('d M Y') }}
    </div>


    <div class="generated">
        Generated: {{ date('d M Y') }}
    </div>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th style="width:80px;">Date</th>
                <th style="width:60px;">In</th>
                <th style="width:60px;">Out</th>
                <th style="width:75px;">Attendance</th>
                <th>Remarks</th>
                <th style="width:55px;">Calc</th>
            </tr>
        </thead>

        <tbody>

            @php $today = date('Y-m-d'); @endphp

            @foreach($allDates as $d)
                @if($d['date'] > $today) @continue @endif

                @php
                    $rowClass = $d['status'] == 'Present' ? 'present' :
                                ($d['status'] == 'Absent' ? 'absent' :
                                ($d['status'] == 'Leave' ? 'leave' : 'holiday'));

                    $isLate = str_contains($d['remarks'], 'Late');
                @endphp

                <tr class="{{ $rowClass }}">

                    <td>
                        {{ date('d/m/y', strtotime($d['date'])) }}<br>
                        <small>{{ $d['day'] }}</small>
                    </td>

                    <td class="{{ $isLate ? 'late-text' : '' }}">
                        {{ $d['in'] }}
                    </td>

                    <td>{{ $d['out'] }}</td>

                    <td>{{ $d['status'] }}</td>

                    <td style="font-size:12px;">
                        @if($isLate)
                            <span class="late-text">{{ $d['remarks'] }}</span>
                        @else
                            {{ $d['remarks'] }}
                        @endif
                    </td>

                    <td>{{ $d['calc'] }}</td>

                </tr>

            @endforeach

        </tbody>
    </table>


    <table style="margin-top:15px; width:100%; border:1px solid #999; border-collapse:collapse; font-size:12px;">
        <tr style="background:#f2f2f2; font-weight:bold;">
            <td colspan="2" style="padding:7px; text-align:center; font-size:13px;">
                Attendance Overview
            </td>
        </tr>

        <tr>
            <td style="padding:6px; text-align:left;">Total Working Days</td>
            <td style="padding:6px; text-align:center;">{{ $totalWorkingDays }}</td>
        </tr>

        <tr>
            <td style="padding:6px; text-align:left;">Leaves</td>
            <td style="padding:6px; text-align:center;">{{ $totalLeave }}</td>
        </tr>

        <tr>
            <td style="padding:6px; text-align:left;">Absent</td>
            <td style="padding:6px; text-align:center;">{{ $totalAbsent }}</td>
        </tr>

        <tr style="font-weight:bold;">
            <td style="padding:6px; text-align:left;">Net Present Days</td>
            <td style="padding:6px; text-align:center;">{{ $totalWorkingDays -  $totalLeave - $totalAbsent}}</td>
        </tr>
    </table>

    <table style="margin-top:15px; width:100%; border:1px solid #999; border-collapse:collapse; font-size:12px;">
        <tr style="background:#f2f2f2; font-weight:bold;">
            <td colspan="3" style="padding:7px; text-align:center; font-size:13px;">
                Breakdown Summary
            </td>
        </tr>

        <tr>
            <th style="padding:6px;">Type</th>
            <th style="padding:6px;">Count</th>
            <th style="padding:6px;">Equivalent Days</th>
        </tr>

        <tr>
            <td>Full Day Count</td>
            <td>{{ $totalPresent }}</td>
            <td>{{ $totalPresent }}</td>
        </tr>

        <tr>
            <td>Half Day Count</td>
            <td>{{ $totalHalfDay }}</td>
            <td>{{ $totalHalfDay * 0.5 }}</td>
        </tr>

        <tr>
            <td>Less Than 4.5 Hours</td>
            <td>{{ $lessThanHalf }}</td>
            <td>0</td>
        </tr>

        <tr style="font-weight:bold;">
            <td>Total</td>
            <td>{{ $totalPresent + $totalHalfDay + $lessThanHalf }}</td>
            <td>{{ $totalCalc }}</td>
        </tr>
    </table>


    <div class="footer-note">
        This is a system-generated attendance report.
    </div>

</div>

</body>
</html>
