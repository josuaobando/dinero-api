<?php

/**
 * @author Josua
 */
class Manager
{

  /**
   * Account reference
   *
   * @var Account
   */
  private $account;

  /**
   * TblManager reference
   *
   * @var TblManager
   */
  private $tblManager;

  /**
   * new Manager instance
   *
   * @param Account $account
   */
  public function __construct($account)
  {
    $this->account = $account;
    $this->tblManager = TblManager::getInstance();
  }

  /**
   * get a new receiver id from all the available
   *
   * @param float $amount
   * @param int $agencyTypeId
   * @param int $agencyId
   *
   * @return array
   *
   * @throws InvalidStateException
   */
  private function getPersonAvailable($amount, $agencyTypeId, $agencyId)
  {
    $availableList = $this->tblManager->getPersonsAvailable($this->account->getAccountId(), $amount, $agencyTypeId, $agencyId);
    if(!$availableList || !is_array($availableList) || count($availableList) == 0){
      throw new InvalidStateException("there are not names available");
    }
    $selectedId = array_rand($availableList, 1);

    return $availableList[$selectedId];
  }

  /**
   * start and create a new transaction
   *
   * @param WSRequest $wsRequest
   * @param int $transactionType
   *
   * @return \WSResponseOk
   * @throws \InvalidStateException
   */
  public function startTransaction($wsRequest, $transactionType)
  {
    $amount = $wsRequest->requireNumericAndPositive('amount');
    $username = trim($wsRequest->requireNotNullOrEmpty('uid'));

    $transactionStatus = ($transactionType == Transaction::TYPE_RECEIVER) ? Transaction::STATUS_REQUESTED : Transaction::STATUS_SUBMITTED;

    //create customer object
    $customer = new Customer();
    $customer->validateFromRequest($this->account, $wsRequest);

    //create transaction object
    $transaction = new Transaction();
    $transaction->setAccountId($this->account->getAccountId());
    $transaction->setAgencyTypeId($customer->getAgencyTypeId());
    $transaction->setAgencyId($customer->getAgencyId());
    $transaction->setCustomerId($customer->getCustomerId());
    $transaction->setTransactionTypeId($transactionType);
    $transaction->setTransactionStatusId($transactionStatus);
    $transaction->setUsername($username);
    $transaction->setAmount($amount);
    $transaction->setFee(0);

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //check stickiness
    $stickiness = new Stickiness();
    $stickiness->restoreByCustomerId($customer->getCustomerId());

    //get person id from stickiness
    $personId = $stickiness->getPersonId();
    if(!$personId){
      //select and block the person for following transactions
      $personSelected = $this->getPersonAvailable($amount, $customer->getAgencyTypeId(), $customer->getAgencyId());
      $personId = $personSelected['Person_Id'];
    }

    //create person object
    $person = new Person($personId);
    //Check to API Controller and Register Stickiness
    $stickiness->setCustomerId($customer->getCustomerId());
    $stickiness->setCustomer($customer->getCustomer());
    $stickiness->setPersonId($person->getPersonId());
    $stickiness->setPersonalId($person->getPersonalId());
    $stickiness->setPerson($person->getName());
    $stickiness->register();

    //block person
    $person->block();

    //sets personId
    $transaction->setPersonId($person->getPersonId());

    //create transaction after the validation of the data
    $transaction->create();
    if($transaction->getTransactionId()){
      //add stickiness transaction
      if($stickiness->getStickinessId()){
        $stickinessTransaction = new StickinessTransaction();
        $stickinessTransaction->setStickinessId($stickiness->getStickinessId());
        $stickinessTransaction->setVerification($stickiness->getVerification());
        $stickinessTransaction->setVerificationId($stickiness->getVerificationId());
        $stickinessTransaction->setTransactionId($transaction->getTransactionId());
        $stickinessTransaction->add();
      }

      $wsResponse = new WSResponseOk();
      $wsResponse->addElement('transaction', $transaction);
      if($transactionType == Transaction::TYPE_RECEIVER){
        $wsResponse->addElement('sender', $customer);
        $wsResponse->addElement('receiver', $person);
      }else{
        $wsResponse->addElement('sender', $person);
        $wsResponse->addElement('receiver', $customer);
      }
    }else{
      throw new InvalidStateException("The Transaction not has been created. Please, try later!");
    }

    return $wsResponse;
  }

  /**
   * get a new receiver
   *
   * @param WSRequest $wsRequest
   *
   * @return WSResponse
   */
  public function receiver($wsRequest)
  {
    return $this->startTransaction($wsRequest, Transaction::TYPE_RECEIVER);
  }

  /**
   * get a new sender
   *
   * @param WSRequest $wsRequest
   *
   * @return WSResponse
   */
  public function sender($wsRequest)
  {
    return $this->startTransaction($wsRequest, Transaction::TYPE_SENDER);
  }

