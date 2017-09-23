<?php

/**
 * @author Josua
 */
class Stickiness
{

  /**
   * Action was successfully executed with not errors.
   */
  const STATUS_CODE_SUCCESS = '1';
  /**
   * There was an issue with the authentication process or there was an invalid state in the execution.
   */
  const STATUS_CODE_FAILED = '2';
  /**
   * Already linked to a one of your receivers
   */
  const STATUS_CODE_LINKED = '3';
  /**
   * Already linked to a one of your pending receivers
   */
  const STATUS_CODE_LINKED_PENDING = '4';
  /**
   * [Rejected] Already linked to other receiver
   */
  const STATUS_CODE_LINKED_OTHER = '5';

  /**
   * Pending Stickiness
   */
  const STATUS_VERIFICATION_PENDING = 'pending';
  /**
   * Approved Stickiness
   */
  const STATUS_VERIFICATION_APPROVED = 'approved';

  /**
   * Rejected Stickiness
   */
  const STATUS_VERIFICATION_REJECTED = 'rejected';

  /**
   * @var int
   */
  private $stickinessId;
  /**
   * @var int
   */
  private $verificationId;
  /**
   * @var string
   */
  private $verification;
  /**
   * @var string
   */
  private $authCode;
  /**
   * @var string
   */
  private $customer;
  /**
   * @var int
   */
  private $customerId;
  /**
   * @var int
   */
  private $agencyP2P;
  /**
   * @var string
   */
  private $person;
  /**
   * @var int
   */
  private $personId;
  /**
   * @var int
   */
  private $personalId;
  /**
   * @var string
   */
  private $controlNumber;

  /**
   * TblStickiness reference
   *
   * @var TblStickiness
   */
  private $tblStickiness;

  /**
   * new instance
   */
  public function __construct()
  {
    $this->tblStickiness = TblStickiness::getInstance();
  }

  /**
   * @return int
   */
  public function getStickinessId()
  {
    return $this->stickinessId;
  }

  /**
   * @param int $stickinessId
   */
  public function setStickinessId($stickinessId)
  {
    $this->stickinessId = $stickinessId;
  }

  /**
   * @return int
   */
  public function getCustomerId()
  {
    return $this->customerId;
  }

  /**
   * @param int $customerId
   */
  public function setCustomerId($customerId)
  {
    $this->customerId = $customerId;
  }

  /**
   * @return string
   */
  public function getCustomer()
  {
    return $this->customer;
  }

  /**
   * @param string $customer
   */
  public function setCustomer($customer)
  {
    $this->customer = $customer;
  }

  /**
   * @return int
   */
  public function getPersonId()
  {
    return $this->personId;
  }

  /**
   * @param int $personId
   */
  public function setPersonId($personId)
  {
    $this->personId = $personId;
  }

  /**
   * @return int
   */
  public function getPersonalId()
  {
    return $this->personalId;
  }

  /**
   * @return int
   */
  public function getVerificationId()
  {
    return $this->verificationId;
  }

  /**
   * @return string
   */
  public function getVerification()
  {
    return $this->verification;
  }

  /**
   * @return string
   */
  public function getAuthCode()
  {
    return $this->authCode;
  }

  /**
   * @param int $personalId
   */
  public function setPersonalId($personalId)
  {
    $this->personalId = $personalId;
  }

  /**
   * @return string
   */
  public function getPerson()
  {
    return $this->person;
  }

  /**
   * @param string
   */
  public function setPerson($person)
  {
    $this->person = $person;
  }

  /**
   * @return string
   */
  public function getControlNumber()
  {
    return $this->controlNumber;
  }

  /**
   * @param string $controlNumber
   */
  public function setControlNumber($controlNumber)
  {
    $this->controlNumber = $controlNumber;
  }

  /**
   *  create new stickiness
   */
  public function create()
  {
    $this->stickinessId = $this->tblStickiness->create($this->customerId, $this->personId);
  }

  /**
   * add api-controller information
   */
  private function createProvider()
  {
    if(!$this->stickinessId){
      $this->stickinessId = $this->tblStickiness->create($this->customerId, $this->personId, $this->verificationId, $this->verification);
    }else{
      $this->tblStickiness->update($this->stickinessId, $this->verificationId, $this->verification);
    }
  }

  /**
   * restore or get stickiness data
   */
  public function restore()
  {
    $stickinessData = $this->tblStickiness->get($this->stickinessId);
    if($stickinessData){
      $this->stickinessId = $stickinessData['Stickiness_Id'];
      $this->verificationId = $stickinessData['Verification_Id'];
      $this->verification = $stickinessData['Verification'];
      $this->agencyP2P = $stickinessData['AgencyP2P'];

      $this->customerId = $stickinessData['Customer_Id'];
      $this->customer = $stickinessData['Customer'];

      $this->personId = $stickinessData['Person_Id'];
      $this->person = $stickinessData['Person'];
      $this->personalId = $stickinessData['PersonalId'];
    }
  }

  /**
   * restore or get stickiness data
   *
   * @param $customerId
   */
  public function restoreByCustomerId($customerId)
  {
    $this->customerId = $customerId;

    $stickinessData = $this->tblStickiness->getByCustomerId($this->customerId);
    if($stickinessData)
    {
      $this->stickinessId = $stickinessData['Stickiness_Id'];
      $this->verificationId = $stickinessData['Verification_Id'];
      $this->verification = $stickinessData['Verification'];
      $this->agencyP2P = $stickinessData['AgencyP2P'];

      $this->customerId = $stickinessData['Customer_Id'];
      $this->customer = $stickinessData['Customer'];

      if(!$this->personId)
      {
        $this->personId = $stickinessData['Person_Id'];
        $this->person = $stickinessData['Person'];
        $this->personalId = $stickinessData['PersonalId'];
      }

    }

  }

