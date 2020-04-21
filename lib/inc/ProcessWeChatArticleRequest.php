<?php
namespace nucssa_core\inc;

use nucssa_core\admin_pages\WeChatArticleImportPage;

/**
 * A background process to analyze and persist WeChat articles
 */
class ProcessWeChatArticleRequest extends \WP_Async_Request
{
  protected function handle()
  {
    $url = $_POST['url'];
    $transientKey = "wechat_import_$url";
		// Step 1: analyze article for image assets
		\set_transient($transientKey, ['status' => 'processing', 'step' => 1], 60);

		$content = file_get_contents($url);
    if (is_wp_error($content)) {
			\set_transient($transientKey, ['status' => 'error'], 60);
      exit;
    }
    \preg_match('/<meta property="og:title" content="(.*)" \/>/', $content, $matches);
		$title = $matches[1];
		\preg_match('/<meta property="og:description" content="(.*)" \/>/', $content, $matches);
    $description = $matches[1];
    \preg_match('/<meta property="og:image" content="(.*)" \/>/', $content, $matches);
    $thumbnail = $matches[1];

		$doc = new \DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($content);
		libxml_clear_errors();
		$contentElement = $doc->getElementById('js_content');
		$content = $doc->saveHTML($contentElement);

    // find all images and gifs in article
    \preg_match_all('/<img [^>]*src="(http[^"]+)"[^>]*\/?>/', $content, $matches);
    /** \preg_match_all('/<img [^>]*src="(http[^"]+)"[^>]*(?:alt="([^"]*)")?[^>]*\/?>/', $content, $matches); */
		$images = (array)$matches[1];
		$images = array_unique($images);

		// Step 2: download those assets and move to media folder
		\set_transient($transientKey, ['status' => 'processing', 'step' => 2], 60);
		$path_components = explode('/', parse_url($url, PHP_URL_PATH));
		$articleID = end($path_components);
		foreach ($images as $img) {
			$url = WeChatArticleImportPage::saveImage($articleID, $img);
			if (is_wp_error( $url )) {
				\set_transient($transientKey, ['status' => 'error'], 60);
				exit;
			}
			$image_lookups["data-src=\"$img\""] = "src=\"$url\"";
		}

		// Step 3: process/replace image urls in article
		\set_transient($transientKey, ['status' => 'processing', 'step' => 3], 60);
		$content = str_replace(array_keys($image_lookups), array_values($image_lookups), $content);

		// Step 4: process/set thumbnail
		\set_transient($transientKey, ['status' => 'processing', 'step' => 4], 60);
		// insert and retrieve thumbnail id
		parse_str(parse_url($thumbnail, PHP_URL_QUERY), $query);
		$format = $query['wx_fmt'];
		$thumbnailId = media_sideload_image("$thumbnail.$format", 0, null, 'id');

		// Step 5: save to wordpress
		\set_transient($transientKey, ['status' => 'processing', 'step' => 5], 60);
		$postattr = [
			'post_content'  => $content,
			'post_title'    => $title,
			'post_excerpt'  => $description,
			'_thumbnail_id' => $thumbnailId
		];
		$postID = wp_insert_post($postattr, true);
		if (is_wp_error($postID)) {
			\set_transient($transientKey, ['status' => 'error'], 60);
      exit;
		}

		// Done
		\set_transient($transientKey, ['status' => 'finished', 'postURL' => get_admin_url(null, "post.php?post=$postID&action=edit")], 60);
  }
}
