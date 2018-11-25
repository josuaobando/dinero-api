<?php

/**
 * @author Josua
 */
class AppManager
{

  /**
   * TblApp reference
   *
   * @var TblApp
   */
  private $tblApp;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->tblApp = TblApp::getInstance();
  }

  /**
   * get a list of transactions status
   *
   * @return array
   */
  public function agencies()
  {
    return $this->tblApp->getAgencies();
  }

  /**
   * get a list of transactions status
   *
   * @return array
   */
  public function transactionStatus()
  {
    return $this->tblApp->getTransactionStatus();
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
    return $this->tblApp->getTransactions($statusId, $accountId);
  }

  /**
   * get transactions by filters
   *
   * @param $statusId
   * @param $transactionTypeId
   * @param $filterAgencyType
   * @param $agencyList
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
  public function transactionReport($statusId, $transactionTypeId, $filterAgencyType, $agencyList, $accountId, $beginDate, $endDate, $controlNumber, $customer, $transactionId, $reference, $currentPage)
  {
    return $this->tblApp->transactionReport($statusId, $transactionTypeId, $filterAgencyType, $agencyList, $accountId, $beginDate, $endDate, $controlNumber, $customer, $transactionId, $reference, $currentPage, 9999);
  }

  /**
   * get attempts transactions
   *
   * @return array
   */
  public function transactionAttempts()
  {
    return $this->tblApp->getTransactionAttempts();
  }

  /**
   * get declined transactions
   *
   * @return array
   */
  public function transactionDeclined()
  {
    return $this->tblApp->getTransactionDeclined();
  }

}

?>