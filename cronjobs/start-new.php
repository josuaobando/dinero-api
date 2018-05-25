#!/usr/bin/php
<?php

try{

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

  custom('cronjob', 'Jobs has running');
}catch(Exception $e){
  echo $e->getMessage();
}

?>
