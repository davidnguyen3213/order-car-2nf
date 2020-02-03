<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsHistoryDeletedToRequestUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_users', function (Blueprint $table) {
            $table->tinyInteger('is_history_deleted')->after('is_expired')->default(0)->comment('1: deleted in history list; 0: active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_users', function (Blueprint $table) {
            $table->dropColumn('is_history_deleted');
        });
    }
}
