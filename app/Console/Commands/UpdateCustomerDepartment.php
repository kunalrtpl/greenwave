<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Customer;
use App\CustomerRegisterRequest;

class UpdateCustomerDepartment extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'customer:update-department';

    /**
     * The console command description.
     */
    protected $description = 'Update department based on designation for existing customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating Customer table...');
        $this->updateModel(Customer::class);

        $this->info('Updating CustomerRegisterRequest table...');
        $this->updateModel(CustomerRegisterRequest::class);

        $this->info('Department update completed successfully ✅');
    }

    /**
     * Update department for a model
     */
    private function updateModel(string $model)
    {
        $model::whereNotNull('designation')
            ->chunk(100, function ($records) use ($model) {
                foreach ($records as $record) {
                    $department = getDepartmentByDesignation($record->designation);

                    if ($department) {
                        $record->department = $department;
                        $record->save();
                    }
                }
            });
    }
}
