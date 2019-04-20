<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\REST;

use NUCSSACore\Utils\Logger;

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
    ];
    register_rest_route($rest_namespace, $rest_route, array(
      [
        'methods' => 'GET',
        'callback' => function() use ($option_keys) {

          $server       = get_option($option_keys['server'], []);
          $schema       = get_option($option_keys['schema'], []);
          $user_schema  = get_option($option_keys['user_schema'], []);
          $group_schema = get_option($option_keys['group_schema'], []);

          return rest_ensure_response(array(
            'server'        => $server,
            'schema'        => $schema,
            'user_schema'   => $user_schema,
            'group_schema'  => $group_schema
          ));
        },
        'permission_callback' => array($this, 'permissionCheck')
      ],
      [
        'methods' => 'POST',
        'callback' => function($request) use ($option_keys) {
          $params = $request->get_params();
          // Logger::singleton()->log_action('post params', $params);
          $server       = $params["data"][$option_keys['server']];
          $schema       = $params["data"][$option_keys['schema']];
          $user_schema  = $params["data"][$option_keys['user_schema']];
          $group_schema = $params["data"][$option_keys['group_schema']];

          $success  = update_option($option_keys['server'], $server);
          // Logger::singleton()->log_action('server', $server);
          update_option($option_keys['schema'], $schema);
          update_option($option_keys['user_schema'], $user_schema);
          update_option($option_keys['group_schema'], $group_schema);
          // Logger::singleton()->log_action('success ', $success);
        },
        'permission_callback' => array($this, 'permissionCheck')
      ]
    ));
  }

  public function permissionCheck($request)
  {
    return current_user_can('manage_options');
  }

}