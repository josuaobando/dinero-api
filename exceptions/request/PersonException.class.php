<?php

/**
 * @author Josua
 */
class PersonException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_PERSON);
  }

}

?>