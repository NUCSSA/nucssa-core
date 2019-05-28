<?php
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

// use function nucssa_core\utils\{console_log, file_log};
require_once 'lib/bootstrap/index.php';

// $jasonj = (new Accounts)->signin('jasonj', 'erqeaZg#0q5*&GNPmkFMdax&');
// $jilu = (new Accounts)->signin('jilu', 'Dg8D22C3fjJo');
// $wrong = (new Accounts)->signin('jilu', 'kkkk');
// var_dump($jasonj);
// var_dump($jilu);
// var_dump($wrong);