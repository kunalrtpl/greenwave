<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyWorkNotesTableAddActivityFields extends Migration
{
    // ── Helpers (avoid Doctrine DBAL entirely) ────────────────────────────────

    private function foreignKeyExists(string $table, string $fkName): bool
    {
        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME    = ?
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

    // ─────────────────────────────────────────────────────────────────────────

    public function up()
    {
        // ── 1. Drop old columns only if they exist ────────────────────────────
        Schema::table('work_notes', function (Blueprint $table) {
            $toDrop = array_filter(
                ['type', 'type_other', 'title', 'note'],
                fn($col) => Schema::hasColumn('work_notes', $col)
            );
            if (!empty($toDrop)) {
                $table->dropColumn(array_values($toDrop));
            }
        });

        // ── 2. Rename request_date → date if needed ───────────────────────────
        if (Schema::hasColumn('work_notes', 'request_date') && !Schema::hasColumn('work_notes', 'date')) {
            DB::statement('ALTER TABLE work_notes CHANGE `request_date` `date` DATE NOT NULL');
        }

        // ── 3. Add new columns (each guarded) ────────────────────────────────
        Schema::table('work_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('work_notes', 'related_to')) {
                $table->enum('related_to', ['dealer', 'customer', 'customer_register_request'])
                      ->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('work_notes', 'dealer_id')) {
                $table->unsignedBigInteger('dealer_id')->nullable()->after('related_to');
            }
            if (!Schema::hasColumn('work_notes', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('dealer_id');
            }
            if (!Schema::hasColumn('work_notes', 'customer_register_request_id')) {
                $table->unsignedBigInteger('customer_register_request_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('work_notes', 'subject')) {
                $table->string('subject', 255)->nullable()->after('customer_register_request_id');
            }
            if (!Schema::hasColumn('work_notes', 'activity_mode')) {
                $table->string('activity_mode', 100)->nullable()->after('subject');
            }
            if (!Schema::hasColumn('work_notes', 'description')) {
                $table->longText('description')->nullable()->after('activity_mode');
            }
            if (!Schema::hasColumn('work_notes', 'key_take_away')) {
                $table->longText('key_take_away')->nullable()->after('description');
            }
            if (!Schema::hasColumn('work_notes', 'further_action_required')) {
                $table->tinyInteger('further_action_required')->default(0)->after('key_take_away');
            }
            if (!Schema::hasColumn('work_notes', 'action_date')) {
                $table->date('action_date')->nullable()->after('further_action_required');
            }
            if (!Schema::hasColumn('work_notes', 'action_time')) {
                $table->time('action_time')->nullable()->after('action_date');
            }
            if (!Schema::hasColumn('work_notes', 'action_remarks')) {
                $table->text('action_remarks')->nullable()->after('action_time');
            }
        });

        // ── 4. Foreign keys (raw information_schema check, no Doctrine) ───────
        Schema::table('work_notes', function (Blueprint $table) {
            if (!$this->foreignKeyExists('work_notes', 'work_notes_dealer_id_foreign')) {
                $table->foreign('dealer_id')->references('id')->on('dealers')->onDelete('set null');
            }
            if (!$this->foreignKeyExists('work_notes', 'work_notes_customer_id_foreign')) {
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            }
            if (!$this->foreignKeyExists('work_notes', 'work_notes_customer_register_request_id_foreign')) {
                $table->foreign('customer_register_request_id')
                      ->references('id')->on('customer_register_requests')
                      ->onDelete('set null');
            }
        });

        // ── 5. Indexes ────────────────────────────────────────────────────────
        Schema::table('work_notes', function (Blueprint $table) {
            if (!$this->indexExists('work_notes', 'work_notes_related_to_index')) {
                $table->index('related_to');
            }
            if (!$this->indexExists('work_notes', 'work_notes_dealer_id_index')) {
                $table->index('dealer_id');
            }
            if (!$this->indexExists('work_notes', 'work_notes_customer_id_index')) {
                $table->index('customer_id');
            }
            if (!$this->indexExists('work_notes', 'work_notes_customer_register_request_id_index')) {
                $table->index('customer_register_request_id');
            }
        });
    }

    public function down()
    {
        Schema::table('work_notes', function (Blueprint $table) {
            if ($this->foreignKeyExists('work_notes', 'work_notes_dealer_id_foreign')) {
                $table->dropForeign(['dealer_id']);
            }
            if ($this->foreignKeyExists('work_notes', 'work_notes_customer_id_foreign')) {
                $table->dropForeign(['customer_id']);
            }
            if ($this->foreignKeyExists('work_notes', 'work_notes_customer_register_request_id_foreign')) {
                $table->dropForeign(['customer_register_request_id']);
            }

            if ($this->indexExists('work_notes', 'work_notes_related_to_index')) {
                $table->dropIndex(['related_to']);
            }
            if ($this->indexExists('work_notes', 'work_notes_dealer_id_index')) {
                $table->dropIndex(['dealer_id']);
            }
            if ($this->indexExists('work_notes', 'work_notes_customer_id_index')) {
                $table->dropIndex(['customer_id']);
            }
            if ($this->indexExists('work_notes', 'work_notes_customer_register_request_id_index')) {
                $table->dropIndex(['customer_register_request_id']);
            }

            $toDrop = array_filter([
                'related_to', 'dealer_id', 'customer_id', 'customer_register_request_id',
                'subject', 'activity_mode', 'description', 'key_take_away',
                'further_action_required', 'action_date', 'action_time', 'action_remarks',
            ], fn($col) => Schema::hasColumn('work_notes', $col));

            if (!empty($toDrop)) {
                $table->dropColumn(array_values($toDrop));
            }
        });

        // Rename date → request_date
        if (Schema::hasColumn('work_notes', 'date') && !Schema::hasColumn('work_notes', 'request_date')) {
            DB::statement('ALTER TABLE work_notes CHANGE `date` `request_date` DATE NOT NULL');
        }

        // Restore old columns
        Schema::table('work_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('work_notes', 'type')) {
                $table->string('type', 100)->nullable();
            }
            if (!Schema::hasColumn('work_notes', 'type_other')) {
                $table->string('type_other', 255)->nullable();
            }
            if (!Schema::hasColumn('work_notes', 'title')) {
                $table->string('title', 255)->nullable();
            }
            if (!Schema::hasColumn('work_notes', 'note')) {
                $table->longText('note')->nullable();
            }
        });
    }
}