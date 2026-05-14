<?php

namespace App\Services\Report;

use Illuminate\Support\Facades\DB;

/**
 * ReportQueryBuilder
 * ──────────────────
 * All DB work. Zero N+1. Max 2 queries per report.
 *
 * KEY DESIGN DECISIONS
 * ────────────────────
 * 1. NO whereNotExists on sale_invoices.
 *    Reason: a PO can be PARTIALLY invoiced (e.g. order 1000, invoiced 200 → 800 still pending).
 *    Excluding the whole PO when ANY invoice exists is wrong.
 *    Instead, pendingPoInvoicedQtyMap() fetches invoiced qty and the DataBuilder
 *    subtracts it per product/pack group. Groups where pending = 0 are dropped there.
 *
 * 2. EXCLUDE purchase_order_items.item_action = 'Cancel'.
 *    Cancelled line items must never appear in pending reports.
 *
 * Tables used:
 *   purchase_orders, purchase_order_items, products,
 *   packing_sizes, sale_invoices, sale_invoice_items,
 *   dealers, customers
 *
 * Column facts confirmed from DB screenshots:
 *   products.product_name  (NOT products.name)
 *   dealers.business_name  (display name, not nullable)
 *   dealers.name           (person name, nullable)
 *   customers.name
 *   purchase_order_items.mini_pack_size  (no purchase_order_item_details table)
 *   purchase_order_items.item_action     ('Cancel' = exclude)
 */
class ReportQueryBuilder
{
    // =========================================================================
    // A) PENDING PO  (purchase_orders.action = 'dealer')
    // =========================================================================

    public function pendingPoItems(ReportContext $ctx)
    {
        $query = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po',   'po.id', '=', 'poi.purchase_order_id')
            ->join('products as p',           'p.id',  '=', 'poi.product_id')
            ->leftJoin('packing_sizes as ps', 'ps.id', '=', 'poi.packing_size_id')
            ->leftJoin('dealers as d',        'd.id',  '=', 'po.dealer_id')

            ->where('po.action', 'dealer')
            ->whereIn('po.po_status', $ctx->poStatuses)

            // Exclude cancelled line items
            ->where(function ($q) {
                $q->whereNull('poi.item_action')
                  ->orWhere('poi.item_action', '!=', 'Cancel');
            });

        // Actor scope
        if ($ctx->isDealer()) {
            $query->where('po.dealer_id', $ctx->dealerId);
        }
        if ($ctx->isAdmin() && !empty($ctx->dealerIds)) {
            $query->whereIn('po.dealer_id', $ctx->dealerIds);
        }

        // Date range
        if ($ctx->dateFrom) {
            $query->whereDate('po.created_at', '>=', $ctx->dateFrom);
        }
        if ($ctx->dateTo) {
            $query->whereDate('po.created_at', '<=', $ctx->dateTo);
        }

        // Product filter
        if (!empty($ctx->productIds)) {
            $query->whereIn('poi.product_id', $ctx->productIds);
        }

        $cols = [
            'poi.id                                    as poi_id',
            'poi.purchase_order_id',
            'poi.product_id',
            'poi.packing_size_id',
            'poi.qty                                   as ordered_qty',
            'poi.actual_qty',
            'poi.mini_pack_size',
            'poi.item_action',
            DB::raw('COALESCE(poi.actual_qty, poi.qty) as effective_qty'),
            'po.dealer_id',
            'po.is_mini_pack_order',
            'po.po_ref_no_string             as po_ref_no',
            'po.created_at                             as po_date',
            'po.po_status',
            'po.remarks',
            'p.product_name                            as product_name',
            'ps.size                                   as packing_size',
            'd.business_name                           as dealer_name',
        ];

        // Always fetch net_price — value calculated regardless of withPrice flag
        $cols[] = 'poi.net_price as unit_price';

        return $query->select($cols)
            ->orderBy('p.product_name')
            ->orderBy('po.created_at')
            ->get();
    }

