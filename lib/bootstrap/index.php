<?php
use nucssa_core\admin_dashboard\menu_page\TopLevelMenuPage;
use nucssa_core\inc\AdminScripts;
use nucssa_core\inc\rest\AdminRESTAPI;
use nucssa_core\inc\Cron;
use function nucssa_core\utils\debug\{console_log, file_log};
use nucssa_core\inc\accounts\DirectoryGroup;
use nucssa_core\inc\accounts\UserDirectory;
use nucssa_core\inc\accounts\DirectoryUser;

/**
 * Required
 */
// console_log('plugin file path: ' . NUCSSA_CORE_PLUGIN_FILE_PATH);
register_activation_hook(NUCSSA_CORE_PLUGIN_FILE_PATH, ['nucssa_core\inc\Activation', 'init']); // can only call static method this way
register_deactivation_hook(NUCSSA_CORE_PLUGIN_FILE_PATH, ['nucssa_core\inc\Deactivation', 'init']);
new TopLevelMenuPage();
new AdminScripts();
new AdminRESTAPI();
(new Cron())->addCronInterval();

add_filter('authenticate', 'nucssa_core\inc\accounts\Accounts::login', 0, 3);


/**
 * Debugging
 */
// file_log(UserDirectory::singleton()->getGroupMembers( 'Engineers'));
// console_log(DirectoryGroup::find(767)->users());
// console_log(DirectoryUser::find(2168)->groups());
// console_log(DirectoryUser::find(2168)->allRoles());
