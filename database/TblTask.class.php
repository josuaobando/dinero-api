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
   *
   * @return array
   */
  public function getTask()
  {
    $sql = "CALL spTask()";

    $row = array();
    $this->executeQuery($sql, $rows);

    return $row;
  }

}

?>