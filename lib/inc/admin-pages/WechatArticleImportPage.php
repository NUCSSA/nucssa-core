<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\admin_pages;

use nucssa_core\inc\ProcessWeChatArticleRequest;
use WP_Example_Request;

/**
 * SPA to process wechat article imports
 */
class WeChatArticleImportPage
{
  static $asyncRequest = null;
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
      'rest_url'  => $base_rest_url . 'nucssa-core/v1/wechat-article-import',
    ];
    wp_localize_script( $handle, 'wechat_import_page_data', $l10n );
  }

  public static function restfulCallback(\WP_REST_Request $request)
  {
    $purpose = $request->get_param('purpose');
    $url     = $request->get_param('url');
    if (substr($url, 0, 4) !== 'http'){
      $url = 'https://' . $url;
    }

    switch ($purpose) {
      case 'preview':
        return self::restGetArticlePreviewData($url);
      case 'process':
        return self::restProcessArticle($url);
      case 'status': // status query
        return self::restGetImportStatus($url);
      default:
        return new \WP_REST_Response(null, 400);
        break;
    }
  }

  private static function restGetArticlePreviewData($url)
  {
    try {
      // use timeout=1 to test error in frontend
      $result = \wp_remote_get($url, ['timeout' => 10]);
    } catch (\Throwable $th) {
      return $result;
    }
    $content = $result['body'];
    \preg_match('/<meta property="og:title" content="(.*)" \/>/', $content, $matches);
    $title = $matches[1];
    \preg_match('/<meta property="og:description" content="(.*)" \/>/', $content, $matches);
    $description = $matches[1];
    \preg_match('/<meta property="og:image" content="(.*)" \/>/', $content, $matches);
    $thumbnail = $matches[1];

    return rest_ensure_response( array(
      'title' => $title,
      'description' => $description,
      'thumbnail' => $thumbnail
    ) );
  }

  private static function restProcessArticle($url)
  {
    self::$asyncRequest->data(['url' => $url]);
    self::$asyncRequest->dispatch();
  }

  private static function restGetImportStatus($url)
  {
    $transientKey = "wechat_import_$url";
    $status = get_transient($transientKey);
    return rest_ensure_response($status);
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

  /**
   * Save image to wp-content/ without saving to DB,
   * @param $article uuid of wechat article
   *                 this will be the directory name for saved images
   * @param $src     external URL to the image to import
   * @return string URL to the saved image in our system
   */
  public static function saveImage(string $article, string $src)
  {
    $uploadsDir = wp_get_upload_dir();
    $pathBase = $uploadsDir['basedir'] . '/' . $article;
    $urlBase  = $uploadsDir['baseurl'] . '/' . $article;
    $fileName = md5($src);

    if (!file_exists($pathBase)) mkdir($pathBase);
    if (!copy("$src", "$pathBase/$fileName")){
      return new \WP_Error();
    }
    return "$urlBase/$fileName";
  }
}
