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
    $fpath = NUCSSA_CORE_DIR_PATH . '/public/css/style.css';
    $furl = NUCSSA_CORE_DIR_URL . '/public/css/style.css';
    $version = filemtime($fpath);
    wp_enqueue_style(
      'nucssa_core_block_shared_style',
      $furl,
      [],
      $version
    );
  }

  private static function editorScript() {
    $fpath = NUCSSA_CORE_DIR_PATH . '/public/js/editor.js';
    $furl = NUCSSA_CORE_DIR_URL . '/public/js/editor.js';
    $version = filemtime($fpath);
    wp_enqueue_script(
      'nucssa_core_block_assets_script',
      $furl,
      ['wp-element', 'wp-plugins', 'wp-edit-post', 'wp-data', 'wp-compose', 'wp-components', 'wp-blocks'], // deps
      $version,
      true // in_footer?
    );
  }

  private static function editorStyle() {
    $fpath = NUCSSA_CORE_DIR_PATH . '/public/css/editor.css';
    $furl = NUCSSA_CORE_DIR_URL . '/public/css/editor.css';
    $version = filemtime($fpath);
    wp_enqueue_style(
      'nucssa_core_block_assets_style',
      $furl,
      [],
      $version
    );
  }
}
