<?php
namespace nucssa_core\utils\pluggable;

/**
 * @param string $field The field to query against: 'id', 'ID', 'slug', 'email', 'login', 'external_id'.
 * @param string|int $value The field value
 * @return object|false WP_User object on success, false on failure.
 */
function get_user_by($field, $value) {
  global $wpdb;

  switch ($field) {
    case 'external_id':
      $userdata = $wpdb->get_row(
        $wpdb->prepare(
          "SELECT * FROM $wpdb->users WHERE external_id = %s LIMIT 1",
          $value
        )
      );
      if (!$userdata) return false;

      update_user_caches($userdata);

      $user = new \WP_User;
      $user->init($userdata);
      return $user;

    default:
      return \get_user_by($field, $value);
  }
}
