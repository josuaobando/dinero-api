<?php

/**
 * @author Josua
 */
class DBException extends GeneralException
{

  static $ERROR_INVALID_SQL = 1001;
  static $ERROR_CONNECTION_REFUSED = 1002;
  static $ERROR_OUTPUT_PARAMS = 1003;

  private $errorCode;
  private $sql;
  private $errorMsg;

  private $user = null;
  private $db = null;
  private $server = null;

  public function __construct($errorCode, $sql, $errorMsg, $user = null, $db = null, $server = null)
  {
    parent::__construct(__CLASS__);

    $this->errorCode = $errorCode;
    $this->sql = $sql;
    $this->errorMsg = $errorMsg;

    $this->user = $user;
    $this->db = $db;
    $this->server = $server;

    $this->description = $this->getSQLDetails();
  }

  /**
   * get the sql details of the error
   *
   * @return string
   */
  private function getSQLDetails()
  {
    $newLine = "\n";

    $details = "";
    if($this->user){
      $details .= "Username: " . $this->user . " $newLine";
    }
    if($this->db){
      $details .= "Database: " . $this->db . " $newLine";
    }
    if($this->server){
      $details .= "Server: " . $this->server . " $newLine";
    }
    $details .= "SQL Executed: [ " . $this->sql . " ]$newLine";
    $details .= "Database error: " . $this->errorMsg . "$newLine";

    return $details;
  }
}

?>