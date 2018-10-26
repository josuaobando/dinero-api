<?php

/**
 * @author Josua
 */
class MQueue
{

  const MQUEUE_TYPE_ID = "mQueueTypeId";

  /**
   * different type for messages in the queue
   */
  const TYPE_STATS_DB = "StatsDB";
  const TYPE_STATS_WS = "StatsWS";
  const TYPE_EXCEPTION = "Exception";
  const TYPE_WEBSERVICE = "Webservice";

  /**
   * send a new message queue request
   *
   * @param string $mQueueType
   * @param array $data
   *
   * @return bool
   */
  public static function push($mQueueType, $data)
  {
    if(defined("CoreConfig::MESSAGE_QUEUE_ENABLE") && !CoreConfig::MESSAGE_QUEUE_ENABLE){
      return false;
    }

    if($mQueueType == MQueue::TYPE_WEBSERVICE && defined("CoreConfig::MESSAGE_QUEUE_WS_REQUEST_ENABLE") && !CoreConfig::MESSAGE_QUEUE_WS_REQUEST_ENABLE){
      return false;
    }

    $connector = new Connector();
    $data[MQueue::MQUEUE_TYPE_ID] = $mQueueType;

    //prevent script hanging and delaying the main process
    $connector->setTimeout(3);
    $connector->setPostParams($data);
    $connector->loadContent(CoreConfig::MESSAGE_QUEUE_URL);

    return true;
  }
}

?>