<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCorrespondingArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corresponding_area', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->index();
            $table->string('corresponding_area')->index();
            $table->tinyInteger('type')->default(1)->comment('1: company; 2: unregistered_company');
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
        Schema::dropIfExists('corresponding_area');
    }
}
