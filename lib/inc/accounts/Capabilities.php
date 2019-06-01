<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc\accounts;
use function nucssa_core\utils\debug\{file_log, console_log};

class Capabilities {
  // private static

  /**
   * @param array $allcaps All the capabilities of the user
   * @param array $cap     [0] Required capability
   * @param array $args    [0] Requested capability
   *                       [1] User ID
   *                       [2] Associated object ID
   */
  public static function userHasCap($allcaps, $cap, $args){
    file_log('all caps', $allcaps);
    // file_log('required cap', $cap);
    // console_log($args['list_users'], 'user id');
    $ID = $args[1];
    $dirUser = DirectoryUser::findByUserID($ID);
    // console_log($dirUser, 'dirUser');
    if (!$dirUser){ // is a local user, return $allcaps for wordpress default behavior
      return $allcaps;
    } else { // is a NUCSSA directory user
      $roles = $dirUser->allRoles();

      if (in_array('administrator', $roles)){
        $allcaps['list_users'] = true;
        $allcaps['delete_users'] = true;
        $allcaps['create_users'] = true;
        $allcaps['promote_users'] = true;
        $allcaps['edit_pages'] = true;
        $allcaps['edit_others_pages'] = true;
        $allcaps['edit_published_pages'] = true;
        $allcaps['publish_pages'] = true;
        $allcaps['delete_pages'] = true;
        $allcaps['delete_others_pages'] = true;
        $allcaps['delete_published_pages'] = true;
        $allcaps['delete_private_pages'] = true;
        $allcaps['edit_private_pages'] = true;
        $allcaps['read_private_pages'] = true;
        $allcaps["edit_posts"] = true;
        $allcaps["edit_others_posts"] = true;
        $allcaps["edit_published_posts"] = true;
        $allcaps["publish_posts"] = true;
        $allcaps["delete_posts"] = true;
        $allcaps["delete_others_posts"] = true;
        $allcaps["delete_published_posts"] = true;
        $allcaps["delete_private_posts"] = true;
        $allcaps["edit_private_posts"] = true;
        $allcaps["read_private_posts"] = true;
        $allcaps["switch_themes"] = true;
        $allcaps["edit_themes"] = true;
        $allcaps["activate_plugins"] = true;
        $allcaps["edit_plugins"] = true;
        $allcaps["edit_files"] = true;
        $allcaps["manage_options"] = true;
        $allcaps["moderate_comments"] = true;
        $allcaps["manage_categories"] = true;
        $allcaps["manage_links"] = true;
        $allcaps["upload_files"] = true;
        $allcaps["import"] = true;
        $allcaps["edit_dashboard"] = true;
        $allcaps["update_plugins"] = true;
        $allcaps["delete_plugins"] = true;
        $allcaps["install_plugins"] = true;
        $allcaps["update_themes"] = true;
        $allcaps["install_themes"] = true;
        $allcaps["update_core"] = true;
        $allcaps["edit_theme_options"] = true;
        $allcaps["delete_themes"] = true;
        $allcaps["export"] = true;
        $allcaps["administrator"] = true;
        $allcaps["install_languages"] = true;
        $allcaps["resume_plugins"] = true;
        $allcaps["resume_themes"] = true;
      }
      return $allcaps;
    }
  }
}
