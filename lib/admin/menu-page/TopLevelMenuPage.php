<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Admin\MenuPage;

use NUCSSACore\Utils\Constants;
use NUCSSACore\Utils\Logger;

/**
 * The top level menu for the core plugin
 */
class TopLevelMenuPage
{
  private $page;

  public function __construct()
  {
    $this->page = new \NUCSSACore\Admin\MenuPage\UsersAndGroups();
    // create the sidebar menu item
    add_action( 'admin_menu', function(){
      $this->addMenus();
      $this->removeWpFooter();
    } );

    add_action( 'admin_init', function(){
      // register settings options
      $this->registerSettings();
      // add settings
      $this->addSettings();
    } );


  }

  private function addMenus()
  {
    // add top level menu
    add_menu_page('Settings for NUCSSA Core', 'NUCSSA Core', 'manage_options', 'admin-menu-page-nucssa-core', function(){$this->render();}, 'none');
  }

  private function removeWpFooter()
  {
    add_filter('update_footer', '__return_empty_string', 11);
    add_filter('admin_footer_text', '__return_empty_string', 11);
  }

  private function registerSettings()
  {
    // register settings for users and groups
    $this->page->registerSettings();
  }

  private function addSettings()
  {
    // Add Settings and Section
    $this->page->addSettings();
  }

  private function render()
  {
    global $menu, $_parent_pages, $_registered_pages, $admin_page_hooks;
    // $this->page->render();
    // var_dump( $admin_page_hooks);

    /**
     * React component
     */
    echo '<div id="users-and-groups-admin-page"></div>';

    /**
     * Footer Branding
     */
    $year = date('Y');
    echo '<div class="nucssa-footer">
      <div class="brand-title">NUCSSA IT</div>
      <img class="brand-image" src="' . Constants::singleton()->plugin_dir_url . '/public/images/logo.png' . '" />
      <div class="copyright">© ' . $year . ' NUCSSA IT All Rights Reserved</div>
    </div>';
  }
}
