<?php

/**
 * @author Josua
 */
class WS
{

  /**
   * last content
   *
   * @var string
   */
  protected $lastContent = null;

  /**
   * last call stats
   *
   * @var array
   */
  protected $lastStats = null;

  /**
   * last request made
   *
   * @var string
   */
  protected $lastRequest = null;

  /**
   * Last error message reported
   * @var string
   */
  protected $lastError = null;

  /**
   * Last error code reported
   * @var integer
   */
  protected $lastErrorCode = 0;

  /**
   * the reader for the response
   *
   * @var Reader
   */
  protected $reader = null;

  /**
   * headers curl call
   */
  protected $headers = array();

  /**
   * username
   */
  protected $username = null;

  /**
   * password
   */
  protected $password = null;

  /**
   * put method curl
   */
  protected $usePut = null;

  /**
   * execution timeout
   */
  protected $timeout = null;

  /**
   * connect timeout
   */
  protected $timeoutOnConnect = null;

  /**
   * success execution
   */
  protected $success = true;

  /**
   * @param array $header
   */
  public function addHeader($header)
  {
    if(in_array($header, $this->headers)){
      return;
    }
    array_push($this->headers, $header);
  }

  /**
   * set the headers
   *
   * @param array $headers
   *
   */
  public function setHeaders($headers)
  {
    $this->headers = $headers;
  }

  /**
   * set the reader for the response
   *
   * @param Reader $reader
   */
  public function setReader($reader)
  {
    $this->reader = $reader;
  }

  /**
   * set user and password
   *
   * @param string $username
   * @param string $password
   */
  public function setUsernamePassword($username, $password)
  {
    $this->username = $username;
    $this->password = $password;
  }