  /**
   * confirm transaction with the control number
   *
   * @param WSRequest $wsRequest
   *
   * @return WSResponseOk
   *
   * @throws InvalidStateException
   */
  public function confirm($wsRequest)
  {
    //transaction id
    $transactionId = $wsRequest->requireNumericAndPositive('transaction_id');
    $controlNumber = $wsRequest->requireNumericAndPositive('control_number');
    $amount = $wsRequest->requireNumericAndPositive('amount');
    $fee = $wsRequest->getParam('fee');

    //restore and load transaction information
    $transaction = new Transaction();
    $transaction->restore($transactionId);
    if(!$transaction->getTransactionId()){
      throw new InvalidStateException("this transaction not exist or not can be loaded: " . $transactionId);
    }

    $wsRequest->putParam('type', $transaction->getAgencyTypeId());

    //validate customer
    //$customer = new Customer();
    //$customer->validateFromRequest($this->account, $wsRequest);

    if($transaction->getTransactionStatusId() != Transaction::STATUS_REQUESTED && $transaction->getTransactionStatusId() != Transaction::STATUS_REJECTED){
      throw new InvalidStateException("this transaction cannot be confirmed since the current status is: " . $transaction->getTransactionStatus());
    }

    //set new values
    //$transaction->setCustomerId($customer->getCustomerId());
    $transaction->setAmount($amount);
    $transaction->setFee($fee);
    $transaction->setControlNumber($controlNumber);
    $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);
    $transaction->setAccountId($this->account->getAccountId());

    //update transaction after the validation of the data
    $transaction->update();

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transaction', $transaction);

    return $wsResponse;
  }

  /**
   * update transaction data
   *
   * @param WSRequest $wsRequest
   *
   * @return bool
   */
  public function transactionUpdate($wsRequest)
  {
    $transactionId = $wsRequest->requireNumericAndPositive("transactionId");

    $transactionTypeId = $wsRequest->requireNumericAndPositive("transactionTypeId");
    $statusId = $wsRequest->requireNumericAndPositive("status");
    $reason = $wsRequest->getParam("reason", "");
    $note = $wsRequest->getParam("note", "");
    $amount = $wsRequest->requireNumericAndPositive("amount");
    $fee = $wsRequest->getParam("fee");

    if($transactionTypeId == Transaction::TYPE_SENDER && $statusId == Transaction::STATUS_REJECTED){
      $controlNumber = $wsRequest->getParam("controlNumber", '');
    }else{
      $controlNumber = $wsRequest->requireNumericAndPositive("controlNumber");
    }

    //restore and load transaction information
    $transaction = new Transaction();
    $transaction->restore($transactionId);

    //set new values
    $transaction->setTransactionStatusId($statusId);
    $transaction->setReason($reason);
    $transaction->setNote($note);
    $transaction->setAmount($amount);
    $transaction->setFee($fee);
    $transaction->setControlNumber($controlNumber);
    $transaction->setAccountId($this->account->getAccountId());

    if($transaction->getTransactionStatusId() == Transaction::STATUS_APPROVED){

      $stickiness = new Stickiness();
      $stickiness->restoreByTransactionId($transaction->getTransactionId());
      if($stickiness->getStickinessId()){

        //Completed to API Controller
        $stickiness->setControlNumber($controlNumber);
        $stickiness->complete();

        if($stickiness->getStickinessId()){
          //restore stickiness transaction
          $stickinessTransaction = new StickinessTransaction();
          $stickinessTransaction->setTransactionId($transaction->getTransactionId());
          $stickinessTransaction->restore();
          if($stickinessTransaction->getStickinessTransactionId()){
            //update stickiness transaction
            $stickinessTransaction->setVerification($stickiness->getVerification());
            $stickinessTransaction->setVerificationId($stickiness->getVerificationId());
            $stickinessTransaction->setAuthCode($stickiness->getAuthCode());
            $stickinessTransaction->update();
          }
        }

      }

    }

    //update transaction after the validation of the data
    $update = $transaction->update();

    return $update;
  }

  /**
   * gets a new person to the transaction
   *
   * @param int $transactionId
   *
   * @throws InvalidStateException
   *
   * @return Person
   */
  public function getNewPerson($transactionId)
  {
    $transaction = new Transaction();
    $transaction->restore($transactionId);
    if(!$transaction->getTransactionId()){
      throw new InvalidStateException("The transaction [$transactionId] has not been restored, please check!");
    }

    //select new person
    $personSelected = $this->getPersonAvailable($transaction->getAmount(), $transaction->getAgencyTypeId(), $transaction->getAgencyId());
    $personId = $personSelected['Person_Id'];

    //unblock current person
    $currentPerson = new Person($transaction->getPersonId());
    $currentPerson->unblock();

    //block new person
    $newPerson = new Person($personId);
    $newPerson->block();

    //update transaction
    $transaction->setPersonId($newPerson->getPersonId());
    $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);
    $transaction->setAccountId($this->account->getAccountId());
    $transaction->setReason('');
    $success = $transaction->update();

    if(!$success){
      throw new InvalidStateException("The transaction [$transactionId] has not been updated, please check!");
    }

    return $newPerson;
  }

}

?>