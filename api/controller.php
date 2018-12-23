<?php

require_once('system/Startup.class.php');

/**
 * here is where we start processing the request
 * checking what function is being requested
 */
function startController()
{
  //check if the request was sent using json
  $wsRequest = new WSRequest(json_decode(file_get_contents("php://input"), true));
  if($wsRequest->isEmpty()){
    //if empty try with the regular request
    $wsRequest->overwriteRequest($_REQUEST);
  }

  //prefix in order to avoid a conflict with the function names in the webservices
  $prefix = "f_";

  //check if the requested function is valid
  $action = $wsRequest->getParam('f');
  if(!empty($action)){

    //get session id
    $sessionId = $wsRequest->getParam('token');
    if($sessionId){

      try{
        Session::startSession($sessionId);
        $account = Session::getAccount();
        if($account->isAuthenticated()){
          $sessionTracker = new SessionTracker($sessionId, true);
          if($sessionTracker->active()){
            //update bd session active
            $sessionTracker->update();
            //call the proper function
            if(function_exists($prefix . $action)){
              //call the function and exit since the function will do the whole work
              call_user_func($prefix . $action);
              exit();
            }else{
              //this section is to handle the invalid function error
              $wsResponse = new WSResponseError("Invalid action ('$action')", "invalid.action");
            }
          }else{
            Session::destroySession();
            $wsResponse = new WSResponseError('Invalid Request', 'invalid.session.expired');
          }
        }else{
          //this section is to handle the invalid function error
          if($account->getAccountId()){
            $wsResponse = new WSResponseError('Invalid Request', 'authenticate.reject');
          }else{
            $wsResponse = new WSResponseError('Invalid Request', 'authenticate.fail');
          }
        }
      }catch(SessionException $ex){
        $sessionTracker = new SessionTracker($sessionId);
        $sessionTracker->close();
        $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.session.expired');
      }catch(Exception $ex){
        $wsResponse = new WSResponseError($ex->getMessage(), 'invalid.exception');
      }

    }elseif($action === 'authenticate'){
      //call the proper function
      if(function_exists($prefix . $action)){
        //call the function and exit since the function will do the whole work
        call_user_func($prefix . $action);
        exit();
      }else{
        //this section is to handle the invalid function error
        $wsResponse = new WSResponseError("Invalid action ('$action')", "invalid.action");
      }
    }else{
      $wsResponse = new WSResponseError('Invalid Request', 'invalid.session.empty');
    }

  }else{
    $wsResponse = new WSResponseError('Action is empty', "invalid.action.empty");
  }

  //set the header of the response
  Util::putResponseHeaders(WSResponse::FORMAT_JSON, 'UTF-8');

  //send the object to the output converting it to string
  echo $wsResponse->toString(WSResponse::FORMAT_JSON);
}

/**
 * login account
 */
function f_authenticate()
{
  require_once('api/client.php');
}

/**
 * logout account
 */
function f_logout()
{
  require_once('api/client.php');
}

/**
 * update password
 */
function f_changePassword()
{
  require_once('api/client.php');
}

/**
 * get users
 */
function f_users()
{
  require_once('api/client.php');
}

/**
 * get countries
 */
function f_countries()
{
  require_once('api/client.php');
}

/**
 * get agencies
 */
function f_agencies()
{
  require_once('api/client.php');
}

/**
 * get companies
 */
function f_companies()
{
  require_once('api/client.php');
}

/**
 * get transaction status
 */
function f_transactionStatus()
{
  require_once('api/client.php');
}

/**
 * get transactions
 */
function f_transactions()
{
  require_once('api/client.php');
}

/**
 * get report data
 */
function f_transactionReport()
{
  require_once('api/client.php');
}

/**
 * update transaction
 */
function f_transactionUpdate()
{
  require_once('api/client.php');
}

/**
 * get transaction data
 */
function f_transaction()
{
  require_once('api/client.php');
}

/**
 * get attempts transaction
 */
function f_attempts()
{
  require_once('api/client.php');
}

/**
 * get transaction rejected
 */
function f_rejections()
{
  require_once('api/client.php');
}

/**
 * start controller
 */
startController();

?>