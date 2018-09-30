<?php

/**
 * @author Josua
 */
class TblTransaction extends Db
{

  /**
   * singleton reference for TblTransaction
   *
   * @var TblTransaction
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblTransaction
   *
   * @return TblTransaction
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblTransaction();
    }
    return self::$singleton;
  }

  /**
   * insert a new transaction
   *
   * @param int $transactionTypeId
   * @param int $transactionStatusId
   * @param int $agencyTypeId
   * @param int $customerId
   * @param int $personId
   * @param string $username
   * @param float $amount
   * @param float $fee
   * @param int $agencyId
   * @param int $accountId
   * @param string $merchantTransId
   * @param int $apiTransactionId
   *
   * @return int
   */
  public function insert($transactionTypeId, $transactionStatusId, $agencyTypeId, $customerId, $personId, $username, $amount, $fee, $agencyId, $accountId, $merchantTransId, $apiTransactionId)
  {
    $sql = "CALL spTransaction_Insert('{transactionTypeId}', '{transactionStatusId}', '{agencyTypeId}', '{agencyId}', '{customerId}', '{personId}', '{username}', '{amount}', '{fee}', '{accountId}', '{reference}', '{apiTransactionId}', @TransactionId)";

    $params = array();
    $params['transactionTypeId'] = $transactionTypeId;
    $params['transactionTypeId'] = $transactionTypeId;
    $params['transactionStatusId'] = $transactionStatusId;
    $params['agencyTypeId'] = $agencyTypeId;
    $params['agencyId'] = $agencyId;
    $params['customerId'] = $customerId;
    $params['personId'] = $personId;
    $params['username'] = $username;
    $params['amount'] = $amount;
    $params['fee'] = $fee;
    $params['accountId'] = $accountId;
    $params['reference'] = $merchantTransId;
    $params['apiTransactionId'] = $apiTransactionId;

    $this->setOutputParams(array('TransactionId'));
    $this->executeUpdate($sql, $params);
    $output = $this->getOutputResults();
    $transactionId = $output['TransactionId'];

    return $transactionId;
  }

  /**
   * update transaction
   *
   * @param int $transactionId
   * @param int $transactionStatusId
   * @param int $customerId
   * @param int $personId
   * @param float $amount
   * @param float $fee
   * @param int $agencyId
   * @param int $modifiedBy
   * @param string $controlNumber
   * @param string $reason
   * @param string $note
   * @param int $apiTransactionId
   *
   * @return int
   */
  public function update($transactionId, $transactionStatusId, $customerId, $personId, $amount, $fee, $agencyId, $modifiedBy, $controlNumber, $reason, $note, $apiTransactionId)
  {
    $sql = "CALL spTransaction_Update('{transactionId}', '{transactionStatusId}', '{customerId}', '{personId}', '{amount}', '{fee}', '{agencyId}', '{modifiedBy}', '{controlNumber}', '{reason}', '{note}', '{apiTransactionId}')";

    if(!$modifiedBy){
      $modifiedBy = CoreConfig::USER_SYSTEM;
    }

    $params = array();
    $params['transactionId'] = $transactionId;
    $params['transactionStatusId'] = $transactionStatusId;
    $params['customerId'] = $customerId;
    $params['personId'] = $personId;
    $params['amount'] = $amount;
    $params['fee'] = $fee;
    $params['agencyId'] = $agencyId;
    $params['modifiedBy'] = $modifiedBy;
    $params['controlNumber'] = $controlNumber;
    $params['reason'] = $reason;
    $params['note'] = $note;
    $params['apiTransactionId'] = $apiTransactionId;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * get transaction
   *
   * @param int $transactionId
   *
   * @return array
   */
  public function getTransaction($transactionId)
  {
    $sql = "CALL spTransaction('{transactionId}')";

    $params = array();
    $params['transactionId'] = $transactionId;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * get transaction by control number
   *
   * @param int $controlNumber
   *
   * @return array
   */
  public function getTransactionByControlNumber($controlNumber)
  {
    $sql = "CALL spTransaction_byControlNumber('{controlNumber}')";

    $params = array();
    $params['controlNumber'] = $controlNumber;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

}

?>