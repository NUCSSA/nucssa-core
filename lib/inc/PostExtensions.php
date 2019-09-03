<?php
namespace nucssa_core\inc;

class PostExtensions {
  public static function init() {
    self::registerPostMetas();
    self::registerTaxonomies();
  }

  private static function registerTaxonomies() {
    /**
     * 专栏Taxonomies:
     *  - 东篱说
     *  - 狗粮
     *  - 摄影
     */
    $labels = [
      'name' => '专栏',
      'singular_name' => '专栏',
      'search_items' => '专栏搜索',
      'popular_items' => '常用专栏',
      'all_items' => '所有专栏',
      'edit_item' => '编辑专栏',
      'view_item' => '查看专栏',
      'update_item' => '更新专栏',
      'add_new_item' => '添加新专栏',
      'new_item_name' => '新专栏名称',
      'separate_items_with_commas' => '用逗号分隔专栏',
      'add_or_remove_items' => '添加/删除专栏',
      'choose_from_most_used' => '选择常用专栏',
      'not_found' => '没有此专栏',
      'no_terms' => '还没有添加专栏',
    ];
    $args = [
      'labels' => $labels,
      'description' => '专栏分支',
      'public' => true,
      'hierarchical' => false,
      'show_in_rest' => true,
      'show_admin_column' => true,
      'meta_box_cb' => false,
    ];
    register_taxonomy( 'column', ['post'], $args );
  }

  private static function registerPostMetas() {
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
      'coupon', 'style',
      [
        'show_in_rest' => true,
        'single' => true,
        'auth_callback' => function () {
          return current_user_can('edit_posts');
        }
      ]
    );
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
      'coupon', 'website',
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
