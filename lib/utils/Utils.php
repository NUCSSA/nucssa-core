<?php
namespace NUCSSACore\Utils;

class Utils
{
  public static function enableBrowserSyncOnDebugMode(){
    if (WP_DEBUG) {
      add_action('admin_print_scripts', function () {
        echo '<script async="" src="http://wp.localhost:3000/browser-sync/browser-sync-client.js"></script>';
      });
    }
  }
}
