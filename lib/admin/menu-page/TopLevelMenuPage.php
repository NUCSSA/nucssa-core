<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Admin\MenuPage;

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
    add_action( 'admin_menu', function(){$this->addMenus();} );

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
    echo '<div id="users-and-groups-admin-page"></div>';
  }
}
