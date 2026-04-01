<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeaveTypesTable extends Migration
{
    public function up()
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');                          // Sick Leave, Casual Leave, Earned Leave, LWP
            $table->string('code', 10)->unique();            // SL, CL, EL, LWP
            $table->boolean('has_quota')->default(true);     // LWP = false (no quota to track)
            // ⭐ KEY FLAG: Admin can edit quota for SL/CL but NOT for EL
            $table->boolean('quota_editable')->default(true);// EL = false
            $table->decimal('default_quota', 5, 1)->default(0); // Annual default (e.g. 12.0)
            $table->string('color', 10)->nullable();         // For calendar UI (#hex)
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_types');
    }
}