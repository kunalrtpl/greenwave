<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Discontinuing a product from Product Pricing, and telling the people who
 * deal in it.
 *
 * Who is "linked" to a product:
 *   employees  rows in `user_products`, active users only (users.status = 1)
 *   dealers    rows in `dealer_linked_products`, active and not deleted
 *
 * Emails are sent one recipient at a time, in small batches posted by the
 * admin screen, so the screen can show live progress. Nothing is queued: each
 * batch request sends its emails before it returns.
 *
 * The browser only ever sends recipient ids. Addresses are looked up again
 * here, and anyone who is no longer linked to the product is skipped, so a
 * tampered request cannot email an arbitrary address.
 *
 * While config('mail.redirect_to') is set, every message goes to that one
 * inbox instead — see EmailService::applyRedirect().
 */
class ProductDiscontinueService
{
    const EVENT_EMPLOYEE = 'product_discontinued_employee';
    const EVENT_DEALER   = 'product_discontinued_dealer';

    /** Most recipients a single batch request may carry. */
    const MAX_BATCH = 10;

    // ─────────────────────────────────────────────────────────────────────────
    // Status
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Set or clear the discontinued flag. Returns an error message, or null.
     */
    public function setDiscontinued($product, bool $discontinued)
    {
        if ($discontinued && $product->not_available) {
            return 'This product is saved as Not Available. Untick N.A. and click Update first, then mark it as discontinued.';
        }

        DB::table('products')->where('id', $product->id)->update([
            'discontinued' => $discontinued ? 1 : 0,
            'updated_at'   => now(),
        ]);

        return null;
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
     * Email one batch of recipients and report how each one went.
     *
     * @param object $product     row from `products`
     * @param array  $recipients  [['type' => 'employee'|'dealer', 'id' => 12], ...]
     * @param string|null $remarks    optional note from the admin, shown in the email
     * @return array  [['type','id','status' => sent|failed|skipped,'message'], ...]
     */
    public function notifyBatch($product, array $recipients, $remarks = null): array
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

        $common = [
            'product'            => $product,
            'remarks'        => $remarks ? trim($remarks) : null,
            'discontinuedOn' => Carbon::now()->format('d M Y'),
        ];

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

            $event = $type === 'dealer' ? self::EVENT_DEALER : self::EVENT_EMPLOYEE;
            $sent  = EmailService::send($event, $common + ['recipient' => $recipient], $recipient->emails);

            $results[] = $row + ($sent
                ? ['status' => 'sent',   'message' => 'Sent to ' . $recipient->email]
                : ['status' => 'failed', 'message' => 'Could not be sent — see the log']);
        }

        return $results;
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
