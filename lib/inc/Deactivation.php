<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc;

use nucssa_core\inc\Cron;
use nucssa_core\inc\accounts\DirectoryUser;
use function nucssa_core\utils\pluggable\{get_user_by};

class Deactivation {
  public static function init(){

    self::removeCronTasks();
    self::cleanUsersAndUsermetas();
    self::dropDBTables();
    self::cleanOptions();
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

  private static function cleanOptions(){
    // remove option `LDAP_SYNC_LAST_TIMESTAMP`
    delete_option(\LDAP_SYNC_LAST_TIMESTAMP);
  }

  private static function cleanUsersAndUsermetas(){
    global $wpdb;
    $query = "SELECT external_id FROM nucssa_user";
    foreach ($wpdb->get_col($query) as $external_id) {
      if ($user = get_user_by('external_id', $external_id)){
        \wp_delete_user($user->ID, 1);
      }
    }
  }
}
