<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChannelPartnerConfirmFieldsToDealersTable extends Migration
{
    public function up()
    {
        Schema::table('dealers', function (Blueprint $table) {
            if (!Schema::hasColumn('dealers', 'security_deposit_status')) {
                $table->string('security_deposit_status', 20)->nullable()->after('cp_status');
            }
            if (!Schema::hasColumn('dealers', 'security_deposit_received_amount')) {
                $table->decimal('security_deposit_received_amount', 12, 2)->nullable()->after('security_deposit_status');
            }
            if (!Schema::hasColumn('dealers', 'deposit_credit_details')) {
                $table->string('deposit_credit_details', 255)->nullable()->after('security_deposit_received_amount');
            }
            if (!Schema::hasColumn('dealers', 'gst_checked')) {
                $table->boolean('gst_checked')->default(0)->after('deposit_credit_details');
            }
            if (!Schema::hasColumn('dealers', 'pan_checked')) {
                $table->boolean('pan_checked')->default(0)->after('gst_checked');
            }
            if (!Schema::hasColumn('dealers', 'bank_details_checked')) {
                $table->boolean('bank_details_checked')->default(0)->after('pan_checked');
            }
            if (!Schema::hasColumn('dealers', 'evaluation_form_pdf')) {
                $table->string('evaluation_form_pdf', 255)->nullable()->after('bank_details_checked');
            }
            if (!Schema::hasColumn('dealers', 'onboarding_form_pdf')) {
                $table->string('onboarding_form_pdf', 255)->nullable()->after('evaluation_form_pdf');
            }
            if (!Schema::hasColumn('dealers', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('onboarding_form_pdf');
            }
            // Safety net — only add if these were never created on this install:
            if (!Schema::hasColumn('dealers', 'source_of_lead')) {
                $table->string('source_of_lead', 255)->nullable()->after('short_name');
            }
        });
    }

    public function down()
    {
        Schema::table('dealers', function (Blueprint $table) {
            foreach ([
                'security_deposit_status', 'security_deposit_received_amount', 'deposit_credit_details',
                'gst_checked', 'pan_checked', 'bank_details_checked',
                'evaluation_form_pdf', 'onboarding_form_pdf', 'confirmed_at',
            ] as $col) {
                if (Schema::hasColumn('dealers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
