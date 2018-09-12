<?php

/**
 * @author Josua
 */
class ProviderTransaction
{

  /**
   * @var WSRequest
   */
  private $wsRequest;

  /**
   * @var array
   */
  private $providers = array();

  /**
   * ProviderTransaction constructor.
   *
   * @param WSRequest $wsRequest
   *
   * @throws TransactionException
   */
  public function __construct($wsRequest)
  {
    $agencyTypeId = $wsRequest->getParam('type');
    if($agencyTypeId){
      $tblSystem = TblSystem::getInstance();
      $this->providers = $tblSystem->getAgencyProviders($agencyTypeId);
      if(!$this->providers){
        throw new TransactionException("At this time, we can not process your request. Please, try later!");
      }
    }
    $this->wsRequest = $wsRequest;
    //clean session object
    Session::getTransaction(true);
  }

  /**
   * get a receiver from API
   *
   * @return WSResponse
   *
   * @throws APIBlackListException|APIException|Exception|P2PException|TransactionException
   */
  public function receiver()
  {
    $account = Session::getAccount();
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    $transaction->setAccountId($account->getAccountId());
    $transaction->setCompanyId($account->getCompanyId());
    $transaction->setTransactionTypeId(Transaction::TYPE_RECEIVER);
    $transaction->setTransactionStatusId(Transaction::STATUS_REQUESTED);

    //transaction request
    $merchantId = trim($this->wsRequest->getParam('merchantId'));
    $amount = $this->wsRequest->requireNumericAndPositive('amount');
    $username = trim($this->wsRequest->requireNotNullOrEmpty('uid'));
    $agencyTypeId = $this->wsRequest->requireNumericAndPositive('type');

    $transaction->setFee(0);
    $transaction->setAmount($amount);
    $transaction->setUsername($username);
    $transaction->setMerchantId($merchantId);
    $transaction->setAgencyTypeId($agencyTypeId);

    //validate if need to create the customer
    $customer->validateFromRequest($account, $this->wsRequest);
    $customer->getLastTransaction($transaction->getTransactionTypeId());
    $transaction->setCustomerId($customer->getCustomerId());
    //default values
    $transaction->setAgencyId($customer->getLastTransaction()->getAgencyId());
    $transaction->setProviderId($customer->getLastTransaction()->getProviderId());

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //get name
    $person = new Person();
    $provider = new Provider();
    $providerException = null;
    foreach($this->providers as $providerData){
      $providerException = null;
      $providerClassName = $providerData['Name'];
      if(class_exists($providerClassName)){
        try{
          $provider = new $providerClassName();
          $person = $provider->receiver();
          if($person && $person->getPersonId()){
            break;
          }
        }catch(CustomerBlackListException $exception){
          $providerException = $exception;
          continue;
        }catch(P2PException $exception){
          $providerException = $exception;
          continue;
        }catch(APIBlackListException $exception){
          $providerException = $exception;
          continue;
        }catch(APIPersonException $exception){
          $providerException = $exception;
          continue;
        }catch(Exception $exception){
          throw $exception;
        }
      }
    }

    if(!$person || !$person->getPersonId()){
      if($providerException){
        throw $providerException;
      }elseif($provider->getApiMessage()){
        throw new APIException($provider->getApiMessage());
      }else{
        throw new TransactionException('We cannot give a Receiver for this Customer (Sender)');
      }
    }

    //block person
    $person->block();
    //sets personId
    $transaction->setPersonId($person->getPersonId());
    //create transaction after the validation of the data
    $transaction->create();
    if($transaction->getTransactionId()){
      $provider->stickiness();

      //extra information
      if($transaction->getAgencyTypeId() == Transaction::AGENCY_TYPE_RIA){
        $transaction->setReason('EasyPay-Phillgus');
        if($transaction->getProviderId() != Dinero::PROVIDER_ID){
          $transaction->setReason('TeleDolar');
        }
      }

      $wsResponse = new WSResponseOk();
      $wsResponse->addElement('transaction', $transaction);
      $wsResponse->addElement('sender', $customer);
      $wsResponse->addElement('receiver', $person);

    }else{
      throw new TransactionException("The Transaction not has been created. Please, try later!");
    }

    return $wsResponse;
  }

  /**
   * get a sender from API
   *
   * @return WSResponse
   *
   * @throws APIBlackListException|APIException|Exception|P2PException|TransactionException
   */
  public function sender()
  {
    $account = Session::getAccount();
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    $transaction->setAccountId($account->getAccountId());
    $transaction->setCompanyId($account->getCompanyId());
    $transaction->setTransactionTypeId(Transaction::TYPE_SENDER);
    $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);

