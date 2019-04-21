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
      // adding '/nucssa-core/v1/ldap-config' path
      // GET & POST
      $this->ldapConfig();
    });
  }

  private function ldapConfig()
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
    // Logger::singleton()->log_action('sync ldap ');
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

  public function permissionCheck($request)
  {
    return current_user_can('manage_options');
  }

}