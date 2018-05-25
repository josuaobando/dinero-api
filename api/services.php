<?php

/**
 * Created by Josua
 * Date: 26/05/2017
 * Time: 22:51
 */

require_once('system/Startup.class.php');

try{

  Log::custom('JobService', 'Service has started');

}catch(Exception $ex){
  ExceptionManager::handleException($ex);
}


Log::custom('JobService', 'Service has finish');
?>