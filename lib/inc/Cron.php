<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc;

class Cron {
  const ten_min_cron_hook = 'nucssa-core-10-min-cron';
  const interval_name = '10-min';

  /**
   * @param Array $schedules Existing cron schedules/intervals
   * @return Array $schedules
   */
  public static function registerCronInterval($schedules){
    // Add 10min Cron Interval
    $schedules[self::interval_name] = array(
      'interval'    => 600,
      'display'     => __('Every Ten Minutes')
    );
    return $schedules;
  }


  public static function scheduleEvents(){
    if (!\wp_next_scheduled(self::ten_min_cron_hook)) {
      \wp_schedule_event(time(), self::interval_name, self::ten_min_cron_hook);
    }
  }

  public static function unscheduleCron(){
    $timestamp = \wp_next_scheduled(self::ten_min_cron_hook);
    \wp_unschedule_event($timestamp, self::ten_min_cron_hook);
  }
}
