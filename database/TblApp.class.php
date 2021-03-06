<?php

/**
 * @author Josua
 */
class TblApp extends Db
{

  /**
   * singleton reference for TblApp
   *
   * @var TblApp
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblApp
   *
   * @return TblApp
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblApp();
    }

    return self::$singleton;
  }

  /**
   * get companies
   *
   * @param $accountId
   *
   * @return array
   */
  public function getCompanies($accountId)
  {
    $sql = "CALL spCompany('{accountId}')";

    $params = array();
    $params['accountId'] = $accountId;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get agencies
   *
   * @return array
   */
  public function getAgencies()
  {
    $sql = "CALL spAgencies()";

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
    $sql = "CALL spApp_Transactions('{statusId}','{accountId}')";

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
   * @param $providerId
   * @param $statusList
   * @param $transactionTypeId
   * @param $filterAgencyType
   * @param $agencyList
   * @param $accountId
   * @param $companyId
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
  public function transactionReport($providerId, $statusList, $transactionTypeId, $filterAgencyType, $agencyList, $accountId, $companyId, $beginDate, $endDate, $controlNumber, $customer, $transactionId, $reference, $currentPage, $pageSize)
  {
    $sql = "CALL spApp_Report_Transactions('{providerId}', '{statusLis}', '{transactionTypeId}', '{agencyType}', '{agencyList}', '{accountId}', '{companyId}', '{beginDate}', '{endDate}', '{controlNumber}', '{customer}', '{transactionId}', '{reference}', '{pageStart}', '{pageSize}')";

    $params = array();
    $params['providerId'] = $providerId;
    $params['statusLis'] = $statusList;
    $params['transactionTypeId'] = $transactionTypeId;
    $params['agencyType'] = $filterAgencyType;
    $params['agencyList'] = $agencyList;
    $params['accountId'] = $accountId;
    $params['companyId'] = $companyId;
    $params['beginDate'] = $beginDate;
    $params['endDate'] = $endDate;
    $params['controlNumber'] = $controlNumber;
    $params['customer'] = $customer;
    $params['transactionId'] = $transactionId;
    $params['reference'] = $reference;
    $params['pageStart'] = $currentPage;
    $params['pageSize'] = $pageSize;

    return $this->executeMultiQuery($sql, array('transactions', 'summary'), $params);
  }

  /**
   * get attempts transactions
   *
   * @return array
   */
  public function getTransactionAttempts()
  {
    $sql = "CALL spReport_Attempts()";

    $rows = array();
    $params = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get declined transactions
   *
   * @return array
   */
  public function getTransactionDeclined()
  {
    $sql = "CALL spReport_Declined()";

    $rows = array();
    $params = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * get users
   *
   * @return array
   */
  public function getUsers()
  {
    $sql = "CALL spUsers()";

    $params = array();
    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

}

?>