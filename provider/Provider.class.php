<?php

/**
 * @author Josua
 */
class Provider extends WS
{

  const SETTING_URL = 'URL';
  const SETTING_USER = 'USER';
  const SETTING_PASSWORD = 'PASSWORD';
  const SETTING_KEY = 'KEY';
  const REQUEST_ERROR = 'error';

  /**
   * @var string|int
   */
  protected $apiCode = self::REQUEST_ERROR;

  /**
   * @var string
   */
  protected $apiTransactionId;

  /**
   * @var string
   */
  protected $apiStatus;

  /**
   * @var string
   */
  protected $apiMessage;

  /**
   * @var array
   */
  protected $settings;

  /**
   * @var array
   */
  protected $request = array();

  /**
   * @var array
   */
  protected $response;

  /**
   * @var TblSystem
   */
  protected $tblSystem;

  /**
   * the response code of the request (ex.: fail, error)
   *
   * @return int|string
   */
  public function getApiCode()
  {
    return $this->apiCode;
  }

  /**
   * status of the request (ex.: approved, rejected, 0, 1)
   *
   * @return string
   */
  public function getApiStatus()
  {
    return $this->apiStatus;
  }

  /**
   * message of the request (ex.: transaction completed)
   *
   * @return string
   */
  public function getApiMessage()
  {
    return $this->apiMessage;
  }

  /**
   * @return string
   */
  public function getApiTransactionId()
  {
    return $this->apiTransactionId;
  }

  /**
   * @param $code
   *
   * @return string
   *
   * @throws InvalidStateException
   */
  protected function getSetting($code)
  {
    $setting = $this->settings[$code];
    if(!$setting){
      throw new InvalidStateException("Missing setting: $code");
    }

    return $setting;
  }

  /**
   * @return array
   */
  protected function getRequest()
  {
    return $this->request;
  }

  /**
   * @return array|stdClass
   */
  protected function getResponse()
  {
    return $this->response;
  }

  /**
   * Provider constructor.
   *
   * @param int $providerId
   *
   * @throws InvalidStateException
   */
  public function __construct($providerId = 0)
  {
    $this->tblSystem = TblSystem::getInstance();
    if($providerId){
      $settings = $this->tblSystem->getProvider($providerId);
      if(!count($settings)){
        throw new InvalidStateException("Missing Provider Setting");
      }else{
        foreach($settings as $setting){
          $code = $setting['Code'];
          $value = $setting['Value'];
          $this->settings[$code] = $value;
        }
      }
    }
  }

  /**
   * execute request
   *
   * @param $method
   *
   * @throws InvalidStateException
   */
  protected function execute($method = null)
  {
    throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

  /**
   * unpack response
   *
   * @param $response
   *
   * @throws InvalidStateException
   */
  protected function unpack($response)
  {
    throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

  /**
   * get name for the customer
   *
   * @return Person
   *
   * @throws InvalidStateException
   */
  public function receiver()
  {
    throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

  /**
   * get name for the customer
   *
   * @return Person
   *
   * @throws InvalidStateException
   */
  public function sender()
  {
    throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

  /**
   * Submit or Re-Submit transaction
   *
   * @return bool
   *
   * @throws InvalidStateException
   */
  public function confirm()
  {
    throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

  /**
   * get transaction status
   *
   * @return bool
   *
   * @throws InvalidStateException
   */
  public function status()
  {
    return true;
    //throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

  /**
   * @return bool
   */
  public function stickiness()
  {
    return true;
  }

}

?>