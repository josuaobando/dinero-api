<?php

/**
 * @author: josua
 */
class Task_Status extends Task
{

  /**
   * @var array
   */
  private $transactions = array();

  public function init($setting)
  {
    parent::init($setting);
  }

  /**
   * process task
   */
  public function process()
  {
    $account = Session::getAccount();
    $this->transactions = $this->tblTask->getPendingTransactions();
    foreach($this->transactions as $transaction){

      $transactionId = $transaction['Transaction_Id'];
      $transaction = Session::getTransaction();
      $transaction->restore($transactionId);
      $transaction->setModifiedBy($account->getAccountId());

      //check status
      $provider = $transaction->getProvider();
      $provider->status();
    }
  }

}

?>