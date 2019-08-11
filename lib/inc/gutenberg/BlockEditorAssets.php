<?php

namespace nucssa_core\inc\gutenberg;

class BlockEditorAssets {
  public static function init() {
    self::editorScript();
    self::editorStyle();
  }

  private static function editorScript() {
    wp_enqueue_script(
      'nucssa_core_block_assets_script',
      NUCSSA_CORE_DIR_URL.'/public/js/editor.js',
      ['wp-element', 'wp-plugins', 'wp-edit-post', 'wp-data', 'wp-compose', 'wp-components']
    );
  }

  private static function editorStyle() {
    wp_enqueue_style(
      'nucssa_core_block_assets_style',
      NUCSSA_CORE_DIR_URL.'/public/css/editor.css'
    );
  }
}
