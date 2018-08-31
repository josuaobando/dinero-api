<?php

/**
 * @author Josua
 */
class APIPersonException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_API_PERSON);
  }

}

?>