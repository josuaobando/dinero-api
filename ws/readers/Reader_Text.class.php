<?php

/**
 * @author Josua
 */
class Reader_Text extends Reader
{

  /**
   * the reader delimiter
   *
   * @var string
   */
  protected $delimiter = null;

  /**
   * the reader pair delimiter
   *
   * @var string
   */
  protected $pairDelimiter = null;

  public function __construct($delim = null, $pairDelim = null)
  {
    $this->delimiter = $delim;
    $this->pairDelimiter = $pairDelim;
  }

  /**
   * @see Reader::parse()
   *
   * @return array
   */
  public function parse($data)
  {
    $values = array();
    if($this->delimiter && is_string($data)){
      $values = explode($this->delimiter, $data);
      if($this->pairDelimiter && is_array($values)){
        $subValues = array();
        foreach($values as $value){
          $value = trim($value);
          if(!$value){
            continue;
          }
          $parts = explode($this->pairDelimiter, $value);
          $subValues[$parts[0]] = $parts[1];
        }
        $values = $subValues;
      }
    }else{
      parse_str($data, $values);
    }

    return $values;
  }

}

?>