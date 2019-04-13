<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Accounts;
use NUCSSACore\Accounts\UserDirectory;
use NUCSSACore\config\LDAP_Config;

class Accounts {
  public function construct(){

  }

  /**
   * sync users and groups from LDAP server to wordpress database
   */
  public function syncFromDirectory() {
    $allRecords = UserDirectory::instance() -> fetchAll();
    $this->syncUsers($allRecords["users"]);
    $this->syncGroups($allRecords["groups"]);
    $this->syncMembership($allRecords["groups"]);
  }

  /**
   * Users
   * columns: uid, givenName, sn, displayName, mailPrimaryAddress, uidNumber (used for identifying user across uid changes)
   */
  private function syncUsers($usersFromDirectory){
    global $wpdb;

    $table_name = "nucssa_user";

    /***** First remove deleted users from db *****/
    $uidNumbers_in_db = $wpdb->get_col("SELECT external_id FROM $table_name");
    $uidNumbers_in_directory = array_map(
      function ($user) {
        return $user[LDAP_Config::$USER_SCHEMA["USER_ID_ATTRIBUTE"]];
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
        function ($user) {
          return "(" . '"' . $user[LDAP_Config::$USER_SCHEMA["USERNAME_ATTRIBUTE"]] . '"' . ","
            . '"' . $user[LDAP_Config::$USER_SCHEMA["FIRST_NAME_ATTRIBUTE"]] . '"' . ","
            . '"' . $user[LDAP_Config::$USER_SCHEMA["LAST_NAME_ATTRIBUTE"]] . '"' . ","
            . '"' . $user[LDAP_Config::$USER_SCHEMA["DISPLAY_NAME_ATTRIBUTE"]] . '"' . ","
            . '"' . $user[LDAP_Config::$USER_SCHEMA["EMAIL_ATTRIBUTE"]] . '"' . ","
            . '"' . $user[LDAP_Config::$USER_SCHEMA["USER_ID_ATTRIBUTE"]] . '"' .
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

    /***** First remove deleted groups from db *****/
    $gidNumbers_in_db = $wpdb->get_col("SELECT external_id FROM $table_name");
    $gidNumbers_in_directory = array_map(
      function ($group) {
        return $group[LDAP_Config::$GROUP_SCHEMA["GROUP_ID_ATTRIBUTE"]];
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
        function ($group) {
          return "(" . '"' . $group[LDAP_Config::$GROUP_SCHEMA["NAME_ATTRIBUTE"]] . '"' . ","
            . '"' . $group[LDAP_Config::$GROUP_SCHEMA["DESCRIPTION_ATTRIBUTE"]] . '"' . ","
            . '"' . $group[LDAP_Config::$GROUP_SCHEMA["GROUP_ID_ATTRIBUTE"]] . '"' .
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
    $user_table_name = "nucssa_user";
    $group_table_name = "nucssa_group";
    $group_table_name = "nucssa_group";
    $membership_table_name = "nucssa_membership";
    $touchedRecords = [];
    /***** First update memberships in db and track touched records by ID *****/
    foreach($groups as $group){
      $memberDNs = $group[LDAP_Config::$MEMBERSHIP_SCHEMA["GROUP_MEMBERS_ATTRIBUTE"]];
      if ($memberDNs === NULL) continue; // skip if NULL
      $memberDNs = ("string" == gettype($memberDNs)) ? [$memberDNs] : $memberDNs;
      $gidNumber = $group[LDAP_Config::$GROUP_SCHEMA["GROUP_ID_ATTRIBUTE"]];
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
    $firstPart = explode(',', $memberDN)[0];
    if (strpos($memberDN, LDAP_Config::$LDAP_SCHEMA["ADDITIONAL_USER_DN"]) !== false) {
      $type = "user";
      $name = substr($firstPart, strlen(LDAP_Config::$USER_SCHEMA["USERNAME_ATTRIBUTE"] . "="));
    } else {
      $type = "group";
      $name = substr($firstPart, strlen(LDAP_Config::$GROUP_SCHEMA["NAME_ATTRIBUTE"] . "="));
    }

    return array(
      "type" => $type,
      "name" => $name
    );
  }
}
