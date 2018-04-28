<?php

/**
 * @author Josua
 */
class TransactionAPI extends Transaction
{

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