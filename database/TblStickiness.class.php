<?php

/**
 * jobando
 */
class TblStickiness extends Db
{

  /**
   * singleton reference for TblStickiness
   *
   * @var TblStickiness
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblStickiness
   *
   * @return TblStickiness
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblStickiness();
    }

    return self::$singleton;
  }

  /**
   * add new stickiness
   *
   * @param $customerId
   * @param $personId
   * @param $verificationId
   * @param $verification
   *
   * @return int
   */
  public function create($customerId, $personId, $verificationId = null, $verification = null)
  {
    $sql = "CALL spStickiness_Add('{customerId}', '{personId}', '{verificationId}', '{verification}', @stickinessId)";

    $params = array();
    $params['customerId'] = $customerId;
    $params['personId'] = $personId;
    $params['verificationId'] = $verificationId;
    $params['verification'] = $verification;

    $this->setOutputParams(array('stickinessId'));
    $this->executeUpdate($sql, $params);
    $output = $this->getOutputResults();
    $stickinessId = $output['stickinessId'];

    return $stickinessId;
  }

  /**
   * update stickiness
   *
   * @param $stickinessId
   * @param $verificationId
   * @param $verification
   *
   * @return int
   */
  public function update($stickinessId, $verificationId, $verification)
  {
    $sql = "CALL spStickiness_Update('{stickinessId}', '{verificationId}', '{verification}')";

    $params = array();
    $params['stickinessId'] = $stickinessId;
    $params['verificationId'] = $verificationId;
    $params['verification'] = $verification;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * disable stickiness
   *
   * @param $stickinessId
   * @param $isActive
   *
   * @return int
   */
  public function isActive($stickinessId, $isActive)
  {
    $sql = "CALL spStickiness_IsActive('{stickinessId}', '{isActive}')";

    $params = array();
    $params['stickinessId'] = $stickinessId;
    $params['isActive'] = $isActive;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * get stickiness by id
   *
   * @param int $stickinessId
   *
   * @return array
   */
  public function get($stickinessId)
  {
    $sql = "CALL spStickiness('{stickinessId}')";

    $params = array();
    $params['stickinessId'] = $stickinessId;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * get stickiness data by Customer Id
   *
   * @param int $customerId
   *
   * @return array
   */
  public function getByCustomerId($customerId)
  {
    $sql = "CALL spStickiness_ByCustomerId('{customerId}')";

    $params = array();
    $params['customerId'] = $customerId;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * add stickiness provider message
   *
   * @param $stickinessId
   * @param $request
   * @param $response
   *
   * @return int
   */
  public function addProviderMessage($stickinessId, $request, $response)
  {
    $sql = "CALL spStickinessProvider_AddMessage('{stickinessId}', '{request}', '{response}')";

    $params = array();
    $params['stickinessId'] = $stickinessId;
    $params['request'] = Util::toString($request);
    $params['response'] = Util::toString($response);

    return $this->executeUpdate($sql, $params);
  }

  /**
   * get stickiness pending
   *
   * @return array
   */
  public function getPending()
  {
    $sql = "CALL spStickiness_Pending()";

    $params = array();
    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get stickiness approved
   *
   * @return array
   */
  public function getApproved()
  {
    $sql = "CALL spStickiness_Approved()";

    $params = array();
    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

}

?>