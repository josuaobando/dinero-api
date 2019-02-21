<?php

/**
 * @author Josua
 */
class RequestException extends Exception
{

  const SUCCESS = 0;
  const ERROR_TRANSACTION = 100;
  const ERROR_CUSTOMER = 200;
  const ERROR_CUSTOMER_BLACKLIST = 201;
  const ERROR_PERSON = 300;
  const ERROR_LIMIT = 400;
  const ERROR_P2P = 500;
  const ERROR_P2P_CUSTOMER = 510;
  const ERROR_P2P_PERSON = 520;
  const ERROR_P2P_LIMIT = 530;
  const ERROR_P2P_AGENCY = 540;
  const ERROR_P2P_RELATION = 550;
  const ERROR_API = 600;
  const ERROR_API_BLACKLIST = 610;
  const ERROR_API_PERSON = 620;
  const ERROR_API_LIMIT = 630;

  protected $description = "Alert";

  /**
   * current request
   *
   * @var array
   */
  protected $request = null;

  /**
   * current session values
   *
   * @var array
   */
  protected $session = null;

  /**
   * current server configuration
   *
   * @var array
   */
  protected $serverSettings = null;

  /**
   * RequestException constructor.
   *
   * @param string $errorMsg
   * @param int $errorCode
   */
  public function __construct($errorMsg, $errorCode = self::SUCCESS)
  {
    parent::__construct($errorMsg, $errorCode);

    $this->request = array_merge($_REQUEST, array());
    $this->session = is_array($_SESSION) ? array_merge($_SESSION, array()) : array();
    $this->serverSettings = array_merge($_SERVER, array());
  }

  /**
   * you could override this method in order to add more info in child implementations
   *
   * @return string
   */
  private function getDescription()
  {
    $newLine = "\n";
    $details = $newLine . $this->description . $newLine;

    return $details;
  }

  /**
   * get technical details of the exception
   *
   * @return string
   */
  private function getTechnicalDetails()
  {
    $mem = Util::getMemoryDisplay();

    $newLine = "\n";

    $details = $newLine;
    $details .= "File: " . $this->getFile() . ":" . $this->getLine() . "$newLine";
    $details .= "Time: " . date("F jS, Y, g:i a") . "$newLine";
    $details .= "Mem: " . $mem . "$newLine";

    return $details;
  }

  /**
   * get the stack trace of the execution
   *
   * @return string
   */
  private function getStack()
  {
    $newLine = "\n";

    $details = $newLine;
    $details .= "Stack Trace:$newLine";
    $trace = $this->getTrace();
    if(is_array($trace)){
      foreach($trace as $t){
        $file = $t['file'];
        $line = $t['line'];
        $function = $t['function'];
        $class = $t['class'];
        $type = $t['type'];
        $details .= "$file:$line$newLine";
        $details .= "$class$type$function$newLine";
      }
    }

    return $details;
  }

  /**
   * get details about the last request made if exists
   *
   * @return string
   */
  private function getRequestDetails()
  {
    $newLine = "\n";

    $details = $newLine;
    $details .= "Request Parameters: $newLine";
    $details .= $newLine;
    if($this->request && is_array($this->request) && count($this->request) > 0){
      foreach($this->request as $key => $value){
        $details .= "$key = $value $newLine";
      }
    }

    $details .= $newLine;
    $details .= "Session: $newLine";
    $details .= $newLine;
    if($this->session && is_array($this->session) && count($this->session) > 0){
      foreach($this->session as $key => $value){
        if(is_object($value) && !method_exists($value, '__toString')){
          $details .= "$key = " . get_class($value) . $newLine;
        }else if(is_object($value) && method_exists($value, '__toString')){
          $details .= "$key = " . get_class($value) . $newLine;
        }else{
          $details .= "$key = unknown value type $newLine";
        }
      }
    }

    $details .= $newLine;
    $details .= "Server:$newLine";
    $details .= $newLine;
    if($this->serverSettings && is_array($this->serverSettings) && count($this->serverSettings) > 0){
      $details .= "HTTP_HOST = " . $this->serverSettings['HTTP_HOST'] . $newLine;
      $details .= "SERVER_NAME = " . $this->serverSettings['SERVER_NAME'] . $newLine;
      $details .= "SERVER_ADDR = " . $this->serverSettings['SERVER_ADDR'] . $newLine;
      $details .= "SERVER_PORT = " . $this->serverSettings['SERVER_PORT'] . $newLine;
      $details .= "REMOTE_ADDR = " . $this->serverSettings['REMOTE_ADDR'] . $newLine;
      $details .= "SCRIPT_FILENAME = " . $this->serverSettings['SCRIPT_FILENAME'] . $newLine;
      $details .= "REMOTE_PORT = " . $this->serverSettings['REMOTE_PORT'] . $newLine;
      $details .= "GATEWAY_INTERFACE = " . $this->serverSettings['GATEWAY_INTERFACE'] . $newLine;
      $details .= "SERVER_PROTOCOL = " . $this->serverSettings['SERVER_PROTOCOL'] . $newLine;
      $details .= "REQUEST_METHOD = " . $this->serverSettings['REQUEST_METHOD'] . $newLine . $newLine;

      $details .= "QUERY_STRING = " . $this->serverSettings['QUERY_STRING'] . $newLine . $newLine;
      $details .= "REQUEST_URI = " . $this->serverSettings['REQUEST_URI'] . $newLine . $newLine;
      $details .= "HTTP_USER_AGENT = " . $this->serverSettings['HTTP_USER_AGENT'] . $newLine . $newLine;
    }

    return $details;
  }

  /**
   * string representation
   *
   * @return string
   */
  public function __toString()
  {
    $newLine = "\n";

    $representation = $this->getMessage() . $newLine;
    $representation .= $this->getDescription();
    $representation .= $this->getTechnicalDetails();
    $representation .= $this->getStack();
    $representation .= $this->getRequestDetails();

    return $representation;
  }

}

?>