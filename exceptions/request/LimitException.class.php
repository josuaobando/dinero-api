<?php

/**
 * @author Josua
 */
class LimitException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_LIMIT);
  }

}

?>