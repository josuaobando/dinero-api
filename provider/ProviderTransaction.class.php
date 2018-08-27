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
        throw new TransactionException("The Transaction not has been created. Please, try later!");
      }
    }
    $this->wsRequest = $wsRequest;
  }

  /**
   * get a receiver from API
   *
   * @return WSResponse
   *
   * @throws APIException|TransactionException
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
    $transaction->setCustomerId($customer->getCustomerId());

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //get name
    $person = new Person();
    $provider = new Provider();
    foreach($this->providers as $providerData){
      $providerClassName = $providerData['Name'];
      if(class_exists($providerClassName)){
        try{
          $provider = new $providerClassName();
          $person = $provider->receiver();
          if($person && $person->getPersonId()){
            $providerId = $providerData['Provider_Id'];
            $transaction->setProviderId($providerId);
            break;
          }
        }catch(Exception $exception){
          Log::custom(__CLASS__, $exception->getMessage());
        }
      }
    }

    if(!$person || !$person->getPersonId()){
      throw new APIException($provider->getApiMessage());
    }

    //update customer
    $customer->update();
    //block person
    $person->block();
    //sets personId
    $transaction->setPersonId($person->getPersonId());
    $transaction->setAgencyId($customer->getAgencyId());

    $transaction->create();
    if($transaction->getTransactionId()){
      $provider->stickiness();

      //extra information
      if($transaction->getAgencyTypeId() == Transaction::AGENCY_TYPE_RIA){
        $transaction->setReason('EasyPay-Phillgus');
        if($transaction->getAgencyId() == CoreConfig::AGENCY_ID_SATURNO_RIA){
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
   * @throws APIException|TransactionException
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
    $transaction->setCustomerId($customer->getCustomerId());

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //get name
    $person = new Person();
    $provider = new Provider();
    foreach($this->providers as $providerData){
      $providerClassName = $providerData['Name'];
      if(class_exists($providerClassName)){
        try{
          $provider = new $providerClassName();
          $person = $provider->receiver();
          if($person && $person->getPersonId()){
            $providerId = $providerData['Provider_Id'];
            $transaction->setProviderId($providerId);
            break;
          }
        }catch(Exception $exception){
          Log::custom(__CLASS__, $exception->getMessage());
        }
      }
    }

    if(!$person || !$person->getPersonId()){
      throw new APIException($provider->getApiMessage());
    }

    //update customer
    $customer->update();
    //block person
    $person->block();
    //sets personId
    $transaction->setPersonId($person->getPersonId());
    $transaction->setAgencyId($customer->getAgencyId());

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
      if($transaction->getTransactionStatusId() == Transaction::STATUS_CANCELED){
        throw new TransactionException("The transaction has expired. Valid time is 48 hours to confirm.");
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

    $provider = $transaction->getProvider();
    $provider->status();

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

}

?>