<?php

/**
 * @author Josua
 */
class Db
{

  private $dbHost = null;
  private $dbUsername = null;
  private $dbPassword = null;
  private $dbName = null;

  private $EMPTY = "";
  private $lastId = 0;

  private $timeMonitorResult = array('sql' => null, 'connect' => null, 'execute' => null, 'error' => 'Ok');

  /**
   * this is a basic connection pool that last only during the execution of the request
   * or the whole execution of a script
   */
  private $connections = array();

  private $outputParams = null;
  private $outputResults = null;

  /**
   * flag to define if the current db instance needs to be tracked;
   *
   * this option is overridden by the global CoreConfig::TRACK_DB_STATS_ACTIVE
   *
   * @var bool
   */
  protected $trackThisInstance = true;

  /**
   * flag to define if the DB controller should throw any raising exception;
   * this flag only works for one execution; after that is set to false again.
   *
   * @var bool
   */
  protected $throwException = false;

  protected function __construct()
  {
    $this->loadSettings(CoreConfig::DB_NAME); //load the application database settings as default
  }

  public function __destruct()
  {
    foreach($this->connections as $dbName => $conn){
      $this->closeConnection($dbName);
    }
  }

  /**
   * load the db settings for the db instance
   *
   * @param string $dbName
   */
  protected function loadSettings($dbName)
  {
    $this->dbName = $dbName;
    $this->dbHost = Encrypt::decode(constant("CoreConfig::" . CoreConfig::DB_HOSTNAME__ . $dbName));
    $this->dbUsername = Encrypt::decode(constant("CoreConfig::" . CoreConfig::DB_USERNAME__ . $dbName));
    $this->dbPassword = Encrypt::decode(constant("CoreConfig::" . CoreConfig::DB_PASSWORD__ . $dbName));
  }

  /**
   * load the settings using specific credentials
   *
   * @param string $dbName
   * @param string $dbHost
   * @param string $dbUsername
   * @param string $dbPassword
   * @param bool $encrypted
   */
  protected function loadCredentials($dbName, $dbHost, $dbUsername, $dbPassword, $encrypted = false)
  {
    $this->dbName = !$encrypted ? $dbName : Encrypt::decode($dbName);
    $this->dbHost = !$encrypted ? $dbHost : Encrypt::decode($dbHost);
    $this->dbUsername = !$encrypted ? $dbUsername : Encrypt::decode($dbUsername);
    $this->dbPassword = !$encrypted ? $dbPassword : Encrypt::decode($dbPassword);
  }

  /**
   * close db connection
   *
   * @param string $dbName
   */
  protected function closeConnection($dbName)
  {
    $conn = $this->connections[$dbName];
    if($conn){
      @mysqli_close($conn);
      unset($this->connections[$dbName]);
    }
  }

  /**
   * Get the last id inserted
   *
   * @return int
   */
  protected function getLastInsertId()
  {
    return $this->lastId;
  }

  /**
   * set the output parameters, it only applies for stored procedures
   *
   * @param array $params
   */
  protected function setOutputParams($params)
  {
    $this->outputParams = $params;
  }

  /**
   * it returns the output results after a stored procedure execution
   * it cleans the results to avoid future usages of outdated results
   *
   * @return array
   */
  protected function getOutputResults()
  {
    if($this->outputResults && is_array($this->outputResults)){
      $outputResults = $this->outputResults;
      $this->outputResults = null;
      return $outputResults;
    }
    return array();
  }

  /**
   * process the output parameters for a specific stored procedure
   *
   * @param resource $con
   *
   * @throws DBException
   */
  private function processOutputParams($con)
  {
    if(is_array($this->outputParams)){
      $outputResults = array();
      foreach($this->outputParams as $param){
        $outputSql = "select @$param $param";
        $result = @mysqli_query($con, $outputSql);
        if($result === FALSE){
          throw new DBException(DBException::$ERROR_OUTPUT_PARAMS, $outputSql, @mysqli_error($con));
        }
        $row = @mysqli_fetch_assoc($result);
        $outputResults[$param] = $row[$param];
      }
      $this->outputResults = $outputResults;
    }
    $this->outputParams = null;
  }

  /**
   * Clear out results to prepare a connection for re-use
   *
   * @param mysqli_connection $con
   */
  private function cleanResultSets($con, $lastResultSet = null)
  {
    if($lastResultSet){
      @mysqli_free_result($lastResultSet);
    }
    while(@mysqli_more_results($con)){
      if(@mysqli_next_result($con)){
        $result = @mysqli_use_result($con);
        if($result){
          @mysqli_free_result($result);
        }
      }
    }
  }

