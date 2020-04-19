<?php
use nucssa_core\inc\Cron;

register_activation_hook(NUCSSA_CORE_PLUGIN_FILE_PATH, ['nucssa_core\inc\Activation', 'init']); // can only call static method this way
register_deactivation_hook(NUCSSA_CORE_PLUGIN_FILE_PATH, ['nucssa_core\inc\Deactivation', 'init']);
add_action('admin_menu', ['nucssa_core\admin_pages\UserDirectoryConfigPage', 'init']);
add_action('admin_enqueue_scripts', ['nucssa_core\inc\AdminScripts', 'init']);
add_action('rest_api_init', function () {new nucssa_core\inc\rest\AdminRESTAPI();});
add_action('the_post', ['nucssa_core\inc\Miscellaneous', 'trackViews'], 10, 2);
add_action('init', ['nucssa_core\inc\PostExtensions', 'init']); // add post metas
add_action('init', ['nucssa_core\inc\CustomPostTypes', 'register']); // register new post types
add_filter('manage_edit-club_columns', ['nucssa_core\inc\CustomPostTypes', 'manageClubTableColumns']); // change club main column name to 社团名称
add_filter('manage_edit-coupon_columns', ['nucssa_core\inc\CustomPostTypes', 'manageCouponTableColumns']); // change coupon main column name to 赞助商家
add_action('show_user_profile', ['nucssa_core\admin_pages\UserProfileMods', 'addOccupationField']); // NUCSSA职位 - display - user editing own
add_action('edit_user_profile', ['nucssa_core\admin_pages\UserProfileMods', 'addOccupationField']); // NUCSSA职位 - display - admin editing others'
add_action('personal_options_update', ['nucssa_core\admin_pages\UserProfileMods', 'saveOccupationInfo']); // NUCSSA职位 - save - user editing own
add_action('edit_user_profile_update', ['nucssa_core\admin_pages\UserProfileMods', 'saveOccupationInfo']); // NUCSSA职位 - save - admin editing others'
add_action('admin_init', ['nucssa_core\inc\accounts\RoleMods', 'init']);

// Authenticate via LDAP
add_filter('authenticate', ['nucssa_core\inc\accounts\Accounts', 'login'], 0, 3);

// Gutenberg Features
add_action('enqueue_block_editor_assets', ['nucssa_core\inc\gutenberg\BlockEditorAssets', 'editorAssets']);
add_action('enqueue_block_assets', ['nucssa_core\inc\gutenberg\BlockEditorAssets', 'sharedAssets']);

// Cron Events
add_filter('cron_schedules', ['nucssa_core\inc\Cron', 'registerCronInterval']);
add_action('init', ['nucssa_core\inc\Cron', 'scheduleEvents']);
add_action(Cron::ten_min_cron_hook, ['nucssa_core\inc\accounts\Accounts', 'syncFromDirectory']);
