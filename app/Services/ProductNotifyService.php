<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Product changes made from Admin → Product Pricing, and the emails that tell
 * the people who deal in the product about them.
 *
 * Five changes are announced, each with its own employee and dealer template:
 *
 *   discontinued   Discont. ticked
 *   reinstated     Discont. unticked: the product is available again
 *   not_available  N.A. ticked: temporarily not available
 *   available      N.A. unticked: back in stock
 *   price_changed  the standard dealer price was raised or lowered
 *
 * Who is "linked" to a product:
 *   employees  rows in `user_products`, active users only (users.status = 1)
 *   dealers    rows in `dealer_linked_products`, active and not deleted
 *
 * The admin screen posts one recipient per request so its progress bar moves
 * after every email. Nothing is queued: each request sends before it returns.
 *
 * The browser only ever sends recipient ids. Addresses are looked up again
 * here, and anyone who is no longer linked to the product is skipped, so a
 * tampered request cannot email an arbitrary address.
 *
 * While config('mail.redirect_to') is set, every message goes to that one
 * inbox instead — see EmailService::applyRedirect().
 */
class ProductNotifyService
{
    /** event => [audience => email_templates.event_key] */
    const EVENTS = [
        'discontinued'  => ['employee' => 'product_discontinued_employee',  'dealer' => 'product_discontinued_dealer'],
        'reinstated'    => ['employee' => 'product_reinstated_employee',    'dealer' => 'product_reinstated_dealer'],
        'not_available' => ['employee' => 'product_not_available_employee', 'dealer' => 'product_not_available_dealer'],
        'available'     => ['employee' => 'product_available_employee',     'dealer' => 'product_available_dealer'],
        'price_changed' => ['employee' => 'product_price_changed_employee', 'dealer' => 'product_price_changed_dealer'],
    ];

    /** Most recipients a single request may carry. */
    const MAX_BATCH = 10;

    // ─────────────────────────────────────────────────────────────────────────
    // Status flags
    // ─────────────────────────────────────────────────────────────────────────

    /** Set or clear Discontinued. Returns an error message, or null. */
    public function setDiscontinued($product, bool $on)
    {
        if ($on && $product->not_available) {
            return 'This product is marked Not Available. Untick N.A. first, then mark it as discontinued.';
        }

        $this->saveFlag($product->id, 'discontinued', $on);
        return null;
    }

    /** Set or clear Not Available. Returns an error message, or null. */
    public function setNotAvailable($product, bool $on)
    {
        if ($on && $product->discontinued) {
            return 'This product is discontinued. Untick Discont. first, then mark it as not available.';
        }

        $this->saveFlag($product->id, 'not_available', $on);
        return null;
    }

    /**
     * Why an email for this event would be wrong for the product as it is now
     * saved, or null when it fits. Stops a stale popup announcing a change
     * that has since been undone.
     */
    public function eventMismatch($event, $product)
    {
        switch ($event) {
            case 'discontinued':  return $product->discontinued   ? null : 'This product is not marked as discontinued.';
            case 'reinstated':    return !$product->discontinued  ? null : 'This product is still marked as discontinued.';
            case 'not_available': return $product->not_available  ? null : 'This product is not marked as not available.';
            case 'available':     return !$product->not_available ? null : 'This product is still marked as not available.';
            case 'price_changed': return $this->currentPrice($product->id) !== null ? null : 'This product has no dealer price.';
        }
        return 'Unknown change.';
    }

    /** Today's standard dealer price, or null when none is set. */
    public function currentPrice($productId)
    {
        $price = DB::table('product_pricings')
            ->where('product_id', $productId)
            ->where('price_date', '<=', Carbon::today()->toDateString())
            ->orderBy('price_date', 'desc')
            ->orderBy('id', 'desc')
            ->value('dealer_price');

        return $price === null ? null : (float) $price;
    }

