<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Hooks;

use NUCSSACore\Admin\CronSchedules;

class Deactivation {
  public static function init(){

    // Remove Cron Tasks
    self::removeCronTasks();
  }

  private static function removeCronTasks(){
    (new CronSchedules())->unscheduleCron();
  }
}
