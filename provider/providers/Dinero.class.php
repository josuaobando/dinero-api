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
   * new Transaction instance
   */
  public function __construct()
  {
    parent::__construct(self::PROVIDER_ID);
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
    $account = Session::getAccount();
    $tblManager = TblManager::getInstance();
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
   * @throws P2PException
   */
  public function receiver()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    /*
    if($customer->getIsAPI()){
      throw new P2PException("Redirect to API...");
    }
    */

    //validate if customer is blacklisted
    $customer->isBlacklisted();

    //check stickiness
    $stickiness = Session::getStickiness(true);
    $stickiness->restoreByCustomerId($customer->getCustomerId());
    //get person id from stickiness
    $personId = $stickiness->getPersonId();
    if(!$personId){
      //select and block the person for following transactions
      $personSelected = $this->getPersonAvailable($transaction->getAmount(), $transaction->getAgencyTypeId(), $customer->getAgencyId());
      $personId = $personSelected['Person_Id'];
    }
    $person = Session::getPerson($personId);
    //Check to API Controller and Register Stickiness
    $stickiness->setPerson($person->getName());
    $stickiness->setPersonId($person->getPersonId());
    $stickiness->setCustomer($customer->getCustomer());
    $stickiness->setPersonalId($person->getPersonalId());
    $stickiness->setCustomerId($customer->getCustomerId());
    $stickiness->register();
    //------------------end validation

    $transaction->setProviderId(self::PROVIDER_ID);
    $transaction->setAgencyId($customer->getAgencyId());
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