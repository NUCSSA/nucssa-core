<?php
namespace nucssa_core\inc\accounts;

use function nucssa_core\utils\debug\{file_log, console_log};

/**
 * Class represents a user from nucssa_user table
 */
class DirectoryUser {
  private static $table = 'nucssa_user';
  public $id, $username, $first_name, $last_name, $display_name, $email, $external_id;
  private $roles = null;
  private $groups = [];

  public function __construct(){

  }

  public static function find($id){
    global $wpdb;
    $query = "SELECT * FROM " . self::$table . " WHERE id = $id";
    return self::findUserWithQuery($query);
  }

  public static function findByUsername($username){
    $query = "SELECT * FROM " . self::$table ." WHERE username = '$username'";
    return self::findUserWithQuery($query);
  }

  public static function findByExternalID($uidNumber) {
    $query = "SELECT * FROM " . self::$table . " WHERE external_id = {$uidNumber}";
    return self::findUserWithQuery($query);
  }

  /**
   * Get DirectoryUser object from $ID
   * @param int $ID, user id
   * @return DirectoryUser|false return false if user is not a NUCSSA Directory User, aka. is a local user
   */
  public static function findByUserID($ID) {
    global $wpdb;
    $query = "SELECT external_id FROM $wpdb->users WHERE ID = $ID";
    $external_id = $wpdb->get_var($query);
    if (!$external_id) return false;
    else return self::findByExternalID($external_id);
  }

  private static function findUserWithQuery(string $query) {
    global $wpdb;
    $record = $wpdb->get_row($query);
    $instance = new self();
    $instance->id           = $record->id;
    $instance->username     = $record->username;
    $instance->first_name   = $record->first_name;
    $instance->last_name    = $record->last_name;
    $instance->display_name = $record->display_name;
    $instance->email        = $record->email;
    $instance->external_id  = $record->external_id;

    return $instance;
  }

  public function ownRoles(){
    global $wpdb;
    $query = "SELECT role FROM nucssa_perm WHERE account_type = 'USER' and account_id = {$this->id}";
    return $wpdb->get_col($query);
  }

  /**
   * Roles assigned to belonging groups
   */
  public function inheritedRoles() {
    $roles = [];
    // get all groups the user is a member of, including inherited from higher level groups
    $groups = $this->groups();

    // get roles of those groups
    foreach ($groups as $group_id) {
      // console_log( DirectoryGroup::find($group_id)->roles(), "gid: $group_id, roles: ");
      $roles = array_merge($roles, DirectoryGroup::find($group_id)->roles());
    }

    return array_unique($roles);
  }

  /**
   * Roles assigned to the user object directly
   */
  public function allRoles(){
    if (!$this->roles){
      $roles = array_merge($this->ownRoles(), $this->inheritedRoles());
      $this->roles = array_unique($roles);
    }
    return $this->roles;
  }

  /**
   * Return all the groups this user belongs to, including indirect groups
   */
  public function groups(){
    if (!$this->groups){
      $this->groups = array_unique($this->_get_groups_for($this->id, 'user'));
    }

    // file_log(">>>>>> groups", $this->groups);
    return $this->groups;
  }

  /**
   * @param int $child_id
   * @param string $child_type: ('user'|'group')
   * @param array $acc
   * @return array
   */
  private function _get_groups_for($child_id, $child_type, $acc = []){
    // file_log(".......", $acc);
    global $wpdb;
    $col_name = "child_{$child_type}_id";
    $query = "SELECT parent_id FROM nucssa_membership WHERE $col_name = $child_id";
    $records = $wpdb->get_col($query);
    foreach ($records as $group_id) {
      $acc[] = $group_id;
      $acc = $this->_get_groups_for($group_id, 'group', $acc);
    }
    return $acc;
  }
}