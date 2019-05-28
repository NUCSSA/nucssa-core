<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc;

use nucssa_core\inc\Cron;

class Deactivation {
  public static function init(){

    // Remove Cron Tasks
    self::removeCronTasks();
    self::dropDBTables();
  }

  private static function removeCronTasks(){
    (new Cron())->unscheduleCron();
  }

  private static function dropDBTables(){
    global $wpdb;

    $tables = ['nucssa_user', 'nucssa_group', 'nucssa_membership', 'nucssa_perm'];

    $wpdb->query('SET foreign_key_checks = 0;');
    array_walk($tables, function($table) use ($wpdb){
      $wpdb->query("DROP TABLE IF EXISTS $table;");
    });
    $wpdb->query('SET foreign_key_checks = 1;');
  }
}
