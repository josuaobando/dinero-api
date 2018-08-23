<?php

/**
 * @author Josua
 */
class APIBlackListException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_API_BLACKLIST);
  }

}

?>