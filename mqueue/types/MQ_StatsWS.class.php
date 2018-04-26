<?php

/**
 * @author Josua
 */
class MQ_StatsWS extends MQ_Type
{

  /**
   * @see MQ_Type::process()
   */
  protected function process($data)
  {
    $request = $data['request'];
    $response = $data['response'];
    $namelookup = round($data['namelookup'], 3);
    $connect = round($data['connect'], 3);
    $execute = round($data['execute'], 3);
    $error = $data['error'];

    $r = $this->tblMQueue->trackWSExecution($request, $response, $namelookup, $connect, $execute, $error);

    return $r > 0;
  }

}

?>