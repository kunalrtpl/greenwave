<?php

namespace App\Services\Report;

/**
 * PendingPoReportService
 * ──────────────────────
 * Single public façade. Controllers call only this class.
 * PHP 7.4 compatible – no constructor property promotion.
 *
 * Usage (dealer):
 *   $ctx    = ReportContext::forDealer($dealerId, $request->all());
 *   $report = app(PendingPoReportService::class)->generate($ctx);
 *
 * Usage (admin stub – wire controller later):
 *   $ctx    = ReportContext::forAdmin($request->all(), [1, 2]);
 *   $report = app(PendingPoReportService::class)->generate($ctx);
 */
class PendingPoReportService
{
    /** @var ReportQueryBuilder */
    protected $query;

    /** @var ReportDataBuilder */
    protected $builder;

    // PHP 7.4 compatible – explicit property assignment, no promotion
    public function __construct(ReportQueryBuilder $query, ReportDataBuilder $builder)
    {
        $this->query   = $query;
        $this->builder = $builder;
    }

    // ─────────────────────────────────────────────────────────────────────────

    public function generate(ReportContext $ctx): array
    {
        if ($ctx->isPendingPo()) {
            return $this->pendingPo($ctx);
        }

        if ($ctx->isIntransit()) {
            return $this->intransit($ctx);
        }

        if ($ctx->isPendingCustomerPo()) {
            return $this->pendingCustomerPo($ctx);
        }

        if ($ctx->isAdminReport()) {
            return $this->adminOrders($ctx);
        }

        throw new \InvalidArgumentException("Unknown report type: {$ctx->reportType}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PIPELINES  (each: max 2 DB queries)
    // ─────────────────────────────────────────────────────────────────────────

    private function pendingPo(ReportContext $ctx): array
    {
        $items       = $this->query->pendingPoItems($ctx);
        $invoicedMap = $this->query->pendingPoInvoicedQtyMap($ctx);
        return $this->builder->buildPendingPo($ctx, $items, $invoicedMap);
    }

    private function intransit(ReportContext $ctx): array
    {
        $items = $this->query->intransitItems($ctx);
        return $this->builder->buildIntransit($ctx, $items);
    }

    private function pendingCustomerPo(ReportContext $ctx): array
    {
        $items       = $this->query->pendingCustomerPoItems($ctx);
        $invoicedMap = $this->query->pendingCustomerPoInvoicedQtyMap($ctx);
        return $this->builder->buildPendingCustomerPo($ctx, $items, $invoicedMap);
    }

    /**
     * Admin pipeline – stub, ready to activate.
     * @todo Wire AdminReportController when needed.
     */
    private function adminOrders(ReportContext $ctx): array
    {
        $items       = $this->query->adminPendingOrderItems($ctx);
        $invoicedMap = $this->query->adminInvoicedQtyMap($ctx);
        return $this->builder->buildAdminOrders($ctx, $items, $invoicedMap);
    }
}