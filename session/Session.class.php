<?php

/**
 * Class Session
 */
class Session
{

  const SID_ACCOUNT = 'account';
  const SID_COUNTRIES = 'countries';
  const SID_AGENCIES = 'agencies';
  const SID_TRANSACTION = 'transaction';
  const SID_CUSTOMER = 'customer';
  const SID_PERSON = 'person';
  const SID_STICKINESS = 'stickiness';

  /**
   * get sid generated
   *
   * @var null
   */
  public static $sid = null;

  /**
   * start a new session with a new SessionId
   *
   * @param string $sessionId
   *
   * @return string
   */
  public static function startSession($sessionId = null)
  {
    if(!$sessionId){
      $sessionId = Encrypt::genKey();
    }

    self::$sid = $sessionId;
    session_id($sessionId);
    session_start();

    return $sessionId;
  }

  /**
   * retrieve object from user session
   *
   * @param string $id
   *
   * @return mixed
   */
  public static function getSessionObject($id)
  {
    //check if the session was started
    if(!session_id()){
      return null;
    }

    return $_SESSION[$id];
  }

  /**
   * store an object in the user session
   *
   * @param string $id
   * @param mixed $obj
   * @param bool $startSession
   *
   * @return bool
   */
  public static function storeSessionObject($id, $obj, $startSession = false)
  {
    if(!session_id() && $startSession){
      self::startSession();
    }

    //check if the session was started
    if(!session_id()){
      return false;
    }
    $_SESSION[$id] = $obj;

    return true;
  }

  /**
   * get account from session
   *
   * @param null $username
   * @param null $accountId
   *
   * @return Account
   * @throws SessionException
   */
  public static function getAccount($username = null, $accountId = null)
  {
    $accountSession = self::getSessionObject(self::SID_ACCOUNT);
    if($accountSession && $accountSession instanceof Account){
      $account = $accountSession;
    }elseif($username || $accountId){
      $account = new Account($username, $accountId);
      self::storeSessionObject(self::SID_ACCOUNT, $account, true);
    }else{
      throw new SessionException("Session has expired");
    }

    return $account;
  }

  /**
   * get countries
   *
   * @return array
   */
  public static function getCountries()
  {
    $countriesSession = self::getSessionObject(self::SID_COUNTRIES);
    if(!$countriesSession){
      $tblCountry = TblCountry::getInstance();
      $countries = $tblCountry->getCountries();
      self::storeSessionObject(self::SID_COUNTRIES, $countries, true);
    }else{
      $countries = $countriesSession;
    }

    return $countries;
  }

  /**
   * get agencies
   *
   * @return array
   */
  public static function getAgencies()
  {
    $agenciesSession = self::getSessionObject(self::SID_AGENCIES);
    if(!$agenciesSession){
      $tblSystem = TblSystem::getInstance();
      $agencies = $tblSystem->getAgencies();
      self::storeSessionObject(self::SID_AGENCIES, $agencies, true);
    }else{
      $agencies = $agenciesSession;
    }

    return $agencies;
  }

  /**
   * get transaction
   *
   * @param bool $crate
   *
   * @return mixed|Transaction
   */
  public static function getTransaction($crate = false)
  {
    $transactionSession = self::getSessionObject(self::SID_TRANSACTION);
    if(!$transactionSession || $crate){
      $transaction = new Transaction();
      self::storeSessionObject(self::SID_TRANSACTION, $transaction, true);
    }else{
      $transaction = $transactionSession;
    }

    return $transaction;
  }

  /**
   * get customer
   *
   * @param null $customerId
   *
   * @return Customer
   */
  public static function getCustomer($customerId = null)
  {
    $customerSession = self::getSessionObject(self::SID_CUSTOMER);
    if(!$customerSession){
      $customer = new Customer($customerId);
      self::storeSessionObject(self::SID_CUSTOMER, $customer, true);
    }else{
      $customer = $customerSession;
    }

    return $customer;
  }

  /**
   * get person
   *
   * @param null $personId
   *
   * @return Person
   */
  public static function getPerson($personId = null)
  {
    $personSession = self::getSessionObject(self::SID_PERSON);
    if(!$personSession){
      $person = new Person($personId);
      self::storeSessionObject(self::SID_PERSON, $person, true);
    }else{
      $person = $personSession;
    }

    return $person;
  }

  /**
   * get person
   *
   * @param bool $crate
   *
   * @return Stickiness
   */
  public static function getStickiness($crate = false)
  {
    $stickinessSession = self::getSessionObject(self::SID_STICKINESS);
    if($crate){
      $stickiness = new Stickiness();
      self::storeSessionObject(self::SID_STICKINESS, $stickiness, true);
    }else{
      $stickiness = $stickinessSession;
    }

    return $stickiness;
  }

  /**
   * set person
   *
   * @param Person $person
   *
   * @return Person
   */
  public static function setPerson($person)
  {
    self::storeSessionObject(self::SID_PERSON, $person, true);
    $personSession = self::getSessionObject(self::SID_PERSON);
    return $personSession;
  }

}

?>