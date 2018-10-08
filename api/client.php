<?php

require_once('system/Startup.class.php');

/**
 * account login
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function authenticate($wsRequest)
{
  try{
    $username = trim($wsRequest->requireNotNullOrEmpty('username'));
    $password = trim($wsRequest->requireNotNullOrEmpty('password'));

    $account = Session::getAccount($username);
    $account->authenticate($password);

    if($account->isAuthenticated()){
      $wsResponse = new WSResponseOk();
      $wsResponse->addElement('account', $account);
      $wsResponse->addElement('token', Session::$sid);
    }else{
      $wsResponse = new WSResponseError('Invalid information!');
    }

  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }

  return $wsResponse;
}

/**
 * get countries
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function getCountries($wsRequest)
{
  try{
    Session::getAccount();
    $countries = Session::getCountries();
    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('countries', $countries);
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }

  return $wsResponse;
}

/**
 * get agencies
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function getAgencies($wsRequest)
{
  try{
    Session::getAccount();
    $agencies = Session::getAgencies();
    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('agencies', $agencies);
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }

  return $wsResponse;
}

/**
 * @param WSRequest $wsRequest
 *
 * @return WSResponseError|WSResponseOk
 */
function transactions($wsRequest)
{
  try{
    $account = Session::getAccount();

    $statusId = $wsRequest->requireNotNullOrEmpty('statusId');
    $system = new System();
    $transactions = $system->transactions($statusId, $account->getAccountId());

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transactions', $transactions);
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }

  return $wsResponse;
}

/**
 * @param WSRequest $wsRequest
 *
 * @return WSResponseError|WSResponseOk
 */
function report($wsRequest)
{
  try{
    $account = Session::getAccount();

    $currentPage = $wsRequest->getParam("currentPage", "1");
    $beginDate = $wsRequest->getParam("beginDate", "");
    $endDate = $wsRequest->getParam("endDate", "");
    $statusId = $wsRequest->getParam("statusId", "3");
    $statusId = ($statusId == "-1") ? "0" : $statusId;
    $transactionType = $wsRequest->getParam("transactionType", "0");
    $agencyType = $wsRequest->getParam("agencyType", "0");
    $agencyId = $wsRequest->getParam("agencyId", "0");
    $username = $wsRequest->getParam("username", "");

    //specific
    $typeFilter = $wsRequest->getParam("filterType", '1');
    if($typeFilter == '2'){

      $transactionId = $wsRequest->getParam("transactionId", "");
      $controlNumber = $wsRequest->getParam("controlNumber", "");
      $merchantTransId = $wsRequest->getParam("merchantTransId", "");

      if(!$transactionId && !$controlNumber && !$merchantTransId){
        $wsResponse = new WSResponseOk();
        $wsResponse->addElement('transactions', array());
        $wsResponse->addElement('summary', array());
        $wsResponse->addElement('total', 0);
        return $wsResponse;
      }

    }

    $system = new System();
    $dataReport = $system->transactionsReport($statusId, $transactionType, $agencyType, $agencyId, $account->getAccountId(), $beginDate, $endDate, $controlNumber, $username, $transactionId, $merchantTransId, $currentPage);
    $transactions = $dataReport['transactions'];
    $summary = $dataReport['summary'];
    $total = $dataReport['total'][0]['total'];

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transactions', $transactions);
    $wsResponse->addElement('summary', $summary);
    $wsResponse->addElement('total', $total);
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }

  return $wsResponse;
}

WSProcessor::process();

?>