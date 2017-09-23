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
  try
  {
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

  }
  catch(InvalidParameterException $ex)
  {
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
  try
  {
    $countries = Session::getCountries();
    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('countries', $countries);
  }
  catch(InvalidParameterException $ex)
  {
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
  try
  {
    $agencies = Session::getAgencies();
    $wsResponse = new WSResponseOk();
    $wsResponse->addElement('agencies', $agencies);
  }
  catch(InvalidParameterException $ex)
  {
    $wsResponse = new WSResponseError($ex->getMessage());
  }

  return $wsResponse;
}

WSProcessor::process();

?>