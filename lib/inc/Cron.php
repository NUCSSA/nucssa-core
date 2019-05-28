<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc;

use nucssa_core\inc\accounts\Accounts;

class Cron {
  private $cron_hook = 'nucssa-core-cron-hook';
  private $interval_name_10m = '10-min';

  public function __construct(){}

  /**
   * @param Array $schedules Existing cron jobs
   * @return Array $schedules
   */
  public function addCronInterval()
  {
    // Add 10min Cron Interval
    add_filter('cron_schedules', function ($schedules) {

      $schedules[$this->interval_name_10m] = array(
        'interval'    => 600,
        'display'     => __('Every Ten Minutes')
      );
      return $schedules;
    });

  }


  public function scheduleCron(){
    add_action($this->cron_hook, function () {
      $this->tenMinuteCronTasks();
    });
    if (!\wp_next_scheduled($this->cron_hook)) {
      \wp_schedule_event(time(), $this->interval_name_10m, $this->cron_hook);
    }
  }

  public function unscheduleCron(){
    $timestamp = \wp_next_scheduled($this->cron_hook);
    \wp_unschedule_event($timestamp, $this->cron_hook);
  }

  private function tenMinuteCronTasks(){
    Accounts::syncFromDirectory();
  }
}