<?php

/**
 * Created by Josua
 * Date: 26/05/2017
 * Time: 22:51
 */

require_once('system/Startup.class.php');

Log::custom('Job', 'Service has started');

$tblStickiness = TblStickiness::getInstance();

$stickinessPending = $tblStickiness->getPending();
foreach($stickinessPending as $pending){
  try{
    $customerId = $pending['Customer_Id'];
    $personId = $pending['Person_Id'];

    $person = new Person($personId);
    $stickiness = new Stickiness();
    $stickiness->setPersonId($person->getPersonId());
    $stickiness->setPersonalId($person->getPersonalId());
    $stickiness->setPerson($person->getName());

    $stickiness->restoreByCustomerId($customerId);
    $stickiness->register();

    $logData = Util::toString($pending);
    Log::custom('Job', 'Check Pending Transaction', $logData);
  }catch(Exception $ex){
    ExceptionManager::handleException($ex);
  }
}

$stickinessApproved = $tblStickiness->getApproved();
foreach($stickinessApproved as $approved){
  try{
    $customerId = $approved['Customer_Id'];
    $personId = $approved['Person_Id'];
    $stickinessId = $approved['Stickiness_Id'];
    $transactionId = $approved['Transaction_Id'];
    $controlNumber = $approved['ControlNumber'];

    $person = new Person($personId);

    $stickiness = new Stickiness();
    $stickiness->setStickinessId($stickinessId);
    $stickiness->setPersonId($person->getPersonId());
    $stickiness->setPersonalId($person->getPersonalId());
    $stickiness->setPerson($person->getName());
    $stickiness->setControlNumber($controlNumber);

    $stickiness->restore();
    $stickiness->complete();

    //restore stickiness transaction
    $stickinessTransaction = new StickinessTransaction();
    $stickinessTransaction->setTransactionId($transactionId);
    $stickinessTransaction->restore();
    if($stickinessTransaction->getStickinessTransactionId()){
      //update stickiness transaction
      $stickinessTransaction->setVerification($stickiness->getVerification());
      $stickinessTransaction->setVerificationId($stickiness->getVerificationId());
      $stickinessTransaction->setAuthCode($stickiness->getAuthCode());
      $stickinessTransaction->update();
    }

    $logData = Util::toString($approved);
    Log::custom('Job', 'Check Approved Transaction', $logData);
  }catch(Exception $ex){
    ExceptionManager::handleException($ex);
  }

}

Log::custom('Job', 'Service has finish');
?>