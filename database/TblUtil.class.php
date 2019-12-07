<?php

/**
 * @author Josua
 */
class TblUtil extends Db
{

  /**
   * singleton reference for TblUtil
   *
   * @var TblUtil
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblUtil
   *
   * @return TblUtil
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblUtil();
    }
    return self::$singleton;
  }

  /**
   * get a country by code
   *
   * @param string $code
   *
   * @return array
   */
  public function getCountry($code)
  {
    $sql = "CALL country('{code}')";

    $params = array();
    $params['code'] = $code;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * get a state by code
   *
   * @param int $countryId
   * @param string $code
   *
   * @return array
   */
  public function getState($countryId, $code)
  {
    $sql = "CALL spCountryState('{countryId}', '{code}')";

    $params = array();
    $params['countryId'] = $countryId;
    $params['code'] = $code;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * get the list of countries
   *
   * @return array
   */
  public function getCountries()
  {
    $sql = "CALL countries()";

    $rows = array();
    $this->executeQuery($sql, $rows);

    return $rows;
  }

  /**
   * get the list of states for a specific country
   *
   * @param string $countryCode
   *
   * @return array
   */
  public function getStates($countryCode)
  {
    $sql = "CALL states('{countryCode}')";

    $params = array();
    $params['countryCode'] = $countryCode;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

}

?>