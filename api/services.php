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

    $taskManager = new TaskManager();
    $taskManager->init();

  }else{
    Log::custom('JobService', 'Services is disabled');
  }

}catch(Exception $ex){
  ExceptionManager::handleException($ex);
}


Log::custom('JobService', 'Finished');
?>