<?php

/**
 * @author Josua
 */
class WSResponseError extends WSResponse
{

  public function __construct($systemMessage, $systemCode = 0)
  {
    parent::__construct($systemMessage);
    $this->setState(WSResponse::STATE_ERROR);
    $this->setCode($systemCode >= 0 ? $systemCode : 0);
  }

}

?>