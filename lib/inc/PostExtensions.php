<?php
namespace nucssa_core\inc;

class PostExtensions {
  public static function init() {
    self::registerPostMetas();
  }

  public static function registerPostMetas() {
    register_post_meta(
      '', '_views',
      [
        'show_in_rest' => true, 'type' => 'number', 'single' => true,
        'auth_callback' => function() {
          return current_user_can('edit_posts');
        }
      ]
    );

    register_post_meta(
      '', '_nucssa_featured_post_priority',
      [
        'show_in_rest' => true, 'type' => 'number', 'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        }
      ]
    );
  }
}
