<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\MonthlyCustomerVisitAnalysisService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;

/**
 * MonthlyCustomerVisitAnalysisController — Admin Module
 * ─────────────────────────────────────────────────────
 * Admin-side screen for the Monthly Customer Visit Analysis report that is
 * otherwise emailed automatically on the 1st of each month by
 * `php artisan report:monthly-customer-visit-analysis`.
 *
 * The screen lists the same eligible employees the command mails (Marketing
 * department, active, minus the excluded ids) and lets an admin download any
 * employee's PDF for any month — the file is produced by the shared
 * MonthlyCustomerVisitAnalysisService, so it is identical to the emailed one.
 */
class MonthlyCustomerVisitAnalysisController extends Controller
{
    /** Earliest year offered in the year dropdown. */
    const FIRST_YEAR = 2021;

    /** @var MonthlyCustomerVisitAnalysisService */
    protected $reportService;

    public function __construct(MonthlyCustomerVisitAnalysisService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $title = "Monthly Customer Visit Analysis";
        Session::put('active', 'monthlyCustomerVisitAnalysis');

        // Default to the month the scheduled command would report on — the
        // previous calendar month.
        $defaultMonth = Carbon::today()->subMonthNoOverflow();

        $month = (int) $request->input('month', $defaultMonth->month);
        $year  = (int) $request->input('year',  $defaultMonth->year);

        if ($month < 1 || $month > 12) {
            $month = (int) $defaultMonth->month;
        }
        if ($year < self::FIRST_YEAR || $year > (int) date('Y')) {
            $year = (int) $defaultMonth->year;
        }

        return view('admin.reports.monthly_customer_visit_analysis', [
            'title'       => $title,
            'employees'   => MonthlyCustomerVisitAnalysisService::eligibleEmployees(),
            'month'       => $month,
            'year'        => $year,
            'monthLabel'  => Carbon::create($year, $month, 1)->format('F Y'),
            'firstYear'   => self::FIRST_YEAR,
        ]);
    }

    /**
     * Generate + download one employee's monthly PDF.
     */
    public function downloadPdf(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'month'   => 'required|integer|min:1|max:12',
            'year'    => 'required|integer|min:' . self::FIRST_YEAR . '|max:' . date('Y'),
        ]);

        // Only employees this report is actually produced for may be pulled —
        // keeps the screen and the mailer on the same eligibility rules.
        $employee = MonthlyCustomerVisitAnalysisService::eligibleEmployees()
            ->firstWhere('id', (int) $request->user_id);

        if (!$employee) {
            return redirect()
                ->to('admin/monthly-customer-visit-analysis?month=' . $request->month . '&year=' . $request->year)
                ->with('flash_message_error', 'That employee is not part of the Monthly Customer Visit Analysis list.');
        }

        ini_set('memory_limit', '256M');

        $user       = User::find($employee->id);
        $monthStart = Carbon::create((int) $request->year, (int) $request->month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $data = $this->reportService->gather($user, $monthStart, $monthEnd);
        $pdf  = $this->reportService->renderPdfString($user, $data, $monthStart, $monthEnd);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->reportService->filename($user, $monthStart) . '"',
        ]);
    }
}
