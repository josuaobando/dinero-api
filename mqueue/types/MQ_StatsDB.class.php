<?php

/**
 * @author Josua
 */
class MQ_StatsDB extends MQ_Type
{

  /**
   * @see MQ_Type::process()
   */
  protected function process($data)
  {
    $username = $data['username'];
    $db = $data['db'];
    $host = $data['host'];

    $sql = $data['sql'];
    $connect = round($data['connect'], 3);
    $execute = round($data['execute'], 3);
    $error = $data['error'];

    $r = $this->tblMQueue->trackDBExecution($username, $db, $host, $sql, $connect, $execute, $error);

    return $r > 0;
  }

}

?>