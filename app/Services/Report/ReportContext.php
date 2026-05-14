<?php

namespace App\Services\Report;

/**
 * ReportContext  –  value object carrying every parameter any report needs.
 * PHP 7.4 compatible – no match, no str_starts_with / str_ends_with.
 *
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║  ALL TYPE VALUES  (pass as "type" in POST body)                          ║
 * ╠══════════════════════════════════════════════════════════════════════════╣
 * ║  DEALER REPORTS                                                          ║
 * ║  — Pending PO (dealer → company) ───────────────────────────────────── ║
 * ║  pending_po_product_consolidated       Product wise Consolidated         ║
 * ║  pending_po_product_detailed           Product wise Detailed (no price)  ║
 * ║  pending_po_product_detailed_price     Product wise Detailed (w/ price)  ║
 * ║  pending_po_date_wise                  Date wise (no price)              ║
 * ║  pending_po_date_wise_price            Date wise (with price)            ║
 * ║  — In-transit ───────────────────────────────────────────────────────── ║
 * ║  intransit_date_wise                   Date wise                         ║
 * ║  intransit_product_wise                Product wise                      ║
 * ║  — Pending Customer PO (customer → dealer) ──────────────────────────── ║
 * ║  pending_customer_po_product_consolidated  Product wise Consolidated     ║
 * ║  pending_customer_po_product_detailed      Product wise Detailed         ║
 * ║  pending_customer_po_customer_detailed     Customer wise Detailed        ║
 * ║  pending_customer_po_date_wise             Date wise                     ║
 * ║  ADMIN (service ready, no controller yet)                                ║
 * ║  admin_pending_orders_product_consolidated                               ║
 * ║  admin_pending_orders_product_detailed                                   ║
 * ║  admin_pending_orders_actor_detailed                                     ║
 * ║  admin_pending_orders_date_wise                                          ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */
class ReportContext
{
    // ── Actor ─────────────────────────────────────────────────────────────────
    /** @var string  'dealer' | 'customer' | 'admin' */
    public $actorType;

    /** @var int|null */
    public $dealerId;

    /** @var int|null */
    public $customerId;

    /** @var int[]  admin multi-dealer filter (empty = all) */
    public $dealerIds = [];

    /** @var int[]  admin multi-customer filter (empty = all) */
    public $customerIds = [];

    // ── Report ────────────────────────────────────────────────────────────────
    /** @var string */
    public $reportType;

    // ── Filters ───────────────────────────────────────────────────────────────
    /** @var int[] */
    public $productIds = [];

    /** @var string[] */
    public $poStatuses = [];

    /** @var string|null  Y-m-d */
    public $dateFrom;

    /** @var string|null  Y-m-d */
    public $dateTo;

    /** @var int|null  admin single-dealer drill */
    public $filterDealerId;

    /** @var int|null  dealer/admin single-customer drill */
    public $filterCustomerId;

    // ── Output ────────────────────────────────────────────────────────────────
    /** @var string  'pdf' | 'json' */
    public $output = 'pdf';

    /** @var bool */
    public $withPrice = false;

    private function __construct() {}

    // ─────────────────────────────────────────────────────────────────────────
    // FACTORIES
    // ─────────────────────────────────────────────────────────────────────────

    public static function forDealer(int $dealerId, array $p): self
    {
        $ctx                   = self::base($p);
        $ctx->actorType        = 'dealer';
        $ctx->dealerId         = $dealerId;
        $ctx->customerId       = null;
        $ctx->filterDealerId   = null;
        $ctx->filterCustomerId = isset($p['customer_id']) ? (int) $p['customer_id'] : null;
        return $ctx;
    }

    public static function forCustomer(int $customerId, array $p): self
    {
        $ctx                   = self::base($p);
        $ctx->actorType        = 'customer';
        $ctx->customerId       = $customerId;
        $ctx->dealerId         = null;
        $ctx->filterDealerId   = null;
        $ctx->filterCustomerId = null;
        return $ctx;
    }

    public static function forAdmin(array $p, array $dealerIds = [], array $customerIds = []): self
    {
        $ctx               = self::base($p);
        $ctx->actorType    = 'admin';
        $ctx->dealerId     = null;
        $ctx->customerId   = null;
        $ctx->dealerIds    = array_map('intval', $dealerIds);
        $ctx->customerIds  = array_map('intval', $customerIds);
        $ctx->filterDealerId   = isset($p['filter_dealer_id'])   ? (int) $p['filter_dealer_id']   : null;
        $ctx->filterCustomerId = isset($p['filter_customer_id']) ? (int) $p['filter_customer_id'] : null;
        return $ctx;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CATEGORY HELPERS  (PHP 7.4: strpos instead of str_starts_with)
    // ─────────────────────────────────────────────────────────────────────────

    public function isDealer(): bool
    {
        return $this->actorType === 'dealer';
    }

    public function isCustomer(): bool
    {
        return $this->actorType === 'customer';
    }

    public function isAdmin(): bool
    {
        return $this->actorType === 'admin';
    }

    public function isPendingPo(): bool
    {
        return strpos($this->reportType, 'pending_po_') === 0;
    }

    public function isIntransit(): bool
    {
        return strpos($this->reportType, 'intransit_') === 0;
    }

    public function isPendingCustomerPo(): bool
    {
        return strpos($this->reportType, 'pending_customer_po_') === 0;
    }

    public function isAdminReport(): bool
    {
        return strpos($this->reportType, 'admin_') === 0;
    }

    /** All valid type strings – used by controller validation */
    public static function allTypes(): array
    {
        return [
            // Dealer – pending PO
            'pending_po_product_consolidated',
            'pending_po_product_detailed',
            'pending_po_product_detailed_price',
            'pending_po_date_wise',
            'pending_po_date_wise_price',
            // Dealer – in-transit
            'intransit_date_wise',
            'intransit_product_wise',
            // Dealer – pending customer PO
            'pending_customer_po_product_consolidated',
            'pending_customer_po_product_detailed',
            'pending_customer_po_customer_detailed',
            'pending_customer_po_date_wise',
            // Admin (service ready, controller future)
            'admin_pending_orders_product_consolidated',
            'admin_pending_orders_product_detailed',
            'admin_pending_orders_actor_detailed',
            'admin_pending_orders_date_wise',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BASE BUILDER
    // ─────────────────────────────────────────────────────────────────────────

    private static function base(array $p): self
    {
        $ctx = new self();

        $ctx->reportType = isset($p['type']) ? $p['type'] : 'pending_po_product_consolidated';
        $ctx->output     = isset($p['output']) ? $p['output'] : 'pdf';

        // withPrice: driven by _price suffix OR explicit param
        // PHP 7.4: use substr instead of str_ends_with
        $type            = isset($p['type']) ? $p['type'] : '';
        $ctx->withPrice  = (substr($type, -6) === '_price')
                        || !empty($p['with_price']);

        $ctx->productIds = self::normaliseIds(isset($p['product_ids']) ? $p['product_ids'] : []);

        $ctx->poStatuses = !empty($p['po_statuses'])
            ? (array) $p['po_statuses']
            : ['approved', 'completed', 'executed'];

        $ctx->dateFrom = isset($p['date_from']) ? $p['date_from'] : null;
        $ctx->dateTo   = isset($p['date_to'])   ? $p['date_to']   : null;

        return $ctx;
    }

    public static function normaliseIds($raw): array
    {
        if (empty($raw)) {
            return [];
        }
        if (is_array($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }
        return array_values(array_filter(array_map('intval', explode(',', (string) $raw))));
    }
}