<?php


header("Connection: close");
header("Content-Length: 0");
header("Content-Encoding: none");
flush();

require_once 'system/Startup.class.php';

$mQueueType = $_REQUEST[MQueue::MQUEUE_TYPE_ID];

//prevent the script from halting if the user closes the connection.
ignore_user_abort(true);

try{
  $mQueue = MQ_Type::getInstance($mQueueType);
  $r = $mQueue->processMessage($_REQUEST);
  echo $r ? "ok" : "unknown error";
}catch(Exception $ex){
  ExceptionManager::handleException($ex);
  echo "error: " . $ex->getMessage();
}

?>