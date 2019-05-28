<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\admin_dashboard\menu_page;

/**
 * The top level menu for the core plugin
 */
class TopLevelMenuPage
{
  private $page;

  public function __construct()
  {
    // create the sidebar menu item
    add_action( 'admin_menu', function(){
      $this->addMenus();
      $this->removeWpFooter();
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
      <img class="brand-image" src="' . NUCSSA_CORE_DIR_URL . '/public/images/logo.png' . '" />
      <div class="copyright">© ' . $year . ' NUCSSA IT All Rights Reserved</div>
    </div>';
  }
}
