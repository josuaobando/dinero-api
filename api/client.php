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
      $account->sessionTracker($wsRequest, Session::$sid, 'authenticate.success');
    }elseif($account->getAccountId()){
      $account->sessionTracker($wsRequest, Session::$sid, 'authenticate.reject');
      $wsResponse = new WSResponseError('Invalid information!', 'authenticate.reject');
    }else{
      $account->sessionTracker($wsRequest, Session::$sid, 'authenticate.fail');
      $wsResponse = new WSResponseError('Invalid information!', 'authenticate.fail');
    }

  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception.parameter');
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
  }

  return $wsResponse;
}

/**
 * @param WSRequest $wsRequest
 *
 * @return WSResponseError|WSResponseOk
 */
function logout($wsRequest)
{
  try{
    $destroy = Session::destroySession();
    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('logout', $destroy);
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
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
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
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
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
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
    $appManager = new AppManager();
    $transactions = $appManager->transactions($statusId, $account->getAccountId());

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transactions', $transactions);
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception.parameter');
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
  }

  return $wsResponse;
}

/**
 * @param WSRequest $wsRequest
 *
 * @return WSResponseError|WSResponseOk
 */
function transactionReport($wsRequest)
{
  try{
    $account = Session::getAccount();

    $beginDate = $wsRequest->getParam("beginDate", "");
    $endDate = $wsRequest->getParam("endDate", "");
    $statusId = $wsRequest->getParam("statusId", "3");
    $statusId = ($statusId == "-1") ? "0" : $statusId;
    $transactionType = $wsRequest->getParam("transactionTypeId", "0");
    $agencyType = $wsRequest->getParam("agencyTypeId", "0");
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

    $appManager = new AppManager();
    $dataReport = $appManager->transactionReport($statusId, $transactionType, $agencyType, $agencyId, $account->getAccountId(), $beginDate, $endDate, $controlNumber, $username, $transactionId, $merchantTransId, 0);
    $transactions = $dataReport['transactions'];
    $summary = $dataReport['summary'];
    $total = $dataReport['total'][0]['total'];

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transactions', $transactions);
    $wsResponse->addElement('summary', $summary);
    $wsResponse->addElement('total', $total);
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception.parameter');
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
  }

  return $wsResponse;
}

/**
 * @param WSRequest $wsRequest
 *
 * @return WSResponseError|WSResponseOk
 */
function transactionUpdate($wsRequest)
{
  try{
    Session::getAccount();

    $providerTransaction = new ProviderTransaction($wsRequest);
    $update = $providerTransaction->transactionUpdate();

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('update', $update);
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception.parameter');
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
  }

  return $wsResponse;
}

/**
 * @param WSRequest $wsRequest
 *
 * @return WSResponseError|WSResponseOk
 */
function transaction($wsRequest)
{
  try{
    Session::getAccount();

    $transactionId = $wsRequest->requireNumericAndPositive("transactionId");

    $tblTransaction = TblTransaction::getInstance();
    $transactionData = $tblTransaction->getTransaction($transactionId);

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transaction', $transactionData);
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception.parameter');
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
  }

  return $wsResponse;
}

WSProcessor::process();

?>