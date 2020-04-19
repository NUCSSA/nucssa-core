<?php

/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */

namespace nucssa_core\admin_pages;

class UserProfileMods {
  public static function addOccupationField($user){
    $occupation = get_user_meta( $user->ID, 'nucssa_occupation', true );
    ?>
    <h3><i class="icon icon-nucssa" style="color: red;"></i> NUCSSA Specific <i class="icon icon-nucssa" style="color: red;"></i></h3>
    <table class="form-table">
      <tr>
        <th><label for="nucssa-occupation">职位</label></th>
        <td>
          <input type="text" name="nucssa_occupation" id="nucssa-occupation" value="<?php echo esc_attr( $occupation ); ?>" class="regular-text"/>
          <p class="description">将被用于展示在<a href="<?php echo get_author_posts_url($user->ID); ?>">Author Archive</a>页面。请如实填写。</p>
        </td>
      </tr>
    </table>
    <?php
  }

  public static function saveOccupationInfo($userID) {
    if (!current_user_can( 'edit_user', $userID )) return false;

    if (!empty($_POST['nucssa_occupation'])) {
      update_user_meta( $userID, 'nucssa_occupation', $_POST['nucssa_occupation']);
    }
  }

}
