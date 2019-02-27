<?php

/**
 * @author Josua
 *
 * @inheritdoc exception to link right name to customer
 */
class P2PRelationCustomerException extends RequestException
{

  public function __construct($description)
  {
    parent::__construct($description, self::ERROR_P2P_RELATION_CUSTOMER);
  }

}

?>