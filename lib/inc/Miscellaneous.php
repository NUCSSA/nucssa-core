<?php
namespace nucssa_core\inc;

class Miscellaneous
{
  /**
   * Keep track of user views of each post and page.
   * @param WP_Post  $post The Post object (passed by reference).
   * @param WP_Query $query The current Query object (passed by reference).
   */
  public static function trackViews($post, $query) {
    if ($query->is_single || $query->is_page){
      $views = get_post_meta($post->ID, '_views', true);
      $views = empty($views) ? 0 : $views;
      update_post_meta($post->ID, '_views', $views + 1);
    }
  }
}
