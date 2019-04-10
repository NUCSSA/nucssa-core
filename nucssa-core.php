<?php
use NUCSSACore\Accounts\Accounts;

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
NUCSSACore\Hooks\Install::init();
(new Accounts())->syncFromDirectory();
register_activation_hook(__FILE__, 'NUCSSACore\Hooks\Install::init');
