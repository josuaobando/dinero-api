<?php

/**
 * @author Josua
 */
class SessionException extends Exception
{

  public function __construct($description)
  {
    parent::__construct($description);
  }

}

?>