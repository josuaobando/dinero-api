<?php

/**
 * Created by Josua
 * Date: 27/05/2018
 * Time: 20:51
 */
class Task
{

  const INTERVAL_TYPE_MINUTE = 1;
  const INTERVAL_TYPE_HOURLY = 2;
  const INTERVAL_TYPE_DAILY = 3;
  const INTERVAL_TYPE_WEEKLY = 4;
  const INTERVAL_TYPE_MONTHLY = 5;

  /**
   * @var int
   */
  protected $id;

  /**
   * @var string
   */
  protected $name;

  /**
   * @var string
   */
  protected $interval;

  /**
   * @var int
   */
  protected $intervalId;

  /**
   * @var string
   */
  protected $intervalType;

  /**
   * @var int
   */
  protected $intervalTypeId;

  /**
   * @var int
   */
  protected $specific;

  /**
   * @var int
   */
  protected $day;

  /**
   * @var int
   */
  protected $hour;

  /**
   * @var int
   */
  protected $minute;

  /**
   * TblTask reference
   *
   * @var TblTask
   */
  protected $tblTask;

  /**
   * Task constructor.
   */
  public function __construct()
  {
    $this->tblTask = TblTask::getInstance();
  }

  /**
   * @param $setting
   */
  public function init($setting)
  {
    $this->id = $setting['Task_Id'];
    $this->name = $setting['Task'];
    $this->interval = $setting['TaskInterval'];
    $this->intervalId = $setting['TaskInterval_Id'];
    $this->intervalType = $setting['TaskIntervalType'];
    $this->intervalTypeId = $setting['TaskIntervalType_Id'];
    $this->specific = $setting['Specific'];
    $this->day = $setting['Day'];
    $this->hour = $setting['Hour'];
    $this->minute = $setting['Minute'];
  }

  /**
   * check if the task have to run
   *
   * @return bool
   *
   * @throws InvalidStateException
   */
  public function check()
  {
    //we get the current day
    $currentDateTime = getdate(time());
    $currentMinutes = $currentDateTime['minutes'];
    $currentHour = $currentDateTime['hours'];

    Log::custom($this->name, "check...");

    switch($this->intervalTypeId){
      case self::INTERVAL_TYPE_MINUTE:

        Log::custom($this->name, "minute interval [$this->minute] current: $currentMinutes");
        if($this->specific){
          return $currentMinutes == $this->minute;
        }
        return $this->minute == 0 || ($currentMinutes % $this->minute) == 0;

      case self::INTERVAL_TYPE_HOURLY:

        Log::custom($this->name, "hour interval [$this->hour] current hour: $currentHour current: $currentMinutes");
        if($this->hour > 0){
          if($this->specific){
            return ($currentHour == $this->hour) && $currentMinutes == $currentMinutes;
          }
          return ($currentHour % $this->hour) == 0 && $currentMinutes == 0;
        }

        return $currentMinutes == 0;
      case self::INTERVAL_TYPE_DAILY:
        //return $this->intervalHour == $currentDateTime['hours'] && $this->intervalMinute == $currentDateTime['minutes'];

      case self::INTERVAL_TYPE_WEEKLY: //1: Monday, 7:Sunday
        //return $this->intervalDay == $currentDateTime['wday'] && $this->intervalHour == $currentDateTime['hours'] && $this->intervalMinute == $currentDateTime['minutes'];

      case self::INTERVAL_TYPE_MONTHLY:
        //return $this->intervalDay == $currentDateTime['mday'] && $this->intervalHour == $currentDateTime['hours'] && $this->intervalMinute == $currentDateTime['minutes'];

      default:
        //throw new InvalidStateException("Invalid interval: $this->intervalTypeId for report ID: $this->reportId");
    }

    return false;
  }

  public function process()
  {
    throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

}


?>