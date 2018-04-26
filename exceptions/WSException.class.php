<?php

/**
 * @author Josua
 */
class WSException extends GeneralException
{

  public function __construct($description)
  {
    parent::__construct($description);
  }

}

?>