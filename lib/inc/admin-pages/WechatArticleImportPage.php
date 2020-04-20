<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\admin_pages;

/**
 * SPA to process wechat article imports
 */
class WeChatArticleImportPage
{
  public static function init()
  {
    self::registerPage();
    PageUtils::removeWpFooter();
  }

  public static function enqueueScript($hook)
  {
    if ($hook != 'posts_page_wechat-article') {
      return;
    }

    $handle = 'nucssa_wechat_article_page_script';
    $fpath = NUCSSA_CORE_DIR_PATH . 'public/js/page-wechat-article-import.js';
    $furl  = NUCSSA_CORE_DIR_URL  . 'public/js/page-wechat-article-import.js';
    $version = filemtime($fpath);
    wp_enqueue_script($handle, $furl, ['wp-element', 'wp-components'], $version, true);

    $base_rest_url = esc_url_raw( rest_url() );
    $l10n = [ // localization data goes here
      'nonce'     => wp_create_nonce('wp_rest'),
      'rest_url'  => $base_rest_url,
    ];
    wp_localize_script( $handle, 'wechat_import_page_data', $l10n );
  }

  private static function registerPage()
  {
    // add submenu to Posts menu
    add_submenu_page('edit.php', '微信文章导入', '微信文章导入', 'edit_posts', 'wechat-article', function(){self::render();});

    // add submenu to +New in admin bar
    add_action('admin_bar_menu', function(\WP_Admin_Bar $admin_bar){
      $menuItem = [
        'id'        => 'wechat-article-import',
        'title'     => '导入微信文章',
        'parent'    => 'new-content',
        'href'      => \get_admin_url(null, 'edit.php?page=wechat-article'),
      ];
      $admin_bar->add_node($menuItem);
    });
  }

  private static function render()
  {
    /**
     * React component
     */
    echo '<div id="wechat-import-admin-page"></div>';

    PageUtils::printNUCSSAFooterBranding();
    PageUtils::printStyleFixForAdminPageLeftPadding();
  }
}
