<?php

namespace App\Services\Report;

use Carbon\Carbon;

/**
 * ReportDataBuilder
 * ─────────────────
 * Pure PHP. Zero DB calls.
 * PHP 7.4 compatible – no match expression, no arrow functions on older syntax.
 */
class ReportDataBuilder
{
    // =========================================================================
    // PUBLIC ENTRY POINTS
    // =========================================================================

    public function buildPendingPo(ReportContext $ctx, $items, array $invoicedMap): array
    {
        $grouped = $this->groupByProductPack($items, $invoicedMap, 'dealer_id');

        if ($ctx->reportType === 'pending_po_product_consolidated') {
            return $this->shapeProductConsolidated($grouped, $ctx);
        }
        if ($ctx->reportType === 'pending_po_product_detailed'
            || $ctx->reportType === 'pending_po_product_detailed_price') {
            return $this->shapeProductDetailed($grouped, $ctx);
        }
        if ($ctx->reportType === 'pending_po_date_wise'
            || $ctx->reportType === 'pending_po_date_wise_price') {
            return $this->shapeDateWise($grouped, $ctx);
        }
        return [];
    }

    public function buildIntransit(ReportContext $ctx, $items): array
    {
        if ($ctx->reportType === 'intransit_date_wise') {
            return $this->shapeIntransitDateWise($items);
        }
        if ($ctx->reportType === 'intransit_product_wise') {
            return $this->shapeIntransitProductWise($items);
        }
        return [];
    }

    public function buildPendingCustomerPo(ReportContext $ctx, $items, array $invoicedMap): array
    {
        $grouped = $this->groupByProductPack($items, $invoicedMap, 'customer_id');

        if ($ctx->reportType === 'pending_customer_po_product_consolidated') {
            return $this->shapeProductConsolidated($grouped, $ctx);
        }
        if ($ctx->reportType === 'pending_customer_po_product_detailed') {
            return $this->shapeProductDetailed($grouped, $ctx);
        }
        if ($ctx->reportType === 'pending_customer_po_customer_detailed') {
            return $this->shapeActorDetailed($grouped, $ctx);
        }
        if ($ctx->reportType === 'pending_customer_po_date_wise') {
            return $this->shapeDateWise($grouped, $ctx);
        }
        return [];
    }

    /**
     * Admin – stubs ready, no controller yet.
     * @todo Wire AdminReportController when needed.
     */
    public function buildAdminOrders(ReportContext $ctx, $items, array $invoicedMap): array
    {
        $normalised = $this->normaliseAdminItems($items);
        $grouped    = $this->groupByProductPack($normalised, $invoicedMap, '_actor_id');

        if ($ctx->reportType === 'admin_pending_orders_product_consolidated') {
            return $this->shapeProductConsolidated($grouped, $ctx);
        }
        if ($ctx->reportType === 'admin_pending_orders_product_detailed') {
            return $this->shapeAdminProductDetailed($grouped, $ctx);
        }
        if ($ctx->reportType === 'admin_pending_orders_actor_detailed') {
            return $this->shapeActorDetailed($grouped, $ctx);
        }
        if ($ctx->reportType === 'admin_pending_orders_date_wise') {
            return $this->shapeDateWise($grouped, $ctx);
        }
        return [];
    }

    // =========================================================================
    // CORE GROUPER
    // =========================================================================

    /**
     * Groups flat query rows into product+pack buckets.
     *
     * @param  mixed  $items
     * @param  array  $invoicedMap
     * @param  string $actorField   'dealer_id' | 'customer_id' | '_actor_id'
     * @return array
     */
    private function groupByProductPack($items, array $invoicedMap, string $actorField): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $actorId = isset($item->$actorField) ? (int) $item->$actorField : 0;

            $packKey = $item->is_mini_pack_order
                ? 'mini_' . $item->mini_pack_size
                : 'pack_' . $item->packing_size_id;