  /**
   * Sets the Execution Timeout that will be sent to the request.
   *
   * @param integer $timeout
   */
  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;
  }

  /**
   * Sets Put Method to be used with curl.
   *
   * @param bool $usePut
   */
  public function setPut($usePut)
  {
    $this->usePut = $usePut;
  }

  /**
   * Sets the Connection Timeout.
   *
   * @param integer $timeoutOnConnect
   */
  public function setTimeoutOnConnect($timeoutOnConnect)
  {
    $this->timeoutOnConnect = $timeoutOnConnect;
  }

  /**
   * get the last content of the request
   *
   * @return string
   */
  public function getLastContent()
  {
    return $this->lastContent;
  }

  /**
   * get the last error reported
   *
   * @return string
   */
  public function getLastError()
  {
    return $this->lastError;
  }

  /**
   * get the last error code reported
   *
   * @return integer
   */
  public function getLastErrorCode()
  {
    return $this->lastErrorCode;
  }

  /**
   * get the stats of the last request made
   *
   * @return array
   */
  public function getLastStats()
  {
    return $this->lastStats;
  }

  /**
   * get the last request as a string
   *
   * @return string
   */
  public function getLastRequest()
  {
    return $this->lastRequest;
  }

  /**
   * set to the last request a pre-defined string
   *
   * @param string $request
   */
  public function setLastRequest($request)
  {
    $this->lastRequest = $request;
  }

  /**
   * get success/fail for last request
   *
   * @return bool
   */
  public function success()
  {
    return $this->success;
  }

  /**
   * execute a web service call sending the parameters using the post method
   *
   * @param string $webservice
   * @param array $params
   *
   * @throws WSException
   *
   * @return XmlElement
   */
  public function execPost($webservice, $params = null)
  {
    return $this->execWS($webservice, $params, null);
  }

  /**
   * execute a web service call sending the parameters using the post method
   *
   * @param string $webservice
   * @param string $method
   * @param array $params
   *
   * @throws WSException
   *
   * @return XmlElement
   */
  public function execPostMethod($webservice, $method, $params = null)
  {
    $webservice .= "/" . $method;

    return $this->execPost($webservice, $params);
  }

  /**
   * execute a web service call sending the parameters using the get method
   *
   * @param string $webservice
   * @param array $params
   *
   * @throws WSException
   *
   * @return XmlElement
   */
  public function execGet($webservice, $params = null)
  {
    return $this->execWS($webservice, null, $params);
  }

  /**
   * execute a soap call
   *
   * @param $wsdl
   * @param $method
   * @param $params
   * @param null $headers
   * @param null $options
   * @param null $setup
   *
   * @return object
   *
   * @throws WSException
   */
  public function execSoapCall($wsdl, $method, $params, $headers = null, $options = null, $setup = null)
  {
    $connector = new Connector();

    // Set values in the connector
    $connector->setUsernamePassword($this->username, $this->password);

    //connect timeout
    $timeoutOnConnect = (!is_null($this->timeoutOnConnect)) ? $this->timeoutOnConnect : CoreConfig::WS_TIMEOUT_ON_CONNECT;
    $connector->setTimeoutOnConnect($timeoutOnConnect);

    //execution timeout
    $timeout = (!is_null($this->timeout)) ? $this->timeout : CoreConfig::WS_TIMEOUT;
    $connector->setTimeout($timeout);

    $obj = $connector->execSoapCall($wsdl, $method, $params, $headers, $options, $setup);

    if($setup['keepPrevRequest']){
      $this->lastRequest .= "\n" . $connector->__toString();
    }else{
      $this->lastRequest = $connector->__toString();
    }
    $this->lastStats = $connector->getLastStats();

    if(defined('CoreConfig::TRACK_WS_STATS_ACTIVE') &&  //tracking for webservices is defined
      CoreConfig::TRACK_WS_STATS_ACTIVE &&             //tracking is enabled

      defined('CoreConfig::TRACK_WS_STATS_TIME') &&    //execution time to track defined
      CoreConfig::TRACK_WS_STATS_TIME <= $this->lastStats['total_time'] && //normal execution time exceed

      defined('CoreConfig::TRACK_WS_STATS_PATTERN') &&  //valid search pattern
      (is_null(CoreConfig::TRACK_WS_STATS_PATTERN) ||    //search pattern is set to match everything
        preg_match(CoreConfig::TRACK_WS_STATS_PATTERN, $wsdl) > 0 //webservice matches the pattern
      )
    ){
      $data = array();
      $data['request'] = $this->lastRequest;
      $data['response'] = Util::objToStr($obj);
      $data['namelookup'] = $this->lastStats['namelookup_time'];
      $data['connect'] = $this->lastStats['connect_time'];
      $data['execute'] = $this->lastStats['total_time'];
      $data['error'] = $connector->getLastError();
      $data['webservice'] = $wsdl;
      MQueue::push(MQueue::TYPE_STATS_WS, $data);
    }

    if(!$obj){
      $this->lastError = $connector->getLastError();
      $this->lastErrorCode = $connector->getLastErrorCode();

      throw new WSException($connector->__toString(), $data);
    }

    return $obj;
  }

  /**
   * this is the main function where the web service is called
   *
   * @param string $webservice
   * @param array $postParams
   * @param array $getParams
   *
   * @throws WSException
   *
   * @return mixed
   */
  private function execWS($webservice, $postParams, $getParams)
  {
    $connector = new Connector();
    if($postParams){
      $connector->setPostParams($postParams);
    }
    $connector->setUsernamePassword($this->username, $this->password);

    $connector->addHeaders($this->headers);

    //connect timeout
    $timeoutOnConnect = (!is_null($this->timeoutOnConnect)) ? $this->timeoutOnConnect : CoreConfig::WS_TIMEOUT_ON_CONNECT;
    $connector->setTimeoutOnConnect($timeoutOnConnect);

    //execution timeout
    $timeout = (!is_null($this->timeout)) ? $this->timeout : CoreConfig::WS_TIMEOUT;
    $connector->setTimeout($timeout);

    //set put method
    $connector->setPut($this->usePut);

    $this->lastContent = $connector->loadContent($webservice, $getParams);
    $this->lastRequest = $connector->__toString();
    $this->lastStats = $connector->getLastStats();

    if(defined('CoreConfig::TRACK_WS_STATS_ACTIVE') &&  //tracking for webservices is defined
      CoreConfig::TRACK_WS_STATS_ACTIVE &&             //tracking is enabled

      defined('CoreConfig::TRACK_WS_STATS_TIME') &&    //execution time to track defined
      CoreConfig::TRACK_WS_STATS_TIME <= $this->lastStats['total_time'] && //normal execution time exceed

      defined('CoreConfig::TRACK_WS_STATS_PATTERN') &&  //valid search pattern
      (is_null(CoreConfig::TRACK_WS_STATS_PATTERN) ||    //search pattern is set to match everything
        preg_match(CoreConfig::TRACK_WS_STATS_PATTERN, $webservice) > 0 //webservice matches the pattern
      )
    ){
      $data = array();
      $data['request'] = $this->lastRequest;
      $data['response'] = $this->lastContent;
      $data['namelookup'] = $this->lastStats['namelookup_time'];
      $data['connect'] = $this->lastStats['connect_time'];
      $data['execute'] = $this->lastStats['total_time'];
      $data['error'] = $connector->getLastError();
      $data['webservice'] = $webservice;
      MQueue::push(MQueue::TYPE_STATS_WS, $data);
    }

    $this->success = $connector->success();
    if(!$this->success){
      $this->lastError = $connector->getLastError();
      $this->lastErrorCode = $connector->getLastErrorCode();

      throw new WSException($connector->__toString(), $data);
    }

    if($this->reader){
      return $this->reader->parse($this->lastContent);
    }

    $defaultReader = new Reader_XML();

    return $defaultReader->parse($this->lastContent);
  }

}

?>