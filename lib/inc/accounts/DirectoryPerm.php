<?php
namespace nucssa_core\inc\accounts;

use function nucssa_core\utils\pluggable\{get_user_by};

/**
 * Represents a perm record from the database `nucssa_perm` table
 */
class DirectoryPerm {
  private static $table = 'nucssa_perm';
  public $id;
  public $role; // @param {String} name of the role, lower-case string
  public $account_type; // @param {String} USER|GROUP constants
  public $account_id; // @param {Integer} id of referencing user or group
  public $account_displayName; //@param {String}

  private function __construct(){}

  /**
   * Find record by id
   */
  public static function find($id){
    global $wpdb;
    $table = self::$table;
    $query = "SELECT * FROM $table WHERE id = $id";
    $record = $wpdb->get_row($query);
    $instance = new self;
    $instance->id           = $id;
    $instance->role         = $record->role;
    $instance->account_type = $record->account_type;
    $instance->account_id   = $record->account_id;

    return $instance;
  }

  /**
   * Create a new Perm object from given params
   */
  public static function new($role, $account_type, $account_id, $account_displayName = NULL){
    $instance = new self();
    $instance->role                 = strtolower($role);
    $instance->account_type         = strtoupper($account_type);
    $instance->account_id           = $account_id;
    $instance->account_displayName  = $account_displayName;

    return $instance;
  }

  public function loadDisplayName(){
    if (!$this->account_displayName){
      if ($this->account_type === \GROUP) {
        $this->account_displayName = DirectoryGroup::find($this->account_id)->group_name;
      } else {
        $this->account_displayName = DirectoryUser::find($this->account_id)->display_name;
      }
    }
  }

  /**
   * Persist record to database
   */
  public function store(){
    global $wpdb;
    $data = [
      'role' => $this->role,
      'account_type' => $this->account_type,
      'account_id' => $this->account_id
    ];
    $data_format = ['%s', '%s', '%d'];
    if ($this->id){ // existing record
      $where = [ 'id' => $this->id ];
      $where_format = ['%d'];
      $wpdb->update(self::$table, $data, $where, $data_format, $where_format);
    } else { // new record
      $wpdb->insert(self::$table, $data, $data_format);
      // update object $id
      $this->id = $wpdb->insert_id;
    }

    $this->updateUserRoles();
  }

  /**
   * Remove record from database
   */
  public function delete(){
    global $wpdb;
    $where = [ 'id' => $this->id ];
    $where_format = ['%d'];
    $wpdb->delete(self::$table, $where, $where_format);


    $this->updateUserRoles();
  }

  private function updateUserRoles(){
    $affected_users = $this->account_type == \USER ? [$this->account_id] : DirectoryGroup::find($this->account_id)->users();
    foreach ($affected_users as $dir_uid) {
      $dir_user = DirectoryUser::find($dir_uid);
      if ($user = get_user_by('external_id', $dir_user->external_id)){
        Accounts::updateUserRoles($user, $dir_user);
      }
    }
  }
}