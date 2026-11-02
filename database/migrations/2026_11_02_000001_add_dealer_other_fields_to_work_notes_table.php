<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * A work note about a dealer who is not on the system yet.
 *
 * `dealer_id` only covers dealers already registered. The field user often
 * meets someone who is not, so these three columns hold whatever they can
 * give us — a name, a mobile, an address — as plain text alongside the note.
 *
 * All three are nullable: the app sends them only when it has them, and
 * builds already in the field never send them at all.
 */
class AddDealerOtherFieldsToWorkNotesTable extends Migration
{
    public function up()
    {
        Schema::table('work_notes', function (Blueprint $table) {
            $table->string('dealer_other', 255)->nullable()->after('dealer_id')
                ->comment('Dealer not on the system — free text name');
            $table->string('dealer_other_mobile', 20)->nullable()->after('dealer_other');
            $table->text('dealer_other_address')->nullable()->after('dealer_other_mobile');
        });
    }

    public function down()
    {
        Schema::table('work_notes', function (Blueprint $table) {
            $table->dropColumn(['dealer_other', 'dealer_other_mobile', 'dealer_other_address']);
        });
    }
}