  /**
   * notify warnings
   *
   * @param resource $link
   * @param string $query
   */
  private function notifyWarnings($link, $query)
  {
    $warningsCount = @mysqli_warning_count($link);

    if($warningsCount){
      $warningsResult = @mysqli_query($link, "SHOW WARNINGS");
      $warnings = @mysqli_fetch_array($warningsResult);

      $message = "Database's Warning has been detected.\n\n";
      $message .= "Level: " . $warnings['Level'] . "\n";
      $message .= "Code: " . $warnings['Code'] . "\n";
      $message .= "Message: " . $warnings['Message'] . "\n";
      $message .= "SQL Statement: $query";

      MailManager::sendCriticalErrorEmail("Database Warning", nl2br($message));
    }
  }

  /**
   * core method to execute
   *
   * @param string $sql
   * @param array $params
   * @param array $rows
   * @param array $row
   * @param bool $multiStatement
   * @return int
   */
  private function executeSQL($sql, $params, &$rows, &$row, $multiStatement = false)
  {
    try{
      $this->timeMonitorResult['sql'] = $sql;
      $this->timeMonitorResult['connect'] = 0;
      $this->timeMonitorResult['execute'] = 0;
      $this->timeMonitorResult['error'] = 'Ok';

      $startTime = Util::getStartTime();

      $con = $this->connections[$this->dbName];
      if(!$con || !@mysqli_ping($con)){
        $con = @mysqli_init();
        @mysqli_real_connect($con, $this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
        $this->connections[$this->dbName] = $con;
      }

      if(is_array($params)){
        $this->setParameters($sql, $params, $con);
      }

      $this->timeMonitorResult['sql'] = $sql;
      $this->timeMonitorResult['connect'] = Util::calculateProcessTime($startTime);

      if(@mysqli_connect_errno()){
        throw new DBException(DBException::$ERROR_CONNECTION_REFUSED, $sql, 'Could not connect: ' . @mysqli_connect_error(), $this->dbUsername, $this->dbName, $this->dbHost);
      }

      @mysqli_query($con, "SET NAMES 'UTF8'");
      @mysqli_query($con, "SET time_zone = '-6:00'");

      $startTime = Util::getStartTime();
      if($multiStatement){
        $result = @mysqli_multi_query($con, $sql);
      }else{
        $result = @mysqli_query($con, $sql);
      }

      if(defined('CoreConfig::DB_NOTIFY_WARNINGS') && CoreConfig::DB_NOTIFY_WARNINGS){
        $this->notifyWarnings($con, $sql);
      }

      $this->timeMonitorResult['execute'] = Util::calculateProcessTime($startTime);

      if($result === FALSE){
        throw new DBException(DBException::$ERROR_INVALID_SQL, $sql, @mysqli_error($con), $this->dbUsername, $this->dbName, $this->dbHost);
      }

      if($multiStatement){
        $multiResults = array();

        do{
          /* store first result set */
          $result = @mysqli_store_result($con);
          if($result){
            $rows = array();
            for(; $currentRow = @mysqli_fetch_assoc($result);){
              $rows[] = $currentRow;
            }
            array_push($multiResults, $rows);
            @mysqli_free_result($result);
          }
        }while(@mysqli_next_result($con));

        $this->cleanResultSets($con);
        return $multiResults;
      }

      $count = 0;
      if(!is_array($rows) && !is_array($row)){
        $count = @mysqli_affected_rows($con);
        $this->lastId = @mysqli_insert_id($con);
      }else{
        $count = @mysqli_num_rows($result);
        if(is_array($rows)){
          for(; $currentRow = @mysqli_fetch_assoc($result);){
            $rows[] = $currentRow;
          }
        }else{
          if($count > 0){
            $row = @mysqli_fetch_assoc($result);
          }else{
            $row = 0;
          }
        }
      }
      $this->cleanResultSets($con, $result);

      if($this->outputParams){
        $this->processOutputParams($con);
      }

      return $count;

    }catch(DBException $dbEx){
      $this->timeMonitorResult['error'] = $dbEx->getMessage();

      if($this->throwException){
        $this->throwException = false;
        $this->cleanResultSets($con, $result);
        throw $dbEx;
      }
      ExceptionManager::handleException($dbEx);
    }catch(Exception $ex){
      $this->timeMonitorResult['error'] = $ex->getMessage();

      $dbEx = new DBException($ex->getCode(), $sql, $ex->getMessage());
      if($this->throwException){
        $this->throwException = false;
        $this->cleanResultSets($con, $result);
        throw $dbEx;
      }
      ExceptionManager::handleException($dbEx);
    }

    $this->cleanResultSets($con, $result);
    return 0;
  }

  /**
   * update $sql with the params
   *
   * @param string $sql
   * @param array $params
   */
  private function setParameters(&$sql, $params, $con)
  {
    if(is_array($params)){
      foreach($params as $key => $value){
        if(is_array($value)){
          $valueStr = Util::generateStrList($value);
          $sql = str_replace("{" . $key . "}", $valueStr, $sql);
        }else
          if(is_object($value)){
            $objStr = Encrypt::pack($value);
            $sql = str_replace("{" . $key . "}", $objStr, $sql);
          }else{
            $sql = str_replace("{" . $key . "}", @mysqli_escape_string($con, $value), $sql);
          }
      }
    }
  }

  /**
   * trackThisInstance the performance of the last statement executed
   */
  private function trackDBStats()
  {
    if(!defined('CoreConfig::TRACK_DB_STATS_ACTIVE') ||  //configuration option not defined
      !CoreConfig::TRACK_DB_STATS_ACTIVE ||             //global tracking option is disabled

      !$this->trackThisInstance ||                      //this db instance is disabled

      !defined('CoreConfig::TRACK_DB_STATS_USERS') || //option not defined
      !defined('CoreConfig::TRACK_DB_STATS_DBS') ||   //option not defined
      !defined('CoreConfig::TRACK_DB_STATS_HOSTS') || //option not defined

      !CoreConfig::TRACK_DB_STATS_USERS || //setting disabled
      !CoreConfig::TRACK_DB_STATS_DBS ||   //setting disabled
      !CoreConfig::TRACK_DB_STATS_HOSTS || //setting disabled

      (
        !in_array($this->dbUsername, explode("|", CoreConfig::TRACK_DB_STATS_USERS)) &&  //db user not in the list
        CoreConfig::TRACK_DB_STATS_USERS != '*'                                          //all users wildcard is not set
      ) ||

      (
        !in_array($this->dbName, explode("|", CoreConfig::TRACK_DB_STATS_DBS)) &&  //db name not in the list
        CoreConfig::TRACK_DB_STATS_DBS != '*'                                      //all dbs wildcard is not set
      ) ||

      (
        !in_array($this->dbHost, explode("|", CoreConfig::TRACK_DB_STATS_HOSTS)) &&  //db server host not in the list
        CoreConfig::TRACK_DB_STATS_HOSTS != '*'                                      //all servers wildcard is not set
      ) ||

      !defined('CoreConfig::TRACK_DB_STATS_TIME') ||  //configuration option not defined
      CoreConfig::TRACK_DB_STATS_TIME > ($this->timeMonitorResult['connect'] + $this->timeMonitorResult['execute'])
    ){
      return;
    }

    $data = array_merge(array(), $this->timeMonitorResult);
    $data['username'] = $this->dbUsername;
    $data['db'] = $this->dbName;
    $data['host'] = $this->dbHost;
    MQueue::push(MQueue::TYPE_STATS_DB, $data);
  }

  /**
   * To execute just query's with rows as result
   *
   * @param string $sql
   * @param array $rows
   * @param array [optional] $params
   * @return int
   */
  protected function executeQuery($sql, &$rows, $params = NULL)
  {
    $r = $this->executeSQL($sql, $params, $rows, $this->EMPTY);
    $this->trackDBStats();
    return $r;
  }

  /**
   * To execute a query with a single row as result
   *
   * @param string $sql
   * @param array $row
   * @param array [optional] $params
   * @return int
   */
  protected function executeSingleQuery($sql, &$row, $params = NULL)
  {
    $r = $this->executeSQL($sql, $params, $this->EMPTY, $row);
    $this->trackDBStats();
    return $r;
  }

  /**
   * to execute updates like insert's, update's, delete's
   *
   * @param string $sql
   * @param array $params
   * @return int
   */
  protected function executeUpdate($sql, $params = NULL)
  {
    $r = $this->executeSQL($sql, $params, $this->EMPTY, $this->EMPTY);
    $this->trackDBStats();
    return $r;
  }

  /**
   * execute multiple queries
   *
   * @param string $sql
   * @param array $ids
   * @param array $params
   *
   * @return array
   */
  protected function executeMultiQuery($sql, $ids, $params = NULL)
  {
    $multiResults = $this->executeSQL($sql, $params, $this->EMPTY, $this->EMPTY, true);
    $this->trackDBStats();
    if(!$ids || !is_array($ids) || count($ids) != count($multiResults)){
      return $multiResults;
    }
    $assocResults = array();
    for($i = 0; $i < count($ids); $i++){
      $result = $multiResults[$i];
      $resultId = $ids[$i];
      $assocResults[$resultId] = $result;
    }
    return $assocResults;
  }

  /**
   *
   * Convert String into an Object
   * @param String $data
   * @return Object
   */
  protected function getObject($data)
  {

    if(!$data || !is_string($data)){
      return null;
    }

    return Encrypt::unpack($data);
  }

  /**
   * Decript key
   *
   * @return string
   */
  protected function getDecryptKey()
  {
    $key = Encrypt::decode(CoreConfig::ENCRIPT_KEY);
    return $key;
  }
}

?>