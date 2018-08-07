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
   * @throws PersonException
   */
  private function getPersonAvailable($amount, $agencyTypeId, $agencyId)
  {
    $availableList = $this->tblManager->getPersonsAvailable($this->account->getAccountId(), $amount, $agencyTypeId, $agencyId);
    if(!$availableList || !is_array($availableList) || count($availableList) == 0){

      try{
        $subject = "There are not names available";
        $body = "There are not names available. \n\n Agency Type: $agencyTypeId \n\n Agency Id: $agencyId";
        MailManager::sendEmail(MailManager::getRecipients(), $subject, $body);
      }catch(WSException $ex){
        //do nothing
      }

      throw new PersonException("There are not names available");
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
   * @return WSResponseOk
   *
   * @throws TransactionException|P2PException
   */
  public function startTransaction($wsRequest, $transactionType)
  {
    $amount = $wsRequest->requireNumericAndPositive('amount');
    $username = trim($wsRequest->requireNotNullOrEmpty('uid'));
    $merchantId = trim($wsRequest->getParam('merchantId'));

    $transactionStatus = ($transactionType == Transaction::TYPE_RECEIVER) ? Transaction::STATUS_REQUESTED : Transaction::STATUS_SUBMITTED;

    //create customer object
    $customer = Session::getCustomer();
    $customer->validateFromRequest($this->account, $wsRequest);

    //create transaction object
    $transaction = Session::getTransaction();
    $transaction->setAccountId($this->account->getAccountId());
    $transaction->setAgencyTypeId($customer->getAgencyTypeId());
    $transaction->setAgencyId($customer->getAgencyId());
    $transaction->setCustomerId($customer->getCustomerId());
    $transaction->setTransactionTypeId($transactionType);
    $transaction->setTransactionStatusId($transactionStatus);
    $transaction->setMerchantId($merchantId);
    $transaction->setUsername($username);
    $transaction->setAmount($amount);
    $transaction->setFee(0);

    //Sent to API
    if($customer->getAgencyId() == CoreConfig::AGENCY_ID_SATURNO){
      if(CoreConfig::SATURNO_ACTIVE){
        throw new P2PException("Redirect to API...");
      }else{
        throw new P2PException("Due to external factors, we cannot give this Customer a name.");
      }
    }

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //------------------begin validation
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
    //------------------end validation

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
      throw new TransactionException("The Transaction not has been created. Please, try later!");
    }

    return $wsResponse;
  }

  /**
   * start and create a new API transaction
   *
   * @param WSRequest $wsRequest
   * @param int $transactionType
   *
   * @return WSResponse
   *
   * @throws APIException|TransactionException
   */
  public function startAPITransaction($wsRequest, $transactionType)
  {
    $amount = $wsRequest->requireNumericAndPositive('amount');
    $username = trim($wsRequest->requireNotNullOrEmpty('uid'));
    $merchantId = trim($wsRequest->getParam('merchantId'));

    $transactionStatus = ($transactionType == Transaction::TYPE_RECEIVER) ? Transaction::STATUS_REQUESTED : Transaction::STATUS_SUBMITTED;

    //create transaction object
    $transaction = Session::getTransaction();
    //create customer object
    $customer = Session::getCustomer();
    if(!$customer->getCustomerId()){
      $customer->validateFromRequest($this->account, $wsRequest);

      $transaction->setAccountId($this->account->getAccountId());
      $transaction->setAgencyTypeId($customer->getAgencyTypeId());
      $transaction->setCustomerId($customer->getCustomerId());
      $transaction->setTransactionTypeId($transactionType);
      $transaction->setTransactionStatusId($transactionStatus);
      $transaction->setMerchantId($merchantId);
      $transaction->setUsername($username);
      $transaction->setAmount($amount);
      $transaction->setFee(0);
    }

    //change agency to customer
    if($customer->getAgencyId() != CoreConfig::AGENCY_ID_SATURNO && $customer->getAgencyId() != CoreConfig::AGENCY_ID_SATURNO_RIA){
      if($customer->getAgencyTypeId() == Transaction::AGENCY_RIA){
        $customer->setAgencyId(CoreConfig::AGENCY_ID_SATURNO_RIA);
      }else{
        $customer->setAgencyId(CoreConfig::AGENCY_ID_SATURNO);
      }
      $customer->setIsAPI(1);
    }

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    $transactionAPI = new TransactionAPI();
    if($transaction->getTransactionTypeId() == Transaction::TYPE_RECEIVER){
      $person = $transactionAPI->getName();
    }else{
      $person = $transactionAPI->getSender();
    }

    if(!$person || !$person->getPersonId()){
      throw new APIException($transactionAPI->getApiMessage());
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
      if($transactionType == Transaction::TYPE_RECEIVER){
        $wsResponse->addElement('sender', $customer);
        $wsResponse->addElement('receiver', $person);
      }else{
        $wsResponse->addElement('sender', $person);
        $wsResponse->addElement('receiver', $customer);
      }

    }else{
      throw new TransactionException("The Transaction not has been created. Please, try later!");
    }

    return $wsResponse;
  }

  /**
   * get a new receiver (deposits)
   *
   * @param WSRequest $wsRequest
   *
   * @return WSResponse
   *
   * @throws Exception
   */
  public function receiver($wsRequest)
  {
    try{
      return $this->startTransaction($wsRequest, Transaction::TYPE_RECEIVER);
    }catch(P2PException $ex){
      $agencyTypeId = $wsRequest->getParam('type');
      if(CoreConfig::SATURNO_ACTIVE && $agencyTypeId == Transaction::AGENCY_MONEY_GRAM){
        return $this->startAPITransaction($wsRequest, Transaction::TYPE_RECEIVER);
      }else{
        throw $ex;
      }
    }
  }

  /**
   * get a new sender (Payouts)
   *
   * @param WSRequest $wsRequest
   *
   * @return WSResponse
   */
  public function sender($wsRequest)
  {
    $agencyTypeId = $wsRequest->getParam('type');
    if(CoreConfig::SATURNO_ACTIVE && $agencyTypeId == Transaction::AGENCY_MONEY_GRAM){
      return $this->startAPITransaction($wsRequest, Transaction::TYPE_SENDER);
    }else{
      return $this->startTransaction($wsRequest, Transaction::TYPE_SENDER);
    }
  }

  /**
   * confirm transaction with the control number
   *
   * @param WSRequest $wsRequest
   *
   * @return WSResponseOk
   *
   * @throws TransactionException
   */
  public function confirm($wsRequest)
  {
    //transaction id
    $transactionId = $wsRequest->requireNumericAndPositive('transaction_id');
    $controlNumber = $wsRequest->requireNumericAndPositive('control_number');
    $amount = $wsRequest->requireNumericAndPositive('amount');
    $fee = $wsRequest->getParam('fee', 0);

    //restore and load transaction information
    $transaction = Session::getTransaction();
    $transaction->restore($transactionId);
    if(!$transaction->getTransactionId()){
      throw new TransactionException("This transaction not exist or not can be loaded: " . $transactionId);
    }

    $wsRequest->putParam('type', $transaction->getAgencyTypeId());

    if($transaction->getTransactionStatusId() != Transaction::STATUS_REQUESTED && $transaction->getTransactionStatusId() != Transaction::STATUS_REJECTED){
      if($transaction->getTransactionStatusId() == Transaction::STATUS_CANCELED){
        throw new TransactionException("The transaction has expired. Valid time is 48 hours to confirm.");
      }else{
        throw new TransactionException("Transaction cannot be confirmed since the current status is: " . $transaction->getTransactionStatus());
      }
    }

    //validate customer
    $customerRequest = new Customer();
    $customerRequest->restoreFromRequest($wsRequest);
    $newCustomerName = strtoupper($customerRequest->getCustomer());
    $customerTransaction = Session::getCustomer($transaction->getCustomerId());
    $originalCustomerName = strtoupper($customerTransaction->getCustomer());

    $percent = Util::similarPercent($newCustomerName, $originalCustomerName);
    if($newCustomerName != $originalCustomerName && $percent >= CoreConfig::CUSTOMER_SIMILAR_PERCENT){

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

    //set new values
    $transaction->setAmount($amount);
    $transaction->setFee($fee);
    $transaction->setControlNumber($controlNumber);
    $transaction->setModifiedBy($this->account->getAccountId());

    //confirm in Saturno
    if($transaction->getAgencyId() == CoreConfig::AGENCY_ID_SATURNO){
      $transactionAPI = new TransactionAPI();
      $confirm = $transactionAPI->confirm();
      if($confirm){
        $transaction->setApiTransactionId($transactionAPI->getApiTransactionId());
      }else{
        throw new TransactionException("Transaction cannot be confirmed. Please try again in a few minutes!");
      }
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
   * @param WSRequest $wsRequest
   * @param bool $webRequest
   *
   * @return WSResponse|Transaction
   *
   * @throws InvalidStateException
   */
  public function information($wsRequest, $webRequest = false)
  {
    $account = Session::getAccount();
    //transaction id
    $transactionId = $wsRequest->requireNumericAndPositive('transaction_id');

    $transaction = Session::getTransaction();
    $transaction->restore($transactionId);

    //get transaction status from Saturno
    if($webRequest){
      if($transaction->getAgencyId() == CoreConfig::AGENCY_ID_SATURNO || $transaction->getAgencyId() == CoreConfig::AGENCY_ID_SATURNO_RIA){
        if($transaction->getTransactionStatusId() == Transaction::STATUS_SUBMITTED){
          $transaction->setModifiedBy($account->getAccountId());
          $transactionAPI = new TransactionAPI();
          $transactionAPI->getStatus();
        }
      }
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
   * @param WSRequest $wsRequest
   *
   * @return int
   *
   * @throws InvalidStateException+
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
    $transaction = Session::getTransaction();
    $transaction->restore($transactionId);
    if(!$transaction->getTransactionId()){
      throw new InvalidStateException("The transaction [$transactionId] has not been restored, please check!");
    }

    //validation to Saturno transaction
    if($transaction->getAgencyId() == CoreConfig::AGENCY_ID_SATURNO){
      throw new InvalidStateException("Transaction cannot be Modify. Saturno Transaction!");
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
    $transaction->setModifiedBy($this->account->getAccountId());

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

  /**
   * gets a new person to the transaction
   *
   * @param $transactionId
   *
   * @return Person
   *
   * @throws TransactionException
   */
  public function getNewPerson($transactionId)
  {
    $transaction = new Transaction();
    $transaction->restore($transactionId);
    if(!$transaction->getTransactionId()){
      throw new TransactionException("The transaction [$transactionId] has not been restored, please check!");
    }

    //validation to Saturno transaction
    if($transaction->getAgencyId() == CoreConfig::AGENCY_ID_SATURNO){
      throw new TransactionException("Transaction cannot be Modify. Saturno Transaction!");
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
    $transaction->setModifiedBy($this->account->getAccountId());
    $transaction->setReason('');
    $success = $transaction->update();

    if(!$success){
      throw new TransactionException("The transaction [$transactionId] has not been updated, please check!");
    }

    return $newPerson;
  }

}

?>