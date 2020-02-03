<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToResponseForUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('response_for_users', function (Blueprint $table) {
            $table->index('request_id');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('response_for_users', function (Blueprint $table) {
            $table->dropIndex(['request_id']);
            $table->dropIndex(['company_id']);
        });
    }
}
