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
  protected $value;

  /**
   * TblTask reference
   *
   * @var TblTask
   */
  private $tblTask;

  /**
   * Task constructor.
   *
   * @param array $setting
   */
  public function __construct($setting)
  {
    $this->tblTask = TblTask::getInstance();
    $this->id = $setting['Task_Id'];
    $this->name = $setting['Task'];
    $this->interval = $setting['TaskInterval'];
    $this->intervalId = $setting['TaskInterval_Id'];
    $this->intervalType = $setting['TaskIntervalType'];
    $this->intervalTypeId = $setting['TaskIntervalType_Id'];
    $this->specific = $setting['Specific'];
    $this->value = $setting['Value'];
  }

  /**
   * @return bool
   */
  protected function init()
  {
    $datetime = date('Y-m-d H:i:s');

    switch($this->intervalId){
      case self::INTERVAL_TYPE_MINUTE:
        return true;
        break;
      case self::INTERVAL_TYPE_HOURLY:
        break;
      case self::INTERVAL_TYPE_DAILY:
        break;
      case self::INTERVAL_TYPE_WEEKLY:
        break;
      case self::INTERVAL_TYPE_MONTHLY:
        break;
    }

    return false;
  }

  protected function process()
  {
    throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

}


?>