<?php

/**
 * @author Josua
 */
class TransactionAPI extends Transaction
{

  const STATUS_API_REQUESTED = 'requested';
  const STATUS_API_PENDING = 'pending';
  const STATUS_API_APPROVED = 'approved';
  const STATUS_API_REJECTED = 'rejected';
  const STATUS_API_ERROR = 'error';

  /**
   * @var Customer
   */
  private $customer;

  /**
   * new Transaction instance
   */
  public function __construct()
  {
    parent::__construct();

    $this->customer = Session::getCustomer();
  }

  public function getName()
  {

  }

  public function confirm()
  {

  }

  public function getStatus()
  {

  }

  /**
   * @param int $transactionId
   */
  public function restore($transactionId)
  {
  }

  /**
   * serialize object
   *
   * @return array
   */
  public function toArray()
  {
    $data = parent::toArray();

    return $data;
  }

}

?>