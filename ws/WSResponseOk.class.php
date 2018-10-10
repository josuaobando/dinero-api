<?php

/**
 * @author Josua
 */
class WSResponseOk extends WSResponse
{

  /**
   * WSResponseOk constructor.
   *
   * @param string $systemMessage
   */
  public function __construct($systemMessage = "")
  {
    parent::__construct($systemMessage);
  }

}

?>