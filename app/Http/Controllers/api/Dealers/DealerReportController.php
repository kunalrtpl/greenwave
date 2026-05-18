<?php

namespace App\Http\Controllers\api\Dealers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AuthToken;
use App\Services\Report\ReportContext;
use App\Services\Report\PendingPoReportService;
use PDF;

/**
 * DealerReportController
 * ──────────────────────
 * POST /api/dealer/report
 *
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║  type  (required)  – one of:                                            ║
 * ║                                                                         ║
 * ║  PENDING PO (dealer's orders to company)                                ║
 * ║  pending_po_product_consolidated       Product wise Consolidated        ║
 * ║  pending_po_product_detailed           Product wise Detailed (no price) ║
 * ║  pending_po_product_detailed_price     Product wise Detailed (w/ price) ║
 * ║  pending_po_date_wise                  Date wise (no price)             ║
 * ║  pending_po_date_wise_price            Date wise (with price)           ║
 * ║                                                                         ║
 * ║  IN-TRANSIT                                                             ║
 * ║  intransit_date_wise                   Date wise                        ║
 * ║  intransit_product_wise                Product wise                     ║
 * ║                                                                         ║
 * ║  PENDING CUSTOMER PO (customer orders to dealer)                        ║
 * ║  pending_customer_po_product_consolidated  Product wise Consolidated    ║
 * ║  pending_customer_po_product_detailed      Product wise Detailed        ║
 * ║  pending_customer_po_customer_detailed     Customer wise Detailed       ║
 * ║  pending_customer_po_date_wise             Date wise                    ║
 * ║                                                                         ║
 * ║  OTHER PARAMS                                                           ║
 * ║  output          pdf (default) | json                                   ║
 * ║  product_ids     array or "1,2,3" — empty = all                         ║
 * ║  po_statuses     array — default: approved,completed,executed,sales_pending  ║
 * ║  date_from       Y-m-d                                                  ║
 * ║  date_to         Y-m-d                                                  ║
 * ║  customer_id     int — single customer drill (customer_detailed/date)   ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */
class DealerReportController extends Controller
{
    /** @var PendingPoReportService */
    protected $reportService;

    /** @var array|null */
    protected $resp;

    // PHP 7.4 compatible constructor – no property promotion
    public function __construct(Request $request, PendingPoReportService $reportService)
    {
        $this->reportService = $reportService;

        if ($request->header('Authorization')) {
            $this->resp = AuthToken::verifyUser($request->header('Authorization'));
        }
    }

    public function generate(Request $request)
    {
        try {
            // ── Auth ──────────────────────────────────────────────────────────
            $resp = $this->resp;
            if (!$resp || !$resp['status'] || !isset($resp['dealer'])) {
                return response()->json(apiErrorResponse("Unauthorized dealer"), 401);
            }

            $dealer   = $resp['dealer'];
            $dealerId = (int) $dealer['id'];

            // ── Validate ──────────────────────────────────────────────────────
            $request->validate([
                'type'          => 'required|string|in:' . implode(',', ReportContext::allTypes()),
                'output'        => 'nullable|in:json,pdf',
                'product_ids'   => 'nullable',
                'po_statuses'   => 'nullable|array',
                'po_statuses.*' => 'string|in:approved,completed,executed,pending,sales_pending',
                'date_from'     => 'nullable|date_format:Y-m-d',
                'date_to'       => 'nullable|date_format:Y-m-d',
                'customer_id'   => 'nullable|integer',
            ]);

            // ── Build context ─────────────────────────────────────────────────
            $ctx = ReportContext::forDealer($dealerId, $request->all());

            // ── Generate ──────────────────────────────────────────────────────
            $reportData = $this->reportService->generate($ctx);

            if ($ctx->output === 'json') {
                return response()->json(apiSuccessResponse("Report generated", $reportData));
            }

            return $this->buildPdf($dealer, $ctx, $reportData, $dealerId);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(apiErrorResponse($e->getMessage()), 422);
        } catch (\Exception $e) {
            return response()->json(
                apiErrorResponse($e->getMessage() . " | Line " . $e->getLine()), 423
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function buildPdf(array $dealer, ReportContext $ctx, array $reportData, int $dealerId)
    {
        ini_set('memory_limit', '256M');

        $view = $this->resolveView($ctx->reportType);
        $data = compact('dealer', 'reportData', 'ctx');

        $dir  = public_path('DealerReports');
        $file = $ctx->reportType . '.pdf';
        $path = $dir . '/' . $file;

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // Unlink ALL files in folder before saving – folder stays clean always
        foreach (glob($dir . '/*.pdf') ?: [] as $old) {
            @unlink($old);
        }

        PDF::loadView($view, compact('data'))
            ->setPaper('a4', 'portrait')
            ->save($path);

        return response()->json(apiSuccessResponse("PDF generated", [
            'pdf_url' => url('DealerReports/' . $file),
        ]));
    }

    /**
     * Map report type string → blade view path.
     * PHP 7.4 compatible (no match expression).
     */
    private function resolveView(string $type): string
    {
        // ── Pending PO ───────────────────────────────────────────────────────
        if ($type === 'pending_po_product_consolidated') {
            return 'reports.pending_po_product_consolidated';
        }

        if ($type === 'pending_po_product_detailed' || $type === 'pending_po_product_detailed_price') {
            return 'reports.pending_po_product_detailed';
        }

        if ($type === 'pending_po_date_wise' || $type === 'pending_po_date_wise_price') {
            return 'reports.pending_po_date_wise';
        }

        // ── In-transit ───────────────────────────────────────────────────────
        if ($type === 'intransit_date_wise') {
            return 'reports.intransit_date_wise';
        }

        if ($type === 'intransit_product_wise') {
            return 'reports.intransit_product_wise';
        }

        // ── Pending Customer PO ──────────────────────────────────────────────
        if ($type === 'pending_customer_po_product_consolidated') {
            return 'reports.pending_customer_po_product_consolidated';
        }

        if ($type === 'pending_customer_po_product_detailed') {
            return 'reports.pending_customer_po_product_detailed';
        }

        if ($type === 'pending_customer_po_customer_detailed') {
            return 'reports.pending_customer_po_customer_detailed';
        }

        if ($type === 'pending_customer_po_date_wise') {
            return 'reports.pending_customer_po_date_wise';
        }

        throw new \InvalidArgumentException("No view mapped for report type: {$type}");
    }
}