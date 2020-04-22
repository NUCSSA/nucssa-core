<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\admin_pages;

/**
 * The top level menu for the core plugin
 */
class UserDirectoryConfigPage
{
  public static function init()
  {
    // create the sidebar menu item
    self::addMenuPage();
    PageUtils::removeWpFooter();
  }

  private static function addMenuPage()
  {
    // add top level menu
    add_menu_page('Settings for NUCSSA Core', 'NUCSSA Core', 'manage_options', 'admin-menu-page-nucssa-core', function(){self::render();}, 'none');
  }

  private static function render()
  {
    global $menu, $_parent_pages, $_registered_pages, $admin_page_hooks;
    // $this->page->render();
    // var_dump( $admin_page_hooks);

    /**
     * React component
     */
    echo '<div id="users-and-groups-admin-page"></div>';

    PageUtils::printNUCSSAFooterBranding();
    PageUtils::printStyleFixForAdminPageLeftPadding();
  }

  public static function enqueueScript($hook)
  {
    if ($hook != 'toplevel_page_admin-menu-page-nucssa-core') {
      return;
    }

    $handle = 'nucssa_core_page_script';
    $fpath = NUCSSA_CORE_DIR_PATH . 'public/js/page-core.js';
    $furl = NUCSSA_CORE_DIR_URL . 'public/js/page-core.js';
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

  public static function enqueueStyle($hook)
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
  }


}
