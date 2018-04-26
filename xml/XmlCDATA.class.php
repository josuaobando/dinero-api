<?php

/**
 * @author Josua
 */
class XmlCDATA
{

  /**
   * cdata for a xml with value
   *
   * @var string
   */
  public $value = null;

  public function __construct($cData)
  {
    $this->value = $cData;
  }

  /**
   * get the cdata of the element
   *
   * @return string
   */
  public function toCDATA()
  {
    return $this->value;
  }

}

?>