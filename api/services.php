<?php

/**
 * Created by Josua
 * Date: 26/05/2017
 * Time: 22:51
 */

require_once('system/Startup.class.php');

try{

  Log::custom('JobService', 'Started');

  //start session
  Session::startSession();
  //init account session
  $account = Session::getAccount(null, CoreConfig::USER_SYSTEM);

  if(CoreConfig::CRON_JOBS_TASK_ACTIVE){
    /*
        $tblTask = TblTask::getInstance();
        $tasks = $tblTask->getTask();
        if(count($this->tasks) > 0){
          foreach($this->tasks as $task){

            $taskName = $task['Task'];
            $taskNameClass = "Task_$taskName";
            if(!class_exists($taskNameClass)){
              ExceptionManager::handleException(new InvalidStateException("Class definition not found for: $taskNameClass"));
              break;
            }

            $taskClass = new $taskNameClass($task);
            $taskClass->process();

          }
        }
    */
  }else{

    $system = new System();
    $transactions = $system->transactions(Transaction::STATUS_SUBMITTED, $account->getAccountId());

    foreach($transactions as $transaction){
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

}catch(Exception $ex){
  ExceptionManager::handleException($ex);
}


Log::custom('JobService', 'Finished');
?>