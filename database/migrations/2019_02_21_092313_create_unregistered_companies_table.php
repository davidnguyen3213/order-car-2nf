<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnregisteredCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unregistered_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('address')->nullable();
            $table->string('phone');
            $table->string('base_price')->nullable()->default(0);
            $table->integer('display_order')->nullable();
            $table->mediumText('corresponding_area')->nullable();
            $table->string('company_pr')->nullable();
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
        Schema::dropIfExists('unregistered_companies');
    }
}
