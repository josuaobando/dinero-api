<?php

/**
 * @author Josua
 */
class TblSession extends Db
{

  /**
   * singleton reference for TblSession
   *
   * @var TblSession
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblSession
   *
   * @return TblSession
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblSession();
    }
    return self::$singleton;
  }

  /**
   * @param $host
   * @param $referrer
   * @param $remoteAddr
   * @param $protocol
   * @param $agent
   * @param $platform
   * @param $country
   * @param $state
   * @param $account
   * @param $token
   * @param $activity
   *
   * @return int
   */
  public function add($host, $referrer, $remoteAddr, $protocol, $agent, $platform, $country, $state, $account, $token, $activity)
  {
    $sql = "CALL spSessionTracker_Insert('{host}', '{referrer}', '{remoteAddr}', '{protocol}', '{agent}', '{platform}', '{country}', '{state}', '{account}', '{token}', '{activity}', @SessionTrackerId)";

    $params = array();
    $params['host'] = $host;
    $params['referrer'] = $referrer;
    $params['remoteAddr'] = $remoteAddr;
    $params['protocol'] = $protocol;
    $params['agent'] = $agent;
    $params['platform'] = $platform;
    $params['country'] = $country;
    $params['state'] = $state;
    $params['account'] = $account;
    $params['token'] = $token;
    $params['activity'] = $activity;

    $this->setOutputParams(array('SessionTrackerId'));
    $this->executeUpdate($sql, $params);
    $output = $this->getOutputResults();

    return $output['SessionTrackerId'];
  }

  /**
   * @param $token
   *
   * @return array
   */
  public function get($token)
  {
    $sql = "CALL spSessionTracker('{token}')";

    $params = array();
    $params['token'] = $token;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * @param $token
   *
   * @return int
   */
  public function update($token)
  {
    $sql = "CALL spSessionTracker_Update('{token}')";

    $params = array();
    $params['token'] = $token;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * @param $token
   *
   * @return int
   */
  public function close($token)
  {
    $sql = "CALL spSessionTracker_Close('{token}')";

    $params = array();
    $params['token'] = $token;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * @param $account
   *
   * @return array
   */
  public function check($account)
  {
    $sql = "CALL spSessionTracker_Check('{account}')";

    $params = array();
    $params['account'] = $account;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

}

?>