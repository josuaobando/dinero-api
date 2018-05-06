#!#!/usr/bin/php
<?php

try{

  ini_set("include_path", ".:/var/www/api.test.dinerosegurohf.com/http");

  ini_set("short_open_tag", "on");
  ini_set("display_errors", "on");
  ini_set("error_reporting", "6135");

  require_once '../system/Startup.class.php';

  /**
   * customer function for logging
   *
   * @param string $file
   * @param string $message
   * @param array $args
   */
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

    @file_put_contents('/var/www/api.test.dinerosegurohf.com/http/logs'.$logFile, $content, FILE_APPEND);
  }

  custom('cronjob', 'Jobs has running');
}catch(Exception $e){
  echo $e->getMessage();
}

?>
