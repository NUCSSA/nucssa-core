<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Hooks;

use NUCSSACore\Utils\Constants;

/**
 * Manage all JS and CSS scripts used in the admin dashboard
 */
class AdminScripts
{
  public function __construct()
  {
    add_action('admin_enqueue_scripts', function(){
      $this->loadAdminScripts();
      $this->loadAdminStyles();
    });

    // load browserSync script for development
    $this->enableBrowserSyncOnDebugMode();
  }

  private function loadAdminScripts()
  {
    // load core script
    wp_enqueue_script(
      'nucssa_core_amdin_script',
      Constants::singleton()->plugin_dir_url . 'public/js/admin.js',
      array(), // deps
      false, // version
      true // in_footer?
    );


  }

  private function loadAdminStyles()
  {
    wp_enqueue_style(
      'nucssa_core_admin_style',
      Constants::singleton()->plugin_dir_url . 'public/css/admin.css',
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
