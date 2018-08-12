<?php

/**
 * @author Josua
 */
class TblSystem extends Db
{

  /**
   * singleton reference for TblSystem
   *
   * @var TblSystem
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblSystem
   *
   * @return TblSystem
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblSystem();
    }

    return self::$singleton;
  }

  /**
   * get provider
   *
   * @param int $providerId
   * @param int $agencyId
   *
   * @return array
   */
  public function getProvider($providerId, $agencyId)
  {
    $sql = "CALL spProvider('{providerId}', '{agencyId}')";

    $params = array();
    $params['providerId'] = $providerId;
    $params['agencyId'] = $agencyId;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get agencies
   *
   * @param int $agencyId
   *
   * @return array
   */
  public function getAgency($agencyId)
  {
    $sql = "CALL spAgency('{agencyId}')";

    $params = array();
    $params['agencyId'] = $agencyId;

    $rows = array();
    $this->executeSingleQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get agencies
   *
   * @return array
   */
  public function getAgencies()
  {
    $sql = "CALL agencies()";

    $params = array();
    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * update agency
   *
   * @param int $agencyId
   * @param string $agencyName
   * @param int $agencyStatus
   *
   * @return int
   */
  public function updateAgency($agencyId, $agencyName, $agencyStatus)
  {
    $sql = "CALL agency_update('{agencyId}', '{name}', '{status}')";

    $params = array();
    $params['agencyId'] = $agencyId;
    $params['name'] = $agencyName;
    $params['status'] = $agencyStatus;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * get a list of transactions status
   *
   * @return array
   */
  public function getTransactionStatus()
  {
    $sql = "CALL transactionStatus()";

    $params = array();
    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get a list of transactions by status id
   *
   * @param int $statusId
   * @param int $accountId
   *
   * @return array
   */
  public function getTransactions($statusId, $accountId)
  {
    $sql = "CALL spTransactions('{statusId}','{accountId}')";

    $params = array();
    $params['statusId'] = $statusId;
    $params['accountId'] = $accountId;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get a list of transactions report
   *
   * @param $statusId
   * @param $transactionTypeId
   * @param $filterAgencyType
   * @param $filterAgencyId
   * @param $accountId
   * @param $beginDate
   * @param $endDate
   * @param $controlNumber
   * @param $customer
   * @param $transactionId
   * @param $reference
   * @param $currentPage
   * @param $pageSize
   *
   * @return array
   */
  public function getTransactionsReport($statusId, $transactionTypeId, $filterAgencyType, $filterAgencyId, $accountId, $beginDate, $endDate, $controlNumber, $customer, $transactionId, $reference, $currentPage, $pageSize)
  {
    $sql = "CALL spReport_Transactions('{statusId}', '{transactionTypeId}', '{agencyType}', '{agencyId}', '{accountId}', '{beginDate}', '{endDate}', '{controlNumber}', '{customer}', '{transactionId}', '{reference}', '{pageStart}', '{pageSize}')";

    $params = array();
    $params['statusId'] = $statusId;
    $params['transactionTypeId'] = $transactionTypeId;
    $params['agencyType'] = $filterAgencyType;
    $params['agencyId'] = $filterAgencyId;
    $params['accountId'] = $accountId;
    $params['beginDate'] = $beginDate;
    $params['endDate'] = $endDate;
    $params['controlNumber'] = $controlNumber;
    $params['customer'] = $customer;
    $params['transactionId'] = $transactionId;
    $params['reference'] = $reference;
    $params['pageStart'] = $currentPage;
    $params['pageSize'] = $pageSize;

    return $this->executeMultiQuery($sql, array('transactions', 'total', 'summary'), $params);
  }

}

?>