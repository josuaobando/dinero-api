<?php

/**
 * @author Josua
 */
class InvalidParameterException extends GeneralException
{

  /**
   * @var string
   */
  protected $function = '';
  /**
   * @var string
   */
  protected $parameter = '';
  /**
   * @var null
   */
  protected $parameterValue;

  /**
   * @return string
   */
  public function getKey()
  {
    return 'invalid.exception.parameter';
  }

  /**
   * @return string
   */
  public function getParameter()
  {
    return $this->parameter;
  }

  /**
   * @return string
   */
  public function getFunction()
  {
    return $this->function;
  }

  public function __construct($parameter, $parameterValue, $function)
  {
    $this->function = $function;
    $this->parameter = $parameter;
    $this->parameterValue = $parameterValue;
    parent::__construct("missing or invalid parameter: $parameter");
  }

}

?>