    /**
     * Invoiced qty map for pending PO deduction.
     *
     * Fetches the SUM of qty already dispatched (dealer_invoice_no filled)
     * per product/pack/dealer group so the DataBuilder can subtract it.
     * Only invoiced items whose source POI was NOT cancelled are counted.
     *
     * Key: "dealerId-productId-packingId-isMini"
     */
    public function pendingPoInvoicedQtyMap(ReportContext $ctx): array
    {
        $query = DB::table('sale_invoice_items as sii')
            ->join('sale_invoices as si',         'si.id',  '=', 'sii.sale_invoice_id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'sii.purchase_order_item_id')
            ->join('purchase_orders as po',       'po.id',  '=', 'poi.purchase_order_id')
            ->whereNotNull('si.dealer_invoice_no')
            ->where('si.dealer_invoice_no', '!=', '')
            ->where('po.action', 'dealer')
            // Don't count invoiced qty from cancelled items
            ->where(function ($q) {
                $q->whereNull('poi.item_action')
                  ->orWhere('poi.item_action', '!=', 'Cancel');
            });

        if ($ctx->isDealer()) {
            $query->where('po.dealer_id', $ctx->dealerId);
        }
        if ($ctx->isAdmin() && !empty($ctx->dealerIds)) {
            $query->whereIn('po.dealer_id', $ctx->dealerIds);
        }
        if (!empty($ctx->productIds)) {
            $query->whereIn('sii.product_id', $ctx->productIds);
        }

        $rows = $query->select([
                'poi.id                          as poi_id',
                'po.dealer_id',
                'sii.product_id',
                'poi.packing_size_id',
                'po.is_mini_pack_order',
                DB::raw('SUM(sii.qty) as invoiced_qty'),
            ])
            ->groupBy('poi.id', 'po.dealer_id', 'sii.product_id', 'poi.packing_size_id', 'po.is_mini_pack_order')
            ->get();

        // Key by poi_id so each PO line item gets its OWN invoiced deduction.
        // Keying by product alone caused cross-PO contamination (200 invoiced on
        // PO-A was also deducted from PO-B for the same product).
        $map = [];
        foreach ($rows as $r) {
            $key       = 'poi_' . $r->poi_id;
            $map[$key] = isset($map[$key]) ? $map[$key] + (int) $r->invoiced_qty : (int) $r->invoiced_qty;
        }
        return $map;
    }

    // =========================================================================
    // B) IN-TRANSIT
    //    sale_invoices.dealer_invoice_no != ''  AND  is_delivered = 0
    // =========================================================================

    public function intransitItems(ReportContext $ctx)
    {
        $query = DB::table('sale_invoices as si')
            ->join('sale_invoice_items as sii',      'sii.sale_invoice_id',      '=', 'si.id')
            ->join('products as p',                  'p.id',                     '=', 'sii.product_id')
            ->join('purchase_orders as po',          'po.id',                    '=', 'si.purchase_order_id')
            ->leftJoin('packing_sizes as ps',        'ps.id',                    '=', 'sii.packing_size_id')
            ->leftJoin('purchase_order_items as poi','poi.id',                   '=', 'sii.purchase_order_item_id')

            ->whereNotNull('si.dealer_invoice_no')
            ->where('si.dealer_invoice_no', '!=', '')
            ->where('si.is_delivered', 0)
            // Exclude cancelled source items
            ->where(function ($q) {
                $q->whereNull('poi.item_action')
                  ->orWhere('poi.item_action', '!=', 'Cancel');
            });

        if ($ctx->isDealer()) {
            $query->where('po.dealer_id', $ctx->dealerId);
        }
        if ($ctx->isAdmin() && !empty($ctx->dealerIds)) {
            $query->whereIn('po.dealer_id', $ctx->dealerIds);
        }
        if ($ctx->dateFrom) {
            $query->whereDate('si.sale_invoice_date', '>=', $ctx->dateFrom);
        }
        if ($ctx->dateTo) {
            $query->whereDate('si.sale_invoice_date', '<=', $ctx->dateTo);
        }
        if (!empty($ctx->productIds)) {
            $query->whereIn('sii.product_id', $ctx->productIds);
        }

        return $query->select([
                'si.id                    as invoice_id',
                'si.sale_invoice_date     as inv_date',
                'si.dealer_invoice_no     as inv_no',
                'si.lr_no',
                'si.dispatch_date         as lr_date',
                'si.transport_name',
                'sii.product_id',
                'sii.qty',
                'p.product_name           as product_name',
                'ps.size                  as packing_size',
                'po.dealer_id',
                'po.is_mini_pack_order',
                'poi.mini_pack_size',
            ])
            ->orderBy('si.sale_invoice_date')
            ->orderBy('p.product_name')
            ->get();
    }

    // =========================================================================
    // C) PENDING CUSTOMER PO  (purchase_orders.action = 'customer')
    // =========================================================================

    public function pendingCustomerPoItems(ReportContext $ctx)
    {
        $query = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po',   'po.id', '=', 'poi.purchase_order_id')
            ->join('products as p',           'p.id',  '=', 'poi.product_id')
            ->join('customers as c',          'c.id',  '=', 'po.customer_id')
            ->leftJoin('packing_sizes as ps', 'ps.id', '=', 'poi.packing_size_id')

            ->where('po.action', 'customer')
            ->whereIn('po.po_status', $ctx->poStatuses)
            // Exclude cancelled line items
            ->where(function ($q) {
                $q->whereNull('poi.item_action')
                  ->orWhere('poi.item_action', '!=', 'Cancel');
            });

        if ($ctx->isDealer()) {
            $query->where('po.dealer_id', $ctx->dealerId);
        }
        if ($ctx->filterCustomerId) {
            $query->where('po.customer_id', $ctx->filterCustomerId);
        }
        if ($ctx->dateFrom) {
            $query->whereDate('po.created_at', '>=', $ctx->dateFrom);
        }
        if ($ctx->dateTo) {
            $query->whereDate('po.created_at', '<=', $ctx->dateTo);
        }
        if (!empty($ctx->productIds)) {
            $query->whereIn('poi.product_id', $ctx->productIds);
        }

        return $query->select([
                'poi.id                                    as poi_id',
                'poi.purchase_order_id',
                'poi.product_id',
                'poi.packing_size_id',
                'poi.qty                                   as ordered_qty',
                'poi.actual_qty',
                'poi.mini_pack_size',
                DB::raw('COALESCE(poi.actual_qty, poi.qty) as effective_qty'),
                'po.customer_id',
                'po.dealer_id',
                'po.is_mini_pack_order',
                'po.customer_purchase_order_no             as po_ref_no',
                'po.created_at                             as po_date',
                'po.po_status',
                'p.product_name                            as product_name',
                'ps.size                                   as packing_size',
                'c.name                                    as customer_name',
            ])
            ->orderBy('c.name')
            ->orderBy('p.product_name')
            ->orderBy('po.created_at')
            ->get();
    }

    /**
     * Invoiced qty map for pending customer PO deduction.
     * Key: "customerId-productId-packingId-isMini"
     */
    public function pendingCustomerPoInvoicedQtyMap(ReportContext $ctx): array
    {
        $query = DB::table('sale_invoice_items as sii')
            ->join('sale_invoices as si',         'si.id',  '=', 'sii.sale_invoice_id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'sii.purchase_order_item_id')
            ->join('purchase_orders as po',       'po.id',  '=', 'poi.purchase_order_id')
            ->where('po.action', 'customer')
            ->where(function ($q) {
                $q->whereNull('poi.item_action')
                  ->orWhere('poi.item_action', '!=', 'Cancel');
            });

        if ($ctx->isDealer()) {
            $query->where('po.dealer_id', $ctx->dealerId);
        }
        if ($ctx->filterCustomerId) {
            $query->where('po.customer_id', $ctx->filterCustomerId);
        }
        if (!empty($ctx->productIds)) {
            $query->whereIn('sii.product_id', $ctx->productIds);
        }

        $rows = $query->select([
                'poi.id                          as poi_id',
                'po.customer_id',
                'sii.product_id',
                'poi.packing_size_id',
                'po.is_mini_pack_order',
                DB::raw('SUM(sii.qty) as invoiced_qty'),
            ])
            ->groupBy('poi.id', 'po.customer_id', 'sii.product_id', 'poi.packing_size_id', 'po.is_mini_pack_order')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $key       = 'poi_' . $r->poi_id;
            $map[$key] = isset($map[$key]) ? $map[$key] + (int) $r->invoiced_qty : (int) $r->invoiced_qty;
        }
        return $map;
    }

    // =========================================================================
    // D)

    // =========================================================================
    // D) ADMIN – stubs, activate with AdminReportController later
    // =========================================================================

    /**
     * @todo Wire AdminReportController when admin panel reports are needed.
     */
    public function adminPendingOrderItems(ReportContext $ctx)
    {
        $query = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po',   'po.id', '=', 'poi.purchase_order_id')
            ->join('products as p',           'p.id',  '=', 'poi.product_id')
            ->leftJoin('packing_sizes as ps', 'ps.id', '=', 'poi.packing_size_id')
            ->leftJoin('dealers as d',        'd.id',  '=', 'po.dealer_id')
            ->leftJoin('customers as c',      'c.id',  '=', 'po.customer_id')

            ->whereIn('po.action', ['dealer', 'customer'])
            ->whereIn('po.po_status', $ctx->poStatuses)
            // Exclude cancelled line items
            ->where(function ($q) {
                $q->whereNull('poi.item_action')
                  ->orWhere('poi.item_action', '!=', 'Cancel');
            });

        if (!empty($ctx->dealerIds)) {
            $query->whereIn('po.dealer_id', $ctx->dealerIds);
        }
        if (!empty($ctx->customerIds)) {
            $query->whereIn('po.customer_id', $ctx->customerIds);
        }
        if ($ctx->filterDealerId) {
            $query->where('po.dealer_id', $ctx->filterDealerId);
        }
        if ($ctx->filterCustomerId) {
            $query->where('po.customer_id', $ctx->filterCustomerId);
        }
        if ($ctx->dateFrom) {
            $query->whereDate('po.created_at', '>=', $ctx->dateFrom);
        }
        if ($ctx->dateTo) {
            $query->whereDate('po.created_at', '<=', $ctx->dateTo);
        }
        if (!empty($ctx->productIds)) {
            $query->whereIn('poi.product_id', $ctx->productIds);
        }

        return $query->select([
                'poi.id                                    as poi_id',
                'poi.purchase_order_id',
                'poi.product_id',
                'poi.packing_size_id',
                'poi.qty                                   as ordered_qty',
                'poi.mini_pack_size',
                DB::raw('COALESCE(poi.actual_qty, poi.qty) as effective_qty'),
                'po.dealer_id',
                'po.customer_id',
                'po.action                                 as po_action',
                'po.is_mini_pack_order',
                'po.customer_purchase_order_no             as po_ref_no',
                'po.created_at                             as po_date',
                'po.po_status',
                'po.remarks',
                'p.product_name                            as product_name',
                'ps.size                                   as packing_size',
                'd.business_name                           as dealer_name',
                'c.name                                    as customer_name',
            ])
            ->orderBy('p.product_name')
            ->orderBy('po.created_at')
            ->get();
    }

    /**
     * @todo Activate with AdminReportController.
     */
    public function adminInvoicedQtyMap(ReportContext $ctx): array
    {
        $query = DB::table('sale_invoice_items as sii')
            ->join('sale_invoices as si',         'si.id',  '=', 'sii.sale_invoice_id')
            ->join('purchase_order_items as poi', 'poi.id', '=', 'sii.purchase_order_item_id')
            ->join('purchase_orders as po',       'po.id',  '=', 'poi.purchase_order_id')
            ->whereNotNull('si.dealer_invoice_no')
            ->where('si.dealer_invoice_no', '!=', '')
            ->whereIn('po.action', ['dealer', 'customer'])
            ->where(function ($q) {
                $q->whereNull('poi.item_action')
                  ->orWhere('poi.item_action', '!=', 'Cancel');
            });

        if (!empty($ctx->dealerIds)) {
            $query->whereIn('po.dealer_id', $ctx->dealerIds);
        }
        if (!empty($ctx->customerIds)) {
            $query->whereIn('po.customer_id', $ctx->customerIds);
        }
        if ($ctx->filterDealerId) {
            $query->where('po.dealer_id', $ctx->filterDealerId);
        }
        if ($ctx->filterCustomerId) {
            $query->where('po.customer_id', $ctx->filterCustomerId);
        }
        if (!empty($ctx->productIds)) {
            $query->whereIn('sii.product_id', $ctx->productIds);
        }

        $rows = $query->select([
                'poi.id                          as poi_id',
                'po.action as po_action',
                'po.dealer_id',
                'po.customer_id',
                'sii.product_id',
                'poi.packing_size_id',
                'po.is_mini_pack_order',
                DB::raw('SUM(sii.qty) as invoiced_qty'),
            ])
            ->groupBy('poi.id', 'po.action', 'po.dealer_id', 'po.customer_id',
                      'sii.product_id', 'poi.packing_size_id', 'po.is_mini_pack_order')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $key = 'poi_' . $r->poi_id;
            $map[$key] = isset($map[$key]) ? $map[$key] + (int) $r->invoiced_qty : (int) $r->invoiced_qty;
        }
        return $map;
    }
}