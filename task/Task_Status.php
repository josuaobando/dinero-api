<?php

/**
 * Created by Josua
 * Date: 27/05/2018
 * Time: 20:51
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

    $this->transactions = $this->tblTask->getPendingTransactions();
  }

  /**
   * process task
   */
  public function process()
  {
    $account = Session::getAccount();
    foreach($this->transactions as $transaction){

      $agencyId = $transaction['Agency_Id'];
      if($agencyId == CoreConfig::AGENCY_ID_SATURNO){

        $transactionId = $transaction['Transaction_Id'];
        $transaction = Session::getTransaction();
        $transaction->restore($transactionId);
        $transaction->setModifiedBy($account->getAccountId());

        if($transaction->getTransactionStatusId() == Transaction::STATUS_SUBMITTED){
          $transactionAPI = new TransactionAPI();
          $transactionAPI->getStatus();
        }

      }

    }
  }

}

?>