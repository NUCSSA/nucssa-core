<?php
namespace NUCSSACore\Accounts;

/**
 * Class represents a user from the database
 */
class User {
  private static $table = 'nucssa_user';
  private $id, $username, $first_name, $last_name, $display_name, $email, $external_id;

  public function __construct(){

  }

  public static function find($id){
    global $wpdb;
    $query = "SELECT * FROM ${self::$table} WHERE id = $id";
    $record = $wpdb->get_row($query);
    $instance = new self();
    $instance->id = $id;
    $instance->username     = $record->username;
    $instance->first_name   = $record->first_name;
    $instance->last_name    = $record->last_name;
    $instance->display_name = $record->display_name;
    $instance->email        = $record->email;
    $instance->external_id  = $record->external_id;

    return $instance;
  }
}