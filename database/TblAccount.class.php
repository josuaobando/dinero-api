<?php

/**
 * @author Josua
 */
class TblAccount extends Db
{

  /**
   * singleton reference for TblAccount
   *
   * @var TblAccount
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblAccount
   *
   * @return TblAccount
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblAccount();
    }
    return self::$singleton;
  }

  /**
   * get an account by username
   *
   * @param string $username
   *
   * @return array
   */
  public function getAccount($username)
  {
    $sql = "CALL spAccount('{username}')";

    $params = array();
    $params['username'] = $username;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * get an account by username
   *
   * @param int $accountId
   *
   * @return array
   */
  public function getAccountById($accountId)
  {
    $sql = "CALL spAccount_ById('{accountId}')";

    $params = array();
    $params['accountId'] = $accountId;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * get account permission
   *
   * @param int $accountId
   *
   * @return array
   */
  public function getPermission($accountId)
  {
    $sql = "CALL spAccount_Permission('{accountId}')";

    $params = array();
    $params['accountId'] = $accountId;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * Change password account
   *
   * @param int $accountId
   * @param string $newPassword
   *
   * @return bool
   */
  public function changePassword($accountId, $newPassword)
  {
    $sql = "CALL spAccount_ChangePassword('{accountId}', '{newPassword}')";

    $params = array();
    $params['accountId'] = $accountId;
    $params['newPassword'] = $newPassword;

    $r = $this->executeUpdate($sql, $params);
    return $r;
  }

  /**
   * @param $host
   * @param $referrer
   * @param $remoteAddr
   * @param $protocol
   * @param $agent
   * @param $platform
   * @param $account
   * @param $token
   * @param $activity
   *
   * @return int
   */
  public function sessionTracker($host, $referrer, $remoteAddr, $protocol, $agent, $platform, $account, $token, $activity)
  {
    $sql = "CALL spSessionTracker_Insert('{host}', '{referrer}', '{remoteAddr}', '{protocol}', '{agent}', '{platform}', '{account}', '{token}', '{activity}', @SessionTrackerId)";

    $params = array();
    $params['host'] = $host;
    $params['referrer'] = $referrer;
    $params['remoteAddr'] = $remoteAddr;
    $params['protocol'] = $protocol;
    $params['agent'] = $agent;
    $params['platform'] = $platform;
    $params['account'] = $account;
    $params['token'] = $token;
    $params['activity'] = $activity;

    $this->setOutputParams(array('SessionTrackerId'));
    $this->executeUpdate($sql, $params);
    $output = $this->getOutputResults();

    return $output['SessionTrackerId'];
  }

}

?>