<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CustomerPurchaseReturn;
use App\Customer;

class UpdateCustomerPurchaseReturnLinkedEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Run using: php artisan customer-returns:update-linked-employee
     */
    protected $signature = 'customer-returns:update-linked-employee';

    /**
     * The console command description.
     */
    protected $description = 'Update linked_employee_id in customer_purchase_returns using the latest user_customer_shares entry';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating customer_purchase_returns...');

        $returns = CustomerPurchaseReturn::whereNotNull('customer_id')->get();
        $bar = $this->output->createProgressBar($returns->count());
        $bar->start();

        foreach ($returns as $return) {
            $customer = Customer::with(['user_customer_shares' => function ($q) {
                $q->latest(); // order by created_at desc
            }])->find($return->customer_id);

            if ($customer && $customer->user_customer_shares->isNotEmpty()) {
                $latestShare = $customer->user_customer_shares->first();
                $return->linked_employee_id = $latestShare->user_id;
                $return->save();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nAll customer purchase returns updated successfully!");
    }
}
