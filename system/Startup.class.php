<?php

require_once('util/Util.class.php');

class Startup
{

  /**
   * flag to know if the system was already initialized
   *
   * @var bool
   */
  private static $initialized = false;

  private static $sysDirectories = null;

  /**
   * main method where we initialize the system<br/>
   * this method needs to be executed in any entry point of the system<br/>
   * example: Webservices, cronjobs, etc<br/><br/>
   * class autoload<br/>
   */

  public static function initialize()
  {
    if(self::$initialized){
      return;
    }

    self::initClassLoader();
    self::initEnv();

    self::$initialized = true;

  }

  /**
   * init global settings for the system
   */
  private static function initEnv()
  {
    if(!Util::isCli()){
      set_time_limit(CoreConfig::MAX_EXECUTION_TIME);
    }

    //we set the handlers for not caught exceptions and errors
    set_error_handler("ExceptionManager::errorHandler");
    set_exception_handler("ExceptionManager::unhandledException");

    ini_set('sendmail_from', Util::isDEV() ? CoreConfig::MAIL_DEV : CoreConfig::MAIL_FROM);
  }

  /**
   * init the system class loader
   */
  private static function initClassLoader()
  {
    //get current directory
    $pwd = getcwd();
    $myself = __FILE__;
    $myself = str_replace("\\", "/", $myself);

    $includedDirectories = explode(PATH_SEPARATOR, ini_get('include_path'));
    self::$sysDirectories = array();
    foreach($includedDirectories as $includedDirectory){
      if(!is_dir($includedDirectory) || //not a directory
        $includedDirectory == '.' || //current directory
        $includedDirectory == '..'    //parent directory
      ){
        continue;
      }

      //look for the core system path
      $startupFilePath = $includedDirectory . "/system/Startup.class.php";
      $startupFilePath = str_replace("\\", "/", $startupFilePath);
      if(file_exists($startupFilePath)){
        //avoid loading a duplicated Startup file
        if(strtolower($startupFilePath) == strtolower($myself)){
          self::$sysDirectories = Util::getDirectories($includedDirectory, self::$sysDirectories);
          continue;
        }
      }
      if(is_numeric(strpos(strtolower($pwd), strtolower($includedDirectory)))){
        self::$sysDirectories = Util::getDirectories($includedDirectory, self::$sysDirectories);
        continue;
      }
    }

    spl_autoload_register(array('Startup', 'systemClassLoader'));
  }

  /**
   * auto class loader for the entire system
   *
   * @param string $class
   *
   * @return bool
   */
  public static function systemClassLoader($class)
  {
    $standardName = "$class.class.php";
    $defaultName = "$class.php";

    //#1 look for the class into the system folders
    foreach(self::$sysDirectories as $directory){
      $classFile = $directory . DIRECTORY_SEPARATOR . $standardName;
      if(file_exists($classFile)){
        require_once($classFile);
        return true;
      }
      $classFile = $directory . DIRECTORY_SEPARATOR . $defaultName;
      if(file_exists($classFile)){
        require_once($classFile);
        return true;
      }
    }

    //#2 now search the file in the class path using the PEAR Naming Conventions
    if(strpos($class, "_") > 0){
      $classFile = str_replace("_", DIRECTORY_SEPARATOR, $defaultName);
      $realPath = Util::file_exists($classFile);
      if($realPath){
        require_once($realPath);
        return true;
      }
    }

    //#3 finally search the file in the class path
    $realPath = Util::file_exists($defaultName);
    if($realPath){
      require_once($realPath);
      return true;
    }

    //class doesnt exists
    return false;
  }

}

//we force the initialization by calling the method in the require/include
Startup::initialize();

?>