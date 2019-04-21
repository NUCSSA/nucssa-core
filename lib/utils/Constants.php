<?php
namespace NUCSSACore\Utils;

class Constants
{
  private static $_instance = null;

  /**
   * List of all constants
   */
  public $plugin_dir_url;
  public $plugin_dir_path;


  public static function singleton()
  {
    if (! self::$_instance)
      self::$_instance = new self();
    return self::$_instance;
  }

  private function __construct()
  {
    $this->plugin_dir_url = \plugin_dir_url(dirname(dirname(__FILE__)));
    $this->plugin_dir_path = \plugin_dir_path(dirname(dirname(__FILE__)));
  }
}
