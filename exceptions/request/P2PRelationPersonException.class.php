<?php

/**
 * @author Josua
 *
 * @inheritdoc exception to link right customer to name
 */
class P2PRelationPersonException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_P2P_RELATION_PERSON);
  }

}

?>