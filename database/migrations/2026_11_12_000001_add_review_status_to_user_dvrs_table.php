<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * The admin's review of a DVR.
 *
 * dvr_status    'Pending Verification' until an admin marks it 'Verified'. It
 *               only ever moves forward: a verified DVR cannot be put back to
 *               pending. Visits dated up to 31 Oct 2026 are set to 'Verified'
 *               here; November 2026 onwards stays pending for an admin to
 *               verify by hand.
 *
 * query_status  Where the query thread (user_dvr_queries) stands. NULL until an
 *               admin raises a query, then 'Query Raised' each time an admin
 *               writes and 'Query Responded' each time the employee answers.
 *
 * Not to be confused with dvr_verified_date_time, which is the customer
 * contact's OTP confirmation done from the app, not the admin's review.
 */
class AddReviewStatusToUserDvrsTable extends Migration
{
    /** Visits dated on or before this are marked Verified when the column is added. */
    const VERIFIED_UP_TO = '2026-10-31';

    public function up()
    {
        Schema::table('user_dvrs', function (Blueprint $table) {
            $table->enum('dvr_status', ['Pending Verification', 'Verified'])
                  ->default('Pending Verification')
                  ->after('dvr_verified_date_time')
                  ->comment('Admin review. Pending Verification -> Verified only, never back');
            $table->unsignedBigInteger('dvr_verified_by')->nullable()->after('dvr_status')
                  ->comment('users.id of the admin who verified it; null for DVRs verified by this migration');
            $table->dateTime('dvr_verified_at')->nullable()->after('dvr_verified_by');
            $table->enum('query_status', ['Query Raised', 'Query Responded'])
                  ->nullable()
                  ->default(null)
                  ->after('dvr_verified_at')
                  ->comment('Latest side to write on user_dvr_queries: admin = Query Raised, employee = Query Responded');

            $table->index('dvr_status');
            $table->index('query_status');
        });

        // Client decision: every visit up to and including 31 Oct 2026 counts as
        // reviewed. Visits from 1 Nov 2026 onwards keep the column default,
        // 'Pending Verification', and are verified by an admin by hand.
        DB::table('user_dvrs')
            ->where('dvr_date', '<=', self::VERIFIED_UP_TO)
            ->update(['dvr_status' => 'Verified']);
    }

    public function down()
    {
        Schema::table('user_dvrs', function (Blueprint $table) {
            $table->dropIndex(['dvr_status']);
            $table->dropIndex(['query_status']);
            $table->dropColumn(['dvr_status', 'dvr_verified_by', 'dvr_verified_at', 'query_status']);
        });
    }
}
