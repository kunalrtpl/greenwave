<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUserSchedulersTableAddNewFields extends Migration
{
    private function foreignKeyExists(string $table, string $fkName): bool
    {
        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA    = DATABASE()
              AND TABLE_NAME      = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME = ?
        ", [$table, $fkName]);

        return !empty($result);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("
            SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = ?
              AND INDEX_NAME   = ?
        ", [$table, $indexName]);

        return !empty($result);
    }

    public function up()
    {
        // ── 1. Add new columns (all guarded with hasColumn) ───────────────────
        Schema::table('user_schedulers', function (Blueprint $table) {

            // related_to — what this scheduler is linked to
            if (!Schema::hasColumn('user_schedulers', 'related_to')) {
                $table->string('related_to', 100)->nullable()->after('user_id');
            }

            // dealer_id — FK to dealers
            if (!Schema::hasColumn('user_schedulers', 'dealer_id')) {
                $table->unsignedBigInteger('dealer_id')->nullable()->index()->after('related_to');
            }

            // other_customer_name — free-text when no FK applies
            if (!Schema::hasColumn('user_schedulers', 'other_customer_name')) {
                $table->string('other_customer_name', 255)->nullable()->after('dealer_id');
            }

            // subject
            if (!Schema::hasColumn('user_schedulers', 'subject')) {
                $table->string('subject', 255)->nullable()->after('other_customer_name');
            }

            // user_dvr_id — FK to user_dvrs (new; dvr_id kept for old APIs)
            if (!Schema::hasColumn('user_schedulers', 'user_dvr_id')) {
                $table->unsignedBigInteger('user_dvr_id')->nullable()->index()->after('dvr_id');
            }
        });

        // ── 2. Foreign keys ───────────────────────────────────────────────────
        Schema::table('user_schedulers', function (Blueprint $table) {
            if (!$this->foreignKeyExists('user_schedulers', 'user_schedulers_dealer_id_foreign')) {
                $table->foreign('dealer_id')
                      ->references('id')->on('dealers')
                      ->onDelete('set null');
            }

            if (!$this->foreignKeyExists('user_schedulers', 'user_schedulers_user_dvr_id_foreign')) {
                $table->foreign('user_dvr_id')
                      ->references('id')->on('user_dvrs')
                      ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('user_schedulers', function (Blueprint $table) {
            if ($this->foreignKeyExists('user_schedulers', 'user_schedulers_dealer_id_foreign')) {
                $table->dropForeign(['dealer_id']);
            }
            if ($this->foreignKeyExists('user_schedulers', 'user_schedulers_user_dvr_id_foreign')) {
                $table->dropForeign(['user_dvr_id']);
            }

            $toDrop = array_filter(
                ['related_to', 'dealer_id', 'other_customer_name', 'subject', 'user_dvr_id'],
                fn($col) => Schema::hasColumn('user_schedulers', $col)
            );

            if (!empty($toDrop)) {
                $table->dropColumn(array_values($toDrop));
            }
        });
    }
}
