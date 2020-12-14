<?php
namespace nucssa_core\inc;

use nucssa_core\inc\AdminPages\WeChatArticleImportPage;

/**
 * A background process to analyze and persist WeChat articles
 */
class ProcessWeChatArticleRequest extends \WP_Async_Request
{
  protected function handle()
  {
    $url = $_POST['url'];
		$transientKey = "wechat_import_$url";
		$transientDuration = 5*60; // expire in 5 mins for the first a few steps
															 // (in case there are too many images to process),
															 // and expire in 5 seconds on the last step

		// Step 1: analyze article for image assets
		\set_transient($transientKey, ['status' => 'processing', 'step' => 1], $transientDuration);

		$content = file_get_contents($url);
    if (is_wp_error($content)) {
			\set_transient($transientKey, ['status' => 'error'], $transientDuration);
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
		$contentElement->removeAttribute('style');
		$content = $doc->saveHTML($contentElement);

    // find all images and gifs in article
    \preg_match_all('/<img [^>]*src="(http[^"]+)"[^>]*\/?>/', $content, $matches);
    /** \preg_match_all('/<img [^>]*src="(http[^"]+)"[^>]*(?:alt="([^"]*)")?[^>]*\/?>/', $content, $matches); */
		$images = (array)$matches[1];
		$images = array_unique($images);

		// Step 2: download those assets and move to media folder
		\set_transient($transientKey, ['status' => 'processing', 'step' => 2], $transientDuration);
		$path_components = explode('/', parse_url($url, PHP_URL_PATH));
		$articleID = end($path_components);
		foreach ($images as $img) {
			$url = WeChatArticleImportPage::saveImage($articleID, $img);
			if (is_wp_error( $url )) {
				\set_transient($transientKey, ['status' => 'error'], $transientDuration);
				exit;
			}
			$image_lookups["data-src=\"$img\""] = "src=\"$url\"";
		}

		// Step 3: process/replace image urls in article
		\set_transient($transientKey, ['status' => 'processing', 'step' => 3], $transientDuration);
		$content = str_replace(array_keys($image_lookups), array_values($image_lookups), $content);

		// Step 4: process/set thumbnail
		\set_transient($transientKey, ['status' => 'processing', 'step' => 4], $transientDuration);
		// insert and retrieve thumbnail id
		parse_str(parse_url($thumbnail, PHP_URL_QUERY), $query);
		$format = $query['wx_fmt'];
		$thumbnailId = media_sideload_image("$thumbnail.$format", 0, null, 'id');

		// Step 5: save to wordpress
		\set_transient($transientKey, ['status' => 'processing', 'step' => 5], $transientDuration);
		$postattr = [
			'post_content'  => $content,
			'post_title'    => $title,
			'post_excerpt'  => $description,
			'_thumbnail_id' => $thumbnailId,
			'post_name'     => $articleID, // post_name is post slug
			'meta_input'    => ['wechat_article_id' => $articleID],
		];
		$postID = wp_insert_post($postattr, true);
		if (is_wp_error($postID)) {
			\set_transient($transientKey, ['status' => 'error'], $transientDuration);
      exit;
		}

		// Done
		\set_transient($transientKey, ['status' => 'finished', 'postURL' => get_admin_url(null, "post.php?post=$postID&action=edit")], 5);
  }
}
