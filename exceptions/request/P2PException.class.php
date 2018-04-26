<?php

/**
 * @author Josua
 */
class P2PException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_P2P);
  }

}

?>