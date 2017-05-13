<?php

/**
 * Gustavo Granados
 * code is poetry
 */
class Transaction
{

  /**
   * @var int
   */
  private $transactionId;

  /**
   * @var int
   */
  private $transactionStatusId;

  /**
   * @var int
   */
  private $transactionTypeId;

  /**
   * @var int
   */
  private $agencyTypeId;

  /**
   * @var int
   */
  private $customerId;

  /**
   * @var int
   */
  private $personId;

  /**
   * @var string
   */
  private $username;

  /**
   * @var float
   */
  private $amount;

  /**
   * @var float
   */
  private $fee;

  /**
   * @var int
   */
  private $agencyId;

  /**
   * @var string
   */
  private $controlNumber;

  /**
   * @var int
   */
  private $accountId;

  /**
   * @var bool
   */
  private $API;

  /**
   * @var string
   */
  private $note;

  /**
   * @var string
   */
  private $reason;

  /**
   * Transaction Type
   */
  const TYPE_RECEIVER = 1;
  const TYPE_SENDER = 2;

  /**
   * Transaction Status
   */
  const STATUS_REQUESTED = 1;
  const STATUS_SUBMITTED = 2;
  const STATUS_APPROVED = 3;
  const STATUS_REJECTED = 4;
  const STATUS_CANCELED = 5;

  /**
   * Agency Type
   */
  const AGENCY_WESTERN_UNION = 1;
  const AGENCY_MONEY_GRAM = 2;
  const AGENCY_RIA = 3;

  /**
   * TblTransaction reference
   *
   * @var TblTransaction
   */
  private $tblTransaction;

  /**
   * new Transaction instance
   */
  public function __construct()
  {
    $this->tblTransaction = TblTransaction::getInstance();
  }

  /**
   * @return int
   */
  public function getTransactionId()
  {
    return $this->transactionId;
  }

  /**
   * @return int
   *
   * @see Transaction::TYPE_RECEIVER, Transaction::TYPE_SENDER
   */
  public function getTransactionTypeId()
  {
    return $this->transactionTypeId;
  }

  /**
   * @return int
   *
   * @see Transaction::STATUS_REQUESTED, Transaction::STATUS_SUBMITTED, Transaction::STATUS_APPROVED, Transaction::STATUS_REJECTED, Transaction::STATUS_CANCELED
   */
  public function getTransactionStatusId()
  {
    return $this->transactionStatusId;
  }

  /**
   * @return int
   *
   * @see Transaction::AGENCY_WESTERN_UNION, Transaction::AGENCY_MONEY_GRAM, Transaction::AGENCY_RIA
   */
  public function getAgencyTypeId()
  {
    return $this->agencyTypeId;
  }

  /**
   * @return int
   */
  public function getCustomerId()
  {
    return $this->customerId;
  }

  /**
   * @return int
   */
  public function getAgencyId()
  {
    return $this->agencyId;
  }

  /**
   * @return int
   */
  public function getPersonId()
  {
    return $this->personId;
  }

  /**
   * @return int
   */
  public function getAmount()
  {
    return $this->amount;
  }

  /**
   * @return int
   */
  public function getFee()
  {
    return $this->fee;
  }

  /**
   * @param int $transactionTypeId
   *
   * @see Transaction::TYPE_RECEIVER, Transaction::TYPE_SENDER
   */
  public function setTransactionTypeId($transactionTypeId)
  {
    $this->transactionTypeId = $transactionTypeId;
  }

  /**
   * @param int $transactionStatusId
   *
   * @see Transaction::STATUS_REQUESTED, Transaction::STATUS_SUBMITTED, Transaction::STATUS_APPROVED, Transaction::STATUS_REJECTED, Transaction::STATUS_CANCELED
   */
  public function setTransactionStatusId($transactionStatusId)
  {
    $this->transactionStatusId = $transactionStatusId;
  }

  /**
   * @param int $agencyTypeId
   *
   * @see Transaction::AGENCY_WESTERN_UNION, Transaction::AGENCY_MONEY_GRAM, Transaction::AGENCY_RIA
   */
  public function setAgencyTypeId($agencyTypeId)
  {
    $this->agencyTypeId = $agencyTypeId;
  }

  /**
   * @param int $customerId
   */
  public function setCustomerId($customerId)
  {
    $this->customerId = $customerId;
  }

