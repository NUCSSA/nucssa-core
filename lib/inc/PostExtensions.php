<?php
namespace nucssa_core\inc;

class PostExtensions {
  public static function init() {
    self::registerPostMetas();
  }

  public static function registerPostMetas() {
    // Add views count to **all post types**
    register_post_meta(
      '', '_views',
      [
        'show_in_rest' => true, 'type' => 'number', 'single' => true,
        'auth_callback' => function() {
          return current_user_can('edit_posts');
        }
      ]
    );

    // Add featured post feature to **all post types**
    register_post_meta(
      '', '_nucssa_featured_post_priority',
      [
        'show_in_rest' => true, 'type' => 'number', 'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        }
      ]
    );

    // Add the ability to add page icon to **pages**
    register_post_meta(
      'page', '_page_icon',
      [
        'show_in_rest' => true,
        'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_pages');
        }
      ]
    );
  }
}
