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
   * TblManager reference
   *
   * @var TblManager
   */
  private $tblManager;

  /**
   * @var int
   */
  private $apiTransactionId;

  /**
   * @var string
   */
  private $apiMessage;

  /**
   * @var string
   */
  private $apiStatus;

  /**
   * @return int
   */
  public function getApiTransactionId()
  {
    return $this->apiTransactionId;
  }

  /**
   * @param int $apiTransactionId
   */
  public function setApiTransactionId($apiTransactionId)
  {
    $this->apiTransactionId = $apiTransactionId;
  }

  /**
   * @return string
   */
  public function getApiMessage()
  {
    return $this->apiMessage;
  }

  /**
   * @return string
   */
  public function getApiStatus()
  {
    return $this->apiStatus;
  }

  /**
   * new Transaction instance
   */
  public function __construct()
  {
    parent::__construct(self::PROVIDER_ID);
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
    $account = Session::getAccount();
    $availableList = $this->tblManager->getPersonsAvailable($account->getAccountId(), $amount, $agencyTypeId, $agencyId);
    if(!$availableList || !is_array($availableList) || count($availableList) == 0){

      try{
        $subject = "There are not names available";
        $body = "There are not names available. \n\n Agency Type: $agencyTypeId \n\n Agency Id: $agencyId";
        MailManager::sendEmail(MailManager::getRecipients(), $subject, $body);
      }catch(Exception $ex){
        //do nothing
      }

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
   */
  public function receiver()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

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

    return $person;
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