    //transaction request
    $merchantId = trim($this->wsRequest->getParam('merchantId'));
    $amount = $this->wsRequest->requireNumericAndPositive('amount');
    $username = trim($this->wsRequest->requireNotNullOrEmpty('uid'));
    $agencyTypeId = $this->wsRequest->requireNumericAndPositive('type');

    $transaction->setFee(0);
    $transaction->setAmount($amount);
    $transaction->setUsername($username);
    $transaction->setMerchantId($merchantId);
    $transaction->setAgencyTypeId($agencyTypeId);

    //validate if need to create the customer
    $customer->validateFromRequest($account, $this->wsRequest);
    $customer->getLastTransaction($transaction->getTransactionTypeId());
    $transaction->setCustomerId($customer->getCustomerId());
    //default values
    $transaction->setAgencyId($customer->getLastTransaction()->getAgencyId());
    $transaction->setProviderId($customer->getLastTransaction()->getProviderId());

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //get name
    $person = new Person();
    $provider = new Provider();
    $providerException = null;
    foreach($this->providers as $providerData){
      $providerException = null;
      $providerClassName = $providerData['Name'];
      if(class_exists($providerClassName)){
        try{
          $provider = new $providerClassName();
          $person = $provider->sender();
          if($person && $person->getPersonId()){
            break;
          }
        }catch(CustomerBlackListException $exception){
          $providerException = $exception;
          continue;
        }catch(P2PException $exception){
          $providerException = $exception;
          continue;
        }catch(APIBlackListException $exception){
          $providerException = $exception;
          continue;
        }catch(APIPersonException $exception){
          $providerException = $exception;
          continue;
        }catch(Exception $exception){
          throw $exception;
        }
      }
    }

    if(!$person || !$person->getPersonId()){
      if($providerException){
        throw $providerException;
      }elseif($provider->getApiMessage()){
        throw new APIException($provider->getApiMessage());
      }else{
        throw new TransactionException('We cannot give a Sender for this Customer (Receiver)');
      }
    }

    //block person
    $person->block();
    //sets personId
    $transaction->setPersonId($person->getPersonId());
    //create transaction after the validation of the data
    $transaction->create();
    if($transaction->getTransactionId()){
      $wsResponse = new WSResponseOk();
      $wsResponse->addElement('transaction', $transaction);
      $wsResponse->addElement('sender', $person);
      $wsResponse->addElement('receiver', $customer);
    }else{
      throw new TransactionException("The Transaction not has been created. Please, try later!");
    }

