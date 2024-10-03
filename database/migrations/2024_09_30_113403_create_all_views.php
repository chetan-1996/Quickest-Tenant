<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW content_file_timeline_views AS
            SELECT
                `c`.* , `u`.`name` AS `user_name`
                FROM `content_file_timelines` `c`
            LEFT JOIN `users` `u` ON `c`.`user_id` = `u`.`id`
        ");

        DB::statement("
            CREATE VIEW content_message_timeline_views AS
            SELECT
                `c`.* ,
                `u`.`name`             AS `user_name`
                FROM `content_message_timelines` `c`
                LEFT JOIN `users` `u` ON `c`.`user_id` = `u`.`id`
        ");

        DB::statement("
            CREATE VIEW customer_timeline_lead_lost AS
            SELECT `ranked`.`id` AS `id`,
            `ranked`.`estimate_id` AS `estimate_id`,
            `ranked`.`assigned_to` AS `assigned_to`,
            `ranked`.`customer_id` AS `customer_id`,
            `ranked`.`activity_type` AS `activity_type`,
            `ranked`.`activity_name` AS `activity_name`,
            `ranked`.`activity_notes` AS `activity_notes`,
            `ranked`.`internal_remarks` AS `internal_remarks`,
            `ranked`.`follow_up_datetime` AS `follow_up_datetime`,
            `ranked`.`entry_type` AS `entry_type`,
            `ranked`.`is_modified` AS `is_modified`,
            `ranked`.`is_follow_up` AS `is_follow_up`,
            `ranked`.`company_id` AS `company_id`,
            `ranked`.`user_id` AS `user_id`,`ranked`.
            `created_by` AS `created_by`,
            `ranked`.`updated_by` AS `updated_by`,
            `ranked`.`created_at` AS `created_at`,
            `ranked`.`updated_at` AS `updated_at`,
            `ranked`.`estimate_version_no` AS `estimate_version_no`,
            `ranked`.`net_amount` AS `net_amount`,
            `ranked`.`activity_estimate_status` AS `activity_estimate_status`,
            `ranked`.`read_flag` AS `read_flag`,
            `ranked`.`read_by` AS `read_by`,
            `ranked`.`read_date` AS `read_date`,
            `ranked`.`content_id` AS `content_id`,
            `ranked`.`row_num` AS `row_num` 
            FROM (SELECT 
            `ct`.`id` AS `id`,
            `ct`.`estimate_id` AS `estimate_id`,
            `ct`.`assigned_to` AS `assigned_to`,
            `ct`.`customer_id` AS `customer_id`,
            `ct`.`activity_type` AS `activity_type`,
            `ct`.`activity_name` AS `activity_name`,
            `ct`.`activity_notes` AS `activity_notes`,
            `ct`.`internal_remarks` AS `internal_remarks`,
            `ct`.`follow_up_datetime` AS `follow_up_datetime`,
            `ct`.`entry_type` AS `entry_type`,
            `ct`.`is_modified` AS `is_modified`,
            `ct`.`is_follow_up` AS `is_follow_up`,
            `ct`.`company_id` AS `company_id`,
            `ct`.`user_id` AS `user_id`,
            `ct`.`created_by` AS `created_by`,
            `ct`.`updated_by` AS `updated_by`,
            `ct`.`created_at` AS `created_at`,
            `ct`.`updated_at` AS `updated_at`,
            `ct`.`estimate_version_no` AS `estimate_version_no`,
            `ct`.`net_amount` AS `net_amount`,
            `ct`.`activity_estimate_status` AS `activity_estimate_status`,
            `ct`.`read_flag` AS `read_flag`,
            `ct`.`read_by` AS `read_by`,
            `ct`.`read_date` AS `read_date`,
            `ct`.`content_id` AS `content_id`,
            ROW_NUMBER() OVER ( PARTITION BY `ct`.`customer_id`,`ct`.`activity_type` ORDER BY `ct`.`created_at` DESC) AS `row_num`
             FROM `customer_timelines` `ct` WHERE `ct`.`activity_type` = 17) `ranked` WHERE `ranked`.`row_num` = 1
        ");

        DB::statement("
            CREATE VIEW customer_timeline_lead_lost_common AS
            SELECT * FROM (
                SELECT
                    ct.*,
                    ROW_NUMBER() OVER (PARTITION BY customer_id ORDER BY created_at DESC) AS row_num
                FROM customer_timelines ct
                WHERE activity_type IN (17, 18)
            ) ranked
            WHERE row_num = 1
        ");

        DB::statement("
            CREATE VIEW customer_timeline_lead_won AS
                SELECT *
                FROM (
                    SELECT
                        ct.*,
                        ROW_NUMBER() OVER (PARTITION BY customer_id, activity_type ORDER BY created_at DESC) AS row_num
                    FROM customer_timelines ct
                    WHERE activity_type IN (18)
                ) ranked
                WHERE row_num = 1
        ");

        DB::statement("
            CREATE VIEW customers_views AS
                SELECT
                    c.*,
                    s.name AS state_name,
                    cn.name AS country_name,
                    cc.name AS lead_category,
                    cl.name AS lead_origin,
                    u.name AS user_name,
                    e.id AS estimate_id,
                    e.estimate_no AS estimate_no,
                    e.estimate_version AS estimate_version,
                    e.net_amount AS net_amount,
                    e.status AS estimate_status,
                    e.estimate_date AS estimate_date,
                    ct.activity_notes AS last_activity,
                    ct.updated_at AS last_activity_date,
                    ct.activity_type AS last_activity_type,
                    ct.activity_name AS last_activity_name,
                    ct.internal_remarks AS last_internal_remarks,
                    ct.follow_up_datetime AS last_follow_up_datetime,
                    ct.updated_at AS last_activity_updated_at,
                    ct.id AS last_activity_id,
                    ct.read_flag AS last_read_flag,
                    ct.read_by AS last_read_by,
                    ct.read_date AS last_read_date,
                    ct.content_id AS last_content_id,
                    ct.is_modified AS last_is_modified,
                    ct.is_follow_up AS last_is_follow_up,
                    ls.name AS lead_stage_name,
                    ls.color_code AS lead_stage_color_code,
                    e.est_currency_id AS est_currency_id,
                    ct.visit_latitude AS visit_latitude,
                    ct.visit_longitude AS visit_longitude,
                    ct.visit_address AS visit_address
                FROM
                    customers AS c
                LEFT JOIN states AS s
                ON
                    c.state_id = s.id
                LEFT JOIN countries AS cn
                ON
                    c.country_id = cn.id
                LEFT JOIN customer_categories AS cc
                ON
                    c.customer_category_id = cc.id
                LEFT JOIN customer_leads AS cl
                ON
                    c.customer_lead_id = cl.id
                LEFT JOIN users AS u
                ON
                    c.assigned_to_user = u.id
                LEFT JOIN lead_stages AS ls
                ON
                    c.lead_stage_id = ls.id
                LEFT JOIN (
                    SELECT
                        customer_id,
                        MAX(id) as id
                    FROM
                        estimates
                    WHERE status !=''
                    GROUP BY
                        customer_id
                ) AS max_estimates
                ON
                    max_estimates.customer_id = c.id
                LEFT JOIN estimates AS e
                ON
                    e.id = max_estimates.id
                LEFT JOIN (
                    SELECT
                        customer_id,
                        MAX(id) as id
                    FROM
                        customer_timelines
                    GROUP BY
                        customer_id
                ) AS max_timelines
                ON
                    max_timelines.customer_id = c.id
                LEFT JOIN customer_timelines AS ct
                ON
                    ct.id = max_timelines.id AND ct.customer_id = c.id
        ");

        DB::statement("
            CREATE VIEW notification_views AS
            SELECT `c`.*,
                `ct`.`activity_notes`          AS `notes`,
                `ct`.`id`                      AS `timeline_activity_id`,
                `ct`.`activity_type`           AS `timeline_activity_type`,
                `ct`.`updated_at`              AS `timeline_updated_at`,
                `ct`.`read_flag`               AS `timeline_read_flag`,
                `ct`.`read_by`                 AS `timeline_read_by`,
                `ct`.`read_date`               AS `timeline_read_date`,
                `ct`.`content_id`              AS `timeline_content_id`,
                `ct`.`internal_remarks`        AS `timeline_internal_remarks`
                FROM ((`customers_views` `c`
                    JOIN (SELECT
                            `customer_timelines`.`customer_id` AS `customer_id`,
                            MAX(`customer_timelines`.`id`)     AS `max_id`
                        FROM `customer_timelines`
                        WHERE `customer_timelines`.`activity_type` IN(8,9)
                        GROUP BY `customer_timelines`.`customer_id`) `max_ct`
                    ON (`c`.`id` = `max_ct`.`customer_id`))
                LEFT JOIN `customer_timelines` `ct`
                    ON (`ct`.`id` = `max_ct`.`max_id`
                        AND `ct`.`customer_id` = `c`.`id`))
        ");

        DB::statement("
            CREATE VIEW users_views AS 
                SELECT `u`.*, 
                `c`.`name` AS `country_name`, 
                `s`.`name` AS `state_name`, 
                `cc`.`name` AS `business_category_name` 
                FROM `tenants` `u` 
                LEFT JOIN `countries` `c` ON `u`.`country_id` = `c`.`id` 
                LEFT JOIN `states` `s` ON `u`.`state_id` = `s`.`id` 
                LEFT JOIN `company_categories` `cc` ON `u`.`company_category` = `cc`.`id`
        ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('DROP VIEW IF EXISTS content_file_timeline_views');
        Schema::dropIfExists('DROP VIEW IF EXISTS content_message_timeline_views');
        Schema::dropIfExists('DROP VIEW IF EXISTS customer_timeline_lead_lost');
        Schema::dropIfExists('DROP VIEW IF EXISTS customer_timeline_lead_lost_common');
        Schema::dropIfExists('DROP VIEW IF EXISTS customer_timeline_lead_won');
        Schema::dropIfExists('DROP VIEW IF EXISTS customers_views');
        Schema::dropIfExists('DROP VIEW IF EXISTS notification_views');
        Schema::dropIfExists('DROP VIEW IF EXISTS users_views');
    }
};
