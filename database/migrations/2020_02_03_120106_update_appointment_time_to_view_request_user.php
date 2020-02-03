<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAppointmentTimeToViewRequestUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS view_request_user');
        DB::statement("
            CREATE VIEW `view_request_user` 
            AS 
            SELECT
                `request_users`.`id` AS `request_users_id`,
                `request_users`.`user_id` AS `request_users_user_id`,
                `request_users`.`address_from` AS `request_users_address_from`,
                `request_users`.`address_to` AS `request_users_address_to`,
                `request_users`.`address_note` AS `request_users_address_note`,
                `request_users`.`created_time_request` AS `request_users_created_time_request`,
                `request_users`.`first_time_requested` AS `request_users_first_time_requested`,
                `request_users`.`appointment_time` AS `request_users_appointment_time`,
                `request_users`.`is_expired` AS `request_users_is_expired`,
                `request_users`.`is_cancel` AS `request_users_is_cancel`,
                `request_users`.`created_at` AS `request_users_created_at`,
                `request_users`.`updated_at` AS `request_users_updated_at`,
                `response_for_users`.`id` AS `response_for_users_id`,
                `response_for_users`.`request_id` AS `response_for_users_request_id`,
                `response_for_users`.`company_id` AS `response_for_users_company_id`,
                `response_for_users`.`time_pickup` AS `response_for_users_time_pickup`,
                `response_for_users`.`user_accept_time` AS `response_for_users_user_accept_time`,
                IF(ISNULL(`response_for_users`.`status`), 0, `response_for_users`.`status`) AS `response_for_users_status`,
                `response_for_users`.`is_approved` AS `response_for_users_is_approved`,
                `response_for_users`.`is_deleted` AS `response_for_users_is_deleted`,
                `response_for_users`.`is_cancel` AS `response_for_users_is_cancel`,
                `response_for_users`.`created_at` AS `response_for_users_created_at`,
                `response_for_users`.`updated_at` AS `response_for_users_updated_at` 
            FROM
                ( `request_users` LEFT JOIN `response_for_users` ON ( ( `request_users`.`id` = `response_for_users`.`request_id` ) ) ) UNION ALL
            SELECT
                `request_users`.`id` AS `request_users_id`,
                `request_users`.`user_id` AS `request_users_user_id`,
                `request_users`.`address_from` AS `request_users_address_from`,
                `request_users`.`address_to` AS `request_users_address_to`,
                `request_users`.`address_note` AS `request_users_address_note`,
                `request_users`.`created_time_request` AS `request_users_created_time_request`,
                `request_users`.`first_time_requested` AS `request_users_first_time_requested`,
                `request_users`.`appointment_time` AS `request_users_appointment_time`,
                `request_users`.`is_expired` AS `request_users_is_expired`,
                `request_users`.`is_cancel` AS `request_users_is_cancel`,
                `request_users`.`created_at` AS `request_users_created_at`,
                `request_users`.`updated_at` AS `request_users_updated_at`,
                `response_for_users`.`id` AS `response_for_users_id`,
                `response_for_users`.`request_id` AS `response_for_users_request_id`,
                `response_for_users`.`company_id` AS `response_for_users_company_id`,
                `response_for_users`.`time_pickup` AS `response_for_users_time_pickup`,
                `response_for_users`.`user_accept_time` AS `response_for_users_user_accept_time`,
                IF(ISNULL(`response_for_users`.`status`), 0, `response_for_users`.`status`) AS `response_for_users_status`,
                `response_for_users`.`is_approved` AS `response_for_users_is_approved`,
                `response_for_users`.`is_deleted` AS `response_for_users_is_deleted`,
                `response_for_users`.`is_cancel` AS `response_for_users_is_cancel`,
                `response_for_users`.`created_at` AS `response_for_users_created_at`,
                `response_for_users`.`updated_at` AS `response_for_users_updated_at` 
            FROM
                ( `response_for_users` LEFT JOIN `request_users` ON ( ( `request_users`.`id` = `response_for_users`.`request_id` ) ) ) 
            WHERE
                isnull( `request_users`.`id` ) 
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS view_request_user');
        DB::statement("
            CREATE VIEW `view_request_user` 
            AS 
            SELECT
                `request_users`.`id` AS `request_users_id`,
                `request_users`.`user_id` AS `request_users_user_id`,
                `request_users`.`address_from` AS `request_users_address_from`,
                `request_users`.`address_to` AS `request_users_address_to`,
                `request_users`.`address_note` AS `request_users_address_note`,
                `request_users`.`created_time_request` AS `request_users_created_time_request`,
                `request_users`.`first_time_requested` AS `request_users_first_time_requested`,
                `request_users`.`is_expired` AS `request_users_is_expired`,
                `request_users`.`is_cancel` AS `request_users_is_cancel`,
                `request_users`.`created_at` AS `request_users_created_at`,
                `request_users`.`updated_at` AS `request_users_updated_at`,
                `response_for_users`.`id` AS `response_for_users_id`,
                `response_for_users`.`request_id` AS `response_for_users_request_id`,
                `response_for_users`.`company_id` AS `response_for_users_company_id`,
                `response_for_users`.`time_pickup` AS `response_for_users_time_pickup`,
                `response_for_users`.`user_accept_time` AS `response_for_users_user_accept_time`,
                IF(ISNULL(`response_for_users`.`status`), 0, `response_for_users`.`status`) AS `response_for_users_status`,
                `response_for_users`.`is_approved` AS `response_for_users_is_approved`,
                `response_for_users`.`is_deleted` AS `response_for_users_is_deleted`,
                `response_for_users`.`is_cancel` AS `response_for_users_is_cancel`,
                `response_for_users`.`created_at` AS `response_for_users_created_at`,
                `response_for_users`.`updated_at` AS `response_for_users_updated_at` 
            FROM
                ( `request_users` LEFT JOIN `response_for_users` ON ( ( `request_users`.`id` = `response_for_users`.`request_id` ) ) ) UNION ALL
            SELECT
                `request_users`.`id` AS `request_users_id`,
                `request_users`.`user_id` AS `request_users_user_id`,
                `request_users`.`address_from` AS `request_users_address_from`,
                `request_users`.`address_to` AS `request_users_address_to`,
                `request_users`.`address_note` AS `request_users_address_note`,
                `request_users`.`created_time_request` AS `request_users_created_time_request`,
                `request_users`.`first_time_requested` AS `request_users_first_time_requested`,
                `request_users`.`is_expired` AS `request_users_is_expired`,
                `request_users`.`is_cancel` AS `request_users_is_cancel`,
                `request_users`.`created_at` AS `request_users_created_at`,
                `request_users`.`updated_at` AS `request_users_updated_at`,
                `response_for_users`.`id` AS `response_for_users_id`,
                `response_for_users`.`request_id` AS `response_for_users_request_id`,
                `response_for_users`.`company_id` AS `response_for_users_company_id`,
                `response_for_users`.`time_pickup` AS `response_for_users_time_pickup`,
                `response_for_users`.`user_accept_time` AS `response_for_users_user_accept_time`,
                IF(ISNULL(`response_for_users`.`status`), 0, `response_for_users`.`status`) AS `response_for_users_status`,
                `response_for_users`.`is_approved` AS `response_for_users_is_approved`,
                `response_for_users`.`is_deleted` AS `response_for_users_is_deleted`,
                `response_for_users`.`is_cancel` AS `response_for_users_is_cancel`,
                `response_for_users`.`created_at` AS `response_for_users_created_at`,
                `response_for_users`.`updated_at` AS `response_for_users_updated_at` 
            FROM
                ( `response_for_users` LEFT JOIN `request_users` ON ( ( `request_users`.`id` = `response_for_users`.`request_id` ) ) ) 
            WHERE
                isnull( `request_users`.`id` ) 
        ");
    }
}
