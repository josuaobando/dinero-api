<?php

/**
 * @author Josua
 */
class TblManager extends Db
{

  /**
   * singleton reference for TblManager
   *
   * @var TblManager
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblManager
   *
   * @return TblManager
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton))
    {
      self::$singleton = new TblManager();
    }

    return self::$singleton;
  }

  /**
 * get available names
 *
 * @param int $accountId
 * @param float $amount
 * @param int $agencyTypeId
 * @param int $agencyId
 *
 * @return array
 */
  public function getPersonsAvailable($accountId, $amount, $agencyTypeId, $agencyId)
  {
    $sql = "CALL persons_available('{accountId}', '{amount}', '{agencyTypeId}', '{agencyId}')";

    $params = array();
    $params['accountId'] = $accountId;
    $params['amount'] = $amount;
    $params['agencyTypeId'] = $agencyTypeId;
    $params['agencyId'] = $agencyId;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get available names
   *
   * @return array
   */
  public function getRelations()
  {
    $sql = "CALL spRelations()";

    $params = array();
    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

}

?>