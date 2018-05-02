<?php

require_once('system/Startup.class.php');

if(CoreConfig::CRON_JOBS_ACTIVE){
  // Start the Cron job
  $connector = new Connector();
  $response = $connector->loadContent(CoreConfig::CRON_JOB_SERVICES);

  Log::custom('Job', 'Jobs has finish');
}else{
  Log::custom('Job', 'Jobs are turned off!');
}

?>
