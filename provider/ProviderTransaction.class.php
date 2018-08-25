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
   * ProviderTransaction constructor.
   *
   * @param WSRequest $wsRequest
   */
  public function __construct($wsRequest)
  {
    $this->wsRequest = $wsRequest;
  }

  /**
   * get provider instance
   *
   * @return Nicaragua|Ria|Saturno
   *
   * @throws InvalidStateException
   */
  private static function provider()
  {
    $transaction = Session::getTransaction();
    $agencyTypeId = $transaction->getAgencyTypeId();
    $agencyId = $transaction->getAgencyId();

    switch($agencyTypeId){
      case Transaction::AGENCY_TYPE_MG:
        if($agencyId == CoreConfig::AGENCY_ID_NICARAGUA){
          return new Nicaragua();
        }else{
          return new Saturno();
        }
        break;
      case Transaction::AGENCY_TYPE_RIA:
        return new Ria();
        break;
      default:
        throw new InvalidStateException("Missing Provider");
    }
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
    if(!$customer->getCustomerId()){
      $customer->validateFromRequest($account, $this->wsRequest);
    }
    $transaction->setCustomerId($customer->getCustomerId());

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //get a sender
    $provider = self::provider();
    $person = $provider->receiver();
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
    $transaction->setTransactionTypeId(Transaction::TYPE_SENDER);
    $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);

    //transaction request
    $merchantId = trim($this->wsRequest->getParam('merchantId'));
    $amount = $this->wsRequest->requireNumericAndPositive('amount');
    $username = trim($this->wsRequest->requireNotNullOrEmpty('uid'));

    $transaction->setFee(0);
    $transaction->setAmount($amount);
    $transaction->setUsername($username);
    $transaction->setMerchantId($merchantId);

    //validate if need to create the customer
    if(!$customer->getCustomerId()){
      $customer->validateFromRequest($account, $this->wsRequest);
    }
    $transaction->setCustomerId($customer->getCustomerId());
    $transaction->setAgencyTypeId($customer->getAgencyTypeId());

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //get a sender
    $provider = self::provider();
    $person = $provider->sender();
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
    $provider = self::provider();
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
    $provider = self::provider();
    $provider->status();
  }

}

?>