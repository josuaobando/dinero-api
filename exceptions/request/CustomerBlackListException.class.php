<?php

/**
 * @author Josua
 */
class CustomerBlackListException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_CUSTOMER_BLACKLIST);
  }

}

?>