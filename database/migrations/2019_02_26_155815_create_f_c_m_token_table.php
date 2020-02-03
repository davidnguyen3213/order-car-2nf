<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFCMTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('uc_id')->nullable()->index();
            $table->string('device_token',512)->nullable();
            $table->string('platform',50)->nullable();
            $table->tinyInteger('type')->comment('1: USER, 2: COMPANY');
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
        Schema::dropIfExists('fcm_tokens');
    }
}
