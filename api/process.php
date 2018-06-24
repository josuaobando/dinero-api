<?php

require_once('system/Startup.class.php');

/**
 * account login
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function login($wsRequest)
{
  try{
    $username = trim($wsRequest->requireNotNullOrEmpty('username'));
    $password = trim($wsRequest->requireNotNullOrEmpty('password'));

    $account = new Account($username);
    $account->authenticate($password);

    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('account', $account);
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }

  return $wsResponse;
}

/**
 * get a new name
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function name($wsRequest)
{
  try{
    $username = trim($wsRequest->requireNotNullOrEmpty('merchant_user'));
    $apiUser = trim($wsRequest->requireNotNullOrEmpty('api_user'));
    $apiPass = trim($wsRequest->requireNotNullOrEmpty('api_pass'));

    $account = new Account($username);
    $account->authenticateAPI($apiUser, $apiPass);

    if($account->isAuthenticated()){
      $manager = new Manager($account);
      $wsResponse = $manager->receiver($wsRequest);
    }else{
      $wsResponse = new WSResponseError("authentication failed");
    }
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), $ex->getCode());
  }

  return $wsResponse;
}

/**
 * get a new sender
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function sender($wsRequest)
{
  try{
    $username = trim($wsRequest->requireNotNullOrEmpty('merchant_user'));
    $apiUser = trim($wsRequest->requireNotNullOrEmpty('api_user'));
    $apiPass = trim($wsRequest->requireNotNullOrEmpty('api_pass'));

    $account = new Account($username);
    $account->authenticateAPI($apiUser, $apiPass);

    if($account->isAuthenticated()){
      $manager = new Manager($account);
      $wsResponse = $manager->sender($wsRequest);
      $wsResponse->removeElement('sender');
    }else{
      $wsResponse = new WSResponseError("authentication failed");
    }
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), $ex->getCode());
  }

  return $wsResponse;
}

/**
 * get a new name
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function confirm($wsRequest)
{
  try{
    $username = trim($wsRequest->requireNotNullOrEmpty('merchant_user'));
    $apiUser = trim($wsRequest->requireNotNullOrEmpty('api_user'));
    $apiPass = trim($wsRequest->requireNotNullOrEmpty('api_pass'));

    $account = new Account($username);
    $account->authenticateAPI($apiUser, $apiPass);

    if($account->isAuthenticated()){
      $manager = new Manager($account);
      $wsResponse = $manager->confirm($wsRequest);
    }else{
      $wsResponse = new WSResponseError("authentication failed");
    }
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), $ex->getCode());
  }

  return $wsResponse;
}

/**
 * get transaction information
 *
 * @param WSRequest $wsRequest
 *
 * @return WSResponse
 */
function information($wsRequest)
{
  try{
    $username = trim($wsRequest->requireNotNullOrEmpty('merchant_user'));
    $apiUser = trim($wsRequest->requireNotNullOrEmpty('api_user'));
    $apiPass = trim($wsRequest->requireNotNullOrEmpty('api_pass'));

    $account = new Account($username);
    $account->authenticateAPI($apiUser, $apiPass);
    if($account->isAuthenticated()){

      $manager = new Manager($account);
      $wsResponse = $manager->information($wsRequest);

    }else{
      $wsResponse = new WSResponseError("authentication failed");
    }
  }catch(InvalidParameterException $ex){
    $wsResponse = new WSResponseError($ex->getMessage());
  }catch(Exception $ex){
    $wsResponse = new WSResponseError($ex->getMessage(), $ex->getCode());
  }

  return $wsResponse;
}

WSProcessor::process();

?>