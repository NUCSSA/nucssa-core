<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Hooks;

use NUCSSACore\Admin\CronSchedules;
use NUCSSACore\Utils\Logger;

class Activation {
  public static function init(){

    self::createDBTables();
    self::scheduleCronTasks();
  }

  private static function scheduleCronTasks(){
    (new CronSchedules())->scheduleCron();
  }

  private static function createDBTables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql_user_table = <<<create_user_table
    CREATE TABLE IF NOT EXISTS nucssa_user (
      id BIGINT NOT NULL AUTO_INCREMENT,
      username VARCHAR(255) NOT NULL,
      first_name VARCHAR(255),
      last_name VARCHAR(255),
      display_name VARCHAR(255),
      email VARCHAR(255),
      external_id VARCHAR(255) NOT NULL,

      PRIMARY KEY  (id),
      KEY idx_username (username),
      KEY idx_user_email (email),
      KEY idx_user_external_id (external_id),
      UNIQUE KEY unique_external_id (external_id)
    ) $charset_collate;
create_user_table;

    $sql_group_table = <<<create_group_table
    CREATE TABLE IF NOT EXISTS nucssa_group (
      id BIGINT NOT NULL AUTO_INCREMENT,
      group_name VARCHAR(255) NOT NULL,
      description VARCHAR(255),
      external_id VARCHAR(255) NOT NULL,

      PRIMARY KEY  (id),
      KEY idx_group_name (group_name),
      KEY idx_group_external_id (external_id),
      UNIQUE KEY unique_external_id (external_id)
    ) $charset_collate;
create_group_table;

    $sql_membership_table = <<<create_membership_table
    CREATE TABLE IF NOT EXISTS nucssa_membership (
      id BIGINT NOT NULL AUTO_INCREMENT,
      parent_id BIGINT NOT NULL,
      child_group_id BIGINT,
      child_user_id BIGINT,

      PRIMARY KEY  (id),
      KEY idx_mem_parent (parent_id),
      KEY idx_mem_child_user (child_user_id),
      UNIQUE KEY unique_group_membership (parent_id, child_group_id),
      UNIQUE KEY unique_user_membership (parent_id, child_user_id),

      FOREIGN KEY (parent_id)
        REFERENCES nucssa_group (id)
        ON DELETE CASCADE,
      FOREIGN KEY (child_group_id)
        REFERENCES nucssa_group (id)
        ON DELETE CASCADE,
      FOREIGN KEY (child_user_id)
        REFERENCES nucssa_user (id)
        ON DELETE CASCADE
    ) $charset_collate;
create_membership_table;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_user_table);
    dbDelta($sql_group_table);
    dbDelta($sql_membership_table);
  }
}