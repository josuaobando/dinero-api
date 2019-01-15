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
   * @param $agencyTypeId
   * @param $personId
   *
   * @return int
   */
  public function create($customerId, $agencyTypeId, $personId)
  {
    $sql = "CALL spStickiness_Add('{customerId}', '{agencyTypeId}', '{personId}', @stickinessId)";

    $params = array();
    $params['customerId'] = $customerId;
    $params['agencyTypeId'] = $agencyTypeId;
    $params['personId'] = $personId;

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
   *
   * @return int
   */
  public function update($stickinessId)
  {
    $sql = "CALL spStickiness_Update('{stickinessId}')";

    $params = array();
    $params['stickinessId'] = $stickinessId;

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
   * @param int $agencyTypeId
   *
   * @return array
   */
  public function getByCustomerId($customerId, $agencyTypeId)
  {
    $sql = "CALL spStickiness_ByCustomerId('{customerId}', 'agencyTypeId')";

    $params = array();
    $params['customerId'] = $customerId;
    $params['agencyTypeId'] = $agencyTypeId;

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

}

?>