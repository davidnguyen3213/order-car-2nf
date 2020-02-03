<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('address')->nullable();
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('raw_pass');
            $table->mediumText('company_pr')->nullable();
            $table->string('base_price')->default(0);
            $table->string('person_charged')->nullable();
            $table->mediumText('corresponding_area')->nullable();
            $table->tinyInteger('status_notify')->default(1)->comment('0: off; 1: on');
            $table->tinyInteger('status_login')->default(0)->comment('1: enable; 0: disable');
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
        Schema::dropIfExists('companies');
    }
}
