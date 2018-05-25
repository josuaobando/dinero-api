#!/usr/bin/php
<?php

ini_set("include_path", ".:/var/www/api.dinerosegurohf.com/http");
ini_set("display_errors", "on");
ini_set("error_reporting", "6135");
require_once 'system/Startup.class.php';

try{

  // Start the Cronjob
  function custom($file, $message, $args = null)
  {
    $datetime = date('Y-m-d H:i:s');

    //replace variables if there is any
    if($args && is_array($args)){
      foreach($args as $key => $value){
        $message = str_replace("{".$key."}", $value, $message);
      }
    }

    $content = $format = "[%{datetime}] %{message} \n";
    $content = str_replace("%{datetime}", $datetime, $content);
    $content = str_replace("%{message}", $message, $content);

    $logFile = "/log-".$file.'.log';

    @file_put_contents('/var/www/api.dinerosegurohf.com/http/logs'.$logFile, $content, FILE_APPEND);
  }

  custom('cronjob', 'Job initialized');
  if(CoreConfig::CRON_JOBS_ACTIVE){
    custom('cronjob', 'Job initialized');
    $connector = new Connector();
    $connector->loadContent(CoreConfig::CRON_JOB_SERVICES);
    custom('cronjob', 'Job finish');
  }else{
    custom('cronjob', 'Job is turn off');
  }

}catch(Exception $e){
  echo $e->getMessage();
}

?>
