<?php

/**
 * @author Josua
 */
class TransactionException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_TRANSACTION);
  }

}

?>