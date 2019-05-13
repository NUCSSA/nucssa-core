<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Accounts;
use NUCSSACore\Accounts\UserDirectory;
use NUCSSACore\Utils\Logger;

class Accounts {
  public function construct(){}

  /**
   * sync users and groups from LDAP server to wordpress database
   */
  public function syncFromDirectory() {
    $allRecords = UserDirectory::singleton() -> fetchAll();
    $this->syncUsers($allRecords["users"]);
    $this->syncGroups($allRecords["groups"]);
    $this->syncMembership($allRecords["groups"]);
  }

  /**
   * Search users and groups with given $keyword
   *
   * @param String $keyword
   * @return Array array(users => [], groups => [])
   */
  public function search($keyword){
    global $wpdb;
    $user_query =
      "SELECT id, display_name
                  FROM nucssa_user
                  WHERE CONCAT_WS('', username, first_name, last_name, display_name) LIKE '%$keyword%';
                ";
    $group_query =
      "SELECT id, group_name
                  FROM nucssa_group
                  WHERE CONCAT_WS('', group_name, description) LIKE '%$keyword%';
                ";

    $users = $wpdb->get_results($user_query);
    $groups = $wpdb->get_results($group_query);

    return array(
      'users' => $users,
      'groups' => $groups
    );
  }

  /**
   * Fetch all perms
   */
  public function allPerms(){
    global $wpdb;
    $query =
      "SELECT perm.id as id, role, account_type, u.id as account_id, u.display_name as account_display_name
        FROM nucssa_perm as perm JOIN nucssa_user as u
        ON perm.account_type = 'USER' AND u.id = perm.account_id
        UNION
        SELECT perm.id as id, role, account_type, g.id as account_id, g.group_name as account_display_name
        FROM nucssa_perm as perm JOIN nucssa_group as g
        ON perm.account_type = 'GROUP' AND g.id = perm.account_id;
      ";
    $perms = $wpdb->get_results($query);

    return $perms;
  }

