<?php

/**
 * @author Josua
 */
class MQ_Webservice extends MQ_Type
{

  /**
   * @see MQ_Type::process()
   */
  protected function process($data)
  {
    $uniqueId = $data['uniqueId'];
    $type = $data['type'];

    $credentialsData = $data['credentials'];
    $requestData = $data['request'];

    $responseData = $data['response'];
    $responseTime = round($data['responseTime'], 3);

    if($credentialsData){
      /**
       * @var array
       */
      $credentials = Encrypt::unpack($credentialsData);
    }
    if($requestData){
      /**
       * @var array
       */
      $request = Encrypt::unpack($requestData);
    }
    if($responseData){
      /**
       * @var string
       */
      $response = Encrypt::unpack($responseData);
    }

    if($type == 'request'){
      $r = $this->tblMQueue->trackWSRequest($uniqueId, Util::implodeAssoc($credentials), Util::implodeAssoc($request));
    }else if($type == 'response'){
      $r = $this->tblMQueue->trackWSRequestUpdate($uniqueId, $response, $responseTime);
      if(!$r){
        //wait 2 secs and try again.
        sleep(2);
        $r = $this->tblMQueue->trackWSRequestUpdate($uniqueId, $response, $responseTime);
      }
    }

    return $r > 0;
  }

}

?>