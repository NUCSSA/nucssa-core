<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc\accounts;
use function nucssa_core\utils\pluggable\{get_user_by};

class Accounts {
  private function construct(){}

  /**
   * Try to authenticate user in the authentication queue
   * first search LDAP users and then fallback to local users if remote user is not found.
   *
   * @param null|WP_User|WP_Error $user   null indicates no process has authenticated the user yet;
   *                                      WP_Error indicates another process has failed the authentication;
   *                                      WP_User indicates another process has authenticated the user.
   * @param string $username              username or email
   * @param string $password
   */
  public static function login($user, $username, $password) {
    if ($user instanceof WP_User) {
      return $user;
    }
    if (empty($username) || empty($password)) return null;

    // authentication services prior to our service failed
    // OR
    // no service has tried to authenticate the user yet, let's authenticate against our LDAP system
    $directory = UserDirectory::singleton();
    if ($directory->userExists($username)) {
      /**
       * Process:
       * 1. check changes since last sync and do a sync if diff
       * 2. Does user already exist locally?
       * 3. Create local user if not
       * 4. remove local authentication action
       * 5. return WP_User object
       */
      // 1. re-sync if there are changes
      // must do a full sync because deletions won't show in updates
      // `self::syncFromDirectory()` can take care of deletions with a full sync
      $lastSyncTimestamp = self::getLastSyncTimestamp();
      $filter = "(& (modifyTimestamp>={$lastSyncTimestamp})(|(objectClass=inetOrgPerson)(objectClass=posixGroup)))";
      $res = $directory->search($filter, ['uid']);
      if (\ldap_count_entries($directory->conn, $res) > 0) {
        self::updateLastSyncTimestamp();
        self::syncFromDirectory();

        $entry = ldap_first_entry($directory->conn, $res);
        do {
          $uidNumber = @ldap_get_values($directory->conn, $entry, $directory->user_schema['user_id_attribute'])[0];
          if ($uidNumber) {
            $dir_user = DirectoryUser::findByExternalID($uidNumber);
            // find user id in wp_users table
            $user = get_user_by('external_id', $uidNumber);
            // update user in wp-users
            wp_update_user([
              'ID' => $user->ID,
              'user_login' => $dir_user->username,
              'first_name' => $dir_user->first_name,
              'last_name' => $dir_user->last_name,
              'user_email' => $dir_user->email,
              'display_name' => $dir_user->display_name,
            ]);
          }
        } while ($entry = ldap_next_entry($directory->conn, $entry));
      }

      // 2. Does user exists locally in wp-users table?
      $user = get_user_by('login', $username); // get user in wp_users table
      // 3. Create local user if not exists
      if (!$user) {
        $dir_user = DirectoryUser::findByUsername($username); // get user in nucssa_user table
        $userdata = [
          'user_login' => $dir_user->username,
          'first_name' => $dir_user->first_name,
          'last_name' => $dir_user->last_name,
          'user_email' => $dir_user->email,
          'display_name' => $dir_user->display_name,
        ];
        $new_user_id = wp_insert_user($userdata);
        $user = new \WP_User($new_user_id);
        // update external_id of user record
        global $wpdb;
        $wpdb->query(
          "UPDATE $wpdb->users SET external_id = $dir_user->external_id
          WHERE ID = $new_user_id"
        );

        self::updateUserRoles($user, $dir_user);

        /**
         * @param WP_User $user
         * @param DirectoryUser $dir_user
         */
        do_action('nucssa_user_created', $user, $dir_user);
      }

      // now try to authenticate against LDAP record
      if ($directory->authenticateUser($username, $password)){
        return $user;
      } else {
        return new \WP_Error('denied', __('Wrong password.'));
      }
    } else { // give a chance for local authentication
      return new \WP_Error('denied', __('Invalid username.'));
    }
  }

  /**
   * Update roles on usermeta to keep sync with user/group permissions
   *
   * Provide one of the params below, would be even efficient if both are provided.
   * @param WP_User|null $user
   * @param DirectoryUser|null $dir_user
   */
  public static function updateUserRoles(\WP_User $user, DirectoryUser $dir_user){
    if (!$user && !$dir_user) return;

    if (!$user) $user = get_user_by('external_id', $dir_user->external_id);
    if (!$dir_user) $dir_user = DirectoryUser::findByUserID($user->ID);

    if ($user && $dir_user){ // make sure we are not editing locally created users by accident
      // first clear existing roles from user
      $user->set_role('');

      // add roles to user
      $roles = $dir_user->allRoles();
      foreach($roles as $role) {
        $user->add_role( $role );
      }
    }
  }

  /**
   * sync users and groups from LDAP server to wordpress database
   */
  public static function syncFromDirectory() {
    $allRecords = UserDirectory::singleton() -> fetchAll();

    self::syncUsers($allRecords["users"]);
    self::syncGroups($allRecords["groups"]);
    self::syncMembership($allRecords["groups"]);

    // save sync timestamp in wp-option
    self::updateLastSyncTimestamp();
  }

  public static function updateLastSyncTimestamp() {
    update_option(\LDAP_SYNC_LAST_TIMESTAMP, (new \DateTime())->format('YmdHis\Z'), false);
  }

  public static function getLastSyncTimestamp() {
    return get_option(\LDAP_SYNC_LAST_TIMESTAMP, '19700101000000Z');
  }

