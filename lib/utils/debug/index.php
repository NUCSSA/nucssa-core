<?php
namespace nucssa_core\utils\debug;

/**
 * Utility - log info to console (browser)
 *
 * @param Any $data, any serializable data type
 * @param string $label
 *
 * Usage:
 * console_log($router->getRoutes(), "routes");
 */
function console_log($data, string $label = ''){
  if (!WP_DEBUG) return;
  $json_data = json_encode($data);
  echo <<<CONSOLE
<script>
  console.log( '$label', $json_data );
</script>
CONSOLE;
}

/**
 * Add a log message to the log file (log.txt under project root dir)
 *
 * @param string|object $action
 * @param string|object $message
 */

function file_log($action, $message = ''){
  if (!WP_DEBUG) return;
  Logger::singleton()->log_action($action, $message);
}