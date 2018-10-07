<?php

/**
 * @author Josua
 */
class P2PLimitException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_P2P_LIMIT);
  }

}

?>