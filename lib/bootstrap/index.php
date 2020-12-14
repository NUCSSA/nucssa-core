<?php

use nucssa_core\inc\Cron;
use nucssa_core\inc\Activation;
use nucssa_core\inc\AdminScripts;
use nucssa_core\inc\Deactivation;
use nucssa_core\inc\Miscellaneous;
use nucssa_core\inc\PostExtensions;
use nucssa_core\inc\CustomPostTypes;
use nucssa_core\inc\accounts\Accounts;
use nucssa_core\inc\accounts\RoleMods;
use nucssa_core\inc\rest\AdminRESTAPI;
use nucssa_core\inc\AdminPages\UserProfileMods;
use nucssa_core\inc\gutenberg\BlockEditorAssets;
use nucssa_core\inc\ProcessWeChatArticleRequest;
use nucssa_core\inc\AdminPages\UserDirectoryConfigPage;
use nucssa_core\inc\AdminPages\WeChatArticleImportPage;

register_activation_hook(NUCSSA_CORE_PLUGIN_FILE_PATH, [Activation::class, 'init']); // can only call static method this way
register_deactivation_hook(NUCSSA_CORE_PLUGIN_FILE_PATH, [Deactivation::class, 'init']);
add_action('admin_menu', [UserDirectoryConfigPage::class, 'init']);
add_action('admin_menu', [WeChatArticleImportPage::class, 'init']);
add_action('delete_post_meta', [WeChatArticleImportPage::class, 'cleanupOnDeletingPost'], 10, 4);
add_action('admin_enqueue_scripts', [AdminScripts::class, 'init']);
add_action('rest_api_init', function () {new AdminRESTAPI();});
add_action('the_post', [Miscellaneous::class, 'trackViews'], 10, 2);
add_action('init', [PostExtensions::class, 'init']); // add post metas
add_action('init', [CustomPostTypes::class, 'register']); // register new post types
// sets up actions for wechat article import async process
// there might be better async packages that doesn't require pre-setup, but we stick with it for now.
add_action('init', function() {WeChatArticleImportPage::$asyncRequest = new ProcessWeChatArticleRequest();});
add_filter('manage_edit-club_columns', [CustomPostTypes::class, 'manageClubTableColumns']); // change club main column name to 社团名称
add_filter('manage_edit-coupon_columns', [CustomPostTypes::class, 'manageCouponTableColumns']); // change coupon main column name to 赞助商家
add_action('show_user_profile', [UserProfileMods::class, 'addOccupationField']); // NUCSSA职位 - display - user editing own
add_action('edit_user_profile', [UserProfileMods::class, 'addOccupationField']); // NUCSSA职位 - display - admin editing others'
add_action('personal_options_update', [UserProfileMods::class, 'saveOccupationInfo']); // NUCSSA职位 - save - user editing own
add_action('edit_user_profile_update', [UserProfileMods::class, 'saveOccupationInfo']); // NUCSSA职位 - save - admin editing others'
add_action('admin_init', [RoleMods::class, 'init']);

// Authenticate via LDAP
add_filter('authenticate', [Accounts::class, 'login'], 0, 3);

// Gutenberg Features
add_action('enqueue_block_editor_assets', [BlockEditorAssets::class, 'editorAssets']);
add_action('enqueue_block_assets', [BlockEditorAssets::class, 'sharedAssets']);

// Cron Events
add_filter('cron_schedules', [Cron::class, 'registerCronInterval']);
add_action('init', [Cron::class, 'scheduleEvents']);
add_action(Cron::ten_min_cron_hook, [Accounts::class, 'syncFromDirectory']);
