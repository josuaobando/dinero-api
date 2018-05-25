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
    $sql = "CALL account('{username}')";

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
    $sql = "CALL account_permission('{accountId}')";

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
    $sql = "CALL account_changePassword('{accountId}', '{newPassword}')";

    $params = array();
    $params['accountId'] = $accountId;
    $params['newPassword'] = $newPassword;

    $r = $this->executeUpdate($sql, $params);
    return $r;
  }

}

?>