    return $wsResponse;
  }

  /**
   * confirm transaction with the control number
   *
   * @return WSResponse
   *
   * @throws TransactionException
   */
  public function confirm()
  {
    //transaction id
    $fee = $this->wsRequest->getParam('fee', 0);
    $amount = $this->wsRequest->requireNumericAndPositive('amount');
    $transactionId = $this->wsRequest->requireNumericAndPositive('transaction_id');
    $controlNumber = $this->wsRequest->requireNumericAndPositive('control_number');

    $account = Session::getAccount();
    $transaction = Session::getTransaction();
    $transaction->restore($transactionId);
    if(!$transaction->getTransactionId()){
      throw new TransactionException("This transaction not exist or not can be loaded: " . $transactionId);
    }else{
      $this->wsRequest->putParam('type', $transaction->getAgencyTypeId());
    }

    if($transaction->getTransactionStatusId() != Transaction::STATUS_REQUESTED && $transaction->getTransactionStatusId() != Transaction::STATUS_REJECTED){
      if($transaction->getTransactionStatusId() == Transaction::STATUS_CANCELED && $transaction->getProviderId() != Dinero::PROVIDER_ID){
        throw new TransactionException("The transaction has expired. Valid time is 48 hours to confirm.");
      }elseif($transaction->getTransactionStatusId() == Transaction::STATUS_CANCELED && $transaction->getProviderId() == Dinero::PROVIDER_ID){
        throw new TransactionException("The transaction has expired.");
      }else{
        throw new TransactionException("Transaction cannot be confirmed since the current status is: " . $transaction->getTransactionStatus());
      }
    }

    //validate customer
    $customerRequest = new Customer();
    $customerRequest->restoreFromRequest($this->wsRequest);
    $newCustomerName = strtoupper($customerRequest->getCustomer());
    $customerTransaction = Session::getCustomer($transaction->getCustomerId());
    $originalCustomerName = strtoupper($customerTransaction->getCustomer());
    $percent = Util::similarPercent($newCustomerName, $originalCustomerName);
    if($newCustomerName != $originalCustomerName && $percent >= CoreConfig::CUSTOMER_SIMILAR_PERCENT_UPDATE){
      $customerTransaction->setFirstName($customerRequest->getFirstName());
      $customerTransaction->setLastName($customerRequest->getLastName());
      $customerUpdated = $customerTransaction->update();
      $username = $transaction->getUsername();
      if($customerUpdated){
        Log::custom('UpdateCustomer', "Customer updated | Username: $username Original: $originalCustomerName New: $newCustomerName Percent: $percent%");
      }else{
        Log::custom('UpdateCustomer', "Customer not updated | Username: $username Original: $originalCustomerName New: $newCustomerName Percent: $percent%");
      }
    }

    $transaction->setFee($fee);
    $transaction->setAmount($amount);
    $transaction->setControlNumber($controlNumber);
    $transaction->setModifiedBy($account->getAccountId());

    $provider = $transaction->getProvider();
    $confirm = $provider->confirm();
    if(!$confirm){
      throw new TransactionException("Transaction cannot be confirmed. Please try again in a few minutes!");
    }

    //update transaction after the validation of the data
    $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);
    $transaction->setNote('');
    $transaction->setReason('');
    $transaction->update();

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transaction', $transaction);

    return $wsResponse;
  }

  /**
   * get transaction information
   *
   * @param bool $webRequest
   *
   * @return WSResponse|Transaction
   *
   * @throws InvalidStateException
   */
  public function status($webRequest = false)
  {
    $transactionId = $this->wsRequest->requireNumericAndPositive('transaction_id');
    $transaction = Session::getTransaction();
    $transaction->restore($transactionId);

    if($webRequest){
      $provider = $transaction->getProvider();
      $provider->status();
    }

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transaction', $transaction);

    // Payout (Sender) Information
    if($transaction->getTransactionStatusId() == Transaction::STATUS_APPROVED && $transaction->getTransactionTypeId() == Transaction::TYPE_SENDER){
      $person = new Person($transaction->getPersonId());
      $wsResponse->addElement('sender', $person);
    }

    if($webRequest){
      return $transaction;
    }else{
      return $wsResponse;
    }
  }

  /**
   * update transaction data
   *
   * @return int
   *
   * @throws InvalidStateException+
   */
  public function transactionUpdate()
  {
    $account = Session::getAccount();
    $transactionId = $this->wsRequest->requireNumericAndPositive("transactionId");
    $transactionTypeId = $this->wsRequest->requireNumericAndPositive("transactionTypeId");
    $statusId = $this->wsRequest->requireNumericAndPositive("status");
    $reason = $this->wsRequest->getParam("reason", "");
    $note = $this->wsRequest->getParam("note", "");
    $amount = $this->wsRequest->requireNumericAndPositive("amount");
    $fee = $this->wsRequest->getParam("fee");

    if($transactionTypeId == Transaction::TYPE_SENDER && $statusId == Transaction::STATUS_REJECTED){
      $controlNumber = $this->wsRequest->getParam("controlNumber", '');
    }else{
      $controlNumber = $this->wsRequest->requireNumericAndPositive("controlNumber");
    }

    //restore and load transaction information
    $transaction = Session::getTransaction();
    $transaction->restore($transactionId);
    if(!$transaction->getTransactionId()){
      throw new InvalidStateException("The transaction [$transactionId] has not been restored, please check!");
    }

    if($transaction->getProviderId() != Dinero::PROVIDER_ID){
      throw new InvalidStateException("Transaction cannot be Modify!");
    }

    //get current status
    $currentStatusId = $transaction->getTransactionStatusId();

    //set new values
    $transaction->setTransactionStatusId($statusId);
    $transaction->setReason($reason);
    $transaction->setNote($note);
    $transaction->setAmount($amount);
    $transaction->setFee($fee);
    $transaction->setControlNumber($controlNumber);
    $transaction->setModifiedBy($account->getAccountId());

    //validate if is update transaction
    if($currentStatusId != Transaction::STATUS_APPROVED && $statusId == Transaction::STATUS_APPROVED){

      $stickiness = new Stickiness();
      $stickiness->restoreByTransactionId($transaction->getTransactionId());
      if($stickiness->getStickinessId()){

        //restore stickiness transaction
        $stickinessTransaction = new StickinessTransaction();
        $stickinessTransaction->setTransactionId($transaction->getTransactionId());
        $stickinessTransaction->restore();

        if($stickinessTransaction->getStickinessTransactionId() && !$stickinessTransaction->getAuthCode()){
          //Completed to API Controller
          $stickiness->setControlNumber($controlNumber);
          $stickiness->complete();

          //update stickiness transaction
          $stickinessTransaction->setVerification($stickiness->getVerification());
          $stickinessTransaction->setVerificationId($stickiness->getVerificationId());
          $stickinessTransaction->setAuthCode($stickiness->getAuthCode());
          $stickinessTransaction->update();
        }

      }

    }

    //update transaction after the validation of the data
    $update = $transaction->update();

    return $update;
  }

}

?>