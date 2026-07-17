<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCustomerContacts extends Command
{
    protected $signature = 'customers:sync-contacts {--dry-run : Run without making actual changes}';

    protected $description = 'Update customer_contacts table based on customers.customer_register_request_id';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            Log::info('[SyncCustomerContacts] Starting in DRY RUN mode');
        } else {
            $this->info('LIVE MODE - Changes will be applied');
            Log::info('[SyncCustomerContacts] Starting in LIVE mode');
        }

        $customers = DB::table('customers')
            ->whereNotNull('customer_register_request_id')
            ->select('id', 'customer_register_request_id')
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No customers found with customer_register_request_id set.');
            return;
        }

        $this->info("Found {$customers->count()} customer(s) to process.");

        $totalUpdated = 0;

        foreach ($customers as $customer) {
            $contacts = DB::table('customer_contacts')
                ->where('customer_register_request_id', $customer->customer_register_request_id)
                ->get();

            if ($contacts->isEmpty()) {
                // Silently skip - no log, no output
                continue;
            }

            foreach ($contacts as $contact) {
                $this->info(
                    "Updating Contact ID: {$contact->id} | " .
                    "customer_id: " . ($contact->customer_id ?? 'NULL') . " -> {$customer->id} | " .
                    "customer_register_request_id: {$customer->customer_register_request_id} -> NULL"
                );

                Log::info("[SyncCustomerContacts] " . ($isDryRun ? '[DRY RUN] ' : '') .
                    "Contact ID={$contact->id} | " .
                    "customer_id=" . ($contact->customer_id ?? 'NULL') . " -> {$customer->id} | " .
                    "customer_register_request_id={$customer->customer_register_request_id} -> NULL");

                if (!$isDryRun) {
                    DB::table('customer_contacts')
                        ->where('id', $contact->id)
                        ->update([
                            'customer_id'                  => $customer->id,
                            'customer_register_request_id' => null,
                        ]);
                }

                $totalUpdated++;
            }
        }

        if ($isDryRun) {
            $this->warn("DRY RUN complete. {$totalUpdated} contact(s) WOULD BE updated.");
            Log::info("[SyncCustomerContacts] DRY RUN complete. Would update={$totalUpdated}");
        } else {
            $this->info("Done. {$totalUpdated} contact(s) updated.");
            Log::info("[SyncCustomerContacts] LIVE run complete. Updated={$totalUpdated}");
        }
    }
}