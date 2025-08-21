<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\PurchaseOrder;
use App\Customer;

class UpdateLinkedEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Run using: php artisan purchase-orders:update-linked-employee
     */
    protected $signature = 'purchase-orders:update-linked-employee';

    /**
     * The console command description.
     */
    protected $description = 'Update linked_employee_id in purchase_orders from latest user_customer_shares entry';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating purchase_orders...');

        $orders = PurchaseOrder::whereNotNull('customer_id')->get();
        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            $customer = Customer::with(['user_customer_shares' => function ($q) {
                $q->latest(); // order by created_at desc
            }])->find($order->customer_id);

            if ($customer && $customer->user_customer_shares->isNotEmpty()) {
                $latestShare = $customer->user_customer_shares->first();
                $order->linked_employee_id = $latestShare->user_id;
                $order->save();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nAll purchase orders updated successfully!");
    }
}
