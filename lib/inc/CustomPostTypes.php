<?php
namespace nucssa_core\inc;

class CustomPostTypes {
  public static function register() {
    self::registerClubPostType();
  }

  // 学生社团 post type
  private static function registerClubPostType() {
    register_post_type( 'club', [
      'label' => '学生社团',
      'description' => 'NUCSSA旗下学生社团',
      'public' => true,
      'show_in_rest' => true,
      // 'rest_controller_class'
      // 'menu_icon' => 'none', // there is a bug in wp core that <img /> still shows when `menu_icon` is set to `'none'`
      'supports' => ['title', 'editor', 'revisions', 'trackbacks', 'custom-fields', 'author'],
      'delete_with_user' => false,
    ] );

    // Define Content Template
    $club_object = get_post_type_object( 'club' );
    $club_object->template = [
      ['nucssa/club-info'],
    ];

    // Change Title Name to 社团名称
    add_filter('enter_title_here', function($title, $post) {
      if ( 'club' == $post->post_type) {
        $title = '社团名字';
      }
      return $title;
    }, 10, 2);
  }

  public static function manageClubTableColumns($cols) {
    $cols['title'] = '社团名称';
    return $cols;
  }

}