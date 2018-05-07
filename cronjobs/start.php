#!/usr/bin/php
<?php

try{

  ini_set("include_path", ".:/var/www/api.test.dinerosegurohf.com/http");

  ini_set("short_open_tag", "on");
  ini_set("display_errors", "on");
  ini_set("error_reporting", "6135");

  require_once 'system/Startup.class.php';

  Log::custom('cronjob', 'Jobs has running');
}catch(Exception $e){
  Log::custom('cronjob', $e->getMessage());
}

?>
