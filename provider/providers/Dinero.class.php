<?php

/**
 * @author Josua
 */
class Dinero extends Provider
{

  /**
   * ID
   */
  const PROVIDER_ID = 1;

  /**
   * Dinero constructor.
   *
   * @throws InvalidStateException
   */
  public function __construct()
  {
    parent::__construct(self::PROVIDER_ID);
  }

  /**
   * get a new receiver id from all the available
   *
   * @param $amount
   * @param $agencyTypeId
   *
   * @return int
   *
   * @throws PersonException
   * @throws SessionException
   * @throws TransactionException
   */
  private function getPersonAvailable($amount, $agencyTypeId)
  {
    $account = Session::getAccount();
    $tblManager = TblManager::getInstance();

    $agencyId = $tblManager->getNextAgency($agencyTypeId, $account->getAccountId());
    if(!$agencyId){
      $this->apiStatus = self::REQUEST_ERROR;
      $this->apiMessage = "No agency available";
      throw new TransactionException("No agency available");
    }

    $availableList = $tblManager->getPersonsAvailable($account->getAccountId(), $amount, $agencyTypeId, $agencyId);
    if(!$availableList || !is_array($availableList) || count($availableList) == 0){

      $subject = "There are not names available";
      $body = "There are not names available. <br><br> Agency Type: $agencyTypeId <br><br> Agency Id: $agencyId";
      MailManager::sendEmail(MailManager::getRecipients(), $subject, $body);

      $this->apiStatus = self::REQUEST_ERROR;
      $this->apiMessage = "There are not names available";
      throw new PersonException("There are not names available");
    }
    $selectedId = array_rand($availableList, 1);

    return $availableList[$selectedId];
  }

  /**
   * get name for the customer
   *
   * @return Person
   *
   * @throws Exception|P2PAgencyException|P2PException
   */
  public function receiver()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    $customerId = $customer->getCustomerId();
    $agencyTypeId = $transaction->getAgencyTypeId();

    //validate if customer is blacklisted
    $customer->isBlacklisted();

    //evaluate limits
    $limit = new Limit($transaction, $customer);
    $limit->evaluate();

    //check stickiness
    $stickiness = Session::getStickiness(true);
    $stickiness->restoreByCustomerId($customerId, $agencyTypeId);
    //get person id from stickiness
    $personId = $stickiness->getPersonId();
    if(!$personId){
      //select and block the person for following transactions
      $personSelected = $this->getPersonAvailable($transaction->getAmount(), $agencyTypeId);
      $personId = $personSelected['Person_Id'];
    }

    $person = Session::getPerson($personId);
    //Check to API Controller and Register Stickiness
    $stickiness->setPerson($person->getName());
    $stickiness->setPersonId($person->getPersonId());
    $stickiness->setCustomer($customer->getCustomer());
    $stickiness->setPersonalId($person->getPersonalId());
    $stickiness->setCustomerId($customer->getCustomerId());
    $stickiness->setAgencyP2P($person->getAgencyIdRemote());

    try{
      $stickiness->register();
    }catch(P2PAgencyException $agencyException){
      $update = $person->updatePersonList();
      if($update){
        Log::custom(__CLASS__, $person->getName(). "has been changed of person list");
        $person = new Person($person->getPersonId());
        if($person->getAgencyIdRemote()){
          $stickiness->setAgencyP2P($person->getAgencyIdRemote());
          $stickiness->register();
        }else{
          throw $agencyException;
        }
      }else{
        throw $agencyException;
      }
    }catch(Exception $exception){
      throw $exception;
    }
    //------------------end validation

    $transaction->setProviderId(self::PROVIDER_ID);
    $transaction->setAgencyId($person->getAgencyId());
    return Session::setPerson($person);
  }

  /**
   * get name for the customer
   *
   * @return Person
   *
   * @throws InvalidStateException
   */
  public function sender()
  {
    return new Person();
  }

  /**
   * Submit or Re-Submit transaction
   *
   * @return bool
   */
  public function confirm()
  {
    return true;
  }

  /**
   * get transaction status
   *
   * @return bool
   */
  public function status()
  {
    return true;
  }

  /**
   * @see Provider::stickiness()
   *
   * @return bool
   */
  public function stickiness()
  {
    $stickiness = Session::getStickiness();
    $transaction = Session::getTransaction();
    //add stickiness transaction
    $stickinessTransaction = new StickinessTransaction();
    $stickinessTransaction->setStickinessId($stickiness->getStickinessId());
    $stickinessTransaction->setVerification($stickiness->getVerification());
    $stickinessTransaction->setTransactionId($transaction->getTransactionId());
    $stickinessTransaction->setVerificationId($stickiness->getVerificationId());
    $stickinessTransaction->add();
  }

}

?>