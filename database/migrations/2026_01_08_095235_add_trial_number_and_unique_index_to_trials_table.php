<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTrialNumberAndUniqueIndexToTrialsTable extends Migration
{
    public function up()
    {
        // 1️⃣ Add trial_number column
        Schema::table('trials', function (Blueprint $table) {
            $table->unsignedInteger('trial_number')
                  ->after('id')
                  ->nullable();
        });

        /*
         |----------------------------------------------------
         | 2️⃣ Backfill trial_number per user (1,2,3...)
         |----------------------------------------------------
         */
        $users = DB::table('trials')
            ->select('user_id')
            ->distinct()
            ->pluck('user_id');

        foreach ($users as $userId) {
            $trials = DB::table('trials')
                ->where('user_id', $userId)
                ->orderBy('id')
                ->get();

            $counter = 1;
            foreach ($trials as $trial) {
                DB::table('trials')
                    ->where('id', $trial->id)
                    ->update([
                        'trial_number' => $counter
                    ]);
                $counter++;
            }
        }

        // 3️⃣ Add unique constraint
        Schema::table('trials', function (Blueprint $table) {
            $table->unique(['user_id', 'trial_number'], 'unique_user_trial');
        });
    }

    public function down()
    {
        Schema::table('trials', function (Blueprint $table) {
            $table->dropUnique('unique_user_trial');
            $table->dropColumn('trial_number');
        });
    }
}
