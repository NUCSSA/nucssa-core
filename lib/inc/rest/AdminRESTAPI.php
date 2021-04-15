<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc\rest;

use nucssa_core\inc\AdminPages\WeChatArticleImportPage;
use nucssa_core\inc\accounts\{Accounts, UserDirectory, DirectoryPerm};

class AdminRESTAPI
{
  public function __construct()
  {
    // GET & POST '/nucssa-core/v1/ldap-config'
    $this->ldapConfigAPI();

    // POST '/nucssa-core/v1/permissions'
    $this->permissionsAPI();

    // POST '/nucssa-core/v1/wechat-article-import'
    $this->wechatArticleImportAPI();
  }

  public function permissionCheck(): bool
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
          // file_log('post params', $params);
          return match ($params['command']) {
            'save' => $this->saveLdapConfig($params['data'], $option_keys),
            'sync' => $this->syncLdap(),
            'test_connection' => $this->testLdapConnection()
          };
        },
        'permission_callback' => array($this, 'permissionCheck')
      ]
    ));
  }

  private function saveLdapConfig($data, $option_keys): \WP_REST_Response
  {
    $server            = $data['server'];
    $schema            = $data['schema'];
    $user_schema       = $data['user_schema'];
    $group_schema      = $data['group_schema'];
    $membership_schema = $data['membership_schema'];


    // file_log('group_schema ', $group_schema);
    update_option($option_keys['server'], $server);
    update_option($option_keys['schema'], $schema);
    update_option($option_keys['user_schema'], $user_schema);
    update_option($option_keys['group_schema'], $group_schema);
    update_option($option_keys['membership_schema'], $membership_schema);

    return rest_ensure_response(array(
      'update_status' => 'updated'
    ));
  }

  private function syncLdap(): \WP_REST_Response
  {
    // file_log('syncLdap called');
    Accounts::syncFromDirectory();
    // process will die if LDAP failed

    // send success message
    // file_log('sync success');
    return rest_ensure_response('success');
  }

  private function testLdapConnection(): \WP_REST_Response
  {
    // file_log('test ldap connection');
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
              $resp = Accounts::search($keyword);
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
              $perms = Accounts::allPerms();
              return rest_ensure_response($perms);

            case 'save_perms':
              $perms = $params['data'];
              foreach ($perms as $perm) {
                ['account_type' => $account_type, 'account_id' => $account_id, 'role' => $role, 'action' => $action, 'id' => $id] = $perm;
                switch ($action) {
                  case 'add':
                    $perm = DirectoryPerm::new($role, $account_type, $account_id);
                    $perm->store();
                    break;

                  case 'update':
                    $perm = DirectoryPerm::find($id);
                    $perm->role = $role;
                    $perm->store();
                    break;

                  case 'delete':
                    $perm = DirectoryPerm::find($id);
                    $perm->delete();
                    break;

                  default:
                    break;
                }
              }
              wp_send_json_success();

            default:
              return new \Exception('unreachable return statement');
          }
        },
        'permission_callback' => array($this, 'permissionCheck')
      ]
    ));
  }

  private function wechatArticleImportAPI()
  {
    $namespace = 'nucssa-core/v1';
    $route = 'wechat-article-import';

    register_rest_route($namespace, $route, array(
      [
        'methods'  => 'POST',
        'callback' => [WeChatArticleImportPage::class, 'restfulCallback'],
        'permission_callback' => function(){return current_user_can( 'edit_posts');},
      ]
    ));
  }

}
