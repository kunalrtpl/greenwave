<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerRegisterRequestIdToMultipleTables extends Migration
{
    public function up()
    {
        $tables = [
            'complaint_samples',
            'sample_submissions',
            'feedback',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->unsignedBigInteger('customer_register_request_id')
                      ->nullable()
                      ->after('customer_id');

                // Foreign key
                $table->foreign('customer_register_request_id')
                      ->references('id')
                      ->on('customer_register_requests')
                      ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        $tables = [
            'complaint_samples',
            'sample_submissions',
            'feedback',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign([$table.'_customer_register_request_id_foreign']);
                $table->dropColumn('customer_register_request_id');
            });
        }
    }
}

