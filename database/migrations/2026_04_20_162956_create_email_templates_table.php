<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_key')->unique();   // unique identifier e.g. po_admin, user_welcome
            $table->string('name');                  // human readable label
            $table->string('subject');               // email subject
            $table->string('blade_view');            // e.g. emails.po.admin  or  emails.user.welcome
            $table->json('to_emails')->nullable();   // fixed recipients — null if caller provides TO
            $table->json('cc_emails')->nullable();
            $table->json('bcc_emails')->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('email_templates');
    }
}
