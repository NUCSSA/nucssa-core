<?php
namespace nucssa_core\inc\accounts;
use function nucssa_core\utils\debug\{console_log, file_log};

/**
 * Represents a group record from database table `nucssa_group`
 */
class DirectoryGroup {
  private static $table = 'nucssa_group';
  private $id, $group_name, $description, $external_id;
  private $roles, $users;

  public function __construct(){

  }

  public static function find($id){
    global $wpdb;
    $table = self::$table;
    $query = "SELECT * FROM $table WHERE id = $id";
    $record = $wpdb->get_row($query);
    $instance = new self();
    $instance->id = $id;
    $instance->group_name   = $record->group_name;
    $instance->description  = $record->description;
    $instance->external_id  = $record->external_id;

    return $instance;
  }

  /**
   * Direct Roles assigned to this group object
   */
  public function roles() {
    if (!$this->roles) {
      global $wpdb;
      $query = "SELECT role FROM nucssa_perm WHERE account_type = 'GROUP' and account_id = {$this->id}";
      $this->roles = $wpdb->get_col($query);
    }

    return $this->roles;
  }

  /**
   * Get all members of the group, including members deep in the user hierarchy
   * Note that this function returns ids from nucssa_users table, not wp_users table
   *
   * @return array Array of id of user in nucssa_users table
   */
  public function users(){
    if (!$this->users){
      $this->users = $this->_get_users_of_group($this->id);
    }
    return $this->users;
  }

  private function _get_users_of_group($gid, $acc = []){
    global $wpdb;
    $query = "SELECT * FROM nucssa_membership WHERE parent_id = $gid";
    $records = $wpdb->get_results($query);
    // console_log($records, 'records');
    foreach ($records as $record) {
      // console_log($record, 'record');
      if ($user_id = $record->child_user_id){
        if (!in_array($user_id, $acc)) array_push($acc, $user_id);
      } else {
        $acc = $this->_get_users_of_group($record->child_group_id, $acc);
      }
    }
    return $acc;
  }
}
