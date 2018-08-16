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

      if($transaction->getAgencyTypeId() == Transaction::AGENCY_TYPE_RIA){
        $transactionAPI = new Ria();
      }else{
        $transactionAPI = new TransactionAPI();
      }

      $transactionAPI->status();
    }
  }

}

?>