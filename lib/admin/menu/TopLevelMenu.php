<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Admin\Menu;

/**
 * The top level menu for the core plugin
 */
class TopLevelMenu
{

  public function __construct()
  {
    add_action( 'admin_menu', function(){$this->addMenus();});
  }

  private function addMenus()
  {
    // add top level menu
    add_menu_page('Settings for NUCSSA Core', 'NUCSSA Core', 'manage_options', 'admin-menu-nucssa-core', function(){$this->render();}, 'none');
  }

  private function render()
  {
    global $menu, $_parent_pages, $_registered_pages, $admin_page_hooks;
    echo "hello world! this is awesome!";
  }
}


/**
 * TODO:
 * - read webpack.config.js and make changes accordingly to fit our project
 */