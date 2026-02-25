<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerFieldsToTrialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trials', function (Blueprint $blueprint) {
            // Change unsignedInteger to unsignedBigInteger
            $blueprint->unsignedBigInteger('customer_id')->nullable()->after('user_id');
            $blueprint->unsignedBigInteger('customer_register_request_id')->nullable()->after('customer_id');

            // Foreign Key Constraints
            $blueprint->foreign('customer_id')
                      ->references('id')->on('customers')
                      ->onDelete('set null');

            $blueprint->foreign('customer_register_request_id', 'trials_cust_reg_req_foreign')
                      ->references('id')->on('customer_register_requests')
                      ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trials', function (Blueprint $blueprint) {
            // Drop foreign keys first
            $blueprint->dropForeign(['customer_id']);
            $blueprint->dropForeign('trials_cust_reg_req_foreign');

            // Drop columns
            $blueprint->dropColumn(['customer_id', 'customer_register_request_id']);
        });
    }
}