            $key = $actorId . '_' . $item->product_id . '_' . $packKey;

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'actor_id'          => $actorId,
                    'actor_name'        => $this->resolveActorName($item, $actorField),
                    'actor_type'        => $this->resolveActorType($item, $actorField),
                    'product_id'        => (int) $item->product_id,
                    'product_name'      => $item->product_name,
                    'packing_size'      => $this->packLabel($item),
                    'is_mini_pack'      => (bool) $item->is_mini_pack_order,
                    'total_pending_qty' => 0,
                    'total_value'       => 0.0,
                    'orders'            => [],
                ];
            }

            // Deduct invoiced qty keyed by poi_id — each PO line item gets its
            // own deduction so cross-PO contamination is impossible.
            $mapKey        = 'poi_' . $item->poi_id;
            $invoicedSoFar = isset($invoicedMap[$mapKey]) ? (int) $invoicedMap[$mapKey] : 0;

            $orderedQty = (int) $item->effective_qty;
            $pendingQty = max(0, $orderedQty - $invoicedSoFar);
            $unitPrice  = isset($item->unit_price) ? (float) $item->unit_price : 0.0;

            $poDate  = Carbon::parse($item->po_date);
            $ageDays = (int) $poDate->diffInDays(Carbon::now());

            $grouped[$key]['orders'][] = [
                'po_date'       => $poDate->format('d.m.Y'),
                'po_date_raw'   => $poDate->format('Y-m-d'),
                'po_ref_no'     => isset($item->po_ref_no)     ? $item->po_ref_no     : '',
                'customer_name' => isset($item->customer_name) ? $item->customer_name : null,
                'dealer_name'   => isset($item->dealer_name)   ? $item->dealer_name   : null,
                'ordered_qty'   => $orderedQty,
                'pending_qty'   => $pendingQty,
                'unit_price'    => $unitPrice,
                'line_value'    => round($pendingQty * $unitPrice, 2),
                'age_days'      => $ageDays,
                'remarks'       => isset($item->remarks) ? $item->remarks : '',
            ];

            $grouped[$key]['total_pending_qty'] += $pendingQty;
            $grouped[$key]['total_value']       += round($pendingQty * $unitPrice, 2);
        }

        // Drop groups fully fulfilled
        $result = [];
        foreach ($grouped as $g) {
            if ($g['total_pending_qty'] > 0) {
                $result[] = $g;
            }
        }
        return $result;
    }

    // =========================================================================
    // SHAPE: Product Consolidated
    // =========================================================================

    private function shapeProductConsolidated(array $grouped, ReportContext $ctx): array
    {
        $rows     = [];
        $totalQty = 0;
        $totalVal = 0.0;

        foreach ($grouped as $g) {
            $rows[] = [
                'product_name' => $g['product_name'],
                'packing_size' => $g['packing_size'],
                'is_mini_pack' => $g['is_mini_pack'],
                'total_qty'    => $g['total_pending_qty'],
                'total_value'  => round($g['total_value'], 2),
            ];
            $totalQty += $g['total_pending_qty'];
            $totalVal += $g['total_value'];
        }

        return [
            'report_type' => $ctx->reportType,
            'rows'        => $rows,
            'total_qty'   => $totalQty,
            'total_value' => round($totalVal, 2),
        ];
    }

    // =========================================================================
    // SHAPE: Product Detailed
    // =========================================================================

    private function shapeProductDetailed(array $grouped, ReportContext $ctx): array
    {
        $products = [];
        $totalQty = 0;
        $totalVal = 0.0;

        foreach ($grouped as $g) {
            $orders = [];
            foreach ($g['orders'] as $o) {
                $row = [
                    'po_date'     => $o['po_date'],
                    'po_ref_no'   => $o['po_ref_no'],
                    'ordered_qty' => $o['ordered_qty'],
                    'pending_qty' => $o['pending_qty'],
                    'age_days'    => $o['age_days'],
                ];
                if ($ctx->withPrice) {
                    $row['unit_price'] = $o['unit_price'];
                    $row['line_value'] = $o['line_value'];
                }
                // customer name shown on customer PO detailed
                if ($o['customer_name'] !== null) {
                    $row['customer_name'] = $o['customer_name'];
                }
                $orders[] = $row;
            }

            $products[] = [
                'product_name'      => $g['product_name'],
                'packing_size'      => $g['packing_size'],
                'is_mini_pack'      => $g['is_mini_pack'],
                'total_pending_qty' => $g['total_pending_qty'],
                'total_value'       => round($g['total_value'], 2),
                'orders'            => $orders,
            ];

            $totalQty += $g['total_pending_qty'];
            $totalVal += $g['total_value'];
        }

        return [
            'report_type' => $ctx->reportType,
            'products'    => $products,
            'total_qty'   => $totalQty,
            'total_value' => round($totalVal, 2),
        ];
    }

    // =========================================================================
    // SHAPE: Date Wise
    // =========================================================================

    private function shapeDateWise(array $grouped, ReportContext $ctx): array
    {
        // Flatten all orders with product info
        $flat = [];
        foreach ($grouped as $g) {
            foreach ($g['orders'] as $o) {
                $flat[] = [
                    'po_date'      => $o['po_date'],
                    'po_date_raw'  => $o['po_date_raw'],
                    'po_ref_no'    => $o['po_ref_no'],
                    'actor_name'   => $g['actor_name'],
                    'actor_type'   => $g['actor_type'],
                    'product_name' => $g['product_name'],
                    'packing_size' => $g['packing_size'],
                    'is_mini_pack' => $g['is_mini_pack'],
                    'ordered_qty'  => $o['ordered_qty'],
                    'pending_qty'  => $o['pending_qty'],
                    'unit_price'   => $o['unit_price'],
                    'line_value'   => $o['line_value'],
                    'age_days'     => $o['age_days'],
                    'remarks'      => $o['remarks'],
                ];
            }
        }

        // Group by date
        $byDate = [];
        foreach ($flat as $row) {
            $byDate[$row['po_date_raw']][] = $row;
        }
        ksort($byDate);

        $dates    = [];
        $totalQty = 0;
        $totalVal = 0.0;

        foreach ($byDate as $dateRaw => $rows) {
            $dateQty = 0;
            $dateVal = 0.0;
            $lines   = [];

            foreach ($rows as $r) {
                $line = [
                    'po_date'      => $r['po_date'],
                    'po_ref_no'    => $r['po_ref_no'],
                    'actor_name'   => $r['actor_name'],
                    'product_name' => $r['product_name'],
                    'packing_size' => $r['packing_size'],
                    'ordered_qty'  => $r['ordered_qty'],
                    'pending_qty'  => $r['pending_qty'],
                    'age_days'     => $r['age_days'],
                    'remarks'      => $r['remarks'],
                ];
                if ($ctx->withPrice) {
                    $line['unit_price'] = $r['unit_price'];
                    $line['line_value'] = $r['line_value'];
                }
                $lines[]  = $line;
                $dateQty += $r['pending_qty'];
                $dateVal += $r['line_value'];
            }

            $dates[] = [
                'date'       => Carbon::parse($dateRaw)->format('d.m.Y'),
                'lines'      => $lines,
                'date_qty'   => $dateQty,
                'date_value' => round($dateVal, 2),
            ];

            $totalQty += $dateQty;
            $totalVal += $dateVal;
        }

        return [
            'report_type' => $ctx->reportType,
            'dates'       => $dates,
            'total_qty'   => $totalQty,
            'total_value' => round($totalVal, 2),
        ];
    }

    // =========================================================================
    // SHAPE: Actor Detailed (customer-wise / dealer-wise)
    // =========================================================================

    private function shapeActorDetailed(array $grouped, ReportContext $ctx): array
    {
        $byActor  = [];
        foreach ($grouped as $g) {
            $actorKey = $g['actor_type'] . '_' . $g['actor_id'];
            if (!isset($byActor[$actorKey])) {
                $byActor[$actorKey] = [
                    'actor_id'   => $g['actor_id'],
                    'actor_name' => $g['actor_name'],
                    'actor_type' => $g['actor_type'],
                    'total_qty'  => 0,
                    'orders'     => [],
                ];
            }
            foreach ($g['orders'] as $o) {
                $byActor[$actorKey]['orders'][] = [
                    'po_date'      => $o['po_date'],
                    'po_ref_no'    => $o['po_ref_no'],
                    'product_name' => $g['product_name'],
                    'packing_size' => $g['packing_size'],
                    'is_mini_pack' => $g['is_mini_pack'],
                    'ordered_qty'  => $o['ordered_qty'],
                    'pending_qty'  => $o['pending_qty'],
                    'age_days'     => $o['age_days'],
                ];
                $byActor[$actorKey]['total_qty'] += $o['pending_qty'];
            }
        }

        $totalQty = 0;
        $actors   = array_values($byActor);
        foreach ($actors as $a) {
            $totalQty += $a['total_qty'];
        }

        return [
            'report_type' => $ctx->reportType,
            'actors'      => $actors,
            'total_qty'   => $totalQty,
        ];
    }

    // =========================================================================
    // SHAPE: In-transit Date Wise
    // =========================================================================

    private function shapeIntransitDateWise($items): array
    {
        $byDate = [];
        foreach ($items as $item) {
            $date = Carbon::parse($item->inv_date)->format('d.m.Y');
            $byDate[$date][] = $item;
        }

        $dates    = [];
        $totalQty = 0;

        foreach ($byDate as $date => $rows) {
            $dateQty = 0;
            $lines   = [];
            foreach ($rows as $r) {
                $lines[] = [
                    'inv_date'       => $date,
                    'inv_no'         => $r->inv_no,
                    'product_name'   => $r->product_name . ' (' . $this->packLabel($r) . ')',
                    'qty'            => (int) $r->qty,
                    'lr_date'        => $r->lr_date
                                        ? Carbon::parse($r->lr_date)->format('d.m.Y') : '',
                    'lr_no'          => isset($r->lr_no)          ? $r->lr_no          : '',
                    'transport_name' => isset($r->transport_name) ? $r->transport_name : '',
                ];
                $dateQty += (int) $r->qty;
            }
            $dates[]   = ['date' => $date, 'lines' => $lines, 'date_qty' => $dateQty];
            $totalQty += $dateQty;
        }

        return [
            'report_type' => 'intransit_date_wise',
            'dates'       => $dates,
            'total_qty'   => $totalQty,
        ];
    }

    // =========================================================================
    // SHAPE: In-transit Product Wise
    // =========================================================================

    private function shapeIntransitProductWise($items): array
    {
        $byProduct = [];
        foreach ($items as $item) {
            $packLabel = $this->packLabel($item);
            $key       = $item->product_id . '_' . $packLabel;
            if (!isset($byProduct[$key])) {
                $byProduct[$key] = [
                    'product_name' => $item->product_name,
                    'packing_size' => $packLabel,
                    'total_qty'    => 0,
                    'invoices'     => [],
                ];
            }
            $byProduct[$key]['invoices'][] = [
                'inv_date'       => Carbon::parse($item->inv_date)->format('d.m.Y'),
                'inv_no'         => $item->inv_no,
                'qty'            => (int) $item->qty,
                'lr_date'        => $item->lr_date
                                    ? Carbon::parse($item->lr_date)->format('d.m.Y') : '',
                'lr_no'          => isset($item->lr_no)          ? $item->lr_no          : '',
                'transport_name' => isset($item->transport_name) ? $item->transport_name : '',
            ];
            $byProduct[$key]['total_qty'] += (int) $item->qty;
        }

        $totalQty = 0;
        $products = array_values($byProduct);
        foreach ($products as $p) {
            $totalQty += $p['total_qty'];
        }

        return [
            'report_type' => 'intransit_product_wise',
            'products'    => $products,
            'total_qty'   => $totalQty,
        ];
    }

    // =========================================================================
    // ADMIN SHAPE STUB
    // =========================================================================

    /**
     * @todo Activate with AdminReportController.
     */
    private function shapeAdminProductDetailed(array $grouped, ReportContext $ctx): array
    {
        $products = [];
        $totalQty = 0;

        foreach ($grouped as $g) {
            $orders = [];
            foreach ($g['orders'] as $o) {
                $actorName = $o['dealer_name'] ?: ($o['customer_name'] ?: '');
                $orders[]  = [
                    'po_date'     => $o['po_date'],
                    'actor_name'  => $actorName,
                    'po_ref_no'   => $o['po_ref_no'],
                    'ordered_qty' => $o['ordered_qty'],
                    'pending_qty' => $o['pending_qty'],
                    'age_days'    => $o['age_days'],
                ];
            }
            $products[] = [
                'product_name'      => $g['product_name'],
                'packing_size'      => $g['packing_size'],
                'total_pending_qty' => $g['total_pending_qty'],
                'orders'            => $orders,
            ];
            $totalQty += $g['total_pending_qty'];
        }

        return [
            'report_type' => $ctx->reportType,
            'products'    => $products,
            'total_qty'   => $totalQty,
        ];
    }

    // =========================================================================
    // PRIVATE UTILITIES
    // =========================================================================

    private function packLabel($item): string
    {
        $isMini = isset($item->is_mini_pack_order) ? (bool) $item->is_mini_pack_order : false;
        if ($isMini) {
            return isset($item->mini_pack_size) && $item->mini_pack_size
                ? (string) $item->mini_pack_size
                : 'Mini Pack';
        }
        return isset($item->packing_size) && $item->packing_size
            ? $item->packing_size . ' kg'
            : 'N/A';
    }

    private function resolveActorName($item, string $actorField): string
    {
        if ($actorField === 'customer_id') {
            return isset($item->customer_name) ? $item->customer_name : ('Customer #' . $item->customer_id);
        }
        if ($actorField === '_actor_id') {
            return isset($item->_actor_name) ? $item->_actor_name : '';
        }
        return isset($item->dealer_name) ? $item->dealer_name : ('Dealer #' . $item->dealer_id);
    }

    private function resolveActorType($item, string $actorField): string
    {
        if ($actorField === 'customer_id') return 'customer';
        if ($actorField === '_actor_id')   return isset($item->_actor_type) ? $item->_actor_type : 'dealer';
        return 'dealer';
    }

    private function normaliseAdminItems($items): array
    {
        $out = [];
        foreach ($items as $item) {
            $clone              = clone $item;
            $isCustomer         = isset($clone->po_action) && $clone->po_action === 'customer';
            $clone->_actor_id   = $isCustomer ? $clone->customer_id   : $clone->dealer_id;
            $clone->_actor_name = $isCustomer
                ? (isset($clone->customer_name) ? $clone->customer_name : '')
                : (isset($clone->dealer_name)   ? $clone->dealer_name   : '');
            $clone->_actor_type = $isCustomer ? 'customer' : 'dealer';
            $out[]              = $clone;
        }
        return $out;
    }
}