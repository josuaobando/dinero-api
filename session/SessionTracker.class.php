<?php

/**
 * @author Josua
 */
class SessionTracker
{

  /**
   * @var string
   */
  private $token = '';

  /**
   * @var int
   */
  private $sessionTrackerId = 0;

  /**
   * @var string
   */
  private $platform = '';

  /**
   * @var string
   */
  private $ip = '';

  /**
   * @var string
   */
  private $country = '';

  /**
   * @var string
   */
  private $countryState = '';


  /**
   * TblSession reference
   *
   * @var TblSession
   */
  private $tblSession;

  /**
   * SessionTracker constructor.
   *
   * @param null $token
   * @param bool $init
   */
  public function __construct($token = null, $init = false)
  {
    $this->token = $token;
    $this->tblSession = TblSession::getInstance();
    if($token && $init){
      $session = $this->tblSession->get($token);
      if($session && count($session) > 0){
        $this->ip = $session['RemoteAddr'];
        $this->platform = $session['Platform'];
        $this->country = $session['Country'];
        $this->countryState = $session['CountryState'];
        $this->sessionTrackerId = $session['SessionTracker_Id'];
      }
    }
  }

  public function active(){
    return ($this->sessionTrackerId > 0);
  }

  /**
   * @param WSRequest $wsRequest
   * @param string $activity
   */
  public function add($wsRequest, $activity)
  {
    $host = $_SERVER['HTTP_HOST'];
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $protocol = $_SERVER['SERVER_PROTOCOL'];

    $state = $wsRequest->getParam('state');
    $country = $wsRequest->getParam('country');
    $username = $wsRequest->getParam('username');

    $referrer = $wsRequest->getParam('referrer', $_SERVER['HTTP_REFERER']);
    $platform = $wsRequest->getParam('platform', '');
    $remoteAddr = $wsRequest->getParam('remoteIP');

    if(empty($remoteAddr)){
      if($_SERVER['REMOTE_ADDR'] == '127.0.0.1'){
        $remoteAddr = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
      }else{
        $remoteAddr = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] . "," : "") . $_SERVER['REMOTE_ADDR'];
      }
    }

    $this->sessionTrackerId = $this->tblSession->add($host, $referrer, $remoteAddr, $protocol, $agent, $platform, $country, $state, $username, $this->token, $activity);
  }

  /**
   * @return int
   */
  public function update()
  {
    return $this->tblSession->update($this->token);
  }

  /**
   * @return int
   */
  public function close()
  {
    return $this->tblSession->close($this->token);
  }

  /**
   * @param $username
   *
   * @return bool
   */
  public function activeByUsername($username)
  {
    $tblSession = TblSession::getInstance();
    $session = $tblSession->check($username);
    return ($session && count($session) > 0);
  }

}

?>