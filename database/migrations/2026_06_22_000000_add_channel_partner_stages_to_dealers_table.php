<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds the Channel-Partner lifecycle fields to the `dealers` table.
 *
 * NOTE: "Territory" / "Area of Operations" is NOT a new column —
 *       it is already stored in the `dealer_operating_cities` table.
 *       We just display those cities as the territory on the onboarding form.
 *
 * Run:  php artisan migrate
 */
class ChannelPartnerStagesToDealersTable extends Migration
{
    public function up()
    {
        Schema::table('dealers', function (Blueprint $table) {

            // ── 1. Lifecycle stage ────────────────────────────────────
            // Values: evaluation | onboarding | confirmed
            // Default = evaluation (every new dealer starts here)
            $table->enum('stage', ['evaluation', 'onboarding', 'confirmed'])
                  ->default('evaluation')
                  ->after('dealer_type');

            // ── 2. Channel Partner type after confirmation ────────────
            $table->enum('cp_status', ['provisional', 'authorized'])
                  ->nullable()
                  ->after('stage');

            // ── 3. Onboarding token (24-hour secure link) ────────────
            $table->string('onboarding_token', 64)
                  ->nullable()
                  ->unique()
                  ->after('cp_status');

            $table->timestamp('onboarding_token_expires_at')
                  ->nullable()
                  ->after('onboarding_token');

            $table->tinyInteger('onboarding_form_submitted')
                  ->default(0)
                  ->after('onboarding_token_expires_at');

            $table->timestamp('onboarding_submitted_at')
                  ->nullable()
                  ->after('onboarding_form_submitted');

            // ── 4. Fields filled by dealer in the onboarding form ────
            $table->string('business_constitution', 100)->nullable();
            // pan_no — new (gst_no already exists)
            $table->string('pan_no', 20)->nullable();

            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->tinyInteger('same_as_billing')->default(0);

            // Accounts contact (optional)
            $table->string('accounts_contact_person', 191)->nullable();
            $table->string('accounts_mobile', 15)->nullable();
            $table->string('accounts_email', 191)->nullable();

            // Bank details
            $table->string('bank_name', 191)->nullable();
            $table->string('bank_account_name', 191)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_ifsc', 20)->nullable();

            // Document uploads (relative paths under public/uploads/onboarding/{id}/)
            $table->string('doc_gst_certificate', 500)->nullable();
            $table->string('doc_pan_card', 500)->nullable();
            $table->string('doc_cancelled_cheque', 500)->nullable();
            $table->string('doc_visiting_card', 500)->nullable();

            // Declaration
            $table->tinyInteger('declaration_accepted')->default(0);
            $table->timestamp('declaration_accepted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('dealers', function (Blueprint $table) {
            $table->dropColumn([
                'stage', 'cp_status',
                'onboarding_token', 'onboarding_token_expires_at',
                'onboarding_form_submitted', 'onboarding_submitted_at',
                'business_constitution', 'pan_no',
                'billing_address', 'shipping_address', 'same_as_billing',
                'accounts_contact_person', 'accounts_mobile', 'accounts_email',
                'bank_name', 'bank_account_name', 'bank_account_number', 'bank_ifsc',
                'doc_gst_certificate', 'doc_pan_card', 'doc_cancelled_cheque', 'doc_visiting_card',
                'declaration_accepted', 'declaration_accepted_at',
            ]);
        });
    }
}
