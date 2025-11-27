<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Attendance;
use App\Helpers\AttendanceHelper;
use Carbon\Carbon;
use Session;

class AttendanceViewController extends Controller
{
    /**
     * Show filter & attendance listing (after filter).
     */
    public function index(Request $request)
    {
        Session::put('active','attendanceView');

        // Employees allowed for attendance
        $users = User::where('status', 1)
            ->where('type','employee')
            ->where('app_access', 'Yes')
            ->orderBy('name')
            ->get(['id','name']);

        $years        = range(2025, Carbon::now()->year);
        $currentYear  = $request->year ?? Carbon::now()->year;
        $currentMonth = $request->month ?? Carbon::now()->month;
        $selectedUser = $request->user_id ?? null;

        $attendanceData = [];
        $summaryData    = [];

        // Show data ONLY if user pressed filter button
        if ($request->has('filter')) {

            // Basic validation
            $request->validate([
                'user_id' => 'required|numeric',
                'month'   => 'required|numeric',
                'year'    => 'required|numeric',
            ]);

            $user = User::find($request->user_id);

            if ($user) {
                $attendanceData = AttendanceHelper::getMonthlyAttendance($user, $currentMonth, $currentYear);
                $summaryData    = AttendanceHelper::calculateSummary($attendanceData, $currentMonth, $currentYear);
            }
        }
        //echo "<pre>"; print_r($attendanceData); die;
        $title = "Employee Attendance";
        return view('admin.attendance.view.index', compact(
            'title',
            'users',
            'years',
            'currentYear',
            'currentMonth',
            'selectedUser',
            'attendanceData',
            'summaryData'
        ));
    }

    /**
     * AJAX: Update attendance status for a specific user + date.
     *
     * NOTE:
     *  - Adjust mapping here based on your Attendance table structure.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'date'    => 'required|date',
            'status'  => 'required|string',   // Present/Half Day/Absent/Leave/Holiday
        ]);

        $userId = $request->user_id;
        $date   = $request->date;
        $status = $request->status;

        // Try to find existing attendance for that day
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        // If no record exists, create one (for manual entry)
        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->user_id = $userId;
            $attendance->date    = $date;
        }

        switch ($status) {
            case 'present':
                $attendance->status = 'present';
                $attendance->secondary_status = null;
                $attendanceDayTime = env('ATTENDANCE_MORNING_START_TIME', '09:00');

                // Add +5 minutes buffer
                $newTime = Carbon::parse($attendance->date . ' ' . $attendanceDayTime)
                            ->addMinutes(5)
                            ->format('Y-m-d H:i:s');

                $attendance->created_at = $newTime; // override 
                break;

            case 'half_day':
                $attendance->status = 'present';
                $attendance->secondary_status = 'First Half Leave';
                 // ---- AUTO ADJUST CREATED TIME ----
                $halfDayTime = env('ATTENDANCE_HALFDAY_TIME', '10:15');

                // Add +5 minutes buffer
                $newTime = Carbon::parse($attendance->date . ' ' . $halfDayTime)
                            ->addMinutes(5)
                            ->format('Y-m-d H:i:s');

                $attendance->created_at = $newTime; // override check-in time
                break;

            case 'absent':
                $attendance->status = 'absent';
                $attendance->secondary_status = null;
                break;

            case 'leave':
                $attendance->status = 'leave';
                $attendance->secondary_status = null;
                break;

            case 'holiday':
                $attendance->status = 'holiday';
                $attendance->secondary_status = null;
                break;

            default:
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid status selected.'
                ], 422);
        }

        // Optional: For manual update, clear remarks or set some default
        if ($request->filled('remarks')) {
            $attendance->remarks = $request->remarks;
        }
        $attendance->is_edited_by_admin = 1;
        // Save the attendance
        $attendance->save();

        return response()->json([
            'status'  => true,
            'message' => 'Attendance status updated successfully.'
        ]);
    }
}
