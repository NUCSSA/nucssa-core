<?php
namespace nucssa_core\inc\accounts;

use function nucssa_core\utils\{file_log};

/**
 * Class represents a user from nucssa_user table
 */
class DirectoryUser {
  private static $table = 'nucssa_user';
  public $id, $username, $first_name, $last_name, $display_name, $email, $external_id;
  private $roles = null;

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

  /**
   * Builds WP_User object from User props
   *
   * @return WP_User
   */
  public function WP_User(){
    $wpuser = new \WP_User();
    $wpuser->ID = $this->id;
    $wpuser->first_name = $this->first_name;
    $wpuser->last_name = $this->last_name;
    $wpuser->user_email = $this->email;
    $wpuser->display_name = $this->display_name;
    $wpuser->site_id = 1;
    $wpuser->roles = $this->roles();

    return $wpuser;
  }

  public function roles(){
    if (!$this->roles) {
      global $wpdb;
      $query = "SELECT role FROM nucssa_perm WHERE account_type = 'USER' and account_id = {$this->id}";
      $this->roles = $wpdb->get_col($query);
      file_log('roles', $this->roles());
    }

    return $this->roles;
  }
}