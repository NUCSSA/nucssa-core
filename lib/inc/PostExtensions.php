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

    // Add banner images to **all post types**
    register_post_meta( '', '_banner_image_wide',
      [
        'show_in_rest' => true, 'type' => 'number', 'single' => true,
        'auth_callback' => function () {
          return current_user_can('upload_files');
        }
      ]
    );
    register_post_meta( '', '_banner_image_narrow',
      [
        'show_in_rest' => true, 'type' => 'number', 'single' => true,
        'auth_callback' => function () {
          return current_user_can('upload_files');
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

    // Workshop info meta for club post type
    register_post_meta(
      'club', '_workshop_schedule',
      [
        'show_in_rest' => true,
        'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        }
      ]
    );

    // Metas for **coupon**
    register_post_meta(
      'coupon', 'amount',
      [
        'show_in_rest' => true,
        'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        }
      ]
    );
    register_post_meta(
      'coupon', 'terms',
      [
        'show_in_rest' => true,
        'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        }
      ]
    );
    register_post_meta(
      'coupon', 'phone',
      [
        'show_in_rest' => true,
        'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        }
      ]
    );
    register_post_meta(
      'coupon', 'address',
      [
        'show_in_rest' => true,
        'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        }
      ]
    );

  }
}
