<?php

/**
 * @author Josua
 */
class Nexmo extends Provider
{

  /**
   * ID
   */
  const PROVIDER_ID = 2;

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
        $this->apiTransactionId = $response->{'message-id'};
        $this->apiCode = $messageInfo->status;
        $this->apiStatus = $messageInfo->status;
        $this->apiMessage = $messageInfo->{'error-text'};
      }else{
        $this->apiCode = self::REQUEST_ERROR;
        $this->apiMessage = 'At this time, we can not carry out. Please try again in a few minutes!';
        Log::custom(__CLASS__, "Invalid Object Response"."\n Request: \n\n".$this->getLastRequest()."\n Response: \n\n".Util::objToStr($response));
      }
    }catch(Exception $ex){
      $this->apiCode = self::REQUEST_ERROR;
      $this->apiMessage = 'At this time, we can not carry out. Please try again in a few minutes!';
      ExceptionManager::handleException($ex);
    }
  }

}

?>