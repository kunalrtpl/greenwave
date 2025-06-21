<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('name');
            $table->string('dob');
            $table->string('gender');
            $table->string('mobile');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('correspondence_address');
            $table->string('permanent_address');
            $table->string('image');
            $table->string('home_landline_no');
            $table->string('emergency_contact_person');
            $table->string('emergency_contact_person_mobile');
            $table->string('pan');
            $table->string('aadhar');
            $table->string('driving_license');
            $table->string('aadhar_proof');
            $table->string('driving_license_proof');
            $table->string('pan_proof');
            $table->string('joining_date');
            $table->string('joining_type');
            $table->string('permanent_from');
            $table->string('salary_account_no');
            $table->tinyInteger('status')->default(1);
            $table->string('notification_token');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
