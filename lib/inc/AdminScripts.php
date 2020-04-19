<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc;

use nucssa_core\admin_pages\UserDirectoryConfigPage;
use nucssa_core\admin_pages\WechatArticleImportPage;

/**
 * Manage all JS and CSS scripts used in the admin dashboard
 */
class AdminScripts
{
  public static function init($hook)
  {
    self::enqueueGlobalStyle();
    UserDirectoryConfigPage::enqueueScript($hook);
    UserDirectoryConfigPage::enqueueStyle ($hook);
    WechatArticleImportPage::enqueueScript($hook);

    // load browserSync script for development
    self::enableBrowserSyncOnDebugMode();
  }

  private static function enqueueGlobalStyle()
  {
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
