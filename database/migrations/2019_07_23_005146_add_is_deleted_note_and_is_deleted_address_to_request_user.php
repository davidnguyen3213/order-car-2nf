<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDeletedNoteAndIsDeletedAddressToRequestUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_users', function (Blueprint $table) {
            $table->tinyInteger('is_deleted_note')->after('is_history_deleted')->default(0)->comment('1: deleted in suggest list; 0: active');
            $table->tinyInteger('is_deleted_address')->after('is_deleted_note')->default(0)->comment('1: deleted in suggest list; 0: active');
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
            $table->dropColumn('is_deleted_note');
            $table->dropColumn('is_deleted_address');
        });
    }
}
