<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Attendance;
use App\Holiday;
use PDF;  
use Carbon\Carbon;
use Session;
class AttendanceReportController extends Controller
{
    public function index()
    {
        Session::put('active','attendanceReport');
        $users = User::orderBy('name')->select('id','name')->where('status',1)->get();

        $years = range(2025, Carbon::now()->year);
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $title = "Employee Attendance Report";
        return view('admin.attendance.report.form', compact('users','years','currentYear','currentMonth','title'));
    }

    /**
     * Generate monthly attendance report PDF.
     *
     * Rules:
     * - Present: created_at between MORNING_START and MORNING_END → no remarks
     * - Late: > MORNING_END and < HALFDAY_CUT → (Late X)
     * - Half Day: >= HALFDAY_CUT → Half Day
     * - Leave: Full day leave → 0, Half leave → 0.5
     * - Second Half Leave: calc based on worked minutes ( <4.5 hrs = 0, else 0.5 )
     * - Holidays & Sundays are excluded from working days.
     * - If generating current month → summary counts only up to today.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'month'   => 'required|numeric',
            'year'    => 'required|numeric',
        ]);

        $user  = User::findOrFail($request->user_id);
        $month = $request->month;
        $year  = $request->year;

        // Month range
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth();

        $allDates  = [];
        $lateCount = 0;

        // Env Variables
        $morningStart = env('ATTENDANCE_MORNING_START_TIME', '09:00');
        $morningEnd   = env('ATTENDANCE_MORNING_END_TIME', '10:00');
        $halfDayCut   = env('ATTENDANCE_HALFDAY_TIME', '10:15');

        // Holiday list (format: Y-m-d => reason)
        $holidays = Holiday::pluck('reason', 'date')->toArray();

        // Loop month days
        while ($start->lte($end)) {

            $date = $start->toDateString();
            $day  = $start->format('D');

            $attendance = Attendance::where('user_id', $user->id)
                                    ->where('date', $date)
                                    ->first();

            $holidayReason = $holidays[$date] ?? null;

            // -----------------------------
            // Holiday / Sunday
            // -----------------------------
            if ($day == 'Sun' || $holidayReason) {
                $allDates[] = [
                    'date' => $date,
                    'day'  => $day,
                    'in'   => '-',
                    'out'  => '-',
                    'status'  => '-',
                    'remarks' => $holidayReason ? "($holidayReason)" : '-',
                    'calc'    => '-'
                ];
                $start->addDay();
                continue;
            }

            // -----------------------------
            // Absent
            // -----------------------------
            if (!$attendance) {
                $allDates[] = [
                    'date'    => $date,
                    'day'     => $day,
                    'in'      => '-',
                    'out'     => '-',
                    'status'  => 'Absent',
                    'remarks' => '',
                    'calc'    => 0
                ];
                $start->addDay();
                continue;
            }

            // -----------------------------
            // Leave
            // -----------------------------
            if ($attendance->status == 'leave') {

                $calc = 0;

                if ($attendance->secondary_status == 'First Half Leave' ||
                    $attendance->secondary_status == 'Second Half Leave') {
                    $calc = 0.5;
                }

                $allDates[] = [
                    'date'    => $date,
                    'day'     => $day,
                    'in'      => '-',
                    'out'     => '-',
                    'status'  => 'Leave',
                    'remarks' => $attendance->remarks,
                    'calc'    => $calc
                ];

                $start->addDay();
                continue;
            }

            // -----------------------------
            // Present Logic
            // -----------------------------
            $inTime = $attendance->created_at
                ? Carbon::parse($attendance->created_at)->setTimezone(config('app.timezone'))->format('h:i A')
                : '-';

            $outTime = "-";
            if ($attendance->secondary_status == 'Second Half Leave') {
                $outTime = $attendance->leave_time
                    ? Carbon::parse($attendance->leave_time)->setTimezone(config('app.timezone'))->format('h:i A')
                    : "-";
            }

            $remarks = "";
            $calc    = 1;

            // TIME LOGIC
            if ($attendance->created_at) {

                $inTimeOnly = Carbon::parse($attendance->created_at)
                                    ->setTimezone(config('app.timezone'))
                                    ->format('H:i');

                // Half day
                if ($inTimeOnly >= $halfDayCut) {
                    $remarks = "Half Day";
                    $calc = 0.5;
                }
                // Late
                else if ($inTimeOnly > $morningEnd && $inTimeOnly < $halfDayCut) {
                    $lateCount++;
                    $remarks = "(Late $lateCount)";
                    $calc = 1;
                }
                // Present
                else if ($inTimeOnly >= $morningStart && $inTimeOnly <= $morningEnd) {
                    $remarks = "";
                    $calc = 1;
                } else {
                    $remarks = "";
                    $calc = 1;
                }
            }

            // -----------------------------
            // Second Half Leave Work Hour Logic
            // -----------------------------
            if ($attendance->secondary_status == 'Second Half Leave' && $attendance->leave_time) {

                $inCarbon  = Carbon::parse($attendance->created_at)->setTimezone(config('app.timezone'));
                $outCarbon = Carbon::parse($date . ' ' . $attendance->leave_time)->setTimezone(config('app.timezone'));

                $minutes   = $inCarbon->diffInMinutes($outCarbon);
                $hrs       = floor($minutes / 60);
                $mins      = $minutes % 60;

                $durationText = $hrs . " hr " . $mins . " min";

                // <4.5 hrs = 0, ≥4.5 hrs = 0.5
                $calc = ($minutes < 270) ? 0 : 0.5;

                $extra = $attendance->remarks ? ", " . $attendance->remarks : "";

                $remarks = "Partial Day Off$extra, Work Hrs - $durationText";
            }

            // Push row
            $allDates[] = [
                'date'    => $date,
                'day'     => $day,
                'in'      => $inTime,
                'out'     => $outTime,
                'status'  => 'Present',
                'remarks' => trim($remarks),
                'calc'    => $calc
            ];

            $start->addDay();
        }

        // ------------------------------------
        // SUMMARY SECTION
        // ------------------------------------
        $todayCarbon = Carbon::today()->setTimezone(config('app.timezone'));
        $firstOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = $firstOfMonth->copy()->endOfMonth();

        // If this is the current month, limit summary to today
        $lastToConsider = ($firstOfMonth->isSameMonth($todayCarbon))
            ? $todayCarbon
            : $endOfMonth;

        // Calculate Working Days
        $totalWorkingDays = 0;
        $cursor = $firstOfMonth->copy();

        while ($cursor->lte($lastToConsider)) {
            $dstr = $cursor->toDateString();
            $dow  = $cursor->format('D');

            if ($dow !== 'Sun' && !isset($holidays[$dstr])) {
                $totalWorkingDays++;
            }
            $cursor->addDay();
        }

        // Other stats
        $totalPresent = 0;
        $totalHalfDay = 0;
        $totalLeave   = 0;
        $totalAbsent  = 0;
        $totalCalc    = 0;
        $lessThanHalf = 0; // NEW COUNT for <4.5 hours

        foreach ($allDates as $d) {

            if (Carbon::parse($d['date'])->gt($lastToConsider)) {
                continue;
            }

            if ($d['status'] === 'Present') {

                if ((float)$d['calc'] == 1) {
                    $totalPresent++;   // Full day
                }

                if ((float)$d['calc'] == 0.5) {
                    $totalHalfDay++;   // Half day
                }

                if ((float)$d['calc'] == 0) {
                    $lessThanHalf++;   // NEW: Less than 4.5 hrs
                }
            }


            if ($d['status'] === 'Absent') $totalAbsent++;
            if ($d['status'] === 'Leave')  $totalLeave++;

            if (is_numeric($d['calc'])) {
                $totalCalc += (float)$d['calc'];
            }
        }

        // ------------------------------------
        // GENERATE PDF
        // ------------------------------------
        $pdf = PDF::loadView('admin.attendance.report.pdf', compact(
            'allDates','user','month','year',
            'totalWorkingDays','totalPresent','totalHalfDay',
            'totalAbsent','totalLeave','totalCalc','lessThanHalf'
        ))->setPaper('A4','portrait');

        $monthName = \Carbon\Carbon::create()->month($month)->format('F');

        $fileName = "AttendanceReport-{$user->name}-{$monthName}-{$year}.pdf";

        return $pdf->download($fileName);
    }




}
