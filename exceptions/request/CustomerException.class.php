<?php

/**
 * @author Josua
 */
class CustomerException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_CUSTOMER);
  }

}

?>