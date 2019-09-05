<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 *
 * Modify user Roles and Capabilities
 */

namespace nucssa_core\inc\accounts;

class RoleMods {
  public static function init() {
    self::authorCaps();
  }

  /**
   * Give Author more capabilities
   */
  private static function authorCaps() {
    $author = get_role( 'author' );
    $author->remove_cap('publish_posts');
    $author->remove_cap('delete_published_posts');
    $author->add_cap('edit_pages');
    $author->add_cap('edit_others_pages');
    $author->add_cap('edit_published_pages');
    $author->add_cap('delete_pages');
  }
}
