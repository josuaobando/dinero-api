<?php

/**
 * @author Josua
 */
class System
{

  /**
   * TblSystem reference
   *
   * @var TblSystem
   */
  private $tblSystem;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->tblSystem = TblSystem::getInstance();
  }

  /**
   * get agencies
   *
   * @return array
   */
  public function getAgencies()
  {
    return $this->tblSystem->getAgencies();
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
    return $this->tblSystem->updateAgency($agencyId, $agencyName, $agencyStatus);
  }

  /**
   * get a list of transactions status
   *
   * @return array
   */
  public function transactionStatus()
  {
    return $this->tblSystem->getTransactionStatus();
  }

  /**
   * get a list of transactions by status id
   *
   * @param int $statusId
   * @param int $accountId
   *
   * @return array
   */
  public function transactions($statusId, $accountId)
  {
    return $this->tblSystem->getTransactions($statusId, $accountId);
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
   *
   * @return array
   */
  public function transactionsReport($statusId, $transactionTypeId, $filterAgencyType, $filterAgencyId, $accountId, $beginDate, $endDate, $controlNumber, $customer, $transactionId, $reference, $currentPage)
  {
    $pageSize = CoreConfig::PAGINATION_TABLE_MAX_ROWS;
    $currentPage = ($currentPage - 1) * CoreConfig::PAGINATION_TABLE_MAX_ROWS;
    return $this->tblSystem->getTransactionsReport($statusId, $transactionTypeId, $filterAgencyType, $filterAgencyId, $accountId, $beginDate, $endDate, $controlNumber, $customer, $transactionId, $reference, $currentPage, $pageSize);
  }

  /**
   * get attempts transactions
   *
   * @return array
   */
  public function transactionAttempts()
  {
    return $this->tblSystem->getTransactionAttempts();
  }

}

?>