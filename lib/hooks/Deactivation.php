<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Hooks;

use NUCSSACore\Admin\CronSchedules;

class Deactivation {
  public static function init(){

    // Remove Cron Tasks
    self::removeCronTasks();
    self::dropDBTables();
  }

  private static function removeCronTasks(){
    (new CronSchedules())->unscheduleCron();
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
