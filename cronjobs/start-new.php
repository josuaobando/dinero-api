#!/usr/bin/php
<?php

ini_set("include_path", ".:/var/www/api.dinerosegurohf.com/http");
ini_set("display_errors", "on");
ini_set("error_reporting", "6135");
require_once 'system/Startup.class.php';

try{

  if(CoreConfig::CRON_JOBS_ACTIVE){
    $connector = new Connector();
    $connector->loadContent(CoreConfig::CRON_JOB_SERVICES);
  }else{
    Log::custom('JobService', 'Job is turn off');
  }

}catch(Exception $e){
  echo $e->getMessage();
}

?>
