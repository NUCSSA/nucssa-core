<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc;

/**
 * Manage all JS and CSS scripts used in the admin dashboard
 */
class AdminScripts
{
  public static function init($hook)
  {
    self::loadAdminScripts($hook);
    self::loadAdminStyles($hook);

    // load browserSync script for development
    self::enableBrowserSyncOnDebugMode();
  }

  private static function loadAdminScripts($hook)
  {
    if ($hook != 'toplevel_page_admin-menu-page-nucssa-core') {
      return;
    }

    $handle = 'nucssa_core_amdin_script';
    $fpath = NUCSSA_CORE_DIR_PATH . 'public/js/admin.js';
    $furl = NUCSSA_CORE_DIR_URL . 'public/js/admin.js';
    $version = filemtime($fpath);
    // load core script
    wp_enqueue_script(
      $handle,
      $furl,
      [ 'wp-element' ], // deps
      $version,
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

  private static function loadAdminStyles($hook)
  {
    // NUCSSA Core Plugin Page only Styles
    if ($hook === 'toplevel_page_admin-menu-page-nucssa-core') {
      $fpath = NUCSSA_CORE_DIR_PATH . 'public/css/admin-plugin-page.css';
      $furl = NUCSSA_CORE_DIR_URL . 'public/css/admin-plugin-page.css';
      $version = filemtime($fpath);
      wp_enqueue_style(
        'nucssa_core_admin_plugin_page_style',
        $furl,
        array(), // deps
        $version,   // version
        'all'    // media
      );
    }

    // Global Styles
    $fpath = NUCSSA_CORE_DIR_PATH . 'public/css/admin-global.css';
    $furl = NUCSSA_CORE_DIR_URL . 'public/css/admin-global.css';
    $version = filemtime($fpath);
    wp_enqueue_style(
      'nucssa_core_admin_global_style',
      $furl,
      array(), // deps
      $version,   // version
      'all'    // media
    );
  }


  private static function enableBrowserSyncOnDebugMode()
  {
    if (WP_DEBUG) {
      wp_enqueue_script('browser-sync', 'http://localhost:3000/browser-sync/browser-sync-client.js', [], false, true);
    }
  }
}