  /**
   * Users
   * columns: uid, givenName, sn, displayName, mailPrimaryAddress, uidNumber (used for identifying user across uid changes)
   */
  private function syncUsers($usersFromDirectory){
    global $wpdb;
    $directory = UserDirectory::singleton();

    $table_name = "nucssa_user";

    /***** First remove deleted users from db *****/
    $uidNumbers_in_db = $wpdb->get_col("SELECT external_id FROM $table_name");
    $uidNumbers_in_directory = array_map(
      function ($user) use ($directory) {
        return $user[$directory->user_schema['user_id_attribute']];
      },
      $usersFromDirectory
    );
    $uidNumbers_of_deleted_users = array_diff($uidNumbers_in_db, $uidNumbers_in_directory);
    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM $table_name WHERE external_id IN (%s)",
        implode(',', $uidNumbers_of_deleted_users)
      )
    );

    /***** Then upsert updated users to db *****/
    $values_string = implode(
      ",",
      array_map(
        function ($user) use ($directory) {
          return "(" . '"' . $user[$directory->user_schema["username_attribute"]] . '"' . ","
            . '"' . $user[$directory->user_schema["firstname_attribute"]] . '"' . ","
            . '"' . $user[$directory->user_schema["lastname_attribute"]] . '"' . ","
            . '"' . $user[$directory->user_schema["display_name_attribute"]] . '"' . ","
            . '"' . $user[$directory->user_schema["email_attribute"]] . '"' . ","
            . '"' . $user[$directory->user_schema["user_id_attribute"]] . '"' .
            ")";
        },
        $usersFromDirectory
      )
    );
    $wpdb->query(
      "INSERT INTO $table_name
        (username, first_name, last_name, display_name, email, external_id)
        VALUES " . $values_string .
        " ON DUPLICATE KEY UPDATE
        username = VALUES(username),
        first_name = VALUES(first_name),
        last_name = VALUES(last_name),
        display_name = VALUES(display_name),
        email = VALUES(email);"
    );
  }

  /**
   * Groups
   * columns: cn, description, gidNumber, uniqueMember (used for identifying group across name changes)
   */
  private function syncGroups($groupsFromDirectory){
    global $wpdb;

    $table_name = "nucssa_group";
    $directory = UserDirectory::singleton();

    /***** First remove deleted groups from db *****/
    $gidNumbers_in_db = $wpdb->get_col("SELECT external_id FROM $table_name");
    $gidNumbers_in_directory = array_map(
      function ($group) use ($directory) {
        return $group[$directory->group_schema["group_id_attribute"]];
      },
      $groupsFromDirectory
    );
    $gidNumbers_of_deleted_groups = array_diff($gidNumbers_in_db, $gidNumbers_in_directory);
    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM $table_name WHERE external_id IN (%s)",
        implode(',', $gidNumbers_of_deleted_groups)
      )
    );

    /***** Then upsert updated groups to db *****/
    $values_string = implode(
      ",",
      array_map(
        function ($group) use ($directory) {
          return "(" . '"' . $group[$directory->group_schema["name_attribute"]] . '"' . ","
            . '"' . $group[$directory->group_schema["description_attribute"]] . '"' . ","
            . '"' . $group[$directory->group_schema["group_id_attribute"]] . '"' .
            ")";
        },
        $groupsFromDirectory
      )
    );
    $wpdb->query(
      "INSERT INTO $table_name
        (group_name, description, external_id)
        VALUES " . $values_string .
        " ON DUPLICATE KEY UPDATE
        group_name = VALUES(group_name),
        description = VALUES(description);"
    );
  }

  /**
   * Membership
   * columns: parent_id, child_group_id, child_user_id
   *
   * DESCRIPTION:
   * How to find group members?
   * group->gidNumber: found group record in our db
   * group->uniqueMember->extract uid tag: found user record in our db
   * upsert the two records in the membership table
   */
  private function syncMembership($groups){
    global $wpdb;
    $directory = UserDirectory::singleton();

    $user_table_name = "nucssa_user";
    $group_table_name = "nucssa_group";
    $group_table_name = "nucssa_group";
    $membership_table_name = "nucssa_membership";
    $touchedRecords = [];
    /***** First update memberships in db and track touched records by ID *****/
    foreach($groups as $group){
      $memberDNs = $group[$directory->membership_schema["group_membership_attribute"]];
      if ($memberDNs === NULL) continue; // skip if NULL
      $memberDNs = ("string" == gettype($memberDNs)) ? [$memberDNs] : $memberDNs;
      $gidNumber = $group[$directory->group_schema["group_id_attribute"]];
      $parent_id = $wpdb->get_var("SELECT id FROM {$group_table_name} WHERE external_id = {$gidNumber}");

      foreach($memberDNs as $memberDN){
        $child_group_id = $child_user_id = 'NULL';
        ["type" => $type, "name" => $name] = $this->getMemberTypeAndName($memberDN);

        if ($type == "user") {
          // find user id with $name
          $child_user_id = $wpdb->get_var("SELECT id FROM {$user_table_name} WHERE username = '{$name}'");
        } else {
          // find group id with gidNumber
          $child_group_id = $wpdb->get_var("SELECT id FROM {$group_table_name} WHERE group_name = '{$name}'");
        }
      }

      $wpdb->query(
        "INSERT INTO {$membership_table_name}
          (parent_id, child_group_id, child_user_id)
          VALUES ($parent_id, $child_group_id, $child_user_id)
          ON DUPLICATE KEY UPDATE
          parent_id = $parent_id
        "
      );

      // track touched membership record
      $child_col = $child_group_id !== 'NULL' ? 'child_group_id' : 'child_user_id';
      $child_val = $child_group_id !== 'NULL' ? $child_group_id : $child_user_id;
      $touchedRecords[] = $wpdb->get_var("SELECT id FROM {$membership_table_name}
        WHERE parent_id = {$parent_id}
        AND {$child_col} = {$child_val}"
      );
    }

    /***** Delete untouched records *****/
    $allMembershipRecordsInDB = $wpdb->get_col("SELECT id FROM {$membership_table_name}");
    $untouchedRecords = \array_diff($allMembershipRecordsInDB, $touchedRecords);
    foreach($untouchedRecords as $record_id) {
      $wpdb->delete($membership_table_name, array('id' => $record_id));
    }
  }

  /**
   * @return array("type" => , "name" => )
   */
  private function getMemberTypeAndName(string $memberDN){
    $directory = UserDirectory::singleton();

    $firstPart = explode(',', $memberDN)[0];
    if (strpos($memberDN, $directory->schema["additional_user_dn"]) !== false) {
      $type = "user";
      $name = substr($firstPart, strlen($directory->user_schema["username_attribute"] . "="));
    } else {
      $type = "group";
      $name = substr($firstPart, strlen($directory->group_schema["name_attribute"] . "="));
    }

    return array(
      "type" => $type,
      "name" => $name
    );
  }
}
