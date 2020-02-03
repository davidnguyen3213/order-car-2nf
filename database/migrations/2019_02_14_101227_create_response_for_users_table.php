<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResponseForUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('response_for_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_id');
            $table->integer('company_id');
            $table->integer('time_pickup')->default(0);
            $table->string('user_accept_time')->default(-1);
            $table->tinyInteger('status')->default(0)->comment('0: unaccepted; 1: accepted');
            $table->tinyInteger('is_approved')->default(0)->comment('0: unapproved; 1: approved');
            $table->tinyInteger('is_deleted')->default(0)->comment('0: not yet deleted; 1: deleted');
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
        Schema::dropIfExists('response_for_users');
    }
}
