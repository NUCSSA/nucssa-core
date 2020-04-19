<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\admin_pages;

/**
 * SPA to process wechat article imports
 */
class WechatArticleImportPage
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

  }

  private static function registerPage()
  {
    // add submenu to Posts menu
    add_submenu_page('edit.php', '微信文章导入', '微信文章导入', 'edit_posts', 'wechat-article', function(){self::render();});
  }

  private static function render()
  {
    /**
     * React component
     */
    echo '<div id="wechat-import-admin-page"><div>';

    PageUtils::printNUCSSAFooterBranding();
    PageUtils::printStyleFixForAdminPageLeftPadding();
  }
}
