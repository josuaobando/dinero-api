<?php

/**
 * @author Josua
 */
class APILimitException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_API_LIMIT);
  }

}

?>