<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use PDF;
use Carbon\Carbon;
use Session;
use App\Helpers\AttendanceHelper;

class AttendanceReportController extends Controller
{
    /**
     * Display attendance report filter page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        Session::put('active', 'attendanceReport');

        $users = User::orderBy('name')
            ->select('id','name')
            ->where('status', 1)
            ->where('app_access', 'Yes')
            ->whereRaw("FIND_IN_SET('attendance', app_roles)")
            ->get();

        $years        = range(2025, Carbon::now()->year);
        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $title        = "Employee Attendance Report";

        return view('admin.attendance.report.form', compact(
            'users', 'years', 'currentYear', 'currentMonth', 'title'
        ));
    }


    /**
     * Generate attendance PDF for selected employee and month.
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
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

        // Fetch attendance details using helper
        $attendanceData = AttendanceHelper::getMonthlyAttendance($user, $month, $year);

        // Calculate summary
        $summaryData = AttendanceHelper::calculateSummary($attendanceData, $month, $year);

        // Generate PDF
        $pdf = PDF::loadView('admin.attendance.report.pdf', [
            'allDates' => $attendanceData,
            'user'     => $user,
            'month'    => $month,
            'year'     => $year,
        ] + $summaryData)->setPaper('A4', 'portrait');

        $monthName = Carbon::create()->month($month)->format('F');
        $fileName = "AttendanceReport-{$user->name}-{$monthName}-{$year}.pdf";

        return $pdf->download($fileName);
    }
}
