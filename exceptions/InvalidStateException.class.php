<?php

/**
 * @author Josua
 */
class InvalidStateException extends GeneralException
{

  public function __construct($description)
  {
    parent::__construct($description);
  }

}

?>