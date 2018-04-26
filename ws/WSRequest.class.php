<?php

/**
 * @author Josua
 */
class WSRequest extends Request
{

  /**
   * check if the value is numeric
   *
   * @param string $key
   *
   * @return mixed
   *
   * @throws InvalidParameterException
   */
  public function requireNumeric($key)
  {
    $value = $this->getParam($key);
    if(!is_numeric($value)){
      throw new InvalidParameterException($key, $value, __FUNCTION__);
    }

    return $value;
  }

  /**
   * check if the value is numeric AND is more than 0
   *
   * @param string $key
   *
   * @return mixed
   *
   * @throws InvalidParameterException
   */
  public function requireNumericAndPositive($key)
  {
    $value = $this->getParam($key);
    if(!is_numeric($value) || $value <= 0){
      throw new InvalidParameterException($key, $value, __FUNCTION__);
    }

    return $value;
  }

  /**
   * check if the parameter is null or empty;
   * empty only applies for string values
   *
   * @param string $key
   *
   * @return mixed
   *
   * @throws InvalidParameterException
   */
  public function requireNotNullOrEmpty($key)
  {

    $value = $this->getParam($key);
    if(is_null($value) || (is_string($value) && trim($value) == '')){
      throw new InvalidParameterException($key, $value, __FUNCTION__);
    }

    return $value;
  }

  /**
   * check if the parameter is null
   *
   * @param string $key
   *
   * @return mixed
   *
   * @throws InvalidParameterException
   */
  public function requireNotNull($key)
  {
    $value = $this->getParam($key);
    if(is_null($value)){
      throw new InvalidParameterException($key, $value, __FUNCTION__);
    }

    return $value;
  }

  /**
   * check if the value is between the range required
   *
   * @param string $key
   *
   * @return mixed
   *
   * @throws InvalidParameterException
   */
  public function requireRange($key, $min, $max)
  {
    $value = $this->getParam($key);
    if(!is_numeric($value) || $value < $min || $value > $max){
      throw new InvalidParameterException($key, $value, __FUNCTION__);
    }

    return $value;
  }

  /**
   * check if the value matches a valid date.
   *
   * @param $key
   * @param bool $allowEmpty
   *
   * @return false|string
   * @throws \InvalidParameterException.
   */
  public function requireDate($key, $allowEmpty = false)
  {

    $value = trim($this->getParam($key));
    if(empty($value) && $allowEmpty){
      return trim($value);
    }

    $unixTimeStamp = strtotime($value);
    if(!$unixTimeStamp){
      throw new InvalidParameterException($key, $value, __FUNCTION__);
    }

    return date("Y-m-d", $unixTimeStamp);
  }

  /**
   * check if the value matches a valid datetime
   *
   * @param $key
   * @param bool $allowEmpty
   *
   * @return false|string
   *
   * @throws \InvalidParameterException
   */
  public function requireDateTime($key, $allowEmpty = false)
  {

    $value = trim($this->getParam($key));
    if(empty($value) && $allowEmpty){
      return trim($value);
    }

    $unixTimeStamp = strtotime($value);
    if(!$unixTimeStamp){
      throw new InvalidParameterException($key, $value, __FUNCTION__);
    }

    return date("Y-m-d H:i:s", $unixTimeStamp);
  }

  /**
   * check if the value matches a valid date.
   *
   * @param string $key
   *
   * @return string
   */
  public function getDate($key)
  {

    $value = trim($this->getParam($key));
    $unixTimeStamp = strtotime($value);
    if(!$unixTimeStamp){
      return "";
    }

    return date("Y-m-d", $unixTimeStamp);
  }

}

?>