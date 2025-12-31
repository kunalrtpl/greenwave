<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trials', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('trial_report_id')->nullable()->index();
            $table->string('trial_type')->nullable();

            $table->unsignedBigInteger('complaint_id')->nullable()->index();
            $table->unsignedBigInteger('other_team_member_id')->nullable()->index();

            $table->text('objective')->nullable();

            $table->tinyInteger('trial_done')->default(0);
            $table->tinyInteger('is_jointly')->default(0);

            $table->string('other_team_member_name')->nullable();
            $table->string('status')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trials');
    }
}
