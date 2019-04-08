<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Accounts;

use NUCSSACore\config\LDAP_Config;

class UserDirectory {
  private static $_instance = null;

  private $conn;
  private $base_dn;

  private $isBind = false;  // cache for binding status
  private $userInfo = null; // cache for user info

  /**
   * The designated initializer
   * adopting singleton pattern.
   *
   * @return UserDirectory
   */
  public static function instance(){
    if (! self::$_instance)
      self::$_instance = new self();
    return self::$_instance;
  }

  /**
   * Initialize connection to LDAP server
   */
  private function __construct(){
    $host   = LDAP_Config::$SERVER["HOST"];
    $port   = LDAP_Config::$SERVER["PORT"];
    $this->base_dn = LDAP_Config::$LDAP_SCHEMA["BASE_DN"];
    $this->conn = ldap_connect($host, $port);
  }

  /**
   * Authenticate user
   * @param $username string
   * @param $password string
   * @return bool return false on failure
   */
  public function authenticateUser($username, $password) {
    // report if connection is invalid
    $this->checkConn();

    $user_attrib = LDAP_Config::$USER_SCHEMA['USERNAME_ATTRIBUTE'];
    $additional_user_dn = LDAP_Config::$LDAP_SCHEMA["ADDITIONAL_USER_DN"];
    $user_dn = "{$user_attrib}=$username,$additional_user_dn,{$this->base_dn}";
    return ldap_bind($this->conn, $user_dn, $password);
  }

  /**
   * Get user's groups
   * @return array
   */
  public function getGroupsOfUser(string $username) {

    $this->bindWPUser() or die("Could not bind to LDAP");

    $user_attrib = LDAP_Config::$USER_SCHEMA['USERNAME_ATTRIBUTE'];
    $additional_user_dn = LDAP_Config::$LDAP_SCHEMA["ADDITIONAL_USER_DN"];
    $user_membership_attrib = LDAP_Config:: $MEMBERSHIP_SCHEMA[ "USER_MEMBERSHIP_ATTRIBUTE"];
    $res = ldap_search($this->conn, "{$additional_user_dn},{$this->base_dn}", "({$user_attrib}={$username})", [ $user_membership_attrib]);
    $entries = ldap_get_entries($this->conn, $res);

    // No information found, bad user
    if ($entries['count'] == 0) return [];

    return $entries[0][$user_membership_attrib];
  }

  /**
   * Get group members
   * @return array
   */
  public function getGroupMembers(string $groupname) {
    $this->bindWPUser() or die("Could not bind to LDAP");

    $name_attrib = LDAP_Config:: $GROUP_SCHEMA[ 'NAME_ATTRIBUTE'];
    $group_members_attrib = LDAP_Config::$MEMBERSHIP_SCHEMA[ "GROUP_MEMBERS_ATTRIBUTE"];
    $additional_group_dn = LDAP_Config::$LDAP_SCHEMA[ "ADDITIONAL_GROUP_DN"];

    $base_dn = "{$additional_group_dn},{$this->base_dn}";
    $filter = "({$name_attrib}={$groupname})";

    $res = ldap_search($this->conn, $base_dn, $filter, [ $group_members_attrib]);
    $entries = ldap_get_entries($this->conn, $res);

    // No information found, bad user
    if ($entries['count'] == 0) return [];

    // var_dump ($entries[0][ $group_members_attrib]);
    return $entries[0][ $group_members_attrib];
  }

  /**
   * Sync all users and groups
   *
   * @return void bulk upsert directory entries to database
   */
  public function sync($delegate){
    /**
     * Users
     * columns: uid, cn, givenName, sn, displayName, mailPrimaryAddress, uidNumber (used for identifying user across uid changes)
     */
    /**
     * Groups
     * columns: cn, description, gidNumber, uniqueMember (used for identifying group across name changes)
     */
    /**
     * Membership
     * columns: parent_id, child_group_id, child_user_id
     *
     * How to find group members?
     * group->gidNumber: found group record in our db
     * group->uniqueMember->extract uid tag: found user record in our db
     * upsert the two records in the membership table
     */
  }

  /**
   * Bind wp_user for LDAP CRUD operations.
   * @return bool
   */
  private function bindWPUser(){
    $this->checkConn();

    if (!$this->isBind) {
      $wp_dn  = LDAP_Config::$SERVER["USERNAME"];
      $wp_pw  = LDAP_Config::$SERVER["PASSWORD"];

      $success = ldap_bind($this->conn, $wp_dn, $wp_pw);
      $this->isBind = $success;
      return $success;
    }
    return true;
  }

  /**
   * IMPORTANT:
   * Always check connection before doing other operations.
   */
  private function checkConn(){
    if (!$this->conn) {
      die("Error connecting to LDAP server.");
    }
  }
}
