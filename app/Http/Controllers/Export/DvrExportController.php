<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DvrExportController extends Controller
{
    public function exportCsv()
{
    $fileName = 'dvrs_export_' . date('Ymd_His') . '.csv';

    return new StreamedResponse(function () {

        $handle = fopen('php://output', 'w');

        // ✅ Base URL for DVR documents
        $docBaseUrl = 'https://g2app.in/DvrDocuments/';

        // Helper to build document URL
        $docUrl = function ($file) use ($docBaseUrl) {
            return (!empty($file)) ? $docBaseUrl . ltrim($file, '/') : '';
        };

        // ✅ CSV HEADER
        fputcsv($handle, [
            'DVR ID',
            'Employee Name',
            'Customer Name',
            'DVR Date',
            'Start Time',
            'End Time',
            'Start Lat Long',
            'End Lat Long',
            'Visit Recorded',
            'Purpose Of Visit',
            'Trial Type',
            'Other',
            'Query',
            'Visit Type',
            'Visit Detail',
            'Remarks',
            'Is Fruitful',
            'Next Plan',
            'Products',
            'Trial Costing Report',
            'Trial Report',
            'Trial Report Two',
            'Created At',
            'Updated At'
        ]);

        DB::table('dvrs as d')
            ->leftJoin('users as u', 'u.id', '=', 'd.user_id')
            ->leftJoin('customers as c', 'c.id', '=', 'd.customer_id')
            ->leftJoin('customer_register_requests as crr', 'crr.id', '=', 'd.customer_register_request_id')
            ->leftJoin('dvr_products as dp', 'dp.dvr_id', '=', 'd.id')
            ->leftJoin('products as p', 'p.id', '=', 'dp.product_id')
            ->select(
                'd.*',
                'u.name as employee_name',
                DB::raw("COALESCE(c.name, crr.name) as customer_name"),
                DB::raw("GROUP_CONCAT(DISTINCT p.product_name SEPARATOR ', ') as product_names")
            )
            ->groupBy('d.id')
            ->orderBy('d.id')
            ->chunk(500, function ($rows) use ($handle, $docUrl) {

                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->employee_name,
                        $row->customer_name,
                        $row->dvr_date,
                        $row->start_time,
                        $row->end_time,
                        $row->start_lat_long,
                        $row->end_lat_long,
                        $row->visit_recorded,
                        $row->purpose_of_visit,
                        $row->trial_type,
                        $row->other,
                        $row->query,
                        $row->visit_type,
                        $row->visit_detail,
                        $row->remarks,
                        $row->is_fruitful,
                        $row->next_plan,
                        $row->product_names,

                        // ✅ Media URLs
                        $docUrl($row->trial_costing_report),
                        $docUrl($row->trial_report),
                        $docUrl($row->trial_report_two),

                        $row->created_at,
                        $row->updated_at,
                    ]);
                }
            });

        fclose($handle);

    }, 200, [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$fileName\"",
    ]);
}

}
