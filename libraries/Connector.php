<?php

/**
 * @author Josua
 */
class Connector
{

  protected $postParams = null;
  protected $getParams = null;

  protected $username = null;
  protected $password = null;

  protected $lastContent = null;
  protected $lastError;
  protected $lastErrorCode = 0;
  protected $lastUrl;

  protected $lastStats = null;

  protected $usePut = null;

  /**
   * execution timeout (seconds)
   *
   * @var int
   */
  protected $timeout = 30;

  /**
   * connect timeout (seconds)
   *
   * @var int
   */
  protected $timeoutOnConnect = 10;

  /**
   * new line format
   *
   * @var string
   */
  protected $newline = "\n";

  /**
   * headers curl call
   *
   * @var array
   */
  protected $headers = array();

  /**
   * @param string $header
   */
  public function addHeader($header)
  {
    if(in_array($header, $this->headers))
    {
      return;
    }
    array_push($this->headers, $header);
  }

  /**
   * @param array $headers
   */
  public function addHeaders($headers)
  {
    $this->headers = $headers;
  }

  /**
   * execution timeout in seconds
   *
   * @param int $timeout
   */
  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;
  }

  /**
   * sets put method
   *
   * @param int $usePut
   */
  public function setPut($usePut)
  {
    $this->usePut = $usePut;
  }

  /**
   * connect timeout in seconds
   *
   * @param int $timeoutOnConnect
   */
  public function setTimeoutOnConnect($timeoutOnConnect)
  {
    $this->timeoutOnConnect = $timeoutOnConnect;
  }

  /**
   * set user and password
   *
   * @param string $username
   * @param string $password
   */
  public function setUsernamePassword($username, $password)
  {
    $this->username = $username;
    $this->password = $password;
  }

  /**
   * Set the parameters for the POST method
   *
   * @param array $postParams
   */
  public function setPostParams($postParams)
  {
    $this->postParams = $postParams;
  }

  /**
   * Set the parameters, this function is only for GET method usage
   *
   * @param string $fileUrl
   * @param array $params
   *
   * @return string
   */
  protected function setGetParams($fileUrl, $params)
  {
    if(is_array($params) && count($params) > 0)
    {
      foreach($params as $key => $value)
      {
        $fileUrl = str_replace("{".$key."}", urlencode($value), $fileUrl);
      }
    }
    $this->getParams = $params;

    return $fileUrl;
  }

  /**
   * @param string $newline
   */
  public function setNewline($newline)
  {
    $this->newline = $newline;
  }

  /**
   * Convert an array to string, array('key1'=>'val1', 'key2'=>'val2') == 'key1=val1&key1=val1'
   *
   * @param $params
   * @param string $prefix
   *
   * @return string
   */
  private function arrayToString($params, $prefix = '')
  {
    $data = '';

    foreach($params as $key => $value)
    {
      if(is_array($value))
      {
        $isAssoc = Util::array_is_assoc($value);
        $arrayData = '';
        $numK = 0;
        foreach($value as $k => $v)
        {
          if($isAssoc)
          {
            $arrayData .= $key.urlencode("[$k]").'='.urlencode($v).'&';
          }
          else
          {
            if(is_array($v))
            {
              $arrayData .= self::arrayToString($v, $key.urlencode("[$numK]")).'&';
            }
            else
            {
              $keyFixed = $key;
              if($prefix)
              {
                $arrayData .= $prefix;
                $keyFixed = urlencode("[$keyFixed]");
              }

              $arrayData .= $keyFixed.urlencode("[]").'='.urlencode($v).'&';
            }
            $numK++;
          }
        }
        $data .= $arrayData;
      }
      else
      {
        $strValue = $value;

        $key = trim($key);
        if($key == '')
        {
          continue;
        }

        if($prefix)
        {
          $data .= $prefix.urlencode("[").$key.urlencode("]").'='.urlencode($strValue).'&';
        }
        else
        {
          $data .= $key.'='.urlencode($strValue).'&';
        }
      }
    }

    return $data;
  }

  /**
   * get the last content of the request
   *
   * @return string
   */
  public function getLastContent()
  {
    return $this->lastContent;
  }

  /**
   * get the stats of the last request made
   *
   * @return array
   */
  public function getLastStats()
  {
    return $this->lastStats;
  }

  /**
   * get last error occurred
   *
   * @return string
   */
  public function getLastError()
  {
    return $this->lastError;
  }

  /**
   * get last error code occurred
   *
   * @return string
   */
  public function getLastErrorCode()
  {
    return $this->lastErrorCode;
  }

  /**
   * check if the exec was success
   *
   * @return bool
   */
  public function success()
  {
    $noSuccess = $this->lastErrorCode != 200 && $this->lastErrorCode != 201 && $this->lastErrorCode != 302 && $this->lastErrorCode != 304;

    return !$noSuccess;
  }

  /**
   * get the content from an URL using cURL
   *
   * @param $url
   *
   * @return null|string
   */
  private function execCurlCall($url)
  {
    ob_start();

    $this->lastContent = null;
    $resURL = curl_init();
    curl_setopt($resURL, CURLOPT_HEADER, 0);
    curl_setopt($resURL, CURLOPT_HTTPHEADER, $this->headers);
    curl_setopt($resURL, CURLOPT_URL, $url);
    curl_setopt($resURL, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($resURL, CURLOPT_NOBODY, false);
    curl_setopt($resURL, CURLOPT_FOLLOWLOCATION, true);

    curl_setopt($resURL, CURLOPT_FORBID_REUSE, true);
    curl_setopt($resURL, CURLOPT_FRESH_CONNECT, true);

    curl_setopt($resURL, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($resURL, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($resURL, CURLOPT_CONNECTTIMEOUT, $this->timeoutOnConnect); //connection timeout
    curl_setopt($resURL, CURLOPT_TIMEOUT, $this->timeout);                 //execution timeout

    if($this->postParams)
    {
      if(is_array($this->postParams))
      {
        $strParams = $this->arrayToString($this->postParams);
      }
      else
      {
        $strParams = $this->postParams;
      }

      if($this->usePut)
      {
        curl_setopt($resURL, CURLOPT_CUSTOMREQUEST, 'PUT');
      }

      curl_setopt($resURL, CURLOPT_POST, true);
      curl_setopt($resURL, CURLOPT_POSTFIELDS, $strParams);
    }

    if($this->username && $this->password)
    {
      curl_setopt($resURL, CURLOPT_USERPWD, $this->username.":".$this->password);
    }

    curl_exec($resURL);
    $this->lastContent = ob_get_contents();

    $this->lastErrorCode = curl_getinfo($resURL, CURLINFO_HTTP_CODE);
    $this->lastStats = curl_getinfo($resURL);

    if(!$this->success())
    {
      $this->lastError = curl_error($resURL);
      if(!$this->lastError || !trim($this->lastError) || trim($this->lastError) == '')
      {
        $this->lastError = "Code: ".$this->lastErrorCode;
      }
    }
    else
    {
      $this->lastError = 'Ok';
    }

    ob_end_clean();

    curl_close($resURL);

    return $this->lastContent;
  }

  /**
   * execute a simple soap call
   *
   * @param string $wsdl
   * @param string $method
   * @param array $params
   *
   * @return null|object
   *
   * @throws WSException
   */
  public function execSoapSimple($wsdl, $method, $params)
  {

    //stats for the soap call
    $this->lastStats = array();
    $this->lastStats['namelookup_time'] = 0;
    $this->lastStats['connect_time'] = 0;
    $this->lastStats['total_time'] = 0;

    $soapOptions = array();
    $soapOptions['trace'] = 1;
    $soapOptions['soap_version'] = SOAP_1_1;
    $soapOptions['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;

    if($this->username && $this->password)
    {
      $soapOptions['login'] = $this->username;
      $soapOptions['password'] = $this->password;
    }

    /**
     * we set the connection timeout
     */
    $soapOptions['connection_timeout'] = $this->timeoutOnConnect;
    /**
     * for soap calls, the execution timeout is controlled by the default_socket_timeout in the php.ini
     * normally set in 60 secs
     */
    $previousTimeout = ini_set('default_socket_timeout', $this->timeout);

    //defaults
    $location = null;

    $this->lastError = 'Ok';
    //TODO get the real http code after execute the calls
    $this->lastErrorCode = 200;
    try
    {
      $connectStartTime = Util::getStartTime();
      $client = new SoapClient($wsdl, $soapOptions);
      $this->lastStats['connect_time'] = Util::calculateProcessTime($connectStartTime);

      $client->__setLocation($location);
      $execStartTime = Util::getStartTime();

      $this->lastContent = $client->$method($params);
    }
    catch(SoapFault $ex)
    {
      $this->lastError = $ex->getMessage();
      $this->lastContent = null;
      $this->lastErrorCode = 0;
    }
    $this->lastStats['total_time'] = Util::calculateProcessTime($execStartTime);

    // If the timeout was overwritten before, restore it.
    if($previousTimeout !== false && $previousTimeout != $this->timeout)
    {
      ini_set('default_socket_timeout', $previousTimeout);
    }

    $this->lastUrl = $wsdl." ($method)";
    $this->postParams = $params;

    return $this->lastContent;
  }

  /**
   * execute a soap call
   *
   * @param string $wsdl
   * @param string $method
   * @param array $params
   * @param array $headers
   * @param array $options
   * @param array $setup
   *
   * @return object
   */
  public function execSoapCall($wsdl, $method, $params, $headers = null, $options = null, $setup = null)
  {

    //stats for the soap call
    $this->lastStats = array();
    $this->lastStats['namelookup_time'] = 0;
    $this->lastStats['connect_time'] = 0;
    $this->lastStats['total_time'] = 0;

    $soapOptions = array();
    $soapOptions['trace'] = 1;
    $soapOptions['soap_version'] = SOAP_1_2;
    $soapOptions['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;

    if($this->username && $this->password)
    {
      $soapOptions['login'] = $this->username;
      $soapOptions['password'] = $this->password;
    }

    /**
     * we set the connection timeout
     */
    $soapOptions['connection_timeout'] = $this->timeoutOnConnect;
    /**
     * for soap calls, the execution timeout is controlled by the default_socket_timeout in the php.ini
     * normally set in 60 secs
     */
    $previousTimeout = ini_set('default_socket_timeout', $this->timeout);

    if($options && is_array($options))
    {
      foreach($options as $opKey => $op)
      {
        $soapOptions[$opKey] = $op;
      }
    }

    //defaults
    $namespace = "http://tempuri.org/";
    $location = null;
    $noWrapParams = false;

    //overwrites
    if($setup && is_array($setup))
    {
      $namespace = $setup['namespace'] ? $setup['namespace'] : $namespace;
      $location = $setup['location'] ? $setup['location'] : $location;
      $noWrapParams = strtolower($setup['noWrapParams']) == '1' || strtolower($setup['noWrapParams']) == 'true' ? true : false;
      $wsdl = $setup['nonWSDL'] ? null : $wsdl;
    }

    $this->lastError = 'Ok';
    //TODO get the real http code after execute the calls
    $this->lastErrorCode = 200;
    try
    {
      $connectStartTime = Util::getStartTime();
      $client = @new SoapClient($wsdl, $soapOptions);
      $this->lastStats['connect_time'] = Util::calculateProcessTime($connectStartTime);

      if($headers && is_array($headers))
      {
        $soapHeaders = array();
        foreach($headers as $hKey => $h)
        {
          $header = new SoapHeader($namespace, $hKey, $h);
          array_push($soapHeaders, $header);
        }

        $client->__setSoapHeaders($soapHeaders);
      }
      if($location)
      {
        $client->__setLocation($location);
      }
      $execStartTime = Util::getStartTime();
      $this->lastContent = $client->__soapCall($method, $noWrapParams ? $params : array($params), $soapOptions);
    }
    catch(SoapFault $ex)
    {
      $this->lastError = $ex->getMessage();
      $this->lastContent = null;
      $this->lastErrorCode = 0;
    }
    $this->lastStats['total_time'] = Util::calculateProcessTime($execStartTime);

    // If the timeout was overwritten before, restore it.
    if($previousTimeout !== false && $previousTimeout != $this->timeout)
    {
      ini_set('default_socket_timeout', $previousTimeout);
    }

    $this->lastUrl = $wsdl." ($method)";
    $this->postParams = $params;

    return $this->lastContent;
  }

  /**
   * it loads the url content
   *
   * @param $url
   * @param null $params
   *
   * @return null|string
   */
  public function loadContent($url, $params = null)
  {
    $url = $this->setGetParams($url, $params);

    $this->lastContent = $this->execCurlCall($url);
    $this->lastUrl = $url;

    return $this->lastContent;
  }

  /**
   * convert an object into a string
   *
   * @param object $obj
   *
   * @return string
   */
  public function objToStr($obj)
  {
    if(is_object($obj) && method_exists($obj, '__toString'))
    {
      return $obj->__toString();
    }

    ob_start();
    print_r($obj);
    $str = ob_get_contents();
    ob_end_clean();

    return $str;
  }

  /**
   * String representation of the object
   *
   * @return string
   */
  public function __toString()
  {
    $desc = "Url: ".$this->lastUrl.$this->newline;
    $desc .= "Message: ".$this->lastError.$this->newline;

    if($this->postParams)
    {
      $desc .= $this->newline."Post Parameters:$this->newline";
      if(is_array($this->postParams) && count($this->postParams) > 0)
      {
        foreach($this->postParams as $key => $value)
        {
          if(is_array($value))
          {
            $paramValue = Util::arrayAssocToString($value);
          }
          else if(is_object($value))
          {
            $paramValue = self::objToStr($value);
          }
          else
          {
            $paramValue = $value;
          }
          $desc .= $key." : ".$paramValue.$this->newline;
        }
      }
      else if(is_object($this->postParams))
      {
        $desc .= Util::objToStr($this->postParams).$this->newline;
      }
      else if(is_string($this->postParams))
      {
        $desc .= $this->postParams.$this->newline;
      }
    }

    if($this->getParams && is_array($this->getParams) && count($this->getParams) > 0)
    {
      $desc .= $this->newline."Get Parameters:$this->newline";
      foreach($this->getParams as $key => $value)
      {
        $desc .= $key." : ".$value.$this->newline;
      }
    }

    if(!$this->success() && $this->lastContent)
    {
      $desc .= $this->newline."Content:$this->newline";

      if(is_object($this->lastContent))
      {
        $strContent = Util::objToStr($this->lastContent);
      }
      else if(is_array($this->lastContent))
      {
        $strContent = Util::arrayToString($this->lastContent);
      }
      else
      {
        $strContent = $this->lastContent;
      }

      $desc .= $this->newline.$strContent.$this->newline;
    }

    return $desc;
  }

  /**
   * execute asynchronous call
   *
   * @param string $url
   * @param string $hostname
   * @param int $sleepBeforeClose
   *
   * @return bool
   */
  public function execAsyncCall($url, $hostname = null, $sleepBeforeClose = 0)
  {
    $this->lastUrl = $url;

    $strParams = '';

    if($this->postParams)
    {
      if(is_array($this->postParams))
      {
        $strParams = $this->arrayToString($this->postParams);
      }
      else
      {
        $strParams = $this->postParams;
      }
    }
    $parts = parse_url($this->lastUrl);
    $path = $parts['path'];
    $host = $parts['host'];
    $port = isset($parts['port']) ? $parts['port'] : 80;

    $length = strlen($strParams);

    $openResource = @fsockopen($host, $port, $this->lastErrorCode, $this->lastError, $this->timeout);

    if(!$openResource)
    {
      return false;
    }

    $headers = "POST $path HTTP/1.1\r\n";
    if($hostname)
    {
      $headers .= "Host: $hostname\r\n";
    }
    else
    {
      $headers .= "Host: $host\r\n";
    }
    $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $headers .= "Content-Length: $length\r\n";
    $headers .= "Connection: Close\r\n\r\n";

    $headers = ($strParams) ? $headers.$strParams : $headers;

    @fwrite($openResource, $headers);

    if($sleepBeforeClose > 0)
    {
      sleep($sleepBeforeClose);
    }
    @fclose($openResource);

    return true;
  }
}

?>