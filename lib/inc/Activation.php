<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc;

use nucssa_core\inc\Cron;
use function nucssa_core\utils\debug\file_log;

class Activation {
  public static function init(){

    self::createDBTables();
    self::alterWPUsersTable();
    self::scheduleCronTasks();
  }

  private static function scheduleCronTasks(){
    (new Cron())->scheduleCron();
  }

  private static function alterWPUsersTable() {
    global $wpdb;
    $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = {$wpdb->users}
            AND   table_schema = {$wpdb->dbname}
            AND   column_name = 'external_id'";
    $results = $wpdb->get_results($query);
    if (empty($results)) {
      file_log('altering');
      $query = "ALTER TABLE {$wpdb->users}
        ADD COLUMN external_id VARCHAR(255),
        ADD KEY idx_user_external_id (external_id),
        ADD UNIQUE KEY unique_external_id (external_id)";
      $wpdb->query($query);
    }
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

    $sql_perm_table = <<<create_perm_table
    CREATE TABLE IF NOT EXISTS nucssa_perm (
      id BIGINT NOT NULL AUTO_INCREMENT,
      account_id BIGINT NOT NULL,
      account_type VARCHAR(5) NOT NULL,
      role VARCHAR(255) NOT NULL,

      PRIMARY KEY (id),
      KEY idx_perm_role (role),
      KEY idx_perm_account_id_type (account_id, account_type),
      UNIQUE KEY unique_perm_account_id_type_role (account_id, account_type, role)
    ) $charset_collate;
create_perm_table;


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_user_table);
    dbDelta($sql_group_table);
    dbDelta($sql_membership_table);
    dbDelta($sql_perm_table);
  }
}