<?php

/**
 * @author Josua
 */
class Reader_Obj extends Reader
{

  /**
   * @see Reader::parse()
   *
   * @param string $data
   *
   * @return mixed
   *
   * @throws WSException
   */
  public function parse($data)
  {
    $simpleXml = simplexml_load_string($data);
    if(!($simpleXml instanceof SimpleXMLElement)){
      return json_decode($data);
    }else{
      return json_decode(json_encode($simpleXml));
    }
  }

}

?>