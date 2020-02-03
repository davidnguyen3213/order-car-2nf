<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->string('address_from')->index();
            $table->string('address_to');
            $table->string('address_note')->nullable();
            $table->string('created_time_request');
            $table->string('first_time_requested')->nullable();
            $table->tinyInteger('is_expired')->default(0)->comment('1: expired; 0: unexpired');
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
        Schema::dropIfExists('request_users');
    }
}
