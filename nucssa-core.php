<?php
use NUCSSACore\Accounts\Accounts;
use NUCSSACore\Hooks\AdminScripts;
use NUCSSACore\Utils\Logger;
use NUCSSACore\REST\AdminRESTAPI;
use NUCSSACore\Admin\CronSchedules;

/**
 * Plugin Name:     NUCSSA Core Plugin
 * Plugin URI:      https://www.nucssa.org
 * Description:     Plugin to support NUCSSA core features
 * Author:          NUCSSA IT
 * Author URI:      https://www.nucssa.org/IT
 * Text Domain:     nucssa-core
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         NUCSSA_Core
 */

// prevent direct access
defined ( 'ABSPATH' ) || exit;

include_once __DIR__ . '/vendor/autoload.php';

// use NUCSSACore\Accounts\UserDirectory;

// $directory = UserDirectory::instance();
// $directory->getUserGroups('jilu');
// $directory->getGroupMembers('IT部门');
// NUCSSACore\Hooks\Activation::init();

/**
 * Required
 */
register_activation_hook(__FILE__, ['NUCSSACore\Hooks\Activation', 'init']); // can only call static method this way
register_deactivation_hook(__FILE__, ['NUCSSACore\Hooks\Deactivation', 'init']);
new NUCSSACore\Admin\MenuPage\TopLevelMenuPage();
new AdminScripts();
new AdminRESTAPI();
(new CronSchedules())->addCronInterval();
