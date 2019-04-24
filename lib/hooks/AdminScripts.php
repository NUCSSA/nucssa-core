<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Hooks;

use NUCSSACore\Utils\Constants;
use NUCSSACore\Utils\Logger;

/**
 * Manage all JS and CSS scripts used in the admin dashboard
 */
class AdminScripts
{
  public function __construct()
  {
    add_action('admin_enqueue_scripts', function($hook){
      $this->loadAdminScripts($hook);
      $this->loadAdminStyles($hook);
    });

    // load browserSync script for development
    $this->enableBrowserSyncOnDebugMode();
  }

  private function loadAdminScripts($hook)
  {
    // Logger::singleton()->log_action("hook", $hook);
    // if ($hook != 'toplevel_page_admin-menu-page-nucssa-core') {
    //   return;
    // }

    $handle = 'nucssa_core_amdin_script';
    // load core script
    wp_enqueue_script(
      $handle,
      Constants::singleton()->plugin_dir_url . 'public/js/admin.js',
      array(), // deps
      false, // version
      true // in_footer?
    );

    $base_rest_url = esc_url_raw( rest_url() );
    // localize core script with some vars
    wp_localize_script(
      $handle,
      'core_admin_data',
      array(
        'root_url' => get_site_url(),
        'rest_url' => $base_rest_url,
        'ldap_config_rest_url' => $base_rest_url . 'nucssa-core/v1/ldap-config',
        'permissions_rest_url' => $base_rest_url . 'nucssa-core/v1/permissions',
        'nonce' => wp_create_nonce('wp_rest'),
      )
    );


  }

  private function loadAdminStyles($hook)
  {
    // NUCSSA Core Plugin Page only Styles
    if ($hook === 'toplevel_page_admin-menu-page-nucssa-core') {
      wp_enqueue_style(
        'nucssa_core_admin_plugin_page_style',
        Constants::singleton()->plugin_dir_url . 'public/css/admin-plugin-page.css',
        array(), // deps
        false,   // version
        'all'    // media
      );
    }

    // Global Styles
    wp_enqueue_style(
      'nucssa_core_admin_global_style',
      Constants::singleton()->plugin_dir_url . 'public/css/admin-global.css',
      array(), // deps
      false,   // version
      'all'    // media
    );
  }


  private function enableBrowserSyncOnDebugMode()
  {
    if (WP_DEBUG) {
      add_action('admin_print_scripts', function () {
        echo '<script async="" src="http://wp.localhost:3000/browser-sync/browser-sync-client.js"></script>';
      });
    }
  }
}
