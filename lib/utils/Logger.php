<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Utils;

class Logger
{
  private static $_instance;

  private $logfilepath;
  public $errno = 0;
  public $content;

  const LOGGER_ERR_FILE_NOT_FOUND = -1;
  const LOGGER_ERR_FILE_NOT_READABLE = -2;
  const LOGGER_ERR_ERROR_READING_FILE = -3;

  public static function singleton()
  {
    if (!self::$_instance) {
      self::$_instance = new self();
    }

    return self::$_instance;
  }

  private function __construct()
  {
    $this->logfilepath = Constants::singleton()->plugin_dir_path . 'log.txt';
  }

  /**
   * Add a log message to the log file
   *
   * @param string|object $action
   * @param string|object $message
   */
  function log_action($action, $message = "")
  {
    $content = strftime("%F %T | " . print_r($action, true) . ": " . print_r($message, true) . "\n");
    file_put_contents($this->logfilepath, $content, FILE_APPEND);
  }

  function clear_log()
  {
    if (false === file_put_contents($this->logfilepath, ""))
      $this->log_action("log", "clear failed");
    else
      $this->log_action("log", "cleared.");
  }
}
