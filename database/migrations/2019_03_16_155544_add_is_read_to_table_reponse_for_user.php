<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsReadToTableReponseForUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('response_for_users', function (Blueprint $table) {
            $table->tinyInteger('is_read')->after('is_deleted')->default(0)->comment('0: unread; 1: read');
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
            $table->dropColumn('is_read');
        });
    }
}
