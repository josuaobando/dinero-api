<?php

/**
 * @author Josua
 */
class Log
{
	
	const LEVEL_INFO          = 0;
	const LEVEL_WARNING       = 1;
	const LEVEL_ERROR         = 2;
	const LEVEL_CRITICAL      = 3;
	const LEVEL_EXCEPTION     = 4;
	const LEVEL_EVENT         = 5;
	
	/**
	 * format of how we want to record the messages in the files
	 * 
	 * @var string
	 */
	private static $format = "[%{datetime}] %{message} \n";
	
	/**
	 * names for each file type
	 * 
	 * @var array
	 */
	private static $postfixes = array(Log::LEVEL_INFO      => 'info',
																		Log::LEVEL_WARNING   => 'warning',
																		Log::LEVEL_ERROR     => 'error',
																		Log::LEVEL_CRITICAL  => 'critical',
																		Log::LEVEL_EXCEPTION => 'exception',
																		Log::LEVEL_EVENT     => 'event'
																		);

  /**
   * file prefix
   * 
   * @var string
   */
	private static $prefix = "log-";
	
	/**
   * file extension
   * 
   * @var string
   */
	private static $extension = ".log";
	
	/**
	 * main function for logging
	 * 
	 * @param int $level
	 * @param string $message
	 * @param array $args
	 */
	private static function handle($level, $message, $args = null)
	{
		if (!defined('CoreConfig::LOG_PATH') || !is_dir(CoreConfig::LOG_PATH))
		{
			//no valid path has been defined
			return;
		}
		
    $datetime = date('Y-m-d H:i:s');
    
    //replace variables if there is any
    if ($args && is_array($args))
    {
	    foreach ($args as $key=>$value)
	    {
	    	$message = str_replace("{".$key."}", $value, $message);
	    }
    }
    
    $content = Log::$format;
    $content = str_replace("%{datetime}", $datetime, $content);
    $content = str_replace("%{message}", $message, $content);
    
    $logFile = "/" . Log::$prefix . Log::$postfixes[$level] . Log::$extension;
    
		@file_put_contents(CoreConfig::LOG_PATH . $logFile, $content, FILE_APPEND);
	}
	
	/**
	 * it checks if a log file exists
	 * 
	 * @param int $level
	 * 
	 * @return bool
	 */
	public static function logExists($level)
	{
		$logFile = "/" . Log::$prefix . Log::$postfixes[$level] . Log::$extension;
		$fullPath = CoreConfig::LOG_PATH . $logFile;
		return file_exists($fullPath);
	}

  /**
   * customer function for logging
   *
   * @param string $file
   * @param string $message
   * @param array $args
   */
  public static function custom($file, $message, $args = null)
  {
    if (!defined('CoreConfig::LOG_PATH') || !is_dir(CoreConfig::LOG_PATH))
    {
      //no valid path has been defined
      return;
    }

    $datetime = date('Y-m-d H:i:s');

    //replace variables if there is any
    if ($args && is_array($args))
    {
      foreach ($args as $key=>$value)
      {
        $message = str_replace("{".$key."}", $value, $message);
      }
    }

    $content = Log::$format;
    $content = str_replace("%{datetime}", $datetime, $content);
    $content = str_replace("%{message}", $message, $content);

    $logFile = "/" . Log::$prefix . $file . Log::$extension;

    @file_put_contents(CoreConfig::LOG_PATH . $logFile, $content, FILE_APPEND);
  }

  /**
   * customer function for logging
   *
   * @param $file
   * @param $message
   */
  public static function custom2($file, $message)
  {
    if (!defined('CoreConfig::LOG_PATH') || !is_dir(CoreConfig::LOG_PATH))
    {
      //no valid path has been defined
      return;
    }

    $content = "{message} \n";
    $content = str_replace("{message}", $message, $content);
    $logFile = "/" . Log::$prefix . $file . Log::$extension;

    @file_put_contents(CoreConfig::LOG_PATH . $logFile, $content, FILE_APPEND);
  }
	
	/**
	 * log information
	 * 
	 * @param string $message
	 * @param array $args
	 */
	public static function info($message, $args = null)
	{
		Log::handle(Log::LEVEL_INFO, $message, $args);
	}
	
	/**
	 * log a warning message
	 * 
	 * @param string $message
	 * @param array $args
	 */
	public static function warning($message, $args = null)
	{
		Log::handle(Log::LEVEL_WARNING, $message, $args);
	}
	
	/**
	 * log an error message
	 * 
	 * @param string $message
	 * @param array $args
	 */
	public static function error($message, $args = null)
	{
		Log::handle(Log::LEVEL_ERROR, $message, $args);
	}
	
	/**
	 * log a critical error
	 * 
	 * @param string $message
	 * @param array $args
	 */
	public static function critical($message, $args = null)
	{
		Log::handle(Log::LEVEL_CRITICAL, $message, $args);
	}
	
	/**
	 * log an exception
	 * 
	 * @param string $message
	 * @param array $args
	 */
	public static function exception($exception)
	{
		Log::handle(Log::LEVEL_EXCEPTION, $exception);
	}
	
	/**
	 * log an event
	 * 
	 * @param string $message
	 * @param array $args
	 */
	public static function event($event)
	{
		$data = "\n" . $event . "\n";
		$data .= "object >>>\n";
		$data .= Encrypt::pack($event) . "\n";
		$data .= "<<< object\n";
		
		Log::handle(Log::LEVEL_EVENT, $data);
	}
	
}

?>