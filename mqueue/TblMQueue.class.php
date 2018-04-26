<?php

/**
 * @author Josua
 */
class TblMQueue extends Db
{

  /**
   * singleton reference for TblMQueue
   *
   * @var TblMQueue
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblMQueue
   *
   * @return TblMQueue
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblMQueue();
    }
    return self::$singleton;
  }

  protected function __construct()
  {
    parent::__construct();
    $this->trackThisInstance = false;
  }

  /**
   * track a db execution
   *
   * @param string $username
   * @param string $db
   * @param string $host
   * @param string $sql
   * @param float $connect
   * @param float $execute
   * @param string $error
   *
   * @return int
   */
  public function trackDBExecution($username, $db, $host, $sql, $connect, $execute, $error)
  {
    $statement = "CALL sysLogDB_Insert('{username}','{db}','{host}','{sql}','{sql_size}','{connect}','{execute}','{error}');";

    $params = array();
    $params['username'] = $username;
    $params['db'] = $db;
    $params['host'] = $host;
    $params['sql'] = $sql;
    $params['sql_size'] = strlen($sql);
    $params['connect'] = $connect;
    $params['execute'] = $execute;
    $params['error'] = $error;

    $r = $this->executeUpdate($statement, $params);
    return $r;
  }

  /**
   * track a webservice execution
   *
   * @param string $request
   * @param string $response
   * @param float $namelookup
   * @param float $connect
   * @param float $execute
   * @param string $error
   *
   * @return int
   */
  public function trackWSExecution($request, $response, $namelookup, $connect, $execute, $error)
  {
    $statement = "CALL sysLogWS_Insert('{request}','{request_size}','{response}','{response_size}','{namelookup}','{connect}','{execute}','{error}');";

    $params = array();
    $params['request'] = $request;
    $params['request_size'] = strlen($request);
    $params['response'] = $response;
    $params['response_size'] = strlen($response);
    $params['namelookup'] = $namelookup;
    $params['connect'] = $connect;
    $params['execute'] = $execute;
    $params['error'] = $error;

    $r = $this->executeUpdate($statement, $params);
    return $r;
  }

  /**
   * track a webservice request
   *
   * @param string $uniqueId
   * @param string $credentials
   * @param string $request
   */
  public function trackWSRequest($uniqueId, $credentials, $request)
  {
    $statement = "CALL sysLogWSRequest_Insert('{uniqueId}', '{credentials}','{request}','{request_size}');";

    $params = array();
    $params['uniqueId'] = $uniqueId;
    $params['credentials'] = $credentials;
    $params['request'] = $request;
    $params['request_size'] = strlen($request);

    $r = $this->executeUpdate($statement, $params);
    return $r;
  }

  /**
   * update webservice request
   *
   * @param string $uniqueId
   * @param string $response
   * @param string $responseTime
   */
  public function trackWSRequestUpdate($uniqueId, $response, $responseTime)
  {
    $statement = "CALL sysLogWSRequest_Update('{uniqueId}', '{response}', '{response_size}','{response_time}');";

    $params = array();
    $params['uniqueId'] = $uniqueId;
    $params['response'] = $response;
    $params['response_size'] = strlen($response);
    $params['response_time'] = $responseTime;

    $r = $this->executeUpdate($statement, $params);
    return $r;
  }

}

?>