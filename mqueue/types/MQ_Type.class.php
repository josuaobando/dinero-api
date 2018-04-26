<?php


abstract class MQ_Type
{

  /**
   * TblMQueue reference
   *
   * @var TblMQueue
   */
  protected $tblMQueue;

  public function __construct()
  {
    $this->tblMQueue = TblMQueue::getInstance();
  }

  /**
   * factory for message queue types
   *
   * @param string $type
   * @throws InvalidStateException
   *
   * @return MQ_Type
   */
  public static function getInstance($type)
  {
    $class = "MQ_$type";
    if(!class_exists($class)){
      throw new InvalidStateException("No class definition was found for this message queue type: $class");
    }

    $instance = new $class();
    return $instance;
  }

  /**
   * process the incoming message
   *
   * @param array $data
   *
   * @return bool
   */
  public function processMessage($data)
  {
    $r = $this->process($data);
    return $r;
  }

  /**
   * process the message in the specific implementation type
   *
   * @param array $data
   *
   * @return bool
   */
  protected abstract function process($data);
}

?>