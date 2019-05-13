<?php
namespace NUCSSACore\Accounts;

/**
 * Represents a group record from database table `nucssa_group`
 */
class Group {
  private static $table = 'nucssa_group';
  private $id, $group_name, $description, $external_id;

  public function __construct(){

  }

  public static function find($id){
    global $wpdb;
    $query = "SELECT * FROM ${self::$table} WHERE id = $id";
    $record = $wpdb->get_row($query);
    $instance = new self();
    $instance->id = $id;
    $instance->group_name   = $record->group_name;
    $instance->description  = $record->description;
    $instance->external_id  = $record->external_id;

    return $instance;
  }
}
