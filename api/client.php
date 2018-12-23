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
    $sessionTracker = new SessionTracker(Session::$sid);

    if($account->isAuthenticated()){
      $wsResponse = new WSResponseOk();
      $wsResponse->addElement('account', $account);
      $wsResponse->addElement('token', Session::$sid);
      if(!$sessionTracker->activeByUsername($username)){
        $sessionTracker->add($wsRequest, 'authenticate.success');
      }else{
        $sessionTracker->add($wsRequest, 'authenticate.active');
        $wsResponse = new WSResponseError('Invalid information!', 'authenticate.active');
      }
    }elseif($account->getAccountId()){
      $sessionTracker->add($wsRequest, 'authenticate.reject');
      $wsResponse = new WSResponseError('Invalid information!', 'authenticate.reject');
    }else{
      $sessionTracker->add($wsRequest, 'authenticate.fail');
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
    $sessionTracker = new SessionTracker(Session::$sid);
    $sessionTracker->close();

    $destroy = Session::destroySession();
    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('logout', $destroy);
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
  }

  return $wsResponse;
}

/**
 * update password
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function changePassword($wsRequest)
{
  try{
    $newPassword = trim($wsRequest->requireNotNullOrEmpty('new'));
    $confirmPassword = trim($wsRequest->requireNotNullOrEmpty('confirm'));

    $account = Session::getAccount();
    $sessionTracker = new SessionTracker(Session::$sid, true);

    $newPassword = str_replace("~@", "", $newPassword);
    $newPassword = str_replace("@~", "", $newPassword);
    $confirmPassword = str_replace("~!", "", $confirmPassword);
    $confirmPassword = str_replace("!~", "", $confirmPassword);

    $wsResponse = new WSResponseOk();
    if($newPassword === $confirmPassword){
      if($account->changePassword($newPassword)){
        $sessionTracker->add($wsRequest, 'changePassword.success');
        $wsResponse->addElement('changed', true);
        $wsResponse->addElement('result', 'user.changePassword.success');
      }else{
        $sessionTracker->add($wsRequest, 'changePassword.fail');
        $wsResponse->addElement('changed', false);
        $wsResponse->addElement('result', 'user.changePassword.fail');
      }
    }else{
      $sessionTracker->add($wsRequest, 'changePassword.reject');
      $wsResponse->addElement('changed', false);
      $wsResponse->addElement('result', 'user.changePassword.reject');
    }
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
function users($wsRequest)
{
  try{
    Session::getAccount();
    $appManager = new AppManager();
    $users = $appManager->users();

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('users', $users);
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
 * get countries
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function countries($wsRequest)
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
function agencies($wsRequest)
{
  try{
    Session::getAccount();
    $appManager = new AppManager();
    $agencies = $appManager->agencies();

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
 * get companies
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function companies($wsRequest)
{
  try{
    $account = Session::getAccount();
    $appManager = new AppManager();
    $agencies = $appManager->companies($account->getAccountId());

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('companies', $agencies);
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
  }

  return $wsResponse;
}

/**
 * get transaction status
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function transactionStatus($wsRequest)
{
  try{
    Session::getAccount();
    $appManager = new AppManager();
    $status = $appManager->transactionStatus();

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transactionStatus', $status);
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
function attempts($wsRequest)
{
  try{
    Session::getAccount();
    $appManager = new AppManager();
    $attempts = $appManager->transactionAttempts();

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('attempts', $attempts);
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
function rejections($wsRequest)
{
  try{
    Session::getAccount();
    $appManager = new AppManager();
    $rejections = $appManager->transactionDeclined();

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('rejections', $rejections);
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
    $transactionType = $wsRequest->getParam("transactionTypeId", "0");
    $agencyType = $wsRequest->getParam("agencyTypeId", "0");
    $username = $wsRequest->getParam("username", "");

    $agencyList = $wsRequest->getParam("agencies");
    if (is_array($agencyList)){
      $agencyList = implode(",", $agencyList);
    }

    $statusList = $wsRequest->getParam("status");
    if (is_array($statusList)){
      $statusList = implode(",", $statusList);
    }

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
        return $wsResponse;
      }

    }

    $appManager = new AppManager();
    $dataReport = $appManager->transactionReport($statusList, $transactionType, $agencyType, $agencyList, $account->getAccountId(), $beginDate, $endDate, $controlNumber, $username, $transactionId, $merchantTransId, 0);
    $transactions = $dataReport['transactions'];
    $summary = $dataReport['summary'];

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('transactions', $transactions);
    $wsResponse->addElement('summary', $summary);
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
    $wsResponse->addElement('result', 'processing.p2p.updated');
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception.parameter');
  }catch(SessionException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
  }catch(P2PException $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), 'processing.p2p.rejected');
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