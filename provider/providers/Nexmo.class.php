<?php

/**
 * @author Josua
 */
class Nexmo extends Provider
{
  const PROVIDER_ID = 6;
  /**
   * 0: success | Other: Live mobile lookup not returned
   */
  const REQUEST_SUCCESS = array('0', '43', '44', '45');

  /**
   * new Transaction instance
   */
  public function __construct()
  {
    parent::__construct(self::PROVIDER_ID);
  }

  /**
   * @param $message
   * @param $recipient
   *
   * @return bool
   */
  public function sendSMS($message, $recipient)
  {
    try{
      //credentials params
      $params = array();
      $params['to'] = $recipient;
      $params['text'] = $message;

      //execute request
      $this->request = $params;
      $this->execute();
      $response = $this->getResponse();

      //get response
      $this->response = $response;
      $this->unpack($response);

      return in_array($this->getApiStatus(), self::REQUEST_SUCCESS);
    }catch(APIException $ex){
      ExceptionManager::handleException($ex);
    }

    return false;
  }

  /**
   * execute request
   *
   * @see Provider::execute()
   *
   * @param $method
   */
  protected function execute($method = null)
  {
    try{
      //credentials params
      $params = array();
      $params['api_key'] = $this->getSetting(self::SETTING_KEY);
      $params['api_secret'] = $this->getSetting(self::SETTING_PASSWORD);
      $params['from'] = $this->getSetting(self::SETTING_USER);
      //request params
      $params = array_merge($params, $this->getRequest());

      //make ws request
      $url = $this->getSetting(self::SETTING_URL);
      $this->reader = new Reader_Json();
      $response = $this->execPost($url, $params);

      //get response
      $this->response = $response;
      $this->unpack($response);
    }catch(WSException $ex){
      ExceptionManager::handleException($ex);
    }
  }

  /**
   * @see Provider::unpack()
   *
   * @param $response
   */
  protected function unpack($response)
  {
    try{
      if($response && $response->{'message-count'} && $response->{'message-count'} == "1" && $response->messages && is_array($response->messages)){
        $messageInfo = $response->messages[0];
        $this->id = $response->{'message-id'};
        $this->code = $messageInfo->status;
        $this->status = $messageInfo->status;
        $this->message = $messageInfo->{'error-text'};
      }else{
        $this->code = self::REQUEST_ERROR;
        $this->message = 'At this time, we can not carry out. Please try again in a few minutes!';
        Log::custom(__CLASS__, "Invalid Object Response"."\n Request: \n\n".$this->getLastRequest()."\n Response: \n\n".Util::objToStr($response));
      }
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }
  }

}

?>