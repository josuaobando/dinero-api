<?php

/**
 * @author Josua
 */
class TblTask extends Db
{

  /**
   * singleton reference for TblAccount
   *
   * @var TblTask
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblTask
   *
   * @return TblTask
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblTask();
    }
    return self::$singleton;
  }

  /**
   * get task to execute
   *
   * @return array
   */
  public function getTask()
  {
    $sql = "CALL spTask()";

    $rows = array();
    $this->executeQuery($sql, $rows);

    return $rows;
  }

  /**
   * get pending API transactions
   *
   * @return array
   */
  public function getPendingTransactions()
  {
    $sql = "CALL spTask_PendingTransaction()";

    $rows = array();
    $this->executeQuery($sql, $rows);

    return $rows;
  }

  /**
   * get report transactions
   *
   * @param int $agencyTypeId
   * @param int $agencyId
   *
   * @return array
   */
  public function getReportTransactions($agencyTypeId, $agencyId = 0)
  {
    $sql = "CALL spTask_Report_Transactions('{agencyTypeId}', '{agencyId}')";

    $params = array();
    $params['agencyTypeId'] = $agencyTypeId;
    $params['agencyId'] = $agencyId;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

}

?>