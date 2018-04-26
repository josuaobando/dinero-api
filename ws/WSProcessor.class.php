<?php

/**
 * @author Josua
 */
class WSProcessor
{

  /**
   * this is how the web service setup function should be called
   * in order to get a callback in order to initialize variables
   * or objects that will be used across the web services
   *
   * @var string
   */
  const SETUP_FUNCTION = 'wsSetup';

  /**
   * this is the name of the parameter in order to identify
   * which function needs to be executed
   *
   * @var string
   */
  const REQUESTED_FUNCTION = 'f';

  /**
   * parameter's identifier for the needed format of the response
   * [json | xml]
   *
   * @var string
   */
  const REQUESTED_FORMAT = 'format';

  /**
   * parameter's identifier for the needed encoding of the response
   * [ ISO-8859-1 | UTF-8 ]
   *
   * @var string
   */
  const REQUESTED_ENCODING = 'encoding';

  /**
   * parameter to request add the callback function for json response format
   *
   * @var string
   */
  const JSON_CALLBACK = 'jsoncallback';

  /**
   * it prepares the wsRequest
   * $wsRequest is sent to the main web service scope to be prepared
   * this method is not required
   *
   * @param WSRequest $wsRequest
   */
  private static function wsSetup($wsRequest)
  {
    //optional method implemented in the webservice
    if(function_exists(self::SETUP_FUNCTION)){
      call_user_func(self::SETUP_FUNCTION, $wsRequest);
    }
  }

  /**
   * execute the method that is being requested
   *
   * @param WSRequest $wsRequest
   *
   * @throws InvalidStateException
   *
   * @return $wsResponse
   */
  private static function wsExecute($wsRequest)
  {
    $f = $wsRequest->getParam(self::REQUESTED_FUNCTION);

    //call the proper function
    if(function_exists($f)){
      $wsResponse = call_user_func($f, $wsRequest);
    }else{
      throw new InvalidStateException("No method definition was found for this request: '$f'");
    }

    return $wsResponse;
  }

  /**
   * add a message in the queue to log the request
   *
   * @param string $uniqueId
   * @param array $credentials
   * @param WSRequest $wsRequest
   */
  private static function logRequest($uniqueId, $credentials, $wsRequest)
  {
    $data = array();
    $data['uniqueId'] = $uniqueId;
    $data['type'] = 'request';
    $data['credentials'] = Encrypt::pack($credentials);
    $data['request'] = Encrypt::pack($wsRequest->getParams());
    MQueue::push(MQueue::TYPE_WEBSERVICE, $data);
  }

  /**
   * add a message in the queue to log the response
   *
   * @param string $uniqueId
   * @param array $response
   * @param float $responseTime
   */
  private static function logResponse($uniqueId, $response, $responseTime)
  {
    $data = array();
    $data['uniqueId'] = $uniqueId;
    $data['type'] = 'response';
    $data['response'] = Encrypt::pack($response);
    $data['responseTime'] = $responseTime;
    MQueue::push(MQueue::TYPE_WEBSERVICE, $data);
  }

  /**
   * this method processes the request and sends the response to the requester
   */
  public static function process()
  {
    //get the current time for stats
    $startTime = Util::getStartTime();
    $uniqueId = Encrypt::genKey();
    try{
      //check if the request was sent using json
      $wsRequest = new WSRequest(json_decode(file_get_contents("php://input"), true));
      if($wsRequest->isEmpty()){
        //if empty try with the regular request
        $wsRequest->overwriteRequest($_REQUEST);
      }

      //check if the access is valid
      $credentials = WSAccess::getCredentials($wsRequest);

      self::logRequest($uniqueId, $credentials, $wsRequest);

      WSAccess::checkCredentials($credentials);

      //setup the web service before process the request
      self::wsSetup($wsRequest);

      //execute the method that is being requested
      $wsResponse = self::wsExecute($wsRequest);
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
      $wsResponse = new WSResponseError($ex->getMessage());
    }

    $format = $wsRequest->getParam(self::REQUESTED_FORMAT, WSResponse::DEFAULT_FORMAT);
    $encoding = $wsRequest->getParam(self::REQUESTED_ENCODING, CoreConfig::SYS_ENCODING);

    //set the header of the response
    Util::putResponseHeaders($format, $encoding);

    $wsResponse->setJSONCallback($wsRequest->getParam(self::JSON_CALLBACK, null));

    //send the object to the output converting it to string
    $response = $wsResponse->toString($format);

    //calculate the response time and log the request using the message queue
    $endTime = Util::calculateProcessTime($startTime);
    self::logResponse($uniqueId, $response, $endTime);

    echo $response;
  }

}

?>