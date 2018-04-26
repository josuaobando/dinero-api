<?php

/**
 * @author Josua
 */
class UnhandledException extends GeneralException
{

  public function __construct($ex)
  {
    parent::__construct(__CLASS__);

    $this->description = "Message: " . $ex->getMessage() . "\n\n";
    $this->description .= "Unhandled Exception Stack Trace:\n" . $ex->getTraceAsString() . "\n\n";

    $this->description .= "Caused by:\n";
    $this->description .= "------------------------- start of unhandled exception\n\n";
    $this->description .= "Message: " . $ex . "\n";
    $this->description .= "------------------------- end of unhandled exception\n";
  }

}

?>