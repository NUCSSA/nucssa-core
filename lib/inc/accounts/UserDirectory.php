<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc\accounts;
use function nucssa_core\utils\{file_log};


class UserDirectory {
  private static $_instance = null;

  public $conn;
  public $server, $schema, $user_schema, $group_schema, $membership_schema;

  private $isBind = false;  // cache for binding status

  /**
   * The designated initializer
   * adopting singleton pattern.
   *
   * @return UserDirectory
   */
  public static function singleton(){
    if (! self::$_instance)
      self::$_instance = new self();
    return self::$_instance;
  }

  /**
   * Initialize connection to LDAP server
   */
  private function __construct(){
    $this->loadConfig();
    $this->conn = ldap_connect($this->server['host'], $this->server['port']);
  }

  /**
   * Get Last Modified Time of User Object
   */

  /**
   * @return bool
   */
  public function testConnection()
  {
    return $this->bindWPUser();
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

    $user_attrib = $this->user_schema['username_attribute'];
    $additional_user_dn = $this->schema['additional_user_dn'];
    $user_dn = "{$user_attrib}=$username,$additional_user_dn,{$this->schema['base_dn']}";
    return @ldap_bind($this->conn, $user_dn, $password);
  }

  public function userExists($username) {
    $this->bindWPUser() or die("Could not bind to LDAP");
    $base_dn = $this->schema['additional_user_dn'] . "," . $this->schema['base_dn'];
    $filter = "(& {$this->user_schema['object_filter']} ({$this->user_schema['username_attribute']}=$username))";
    $res = ldap_search($this->conn, $base_dn, $filter) or exit("Unable to search");

    return ldap_count_entries($this->conn, $res) > 0;
  }

  /**
   * Initiate a custom search
   *
   * @return LDAP_Result_Type
   */
  public function search(string $filter, array $attributes = []) {
    $this->bindWPUser() or die("Could not bind to LDAP");

    $res = ldap_search($this->conn, $this->schema['base_dn'], $filter, $attributes);
    return $res;
  }

  /**
   * Get user's groups
   * @return array
   */
  public function getGroupsOfUser(string $username) {

    $this->bindWPUser() or die("Could not bind to LDAP");

    $user_attrib = $this->user_schema['username_attribute'];
    $additional_user_dn = $this->schema['additional_user_dn'];
    $user_membership_attrib = $this->membership_schema['user_membership_attribute'];
    $res = ldap_search($this->conn, "{$additional_user_dn},{$this->schema['base_dn']}", "({$user_attrib}={$username})", [$user_membership_attrib]);
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

    $name_attrib = $this->group_schema['name_attribute'];
    $group_members_attrib = $this->membership_schema["group_membership_attribute"];
    $additional_group_dn = $this->schema["additional_group_dn"];

    $base_dn = "{$additional_group_dn},{$this->schema['base_dn']}";
    $filter = "({$name_attrib}={$groupname})";

    $res = ldap_search($this->conn, $base_dn, $filter, [$group_members_attrib]);
    $entries = ldap_get_entries($this->conn, $res);

    // No information found, bad user
    if ($entries['count'] == 0) return [];

    // var_dump ($entries[0][ $group_members_attrib]);
    return $entries[0][ $group_members_attrib];
  }

  /**
   * Fetch all users and groups
   *
   * @return array("users" => [], "groups" => [])
   */
  public function fetchAll(){
    $this->bindWPUser() or die("Could not bind to LDAP");

     return array(
       "users" => $this->fetchUsers(),
       "groups" => $this->fetchGroups()
     );
  }

