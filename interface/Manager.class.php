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
      }catch(Exception $ex){
        //do nothing
      }

      throw new PersonException("There are not names available");
    }
    $selectedId = array_rand($availableList, 1);

    return $availableList[$selectedId];
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

    if($transaction->getProviderId() != Dinero::PROVIDER_ID){
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