<?php

namespace nucssa_core\inc\gutenberg;

class BlockEditorAssets {
  public static function editorAssets() {
    self::editorScript();
    self::editorStyle();

  }

  public static function sharedAssets() {
    self::sharedStyle();

  }

  private static function sharedStyle() {
    $version = WP_DEBUG ? time() : false;
    wp_enqueue_style(
      'nucssa_core_block_shared_style',
      NUCSSA_CORE_DIR_URL . '/public/css/style.css',
      [],
      $version
    );
  }

  private static function editorScript() {
    $version = WP_DEBUG ? time() : false;
    wp_enqueue_script(
      'nucssa_core_block_assets_script',
      NUCSSA_CORE_DIR_URL.'/public/js/editor.js',
      ['wp-element', 'wp-plugins', 'wp-edit-post', 'wp-data', 'wp-compose', 'wp-components', 'wp-blocks'], // deps
      $version,
      true // in_footer?
    );
  }

  private static function editorStyle() {
    $version = WP_DEBUG ? time() : false;
    wp_enqueue_style(
      'nucssa_core_block_assets_style',
      NUCSSA_CORE_DIR_URL.'/public/css/editor.css',
      [],
      $version
    );
  }
}