  /**
   * Search users and groups with given $keyword
   *
   * @param String $keyword
   * @return Array array(users => [], groups => [])
   */
  public static function search($keyword){
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
  public static function allPerms(){
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
  private static function syncUsers($usersFromDirectory){
    if (!$usersFromDirectory) return;

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
    // remove deleted users from nucssa_user table
    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM $table_name WHERE external_id IN (%s)",
        implode(',', $uidNumbers_of_deleted_users)
      )
    );

    // remove deleted users from wp_users table
    // get_user_by('external_id', $uidNumber);
    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM {$wpdb->users} WHERE external_id IN (%s)",
        implode(',', $uidNumbers_of_deleted_users)
      )
    );


    /***** Then upsert updated users to db *****/
    $values_string = implode(
      ",",
      array_map(
        function ($user) use ($directory) {
          $username = $user[$directory->user_schema["username_attribute"]];
          $firstname = $user[$directory->user_schema["firstname_attribute"]] ?? null;
          $lastname = $user[$directory->user_schema["lastname_attribute"]] ?? null;
          $display_name = $user[$directory->user_schema["display_name_attribute"]] ?? null;
          $email = $user[$directory->user_schema["email_attribute"]] ?? null;
          $user_id = $user[$directory->user_schema["user_id_attribute"]];
          $value_str = "("
            . "'$username'" . ","
            . ($firstname ? "'$firstname'" : 'NULL') . ","
            . ($lastname ? "'$lastname'" : 'NULL') . ","
            . ($display_name ? "'$display_name'" : 'NULL') . ","
            . ($email ? "'$email'" : 'NULL') . ","
            . $user_id .
            ")";
          return $value_str;
        },
        $usersFromDirectory
      )
    );
    $query = <<<insert_query
    INSERT INTO $table_name
    (username, first_name, last_name, display_name, email, external_id)
    VALUES $values_string
    ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    display_name = VALUES(display_name),
    email = VALUES(email);
insert_query;
    $wpdb->query($query);
  }

  /**
   * Groups
   * columns: cn, description, gidNumber, uniqueMember (used for identifying group across name changes)
   */
  private static function syncGroups($groupsFromDirectory){
    if (!$groupsFromDirectory) return;

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
          $name = $group[$directory->group_schema["name_attribute"]];
          $description = $group[$directory->group_schema["description_attribute"]] ?? null;
          $group_id = $group[$directory->group_schema["group_id_attribute"]];
          return "("
            . "'$name'" . ","
            . ($description ? "'$description'" : 'NULL') . ","
            . $group_id .
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
  private static function syncMembership($groups){
    if (!$groups) return;

    global $wpdb;
    $directory = UserDirectory::singleton();

    $user_table_name = "nucssa_user";
    $group_table_name = "nucssa_group";
    $membership_table_name = "nucssa_membership";
    $touchedRecords = [];
    /***** First update memberships in db and track touched records by ID *****/
    foreach($groups as $group){
      $memberDNs = $group[$directory->membership_schema["group_membership_attribute"]] ?? NULL;
      if ($memberDNs === NULL) continue; // skip if NULL, aka. no members in this group
      $gidNumber = $group[$directory->group_schema["group_id_attribute"]];
      $parent_id = $wpdb->get_var("SELECT id FROM {$group_table_name} WHERE external_id = {$gidNumber}");

      foreach($memberDNs as $memberDN){

        $child_group_id = $child_user_id = 'NULL';
        ["type" => $type, "name" => $name] = self::getMemberTypeAndName($memberDN);

        if ($type == "user") {
          // find user id with $name
          $child_user_id = $wpdb->get_var("SELECT id FROM {$user_table_name} WHERE username = '{$name}'");
        } elseif ($type == "group") {
          // find group id with gidNumber
          $child_group_id = $wpdb->get_var("SELECT id FROM {$group_table_name} WHERE group_name = '{$name}'");
        } else {
          continue;
        }
        $query = "INSERT INTO {$membership_table_name}
          (parent_id, child_group_id, child_user_id)
          VALUES ($parent_id, $child_group_id, $child_user_id)
          ON DUPLICATE KEY UPDATE
          parent_id = $parent_id
        ";
        $wpdb->query($query);

        // track touched membership record
        $child_col = $child_group_id !== 'NULL' ? 'child_group_id' : 'child_user_id';
        $child_val = $child_group_id !== 'NULL' ? $child_group_id : $child_user_id;
        $touchedRecords[] = $wpdb->get_var("SELECT id FROM {$membership_table_name}
          WHERE parent_id = {$parent_id}
          AND {$child_col} = {$child_val}"
        );
      }
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
  private static function getMemberTypeAndName(string $memberDN){
    $directory = UserDirectory::singleton();

    $firstPart = explode(',', $memberDN)[0];
    if (strpos($memberDN, $directory->schema["additional_user_dn"]) !== false) {
      $type = "user";
      $name = substr($firstPart, strlen($directory->user_schema["username_attribute"] . "="));
    } elseif (strpos($memberDN, $directory->schema["additional_group_dn"]) !== false) {
      $type = "group";
      $name = substr($firstPart, strlen($directory->group_schema["name_attribute"] . "="));
    } else {
      $type = "other";
    }

    return array(
      "type" => $type,
      "name" => $name
    );
  }
}
