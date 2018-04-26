<?php

/**
 * @author Josua
 */
class ExceptionManager
{

  /**
   * Handle the exception
   *
   * @param Exception $exception
   *
   * @return bool
   */
  public static function handleException($exception)
  {
    if(Util::isDEV() && defined('CoreConfig::PRINT_EXCEPTIONS') && CoreConfig::PRINT_EXCEPTIONS){
      echo Util::getNewLine();
      echo Util::getNewLine();
      echo Util::isCli() ? $exception : nl2br($exception);
      echo Util::getNewLine();
      echo Util::getNewLine();
    }

    $data = array();
    $data['exception'] = Encrypt::pack($exception);
    $r = MQueue::push(MQueue::TYPE_EXCEPTION, $data);

    Log::exception($exception);

    return $r;
  }

  /**
   * default exception handler
   *
   * @param Exception $ex
   */
  public static function unhandledException($ex)
  {
    $unhandledEx = new UnhandledException($ex);
    self::handleException($unhandledEx);
  }

  /**
   * handle the errors that was not handled before.
   *
   * @param int $errorCode
   * @param string $errorMessage
   * @param string $file
   * @param int $line
   */
  public static function errorHandler($errorCode, $errorMessage, $file, $line)
  {

    if((error_reporting() == 0) ||      //error has been supressed with an @
      !(error_reporting() & $errorCode) //error reporting has not this error code
    ){
      return;
    }

    $abort = $print = false;
    $notify = true;
    switch($errorCode){
      case E_USER_ERROR:
        $type = "ERROR";
        $abort = $print = true;
        break;

      case E_USER_WARNING:
        $type = "WARNING";
        $print = true;
        break;

      case E_USER_NOTICE:
        $type = "NOTICE";
        break;

      default:
        $type = "SYSTEM";
        $print = true;
        break;
    }

    $message = "Default error handler.\n\n";
    $message .= "Type: $type\n";
    $message .= "Date: " . date('m-d-Y H:i:s a') . "\n";
    $message .= "Error Code: $errorCode\n";
    $message .= "Details: $errorMessage\n";
    $message .= "Source: $file:$line\n\n";

    if($notify){
      self::handleException(new GeneralException("Not caught system error!", $message));
    }
    if($print){
      if(Util::isDEV()){
        echo Util::isCli() ? $message : nl2br($message);
      }
    }

    Log::error("Not caught system error!\n\n$message");

    if($abort){
      exit(1);
    }
  }

}

?>