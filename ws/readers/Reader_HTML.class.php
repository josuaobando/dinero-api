<?php

/**
 * @author jobando
 */
class Reader_HTML extends Reader
{

  /**
   * @see Reader::parse()
   *
   * @param string $data
   *
   * @return array
   *
   * @throws WSException
   */
  public function parse($data)
  {
    // catch internal errors
    $previousState = libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->loadHTML($data);
    // get internal errors on parse
    $errors = $this->getErrorParse();
    // restore state
    libxml_use_internal_errors($previousState);

    if($errors){
      $exceptionMessage = "HTML Parser: \n\n";
      $exceptionMessage .= $data . "\n\n";
      $exceptionMessage .= "HTML Error: \n\n";
      $exceptionMessage .= $errors . "\n";
      throw new WSException($exceptionMessage);
    }

    $xpath = new DOMXPath($doc);
    $elements = $xpath->query('//input[@type]');
    if($elements->length){
      $values = array();
      foreach($elements as $element){
        $index = ($element->getAttribute('name')) ? $element->getAttribute('name') : $element->getAttribute('id');
        $value = $element->getAttribute('value');
        $values[$index] = $value;
      }
    }else{
      $exceptionMessage = "HTML Parser: \n\n";
      $exceptionMessage .= $data . "\n\n";
      throw new WSException($exceptionMessage);
    }

    return $values;
  }

  /**
   * get internal errors/warnings
   *
   * @return array|bool
   */
  private function getErrorParse()
  {
    $errors = array();
    // get errors  
    foreach(libxml_get_errors() as $error){
      array_push($errors, json_encode($error));
    }

    if($errors){
      // clean errors
      libxml_clear_errors();
      return implode(', ', $errors);
    }

    return false;
  }

}

?>