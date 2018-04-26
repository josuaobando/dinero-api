<?php

/**
 * @author Josua
 */
class TblCountry extends Db
{

  /**
   * singleton reference for TblCountry
   *
   * @var TblCountry
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblCountry
   *
   * @return TblCountry
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblCountry();
    }
    return self::$singleton;
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