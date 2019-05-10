<?php
namespace NUCSSACore\Accounts;

/**
 * Represents a perm record from the database `nucssa_perm` table
 */
class Perm {
  private static $table = 'nucssa_perm';
  public $id;
  public $role; // @param {String} name of the role, lower-case string
  public $account_type; // @param {String} "USER" | "GROUP"
  public $account_id; // @param {Integer} id of referencing user or group
  public $account_displayName; //@param {String}

  private function __construct(){}

  /**
   * Find record by id
   */
  public static function find($id){
    global $wpdb;
    $query = "SELECT * FROM ${self::$table} WHERE id = $id";
    $record = $wpdb->get_row($query);
    $instance = new self();
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
      if ($this->account_type === 'GROUP') {
        $this->account_displayName = Group::find($this->account_id)->group_name;
      } else {
        $this->account_displayName = User::find($this->account_id)->display_name;
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
  }

  /**
   * Remove record from database
   */
  public function delete(){
    global $wpdb;
    $where = [ 'id' => $this->id ];
    $where_format = ['%d'];
    $wpdb->delete(self::$table, $where, $where_format);
  }
}