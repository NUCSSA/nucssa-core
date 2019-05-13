<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\REST;

use NUCSSACore\Utils\Logger;
use NUCSSACore\Accounts\Accounts;
use NUCSSACore\Accounts\UserDirectory;
use NUCSSACore\Accounts\Perm;

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
              $resp = (new Accounts)->search($keyword);
              return rest_ensure_response($resp);

            case 'get_all_roles':
              global $wp_roles;
              $roles = $wp_roles->role_names;

              return rest_ensure_response($roles);

            case 'get_all_perms':
              /**
               * get existing permission records
               * perms are persisted in nucssa_perm table
               * @return [...{id, role, account_type, account_id, account_display_name}...]
               */
              $perms = (new Accounts)->allPerms();
              return rest_ensure_response($perms);

            case 'save_perms':
              $perms = $params['data'];
              foreach ($perms as $perm) {
                ['account_type' => $account_type, 'account_id' => $account_id, 'role' => $role, 'action' => $action, 'id' => $id] = $perm;
                switch ($action) {
                  case 'add':
                    $perm = Perm::new($role, $account_type, $account_id);
                    $perm->store();
                    break;

                  case 'update':
                    $perm = Perm::find($id);
                    $perm->role = $role;
                    $perm->store();
                    break;

                  case 'delete':
                    $perm = Perm::find($id);
                    $perm->delete();
                    break;

                  default:
                    break;
                }
              }
              return rest_ensure_response($perm->id);
              break;

            default:
              break;
          }
        },
        'permission_callback' => array($this, 'permissionCheck')
      ]
    ));
  }

}