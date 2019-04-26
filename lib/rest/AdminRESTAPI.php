<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\REST;

use NUCSSACore\Utils\Logger;
use NUCSSACore\Accounts\Accounts;
use NUCSSACore\Accounts\UserDirectory;

class AdminRESTAPI
{
  public function __construct()
  {
    add_action('rest_api_init', function(){
      // GET & POST '/nucssa-core/v1/ldap-config'
      $this->ldapConfigAPI();

      // POST '/nucssa-core/v1/permissions'
      $this->permissionsAPI();
    });
  }

  public function permissionCheck($request)
  {
    return current_user_can('manage_options');
  }

  private function ldapConfigAPI()
  {
    $rest_namespace = 'nucssa-core/v1';
    $rest_route = 'ldap-config';

    $option_keys = [
      'server' => 'nucssa-core.ldap.server',
      'schema' => 'nucssa-core.ldap.schema',
      'user_schema' => 'nucssa-core.ldap.user_schema',
      'group_schema' => 'nucssa-core.ldap.group_schema',
      'membership_schema' => 'nucssa-core.ldap.membership_schema',
    ];
    register_rest_route($rest_namespace, $rest_route, array(
      [
        'methods' => 'GET',
        'callback' => function() use ($option_keys) {

          $server             = get_option($option_keys['server'], []);
          $schema             = get_option($option_keys['schema'], []);
          $user_schema        = get_option($option_keys['user_schema'], []);
          $group_schema       = get_option($option_keys['group_schema'], []);
          $membership_schema  = get_option($option_keys['membership_schema'], []);

          return rest_ensure_response(array(
            'server'              => $server,
            'schema'              => $schema,
            'user_schema'         => $user_schema,
            'group_schema'        => $group_schema,
            'membership_schema'   => $membership_schema
          ));
        },
        'permission_callback' => array($this, 'permissionCheck')
      ],
      [
        'methods' => 'POST',
        'callback' => function($request) use ($option_keys) {
          $params = $request->get_params();
          // Logger::singleton()->log_action('post params', $params);
          switch ($params['command']) {
            case 'save':
              return $this->saveLdapConfig($params['data'], $option_keys);
              break;
            case 'sync':
              return $this->syncLdap();
              break;
            case 'test_connection':
              return $this->testLdapConnection();
              break;

            default:
              # code...
              break;
          }
        },
        'permission_callback' => array($this, 'permissionCheck')
      ]
    ));
  }

  private function saveLdapConfig($data, $option_keys)
  {
    $server            = $data['server'];
    $schema            = $data['schema'];
    $user_schema       = $data['user_schema'];
    $group_schema      = $data['group_schema'];
    $membership_schema = $data['membership_schema'];


    // Logger::singleton()->log_action('group_schema ', $group_schema);
    update_option($option_keys['server'], $server);
    update_option($option_keys['schema'], $schema);
    update_option($option_keys['user_schema'], $user_schema);
    update_option($option_keys['group_schema'], $group_schema);
    update_option($option_keys['membership_schema'], $membership_schema);

    return rest_ensure_response(array(
      'update_status' => 'updated'
    ));
  }

  private function syncLdap()
  {
    // Logger::singleton()->log_action('syncLdap called');
    (new Accounts)->syncFromDirectory();
    // process will die if LDAP failed

    // send success message
    // Logger::singleton()->log_action('sync success');
    return rest_ensure_response('success');
  }

  private function testLdapConnection()
  {
    // Logger::singleton()->log_action('test ldap connection');
    if (UserDirectory::singleton()->testConnection()){
      return rest_ensure_response('success');
    } else {
      sleep(2);
      return rest_ensure_response('failed');
    }
  }

  private function permissionsAPI(){
    $namespace = 'nucssa-core/v1';
    $route = 'permissions';

    register_rest_route($namespace, $route, array(
      [
        'methods' => 'POST',
        'callback' => function($request) {
          $params = $request->get_params();
          switch ($params['command']) {
            case 'search':
              $keyword = $params['data'];
              /**
               * 1. log the keyword
               * 2. search keyword from user table
               * 3. search keyword from group table
               * 4. encapsoluate them into an array and return it back
               */
              // Logger::singleton()->log_action('search keyword', $keyword);
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

              $resp = array(
                'users' => $users,
                'groups' => $groups
              );
              return rest_ensure_response($resp);
              break;

            case 'get_all_roles':
              global $wp_roles;
              $roles = $wp_roles->role_names;

              return rest_ensure_response($roles);
              break;

            case 'set_role':
              /**
               * no need to return
               */
              ['type' => $type, 'id' => $id, 'role' => $role] = $params['data'];
              Logger::singleton()->log_action('set role called', );
              Logger::singleton()->log_action('type', $type);
              // TODO: think about how to treat this with your own tables
              break;

            case 'remove_role':
              /**
               * return true on success
               */
              break;

            default:
              # code...
              break;
          }
        },
        'permission_callback' => array($this, 'permissionCheck')
      ]
    ));
  }

}