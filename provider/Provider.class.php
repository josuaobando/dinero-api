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
  protected $code = self::REQUEST_ERROR;

  /**
   * @var string
   */
  protected $id;

  /**
   * @var string
   */
  protected $status;

  /**
   * @var string
   */
  protected $message;

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
   * @return int|string
   */
  public function getApiCode()
  {
    return $this->code;
  }

  /**
   * @return string
   */
  public function getApiStatus()
  {
    return $this->status;
  }

  /**
   * @return string
   */
  public function getApiMessage()
  {
    return $this->status;
  }

  /**
   * @return string
   */
  public function getApiTransactionId()
  {
    return $this->id;
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
   * @param int $agencyId
   *
   * @throws InvalidStateException
   */
  public function __construct($providerId = 0, $agencyId = 0)
  {
    if(!$providerId && !$agencyId){
      throw new InvalidStateException("Missing Provider Id");
    }

    $this->tblSystem = TblSystem::getInstance();
    $settings = $this->tblSystem->getProvider($providerId, $agencyId);
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

  /**
   * execute request
   *
   * @param $method
   */
  protected function execute($method = null)
  {
    //do nothing
  }

  /**
   * unpack response
   *
   * @param $response
   */
  protected function unpack($response)
  {
    //do nothing
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
    throw new InvalidStateException("'".__METHOD__."' must be implemented in '".get_class($this)."' class.");
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
    throw new InvalidStateException("'".__METHOD__."' must be implemented in '".get_class($this)."' class.");
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
    throw new InvalidStateException("'".__METHOD__."' must be implemented in '".get_class($this)."' class.");
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
    throw new InvalidStateException("'".__METHOD__."' must be implemented in '".get_class($this)."' class.");
  }

  /**
   * @return bool
   */
  public function stickiness()
  {
    //get transaction object
    $transaction = Session::getTransaction();

    return $transaction->getTransactionId();
  }

}

?>