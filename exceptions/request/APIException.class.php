<?php

/**
 * @author Josua
 */
class APIException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_API);
  }

}

?>