  /**
   * @param int $personId
   */
  public function setPersonId($personId)
  {
    $this->personId = $personId;
  }

  /**
   * @param string $username
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }

  /**
   * @param float $amount
   */
  public function setAmount($amount)
  {
    $this->amount = $amount;
  }

  /**
   * @param float $fee
   */
  public function setFee($fee)
  {
    $this->fee = $fee;
  }

  /**
   * @param int $agencyId
   */
  public function setAgencyId($agencyId)
  {
    $this->agencyId = $agencyId;
  }

  /**
   * @param string $controlNumber
   */
  public function setControlNumber($controlNumber)
  {
    $this->controlNumber = $controlNumber;
  }

  /**
   * @param int $accountId
   */
  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }

  /**
   * @param boolean $API
   */
  public function setAPI($API)
  {
    $this->API = $API;
  }

  /**
   * @param string $note
   */
  public function setNote($note)
  {
    $this->note = $note;
  }

  /**
   * @param string $reason
   */
  public function setReason($reason)
  {
    $this->reason = $reason;
  }

  /**
   * create new transaction and load the Transaction ID
   */
  public function create()
  {
    $this->transactionId = $this->tblTransaction->insert($this->transactionTypeId, $this->transactionStatusId, $this->agencyTypeId, $this->customerId, $this->personId, $this->username, $this->amount, $this->fee, $this->agencyId, $this->accountId);
  }

  /**
   * update transaction with current data
   */
  public function update()
  {
    $this->validateControlNumber();
    return $this->tblTransaction->update($this->transactionId, $this->transactionStatusId, $this->customerId, $this->personId, $this->amount, $this->fee, $this->agencyId, $this->accountId, $this->controlNumber, $this->reason, $this->note);
  }

  /**
   * @param int $transactionId
   */
  public function restore($transactionId)
  {
    $transactionData = $this->tblTransaction->getTransaction($transactionId);

    $this->transactionId = $transactionData['Transaction_Id'];
    $this->transactionTypeId = $transactionData['TransactionType_Id'];
    $this->transactionStatusId = $transactionData['TransactionStatus_Id'];
    $this->agencyTypeId = $transactionData['AgencyType_Id'];
    $this->customerId = $transactionData['Customer_Id'];
    $this->personId = $transactionData['Person_Id'];
    $this->username = $transactionData['Username'];
    $this->amount = $transactionData['Amount'];
    $this->fee = $transactionData['Fee'];
    $this->agencyId = $transactionData['Agency_Id'];
    $this->controlNumber = $transactionData['ControlNumber'];
    $this->accountId = $transactionData['Account_Id'];
    $this->API = $transactionData['API'];
    $this->reason = $transactionData['Reason'];
    $this->note = $transactionData['Note'];
  }

  /**
   * get the description of the current transaction status id
   *
   * @return string
   */
  public function getTransactionStatus()
  {
    switch($this->transactionStatusId)
    {
      case Transaction::STATUS_REQUESTED:
        return "requested";
        break;
      case Transaction::STATUS_SUBMITTED:
        return "submitted";
        break;
      case Transaction::STATUS_APPROVED:
        return "approved";
        break;
      case Transaction::STATUS_REJECTED:
        return "rejected";
        break;
      case Transaction::STATUS_CANCELED:
        return "canceled";
        break;
      default:
        return "unknown";
        break;
    }

    return "unknown";
  }

  /**
   * validate if already exists control number
   *
   * @throws InvalidStateException
   */
  private function validateControlNumber()
  {
    if($this->controlNumber)
    {
      $transactionData = $this->tblTransaction->getTransactioByControlNumber($this->controlNumber);
      if($transactionData && count($transactionData) > 0)
      {
        $transactionId = $transactionData['Transaction_Id'];
        if($this->transactionId != $transactionId)
        {
          throw new InvalidStateException("The Tracking Number [$this->controlNumber] already exists, check it please!");
        }
      }
    }
  }

  /**
   * serialize object
   *
   * @return array
   */
  public function toArray()
  {
    $data = array();

    $data['id'] = $this->transactionId;
    $data['status_id'] = $this->transactionStatusId;
    $data['status'] = $this->getTransactionStatus();
    $data['uid'] = $this->username;
    $data['amount'] = $this->amount;
    $data['fee'] = $this->fee;
    $data['notes'] = $this->reason;
    $data['controlNumber'] = $this->controlNumber;

    return $data;
  }

}

?>