  //---------------------------------------------------
  //--External connection to validate Person 2 Person--
  //---------------------------------------------------

  /**
   * authentication params
   *
   * @return array
   */
  private function authParams()
  {
    $params = array();

    $params['format'] = 'json';
    $params['companyId'] = CoreConfig::WS_STICKINESS_CREDENTIAL_COMPANY;
    $params['password'] = CoreConfig::WS_STICKINESS_CREDENTIAL_PASSWORD;
    $params['key'] = CoreConfig::WS_STICKINESS_CREDENTIAL_KEY;
    $params['agencyId'] = $this->agencyP2P;

    return $params;
  }

  /**
   * check credentials
   *
   * @return bool
   */
  private function checkConnection()
  {
    if(!CoreConfig::WS_STICKINESS_CHECK_CONNECTION){
      return true;
    }

    try{
      $params = $this->authParams();

      $wsConnector = new WS();
      $wsConnector->setReader(new Reader_Json());
      $result = $wsConnector->execPost(CoreConfig::WS_STICKINESS_URL . 'account/', $params);

      return ($result && $result->code == 1);
    }catch(WSException $ex){
      ExceptionManager::handleException($ex);
    }

    return false;
  }

  /**
   * The web service checks if the sender is still available for new receiver's, is already linked to a receiver or is linked to a different merchant or company.
   */
  public function register()
  {
    if(CoreConfig::WS_STICKINESS_ACTIVE && $this->checkConnection()){
      $result = null;
      try{
        $params = $this->authParams();
        //required param
        $params['sender'] = $this->customer;
        $params['receiver'] = $this->person;
        $params['receiverId'] = $this->personalId;

        $wsConnector = new WS();
        $wsConnector->setReader(new Reader_Json());
        $result = $wsConnector->execPost(CoreConfig::WS_STICKINESS_URL . 'check/', $params);

        $this->tblStickiness->addProviderMessage($this->stickinessId, $wsConnector->getLastRequest(), $result);
      }catch(Exception $ex){
        ExceptionManager::handleException($ex);
      }

      if($result){

        if($result->response && $result->response->verification){
          $verification = $result->response->verification;
          $this->verificationId = $verification->id;
          $this->verification = $verification->status;
        }

        $resultCode = $result->code;
        switch($resultCode){
          case self::STATUS_CODE_SUCCESS:
          case self::STATUS_CODE_LINKED:
          case self::STATUS_CODE_LINKED_PENDING:
            if($this->verification == self::STATUS_VERIFICATION_PENDING){
              $this->createProvider();
            }else{
              throw new InvalidStateException("The Customer is linked to another Person.");
            }
            break;
          case self::STATUS_CODE_LINKED_OTHER:
            throw new InvalidStateException("The Customer is linked to another Agency.");
            break;
          case self::STATUS_CODE_FAILED:
            throw new InvalidStateException("This Customer is not linked to this Person");
            break;
          default:
            throw new InvalidStateException("Due to external factors, we cannot give this Customer a Person.");
        }
      }else{
        throw new InvalidStateException("The Customer cannot be verify.");
      }

    }else{
      //create stickiness
      $this->create();
    }
  }

  /**
   * The web service confirms or completes the transaction, in this service is where the sender gets linked to the receiver.
   */
  public function complete()
  {
    if(CoreConfig::WS_STICKINESS_ACTIVE && $this->checkConnection()){
      $result = null;
      try{
        $params = $this->authParams();
        //required param
        $params['verificationId'] = $this->verificationId;
        $params['receiver'] = $this->person;
        $params['receiverId'] = $this->personalId;
        $params['controlNumber'] = $this->controlNumber;

        $wsConnector = new WS();
        $wsConnector->setReader(new Reader_Json());
        $result = $wsConnector->execPost(CoreConfig::WS_STICKINESS_URL . 'confirm/', $params);

        $this->tblStickiness->addProviderMessage($this->stickinessId, $wsConnector->getLastRequest(), $result);
      }catch(Exception $ex){
        ExceptionManager::handleException($ex);
      }

      if($result){

        if($result->response && $result->response->verification){
          $verification = $result->response->verification;
          $this->verificationId = $verification->id;
          $this->verification = $verification->status;
          $this->authCode = $verification->authCode;
        }

        $resultCode = $result->code;
        switch($resultCode){
          case self::STATUS_CODE_SUCCESS:
          case self::STATUS_CODE_LINKED:
          case self::STATUS_CODE_LINKED_PENDING:
            if($this->verification == self::STATUS_VERIFICATION_APPROVED){
              $this->createProvider();
            }else{
              throw new InvalidStateException("The Customer is linked to another Person.");
            }
            break;
          case self::STATUS_CODE_LINKED_OTHER:
            throw new InvalidStateException("The Customer is linked to another Agency.");
            break;
          case self::STATUS_CODE_FAILED:
            throw new InvalidStateException("This Customer is not linked to this Person");
            break;
          default:
            throw new InvalidStateException("Due to external factors, we cannot confirm this transaction.");
        }
      }

    }
  }

}

?>