    protected function saveFlag($productId, $column, bool $on)
    {
        DB::table('products')->where('id', $productId)->update([
            $column      => $on ? 1 : 0,
            'updated_at' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Recipients
    // ─────────────────────────────────────────────────────────────────────────

    /** Active employees linked to the product. */
    public function employees($productId, array $onlyIds = null): Collection
    {
        $query = DB::table('user_products as up')
            ->join('users as u', 'u.id', '=', 'up.user_id')
            ->where('up.product_id', $productId)
            ->where('u.status', 1)
            ->select('u.id', 'u.name', 'u.email', 'u.mobile')
            ->distinct()
            ->orderBy('u.name');

        if ($onlyIds !== null) {
            $query->whereIn('u.id', $onlyIds ?: [0]);
        }

        return $query->get()->map(function ($u) {
            $emails = $this->emailsFrom($u->email);
            return (object) [
                'type'      => 'employee',
                'id'        => (int) $u->id,
                'name'      => $u->name,
                'sub'       => $u->mobile,
                'emails'    => $emails,
                'email'     => implode(', ', $emails),
                'has_email' => !empty($emails),
            ];
        })->values();
    }

    /** Active, non-deleted dealers linked to the product. */
    public function dealers($productId, array $onlyIds = null): Collection
    {
        $query = DB::table('dealer_linked_products as dlp')
            ->join('dealers as d', 'd.id', '=', 'dlp.dealer_id')
            ->where('dlp.product_id', $productId)
            ->where('d.status', 1)
            ->where(function ($q) {
                $q->whereNull('d.is_delete')->orWhere('d.is_delete', 0);
            })
            ->select('d.id', 'd.business_name', 'd.owner_name', 'd.name', 'd.city', 'd.email')
            ->distinct()
            ->orderBy('d.business_name');

        if ($onlyIds !== null) {
            $query->whereIn('d.id', $onlyIds ?: [0]);
        }

        return $query->get()->map(function ($d) {
            $emails = $this->emailsFrom($d->email);
            $person = $d->owner_name ?: $d->name;
            return (object) [
                'type'      => 'dealer',
                'id'        => (int) $d->id,
                'name'      => $d->business_name ?: $person,
                'person'    => $person,
                'sub'       => trim(implode(' · ', array_filter([$person, $d->city]))),
                'emails'    => $emails,
                'email'     => implode(', ', $emails),
                'has_email' => !empty($emails),
            ];
        })->values();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sending
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Email one batch of recipients about one change and report how each went.
     *
     * @param string $event       a key of EVENTS
     * @param object $product     row from `products`
     * @param array  $recipients  [['type' => 'employee'|'dealer', 'id' => 12], ...]
     * @param array  $extra       remarks (string|null); for price_changed, old_price
     * @return array  [['type','id','status' => sent|failed|skipped,'message'], ...]
     */
    public function notifyBatch($event, $product, array $recipients, array $extra = []): array
    {
        $ids = ['employee' => [], 'dealer' => []];
        foreach ($recipients as $r) {
            $type = isset($r['type']) ? $r['type'] : null;
            if (isset($ids[$type]) && isset($r['id'])) {
                $ids[$type][] = (int) $r['id'];
            }
        }

        $found = [
            'employee' => $this->employees($product->id, $ids['employee'])->keyBy('id'),
            'dealer'   => $this->dealers($product->id, $ids['dealer'])->keyBy('id'),
        ];

        $common = $this->mailData($event, $product, $extra);
        $results = [];

        foreach ($recipients as $r) {
            $type = isset($r['type']) ? $r['type'] : null;
            $id   = isset($r['id']) ? (int) $r['id'] : 0;
            $row  = ['type' => $type, 'id' => $id];

            if (!isset($found[$type]) || !$found[$type]->has($id)) {
                $results[] = $row + ['status' => 'skipped', 'message' => 'No longer linked to this product'];
                continue;
            }

            $recipient = $found[$type]->get($id);

            if (!$recipient->has_email) {
                $results[] = $row + ['status' => 'skipped', 'message' => 'No email address on record'];
                continue;
            }

            $sent = EmailService::send(
                self::EVENTS[$event][$type],
                $common + ['recipient' => $recipient, 'audience' => $type],
                $recipient->emails
            );

            $results[] = $row + ($sent
                ? ['status' => 'sent',   'message' => 'Sent to ' . $recipient->email]
                : ['status' => 'failed', 'message' => 'Could not be sent — see the log']);
        }

        return $results;
    }

    /** What every email for this change is given, besides the recipient. */
    protected function mailData($event, $product, array $extra): array
    {
        $data = [
            'event'       => $event,
            'product'     => $product,
            'remarks'     => !empty($extra['remarks']) ? trim($extra['remarks']) : null,
            'effectiveOn' => Carbon::now()->format('d M Y'),
        ];

        if ($event === 'price_changed') {
            $old = isset($extra['old_price']) && $extra['old_price'] !== '' && $extra['old_price'] !== null
                ? (float) $extra['old_price'] : null;
            $new = $this->currentPrice($product->id);

            $diff = ($old !== null && $new !== null) ? round($new - $old, 2) : null;
            $direction = $diff === null || $diff == 0 ? 'updated' : ($diff > 0 ? 'increased' : 'decreased');

            $data += [
                'oldPrice'       => $old,
                'newPrice'       => $new,
                'priceDiff'      => $diff,
                'pricePct'       => ($diff !== null && $old > 0) ? round($diff / $old * 100, 1) : null,
                'priceDirection' => $direction,
                // Used in the subject line: "Dealer Price Increased | ..."
                'priceDirectionLabel' => ucfirst($direction),
            ];
        }

        return $data;
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** Valid addresses out of a column that may hold one or a list. */
    protected function emailsFrom($value): array
    {
        $parts = preg_split('/[,;\s]+/', (string) $value);

        return array_values(array_unique(array_filter($parts, function ($e) {
            return filter_var($e, FILTER_VALIDATE_EMAIL);
        })));
    }
}
