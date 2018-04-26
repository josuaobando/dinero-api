<?php

/**
 * @author Josua
 */
class TblWSAccess extends Db
{

  /**
   * singleton reference for TblWSAccess
   *
   * @var TblWSAccess
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblWSAccess
   *
   * @return TblWSAccess
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblWSAccess();
    }
    return self::$singleton;
  }

  /**
   * get a service by id
   *
   * @param string $webservice
   *
   * @return array
   */
  public function getWebservice($webservice)
  {
    $sql = "CALL sysWebservice('{webservice}'); ";

    $params = array('webservice' => $webservice);
    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * get a service by access password
   *
   * @param string $sysAccessPass
   *
   * @return array
   */
  public function getWebserviceUser($sysAccessPass)
  {
    $sql = "CALL sysWebserviceUser('{sysAccessPass}'); ";

    $params = array('sysAccessPass' => $sysAccessPass);
    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * get a service access by webservice id and user id
   *
   * @param int $webserviceId
   * @param int $webserviceUserId
   *
   * @return array
   */
  public function getWebserviceAccess($webserviceId, $webserviceUserId)
  {
    $sql = "CALL sysWebserviceAccess('{webserviceId}', '{webserviceUserId}'); ";

    $params = array();
    $params['webserviceId'] = $webserviceId;
    $params['webserviceUserId'] = $webserviceUserId;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }
}

?>