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
   * #1: There was an issue with the authentication process or there was an invalid state in the execution.
   *
   * #2: [Rejected] Receiver already linked to other company
   */
  const STATUS_CODE_LINKED_OTHER_COMPANY = '2';
  /**
   * [Rejected] Already linked to a one of your receivers
   *
   * @TODO: review
   */
  const STATUS_CODE_LINKED = '3';
  /**
   * [Pending] Already linked to a one of your pending receivers
   */
  const STATUS_CODE_LINKED_PENDING = '4';
  /**
   * [Rejected] Already linked to other receiver (company)
   */
  const STATUS_CODE_LINKED_OTHER = '5';
  /**
   * [Rejected] This receiver is not linked to this sender
   *
   * @TODO: review
   */
  const STATUS_CODE_LINKED_OTHER_CUSTOMER = '6';
  /**
   * [Rejected] Max # of transaction per month exceeded
   */
  const STATUS_CODE_LIMIT_TRANSACTIONS = '7';
  /**
   * [Rejected] Max amount per month exceeded
   */
  const STATUS_CODE_LIMIT_AMOUNT = '8';

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
  private $agencyTypeId;
  /**
   * @var int
   */
  private $agencyP2P;
  /**
   * @var int
   */
  private $agencyP2P_Url;
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
   * @var array
   */
  private $stickinessTransactionData;

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
      $this->stickinessId = $this->tblStickiness->create($this->customerId, $this->personId);
    }
  }

  /**
   *  disable stickiness
   */
  public function rejectProvider()
  {
    /*
    try{
      $tblCustomer = TblCustomer::getInstance();
      $blocked = $tblCustomer->block($this->customer, $this->agencyTypeId, 'P2P Blocked');
      if($blocked){
        Log::custom('Blocked', "Agency: $this->agencyTypeId Customer: $this->customer");
      }
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }
    */
    try{
      if($this->stickinessId){
        $this->tblStickiness->isActive($this->stickinessId, 0);

        if($this->stickinessTransactionData){
          $stickinessTransactionId = $this->stickinessTransactionData['StickinessTransaction_Id'];
          $verificationId = $this->stickinessTransactionData['Verification_Id'];

          $tblStickinessTransaction = TblStickinessTransaction::getInstance();
          $tblStickinessTransaction->update($stickinessTransactionId, $verificationId, 'rejected', 0);
        }

      }
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
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

      $this->agencyTypeId = $stickinessData['AgencyType_Id'];
      $this->agencyP2P = $stickinessData['AgencyP2P'];
      $this->agencyP2P_Url = $stickinessData['AgencyP2P_Url'];

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
    if($stickinessData){
      $this->stickinessId = $stickinessData['Stickiness_Id'];
      $this->agencyTypeId = $stickinessData['AgencyType_Id'];
      $this->agencyP2P = $stickinessData['AgencyP2P'];
      $this->agencyP2P_Url = $stickinessData['AgencyP2P_Url'];

      $this->customerId = $stickinessData['Customer_Id'];
      $this->customer = $stickinessData['Customer'];

      if(!$this->personId){
        $this->personId = $stickinessData['Person_Id'];
        $this->person = $stickinessData['Person'];
        $this->personalId = $stickinessData['PersonalId'];
      }

    }

  }

  /**
   * restore or get stickiness data
   *
   * @param $transactionId
   */
  public function restoreByTransactionId($transactionId)
  {
    $tblStickinessTransaction = TblStickinessTransaction::getInstance();
    $this->stickinessTransactionData = $tblStickinessTransaction->get($transactionId);
    if($this->stickinessTransactionData){

      $this->stickinessId = $this->stickinessTransactionData['Stickiness_Id'];
      $this->verificationId = $this->stickinessTransactionData['Verification_Id'];
      $this->verification = $this->stickinessTransactionData['Verification'];
      $this->agencyP2P = $this->stickinessTransactionData['AgencyP2P'];
      $this->agencyP2P_Url = $this->stickinessTransactionData['AgencyP2P_Url'];

      $this->customerId = $this->stickinessTransactionData['Customer_Id'];
      $this->customer = $this->stickinessTransactionData['Customer'];

      $this->personId = $this->stickinessTransactionData['Person_Id'];
      $this->person = $this->stickinessTransactionData['Person'];
      $this->personalId = $this->stickinessTransactionData['PersonalId'];
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
      $result = $wsConnector->execPost($this->agencyP2P_Url . 'account/', $params);

      return ($result && $result->code == 1);
    }catch(WSException $ex){
      ExceptionManager::handleException($ex);
    }

    return false;
  }

  /**
   * The web service checks if the sender is still available for new receiver's, is already linked to a receiver or is linked to a different merchant or company.
   *
   * @throws P2PException|P2PLimitException
   */
  public function register()
  {
    if(CoreConfig::WS_STICKINESS_ACTIVE && $this->checkConnection()){
      $result = null;
      try{

        $transaction = Session::getTransaction();

        $params = $this->authParams();
        //required param
        $params['sender'] = $this->customer;
        $params['receiver'] = $this->person;
        $params['receiverId'] = $this->personalId;
        $params['amount'] = $transaction->getAmount();
        $params['account'] = $transaction->getUsername();

        $wsConnector = new WS();
        $wsConnector->setReader(new Reader_Json());
        $result = $wsConnector->execPost($this->agencyP2P_Url . 'check/', $params);

        $this->tblStickiness->addProviderMessage($this->stickinessId, $wsConnector->getLastRequest(), $result);
      }catch(WSException $ex){
        ExceptionManager::handleException($ex);
      }

      if($result){

        if($result->response && $result->response->verification){
          $verification = $result->response->verification;
          $this->verificationId = $verification->id;
          $this->verification = $verification->status;
        }

        $resultCodeMessage = $result->systemMessage;
        $resultCode = $result->code;
        switch($resultCode){
          case self::STATUS_CODE_SUCCESS:
          case self::STATUS_CODE_LINKED:
          case self::STATUS_CODE_LINKED_PENDING:
            if($this->verification == self::STATUS_VERIFICATION_PENDING){
              $this->createProvider();
            }else{
              $this->rejectProvider();
              throw new P2PException("The Customer is linked to another Person.");
            }
            break;
          case self::STATUS_CODE_LINKED_OTHER_COMPANY:
            ExceptionManager::handleException(new P2PException("The Person [$this->person] is linked to another Customer [$this->customer]. " . __FUNCTION__));
            throw new P2PException("The Customer is linked to another Person.");
            break;
          case self::STATUS_CODE_LINKED_OTHER:
          case self::STATUS_CODE_LINKED_OTHER_CUSTOMER:
            $this->rejectProvider();
            throw new P2PException("The Customer is linked to another Agency (Merchant).");
            break;
          case self::STATUS_CODE_LIMIT_TRANSACTIONS:
            throw new P2PLimitException("Max # of transaction per month exceeded");
            break;
          case self::STATUS_CODE_LIMIT_AMOUNT:
            throw new P2PLimitException("Max amount per month exceeded");
            break;
          default:
            ExceptionManager::handleException(new P2PException("Invalid Response Code >> Code: $resultCode  Message: $resultCodeMessage : (" . __FUNCTION__ . ")"));
            throw new TransactionException("Due to external factors, we cannot give this Customer a Name.");
        }
      }else{
        throw new TransactionException("The Customer cannot be verify.");
      }

    }else{
      //create stickiness
      $this->create();
    }

    return true;
  }

  /**
   * The web service confirms or completes the transaction, in this service is where the sender gets linked to the receiver.
   *
   * @throws P2PException|P2PLimitException
   */
  public function complete()
  {
    if(CoreConfig::WS_STICKINESS_ACTIVE && $this->checkConnection()){
      $result = null;
      try{
        $transaction = Session::getTransaction();

        $params = $this->authParams();
        //required param
        $params['verificationId'] = $this->verificationId;
        $params['receiver'] = $this->person;
        $params['receiverId'] = $this->personalId;
        $params['controlNumber'] = $this->controlNumber;
        $params['amountConfirmed'] = $transaction->getAmount();

        $apiURL = $this->agencyP2P_Url;
        $wsConnector = new WS();
        $wsConnector->setReader(new Reader_Json());
        $result = $wsConnector->execPost($apiURL . 'confirm/', $params);

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

        $resultCodeMessage = $result->systemMessage;
        $resultCode = $result->code;
        switch($resultCode){
          case self::STATUS_CODE_SUCCESS:
          case self::STATUS_CODE_LINKED:
          case self::STATUS_CODE_LINKED_PENDING:
            if($this->verification == self::STATUS_VERIFICATION_APPROVED){
              $this->createProvider();
            }else{
              $this->rejectProvider();
              throw new P2PException("The Customer is linked to another Person.");
            }
            break;
          case self::STATUS_CODE_LINKED_OTHER:
          case self::STATUS_CODE_LINKED_OTHER_COMPANY:
          case self::STATUS_CODE_LINKED_OTHER_CUSTOMER:
            $this->rejectProvider();
            throw new P2PException("Customer is linked with another Merchant or Person. Reject this transaction.");
            break;
          case self::STATUS_CODE_LIMIT_TRANSACTIONS:
            throw new P2PLimitException("Max # of transaction per month exceeded. Reject this transaction.");
            break;
          case self::STATUS_CODE_LIMIT_AMOUNT:
            throw new P2PLimitException("Max amount per month exceeded. Reject this transaction.");
            break;
          default:
            ExceptionManager::handleException(new P2PException("Invalid Response Code >> Code: $resultCode  Message: $resultCodeMessage : (" . __FUNCTION__ . ")"));
            throw new P2PException("Customer is linked with another Agency (Merchant) or Person. Reject this transaction.");
        }
      }

    }
  }

}

?>