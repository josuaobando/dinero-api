<?php

/**
 * @author Josua
 */
class TblLimit extends Db
{

  /**
   * singleton reference for TblLimit
   *
   * @var TblLimit
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblLimit
   *
   * @return TblLimit
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblLimit();
    }
    return self::$singleton;
  }

  /**
   * get limitDetails
   *
   * @param int $agencyTypeId
   *
   * @return array
   */
  public function getLimitDetails($agencyTypeId)
  {
    $sql = "CALL limitDetails('{agencyTypeId}')";

    $params = array();
    $params['agencyTypeId'] = $agencyTypeId;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

}

?>