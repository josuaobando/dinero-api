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
   * Agency in my company
   *
   * [Rejected] Receiver already linked to other of yours agencies
   */
  const STATUS_CODE_LINKED_OTHER_AGENCY = '9';
  /**
   * Agency in my company
   *
   * [Rejected] This sender already linked to other of yours agencies
   */
  const STATUS_CODE_SENDER_LINKED_OTHER_AGENCY = '12';

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
   * @param $agencyP2P
   */
  public function setAgencyP2P($agencyP2P)
  {
    $this->agencyP2P = $agencyP2P;
  }

  /**
   * new instance
   */
  public function __construct()
  {
    $this->tblStickiness = TblStickiness::getInstance();
  }

  /**
   *  create new stickiness
   */
  public function create()
  {
    $transaction = Session::getTransaction();
    $this->stickinessId = $this->tblStickiness->create($this->customerId, $transaction->getAgencyTypeId(), $this->personId);
  }

  /**
   * add api-controller information
   */
  private function createProvider()
  {
    if(!$this->stickinessId){
      $transaction = Session::getTransaction();
      $this->stickinessId = $this->tblStickiness->create($this->customerId, $transaction->getAgencyTypeId(), $this->personId);
    }
  }

  /**
   *  disable stickiness
   */
  public function reject()
  {
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
   * @param $agencyTypeId
   *
   * @throws P2PException
   */
  public function restoreByCustomerId($customerId, $agencyTypeId)
  {
    $this->customerId = $customerId;
    $stickinessData = $this->tblStickiness->getByCustomerId($this->customerId, $agencyTypeId);
    if($stickinessData){
      $this->stickinessId = $stickinessData['Stickiness_Id'];
      $this->agencyP2P = $stickinessData['AgencyP2P'];

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

      $this->customerId = $this->stickinessTransactionData['Customer_Id'];
      $this->customer = $this->stickinessTransactionData['Customer'];

      $this->personId = $this->stickinessTransactionData['Person_Id'];
      $this->person = $this->stickinessTransactionData['Person'];
      $this->personalId = $this->stickinessTransactionData['PersonalId'];
    }
  }

  /**
   * authentication params
   *
   * @return array
   */
  private function authParams()
  {
    $provider = new Provider(Dinero::PROVIDER_ID);
    $this->agencyP2P_Url = $provider->getSetting(Provider::SETTING_URL);

    $params = array();
    $params['format'] = 'json';
    $params['companyId'] = $provider->getSetting(Provider::SETTING_USER);
    $params['password'] = $provider->getSetting(Provider::SETTING_PASSWORD);
    $params['key'] = $provider->getSetting(Provider::SETTING_KEY);
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
      try{
        $transaction = Session::getTransaction();

        $params = $this->authParams();
        //required param
        $params['sender'] = $this->customer;
        $params['receiver'] = $this->person;
        $params['receiverId'] = $this->personalId;
        $params['amount'] = $transaction->getAmount();
        $params['account'] = $transaction->getUsername();

        if($transaction->getAgencyTypeId() == Transaction::AGENCY_TYPE_MG){
          $params['providerId'] = 1;
        }elseif($transaction->getAgencyTypeId() == Transaction::AGENCY_TYPE_RIA){
          $params['providerId'] = 2;
        }else{
          throw new TransactionException("Problem loading the provider.");
        }

        $wsConnector = new WS();
        $wsConnector->setReader(new Reader_Json());
        $result = $wsConnector->execPost($this->agencyP2P_Url . 'check/', $params);
        $this->tblStickiness->addProviderMessage($this->stickinessId, $wsConnector->getLastRequest(), $result);

      }catch(WSException $ex){
        ExceptionManager::handleException($ex);
        throw new P2PException("Customer cannot be verify.");
      }

      if($result){
        $verification = $result->response->verification;
        $this->verificationId = $verification->id;
        $this->verification = $verification->status;
        if($this->checkResponse($result)){
          if($this->verification == self::STATUS_VERIFICATION_PENDING){
            $this->createProvider();
          }else{
            $this->reject();
            throw new P2PException("Customer is linked to another Person.");
          }
        }
      }else{
        throw new P2PException("Customer cannot be verify.");
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

        if($transaction->getAgencyTypeId() == Transaction::AGENCY_TYPE_MG){
          $params['providerId'] = 1;
        }elseif($transaction->getAgencyTypeId() == Transaction::AGENCY_TYPE_RIA){
          $params['providerId'] = 2;
        }else{
          $params['providerId'] = 0;
        }

        $wsConnector = new WS();
        $wsConnector->setReader(new Reader_Json());
        $result = $wsConnector->execPost($this->agencyP2P_Url . 'confirm/', $params);

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
              $this->reject();
              throw new P2PException("Customer is linked to another Person.");
            }
            break;
          case self::STATUS_CODE_LINKED_OTHER:
          case self::STATUS_CODE_LINKED_OTHER_AGENCY:
          case self::STATUS_CODE_LINKED_OTHER_COMPANY:
          case self::STATUS_CODE_LINKED_OTHER_CUSTOMER:
            $this->reject();
            throw new P2PException("Customer is linked with another Merchant or Person. Reject this transaction.");
            break;
          case self::STATUS_CODE_LIMIT_TRANSACTIONS:
            throw new P2PLimitException("Max # of transactions per month exceeded. Reject this transaction.");
            break;
          case self::STATUS_CODE_LIMIT_AMOUNT:
            throw new P2PLimitException("Max amount per month exceeded. Reject this transaction.");
            break;
          default:
            ExceptionManager::handleException(new GeneralException("Invalid Response Code >> Code: $resultCode  Message: $resultCodeMessage : (" . __FUNCTION__ . ")"));
            throw new P2PException("Customer is linked with another Agency (Merchant) or Person. Reject this transaction.");
        }
      }

    }
  }

  /**
   * @param stdClass $result
   *
   * @return bool
   *
   * @throws P2PAgencyException|P2PException|P2PLimitException
   */
  private function checkResponse($result)
  {
    $resultCodeMessage = $result->systemMessage;
    $resultCode = $result->code;
    switch($resultCode){
      case self::STATUS_CODE_SUCCESS:
      case self::STATUS_CODE_LINKED:
      case self::STATUS_CODE_LINKED_PENDING:
        // do nothing
        return true;
      case self::STATUS_CODE_LINKED_OTHER_COMPANY:
      case self::STATUS_CODE_LINKED_OTHER:
      case self::STATUS_CODE_LINKED_OTHER_CUSTOMER:
        $this->reject();
        throw new P2PException("Customer is linked to another Agency (Merchant)");
      case self::STATUS_CODE_LIMIT_TRANSACTIONS:
        throw new P2PLimitException("Max # of transactions per month exceeded");
      case self::STATUS_CODE_LIMIT_AMOUNT:
        throw new P2PLimitException("Max amount per month exceeded");
      case self::STATUS_CODE_LINKED_OTHER_AGENCY:
      case self::STATUS_CODE_SENDER_LINKED_OTHER_AGENCY:
        Log::custom(__CLASS__, "Customer [$this->customer] already linked to other of yours agencies");
        throw new P2PAgencyException("Customer is linked to another Agency");
      default:
        ExceptionManager::handleException(new P2PException("Invalid Response Code >> Code: $resultCode  Message: $resultCodeMessage : (".__FUNCTION__.")"));
        throw new P2PException("Due to external factors, we cannot give this customer a name");
    }
  }

}

?>