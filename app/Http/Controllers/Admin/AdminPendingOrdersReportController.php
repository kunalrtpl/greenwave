<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\PendingPoReportService;
use App\Services\Report\ReportContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Session;
/**
 * AdminPendingOrdersReportController
 * ───────────────────────────────────
 * Admin-side screen for the two admin report types already built in
 * ReportContext::allTypes() (see ReportContext.php lines 181-182):
 *   - admin_pending_orders_product_consolidated
 *   - admin_pending_orders_product_detailed
 *
 * The query/build pipeline (ReportQueryBuilder::adminPendingOrderItems /
 * adminInvoicedQtyMap + ReportDataBuilder::buildAdminOrders) was already in
 * place — this controller is just the missing wiring on top of it.
 */
class AdminPendingOrdersReportController extends Controller
{
    /** Only these two admin types are exposed on this screen. */
    const ALLOWED_TYPES = [
        'admin_pending_orders_product_consolidated',
        'admin_pending_orders_product_detailed',
    ];

    /** @var PendingPoReportService */
    protected $reportService;

    public function __construct(PendingPoReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        Session::put('active','pendingorder');
        return view('admin.reports.pending_orders_form', $this->filterOptions() + [
            'reportData'          => null,
            'selectedType'        => null,
            'selectedProductIds'  => [],
            'selectedDealerIds'   => [],
            'selectedCustomerIds' => [],
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'type'                  => 'required|string|in:' . implode(',', self::ALLOWED_TYPES),
            'product_ids'           => 'nullable|array',
            'filter_dealer_ids'     => 'nullable|array',
            'filter_customer_ids'   => 'nullable|array',
        ]);

        $ctx        = ReportContext::forAdmin($request->all());
        $reportData = $this->reportService->generate($ctx);

        return view('admin.reports.pending_orders_form', $this->filterOptions() + [
            'reportData'          => $reportData,
            'selectedType'        => $ctx->reportType,
            'selectedProductIds'  => $ctx->productIds,
            'selectedDealerIds'   => $ctx->filterDealerIds,
            'selectedCustomerIds' => $ctx->filterCustomerIds,
        ]);
    }

    /**
     * Option lists for the Product / Dealer / Customer multi-select filters.
     */
    private function filterOptions(): array
    {
        return [
            'products'  => products(),
            'dealers'   => dealers(),
            'customers' => DB::table('customers')
                ->where('status', 1)
                ->orderBy('name')
                ->select('id', 'name')
                ->get(),
        ];
    }

    public function downloadPdf(Request $request)
    {
        $request->validate([
            'type'                => 'required|string|in:' . implode(',', self::ALLOWED_TYPES),
            'product_ids'         => 'nullable|array',
            'filter_dealer_ids'   => 'nullable|array',
            'filter_customer_ids' => 'nullable|array',
        ]);

        ini_set('memory_limit', '256M');

        $ctx        = ReportContext::forAdmin($request->all());
        $reportData = $this->reportService->generate($ctx);

        $view = $ctx->reportType === 'admin_pending_orders_product_consolidated'
            ? 'reports.admin_pending_orders_product_consolidated'
            : 'reports.admin_pending_orders_product_detailed';

        $data = compact('reportData', 'ctx');
        $html = view($view, compact('data'))->render();

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'margin_top'        => 12,
            'margin_bottom'     => 14,
            'margin_left'       => 10,
            'margin_right'      => 10,
            'default_font'      => 'dejavusans',
            'default_font_size' => 9,
            'tempDir'           => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->SetTitle($ctx->reportType);
        $mpdf->SetAuthor('Greenwave');

        $mpdf->SetHTMLFooter(
            '<table width="100%" style="border-top:1px solid #cbd5e1; font-size:7px; color:#64748b;">
                <tr>
                    <td style="font-weight:bold; color:#334155;">Greenwave &bull; Admin Report</td>
                    <td align="center">Confidential &mdash; Internal Use Only</td>
                    <td align="right">Page {PAGENO} of {nbpg} &nbsp;&bull;&nbsp; ' . now()->format('d M Y') . '</td>
                </tr>
            </table>'
        );

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $ctx->reportType . '.pdf"',
        ]);
    }
}
