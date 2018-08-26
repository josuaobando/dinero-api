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
   * @var Provider
   */
  private $provider;

  /**
   * ProviderTransaction constructor.
   *
   * @param WSRequest $wsRequest
   */
  public function __construct($wsRequest)
  {
    $tblSystem = TblSystem::getInstance();
    $agencyTypeId = $wsRequest->requireNumericAndPositive('type');
    $this->providers = $tblSystem->getAgencyProviders($agencyTypeId);
    $this->wsRequest = $wsRequest;
  }

  /**
   * get a receiver from API
   *
   * @return bool
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
    foreach($this->providers as $provider){
      $providerClassName = $provider['Name'];
      if(class_exists($providerClassName)){
        try{
          $this->provider = new $providerClassName();
          $person = $this->provider->receiver();
          if($person && $person->getPersonId()){
            $providerId = $provider['Provider_Id'];
            $transaction->setProviderId($providerId);
            break;
          }
        }catch(Exception $exception){
          Log::custom(__CLASS__, $exception->getMessage().': '.$this->provider->getApiMessage());
        }
      }
    }

    if(!$person || !$person->getPersonId()){
      throw new APIException($this->provider->getApiMessage());
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
      $this->provider->stickiness();
    }else{
      throw new TransactionException("The Transaction not has been created. Please, try later!");
    }

    return true;
  }

  /**
   * get a sender from API
   *
   * @return bool
   *
   * @throws APIException
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
    foreach($this->providers as $provider){
      $providerClassName = $provider['Name'];
      if(class_exists($providerClassName)){
        try{
          $this->provider = new $providerClassName();
          $person = $this->provider->receiver();
          if($person && $person->getPersonId()){
            $providerId = $provider['Provider_Id'];
            $transaction->setProviderId($providerId);
            break;
          }
        }catch(Exception $exception){
          Log::custom(__CLASS__, $exception->getMessage().': '.$this->provider->getApiMessage());
        }
      }
    }

    if(!$person || !$person->getPersonId()){
      throw new APIException($this->provider->getApiMessage());
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

    return $transaction->getTransactionId();
  }

  /**
   * confirm transaction with the control number
   *
   * @return WSResponseOk
   *
   * @throws TransactionException
   */
  public function confirm()
  {
    $account = Session::getAccount();
    $transaction = Session::getTransaction();
    $transaction->setModifiedBy($account->getAccountId());

    //confirm transaction
    $provider = $this->provider();
    $confirm = $provider->confirm();
    if(!$confirm){
      throw new TransactionException("Transaction cannot be confirmed. Please try again in a few minutes!");
    }

    //update transaction after the validation of the data
    $transaction->setApiTransactionId($provider->getApiTransactionId());
    $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);
    $transaction->setNote('');
    $transaction->setReason('');
    $transaction->update();
  }

  /**
   * get transaction information
   *
   * @throws InvalidStateException
   */
  public function status()
  {
    $account = Session::getAccount();
    $transaction = Session::getTransaction();
    $transaction->setModifiedBy($account->getAccountId());

    //check status
    $provider = $this->provider();
    $provider->status();
  }

}

?>