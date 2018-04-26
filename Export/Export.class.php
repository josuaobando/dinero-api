<?php

/**
 * @author jobando
 */
abstract class Export
{
  protected $data = null;
  protected $fileName = null;
  protected $headers = null;
  protected $title = null;
  protected $format = null;

  const FORMAT_TYPE_DECIMAL = '1';
  const FORMAT_TYPE_CURRENCY = '2';
  const FORMAT_TYPE_PERCENTAGE = '3';
  const FORMAT_TYPE_DATE = '4';

  const FIELD_DATA_TYPE_STRING = '1';
  const FIELD_DATA_TYPE_NUMERIC = '2';
  const FIELD_DATA_TYPE_BOOL = '3';

  /**
   * constructor of the class
   *
   * @param string $fileName
   * @param array $data
   * @param array $headers
   * @param string $title
   * @param array $format
   *
   */
  function __construct($fileName, $data, $headers, $title, $format = array())
  {
    $this->fileName = $fileName;
    $this->data = $data;
    $this->headers = $headers;
    $this->title = $title;
    $this->format = $format;
  }

  public function export()
  {
    return $this->process();
  }

  /**
   * apply format
   *
   * @param string $key
   * @param string $value
   *
   * @return string
   */
  protected function applyFormat($key, $value)
  {
    $format = $this->format[$key];

    if($format){
      $type = $format['Type'];
      $format = $format['Format'] ? $format['Format'] : 0;
      switch($type){
        case self::FORMAT_TYPE_DECIMAL:
          return number_format($value, $format);
        case self::FORMAT_TYPE_CURRENCY:
          return "$" . number_format($value, $format);
        case self::FORMAT_TYPE_PERCENTAGE:
          return number_format($value, $format) . "%";
        case self::FORMAT_TYPE_DATE:
          //TODO: not implemented
          break;
      }
    }

    return $value;
  }

  /**
   * return format field
   * @param string $key
   */
  protected function getFormatDataType($key)
  {
    $format = $this->format[$key];
    if($format){
      $type = $format['DataType'];
      switch($type){
        case self::FIELD_DATA_TYPE_NUMERIC:
          return PHPExcel_Cell_DataType::TYPE_NUMERIC;
        case self::FIELD_DATA_TYPE_BOOL:
          return PHPExcel_Cell_DataType::TYPE_BOOL;
        default:
          return PHPExcel_Cell_DataType::TYPE_STRING;
          break;
      }
    }

    return PHPExcel_Cell_DataType::TYPE_STRING;
  }

  /**
   * return format
   * @param string $key
   */
  protected function getFormat($key)
  {
    $format = $this->format[$key];
    if($format){
      return $format['Format'];
    }
    return "";
  }

  abstract protected function process();
}

?>