  /**
   * Fetches users with provided $filters.
   * Fetches all users if $filters is empty
   */
  public function fetchUsers($filters = []) {
    $this->bindWPUser() or die("Could not bind to LDAP");

    /**
     * Users
     * columns: uid, givenName, sn, displayName, mailPrimaryAddress, uidNumber (used for identifying user across uid changes)
     */
    $users = [];
    $base_dn = $this->schema['additional_user_dn'] . "," . $this->schema['base_dn'];
    $filters[] = $this->user_schema['object_filter'];
    $filter = $filters->count == 1 ? $filters[0] : '(& ' . implode($filters) . ')';
    $atts = array(
      $this->user_schema['username_attribute'],
      $this->user_schema['firstname_attribute'],
      $this->user_schema['lastname_attribute'],
      $this->user_schema['display_name_attribute'],
      $this->user_schema['email_attribute'],
      $this->user_schema['user_id_attribute'],
    );
    $res = ldap_search($this->conn, $base_dn, $filter, $atts) or exit("Unable to search");
    $entries = ldap_get_entries($this->conn, $res);

    if ($entries["count"] != 0) {
      array_shift($entries);
      foreach ($entries as $entry) {
        foreach ($atts as $att) {
          if ($vals = @$entry[strtolower($att)]) {
            if ($vals["count"] == 1) {
              $val = $vals[0];
            } else {
              array_shift($vals);
              $val = $vals;
            }
          } else {
            $val = NULL;
          }
          $user[$att] = $val;
        }
        $users[] = $user;
      }
    }
    return $users;
  }

  /**
   * Fetches groups with provided $filters.
   * Fetches all groups if $filters is empty
   */
  public function fetchGroups($filters = []) {
    $this->bindWPUser() or die("Could not bind to LDAP");

    /**
     * Groups
     * columns: cn, description, gidNumber, uniqueMember (used for identifying group across name changes)
     */
    $groups = [];
    $base_dn = $this->schema["additional_group_dn"] . "," . $this->schema['base_dn'];
    $filters[] = $this->group_schema['object_filter'];
    $filter = $filters->count == 1 ? $filters[0] : '(& ' . implode($filters) . ')';
    $atts = array(
      $this->group_schema['name_attribute'],
      $this->group_schema['description_attribute'],
      $this->group_schema['group_id_attribute'],
      $this->membership_schema['group_membership_attribute'],
    );
    $res = ldap_search($this->conn, $base_dn, $filter, $atts) or exit("Unable to search");
    $entries = ldap_get_entries($this->conn, $res);

    if ($entries["count"] != 0) {
      array_shift($entries);
      foreach ($entries as $entry) {
        foreach ($atts as $att) {
          if ($vals = @$entry[strtolower($att)]) {
            if ($vals["count"] == 1) {
              $val = $vals[0];
            } else {
              array_shift($vals);
              $val = $vals;
            }
          } else {
            $val = NULL;
          }
          $group[$att] = $val;
        }
        $groups[] = $group;
      }
    }
    return $groups;
  }

  /**
   * Bind wp_user for LDAP CRUD operations.
   * @return bool
   */
  private function bindWPUser(){
    $this->checkConn();

    if (!$this->isBind) {
      $wp_dn  = $this->server['username'];
      $wp_pw  = $this->server['password'];

      $success = @ldap_bind($this->conn, $wp_dn, $wp_pw);
      $this->isBind = $success;
      file_log(">>> ldap status", \ldap_error($this->conn));
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

  /**
   * Load configurations from DB
   */
  private function loadConfig(){
    $option_keys = [
      'server' => 'nucssa-core.ldap.server',
      'schema' => 'nucssa-core.ldap.schema',
      'user_schema' => 'nucssa-core.ldap.user_schema',
      'group_schema' => 'nucssa-core.ldap.group_schema',
      'membership_schema' => 'nucssa-core.ldap.membership_schema',
    ];

    $this->server             = get_option($option_keys['server'], []);
    $this->schema             = get_option($option_keys['schema'], []);
    $this->user_schema        = get_option($option_keys['user_schema'], []);
    $this->group_schema       = get_option($option_keys['group_schema'], []);
    $this->membership_schema  = get_option($option_keys['membership_schema'], []);
  }
}
