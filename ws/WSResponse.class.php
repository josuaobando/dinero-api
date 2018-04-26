<?php

/**
 * @author Josua
 */
class WSResponse
{
  protected $state = null;
  protected $code = null;
  protected $userMessage = null;
  protected $systemMessage = null;

  const STATE_OK = "1";
  const STATE_ERROR = "2";
  const STATE_SESSION_EXPIRED = "expired";
  const STATE_LOGOUT = "logout";
  const STATE_COMPLETE = "complete";
  const STATE_INCOMPLETE = "incomplete";
  const STATE_CANCELLED = "cancelled";

  const DEFAULT_FORMAT = 'xml';
  const FORMAT_JSON = 'json';
  const FORMAT_XML = 'xml';

  const TO_ARRAY_METHOD = 'toArray';

  private $format = null;

  private $JSONCallback = null;

  private $elements = null;

  public function __construct($systemMessage = null)
  {
    $this->state = WSResponse::STATE_OK;
    $this->systemMessage = $systemMessage;
    $this->format = self::DEFAULT_FORMAT;
    $this->elements = array();
  }

  /**
   * set response state
   *
   * @param string $state
   */
  public function setState($state)
  {
    $this->state = $state;
  }

  /**
   * set response result code
   *
   * @param string $code
   */
  public function setCode($code)
  {
    $this->code = $code;
  }

  /**
   * add a new element to the response
   *
   * @param string $name
   * @param mixed $element
   */
  public function addElement($name, $element)
  {
    if(!$name || trim($name) == ''){
      return;
    }
    $this->elements[$name] = $element;
  }

  /**
   * remove a element to the response
   *
   * @param string $name
   */
  public function removeElement($name)
  {
    if(!$name || trim($name) == ''){
      return;
    }
    unset($this->elements[$name]);
  }

  /**
   * @param string $message
   */
  public function setMessage($message)
  {
    $this->userMessage = $message;
  }

  /**
   * set the format (xml,json)
   *
   * @param string $format
   */
  public function setFormat($format)
  {
    if(strtolower($format) != self::FORMAT_XML && strtolower($format) != self::FORMAT_JSON){
      return;
    }
    $this->format = $format;
  }

  /**
   * get the response in xml format
   *
   * @return string
   */
  private function getXMLResponse()
  {
    $xmlState = new XmlElement("code");
    $xmlState->setValue($this->state);

    $xmlError = new XmlElement("error");
    $xmlError->setValue($this->code);

    $xmlSystemMessage = new XmlElement("message");
    $xmlSystemMessage->setValue($this->systemMessage);

    $xml = new XmlElement('result');
    $xml->addElement($xmlState);
    $xml->addElement($xmlError);
    $xml->addElement($xmlSystemMessage);

    if(count($this->elements) > 0){
      $xml->loadArray($this->elements);
    }

    return $xml->__toString();
  }

  /**
   * get the response in json format
   *
   * @return string
   */
  private function getJSONResponse()
  {
    $data = array();
    $data['code'] = $this->state;
    $data['message'] = $this->systemMessage;

    if(count($this->elements) > 0){
      $elements = array();
      foreach($this->elements as $key => $value){
        if(is_object($value)){
          if(method_exists($value, self::TO_ARRAY_METHOD)){
            $elements[$key] = call_user_func(array($value, self::TO_ARRAY_METHOD));
          }else{
            if($value instanceof XmlElement){
              $elements[$key] = $value->xmlToArray();
            }else{
              $elements[$key] = $value;
            }
          }
        }else{
          if(is_array($value)){
            foreach($value as $item){
              if(is_object($item)){
                if(method_exists($item, 'toArray')){
                  $arrayData = call_user_func(array($item, 'toArray'));
                  $elements[$key][] = $arrayData;
                }else if($item instanceof stdClass){
                  $elements[$key][] = get_object_vars($item);
                }
              }else{
                $elements[$key] = $value;
              }
            }
          }else{
            $elements[$key] = $value;
          }
        }
      }

      $data['response'] = $elements;
    }

    $json = json_encode($data);

    return $json;
  }

  /**
   * @param bool $JSONCallback
   */
  public function setJSONCallback($JSONCallback)
  {
    $this->JSONCallback = $JSONCallback;
  }

  /**
   * convert the wsResponse to string
   *
   * @param $format
   *
   * @return string
   */
  public function toString($format)
  {
    if($format == self::FORMAT_JSON){
      $wsResponseTxt = $this->getJSONResponse();

      if($this->JSONCallback){
        $wsResponseTxt = $this->JSONCallback . " ( $wsResponseTxt )";
      }

    }else{
      $wsResponseTxt = $this->getXMLResponse();
    }

    return $wsResponseTxt;
  }

  public function __toString()
  {
    return $this->toString($this->format);
  }

}

?>