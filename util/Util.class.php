<?php

/**
 * @author Josua
 */
class Util
{

  const REGEX_NUMERIC = '^[0-9]+$';
  const REGEX_ALPHANIMERIC = '^[-a-zA-Z0-9ñÑáóéíóúÁÉÍÓÚ ,.-]+$';
  const FORMAT_DATE_DISPLAY = 'd F H:i:s';

  /**
   * check if the environment is development
   */
  public static function isDEV()
  {
    $isDev = CoreConfig::DEV;

    return $isDev === true;
  }

  /**
   * check if the script is running on the command line or from a browser call.
   *
   * @return bool
   */
  public static function isCli()
  {
    return (php_sapi_name() == 'cli' && empty($_SERVER ['REMOTE_ADDR']));
  }

  /**
   * get server name where the application is running
   *
   * @return string
   */
  public static function getServerName()
  {
    $server = php_uname("n");

    return $server;
  }

  /**
   * retrieve the name of the current project
   *
   * @return string
   */
  public static function getProjectName()
  {
    if(self::isCli()){
      $project = 'script';
    }else{
      $project = $_SERVER['SERVER_NAME'];
    }

    return $project;
  }

  /**
   * get proper new line method depending on the CLI.
   *
   * @return string
   */
  public static function getNewLine()
  {
    return Util::isCli() ? "\n" : "<br/>";
  }

  /**
   * Generate a string with the values in $array separate by , (comma)
   * $array('a', 'b', 'c') => "'a', 'b', 'c'"
   *
   * @param $array
   * @param string $empty
   *
   * @return string
   */
  public static function generateStrList($array, $empty = '-1')
  {
    if(!is_array($array) || count($array) == 0){
      return $empty;
    }

    $result = "'" . implode("','", $array) . "'";

    return $result;
  }

  /**
   * Generate a string with the values in $array separate by , (comma)
   * $array('a', 'b', 'c') => "a, b, c"
   *
   * @param $array
   * @param string $empty
   *
   * @return string
   */
  public static function generateList($array, $empty = '-1')
  {
    if(!is_array($array) || count($array) == 0){
      return $empty;
    }

    $result = implode(",", $array);

    return $result;
  }

  /**
   * Transform an array of rows in a simple array using the key
   *
   * @param array $rows
   * @param string $key
   *
   * @return array
   */
  public static function getRowToArray($rows, $key)
  {
    $array = array();
    foreach($rows as $row){
      array_push($array, $row [$key]);
    }

    return $array;
  }

  /**
   * Get an array from a rows, indexing the array using a value in the row named $key
   *
   * @param array $rows
   * @param string $key
   *
   * @return array
   */
  public static function rowsToIndexedArray($rows, $key)
  {
    $array = array();
    foreach($rows as $row){
      $array [$row [$key]] = $row;
    }

    return $array;
  }

  /**
   * Get an array from a rows, associating the array using the key and value in the row
   *
   * @param $rows
   * @param $keyName
   * @param $keyValue
   *
   * @return array
   */
  public static function rowsToAssocArray($rows, $keyName, $keyValue)
  {
    $array = array();
    foreach($rows as $row){
      $array[$row[$keyName]] = $row[$keyValue];
    }

    return $array;
  }

  /**
   * Convert a array of rows form db to XmlElement
   *
   * @param array $rows
   * @param string $title
   * @param string $subTitle
   * @param array $config [optional]
   *
   * @return XmlElement
   */
  public static function rowsToXmlList($rows, $title, $subTitle, $config = null)
  {
    $xmlList = new XmlElement($title);
    if(is_array($rows)){
      foreach($rows as $row){
        $newElement = Util::rowToXml($row, $subTitle, $config);
        $xmlList->addElement($newElement);
      }
    }

    return $xmlList;
  }

  /**
   * Convert a row into a XmlElement
   *
   * @param array $row
   * @param string $title
   *
   * @return XmlElement
   */
  public static function rowToXmlList($row, $title)
  {
    $xmlList = new XmlElement($title);

    if($row && is_array($row)){
      foreach($row as $key => $value){
        $newElement = new XmlElement($key);
        $newElement->addAttr('value', $value);
        $xmlList->addElement($newElement);
      }
    }

    return $xmlList;
  }

  /**
   * Convert a row into a XmlElement
   *
   * @param $row
   * @param $name
   * @param null $config
   *
   * @return \XmlElement
   */
  public static function rowToXml($row, $name, $config = null)
  {
    $escape = $config ['escape'];
    $xml = new XmlElement($name);
    if(!is_array($row)){
      return $xml;
    }
    foreach($row as $field => $value){
      $fieldName = $field;
      if($config && is_array($config ['exclude']) && array_intersect(array($field), $config ['exclude'])){
        continue;
      }
      if($config && is_array($config ['rename']) && array_intersect_key(array($field => $value), $config ['rename'])){
        $fieldName = $config ['rename'] [$field];
      }
      if($config && (is_array($config ['values']) && array_intersect(array($field), $config ['values'])) || $config ['values-all']){
        if(is_array($value)){
          $newRow = Util::rowToXml($value, $fieldName, $config);
          $xml->addElement($newRow);
        }else{
          $newValue = new XmlElement($fieldName);
          $newValue->setValue(Util::escapeText($value));
          $xml->addElement($newValue);
        }
      }else{
        if($escape){
          $value = Util::escapeText($value);
        }
        $xml->addAttr($fieldName, is_array($value) ? '' : $value);
      }
    }

    return $xml;
  }

  /**
   * Valid all the parameters
   *
   * @param variables
   *
   * @return bool
   */
  public static function validParameters()
  {
    for($i = 0; $i < func_num_args(); $i++){
      $param = func_get_arg($i);
      if(is_null($param) or ($param == "")){
        return false;
      }
    }

    return true;
  }

  /**
   * valid for any valid parameter
   *
   * @return bool
   */
  public static function validParametersAny()
  {
    for($i = 0; $i < func_num_args(); $i++){
      $param = func_get_arg($i);
      if(!is_null($param) or ($param != '')){
        return true;
      }
    }

    return false;
  }

  /**
   * Call back used for the extract vars
   *
   * @param array $matches
   *
   * @return string
   */
  public static function callBack($matches)
  {
    global $vars;
    $var = $matches [0];
    $data = split('=', $var);
    $vars [$data [0]] = $data [1];

    return $matches [0];
  }

  /**
   * Extract var from string, 'var1=value1&var2=value2&...&varX=valueX
   *
   * @param string $text
   *
   * @return array
   */
  public static function extractVars($text)
  {
    global $vars;
    $regularExpr = '/[a-zA-Z0-9-_]+\=[^&]*/';
    $vars = array();
    preg_replace_callback($regularExpr, 'Util::callBack', $text);

    return $vars;
  }

  /**
   * Get the current time
   *
   * @return double
   */
  public static function getStartTime()
  {
    $startTime = microtime(true);

    return $startTime;
  }

  /**
   * Get the process time since $startTime
   *
   * @param double $startTime
   *
   * @return double
   */
  public static function calculateProcessTime($startTime)
  {
    $endTime = microtime(true);
    $time = $endTime - $startTime;

    return $time;
  }

  /**
   * Get the display for $time
   *
   * @param double $time
   *
   * @return string
   */
  public static function timeForDisplay($time)
  {
    if($time < 60){
      return number_format($time, 2) . " seconds";
    }
    $time = $time / 60;
    if($time < 60){
      return number_format($time, 2) . " minutes";
    }
    $time = $time / 60;
    if($time < 60){
      return number_format($time, 2) . " hours";
    }

    return number_format($time, 2) . " [no scale]";
  }

  /**
   * display seconds as hours, minutes and seconds
   *
   * @param $sec
   * @param bool $padHours
   *
   * @return string
   */
  public static function sec2hms($sec, $padHours = false)
  {
    // start with a blank string
    $hms = "";

    // do the hours first: there are 3600 seconds in an hour, so if we divide
    // the total number of seconds by 3600 and throw away the remainder, we're
    // left with the number of hours in those seconds
    $hours = intval(intval($sec) / 3600);

    // add hours to $hms (with a leading 0 if asked for)
    $hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":";

    // dividing the total seconds by 60 will give us the number of minutes
    // in total, but we're interested in *minutes past the hour* and to get
    // this, we have to divide by 60 again and then use the remainder
    $minutes = intval(($sec / 60) % 60);

    // add minutes to $hms (with a leading 0 if needed)
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";

    // seconds past the minute are found by dividing the total number of seconds
    // by 60 and using the remainder
    $seconds = intval($sec % 60);

    // add seconds to $hms (with a leading 0 if needed)
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    // done!
    return $hms;
  }

  /**
   * Get a memory representation to display
   *
   * @param int $mem
   *
   * @return string
   */
  public static function getMemoryDisplay($mem = -1)
  {
    if($mem == -1){
      $mem = memory_get_usage();
    }

    if($mem < 1024){
      return number_format($mem, 2) . " bytes";
    }
    $mem = $mem / 1024;
    if($mem < 1024){
      return number_format($mem, 2) . " kb";
    }
    $mem = $mem / 1024;
    if($mem < 1024){
      return number_format($mem, 2) . " mb";
    }
    $mem = $mem / 1024;
    if($mem < 1024){
      return number_format($mem, 2) . " gb";
    }

    return number_format($mem, 2) . " [no scale]";
  }

  /**
   * Encode some or all the fields of $rows
   *
   * @param array $rows
   * @param array $fields
   *
   * @return array
   */
  public static function encodeRows($rows, $fields)
  {
    $encodedRows = array();
    foreach($rows as $row){
      $newRow = array();
      foreach($row as $key => $value){
        if(array_intersect(array($key), $fields)){
          $newRow [$key] = base64_encode($value);
        }else{
          $newRow [$key] = $value;
        }
      }
      array_push($encodedRows, $newRow);
    }

    return $encodedRows;
  }

  /**
   * Compare two arrays
   *
   * @param array $arrayA
   * @param array $arrayB
   *
   * @return bool
   */
  public static function compareArrays($arrayA, $arrayB)
  {
    if(count($arrayA) != count($arrayB)){
      return false;
    }

    $size = count($arrayA);
    for($i = 0; $i < $size; $i++){
      if($arrayA [$i] != $arrayB [$i]){
        return false;
      }
    }

    return true;
  }

  /**
   * convert array to string representation
   *
   * @param $array
   * @param string $equal
   * @param string $separator
   * @param null $valueId
   *
   * @return string
   */
  public static function arrayAssocToString($array, $equal = '=', $separator = '&', $valueId = null)
  {
    if(!is_array($array)){
      return "";
    }

    $result = "";
    foreach($array as $key => $element){
      $value = ($valueId ? $element [$valueId] : $element);
      if(is_array($value)){
        $value = self::arrayToString($value, $separator);
      }else if(is_object($value)){
        $value = self::objToStr($value);
      }

      $result .= $key . $equal . $value . $separator;
    }

    return $result;
  }

  /**
   * convert array to string representation
   *
   * @param array $array
   * @param string $separator
   *
   * @return string
   */
  public static function arrayToString($array, $separator = ',')
  {
    if(!is_array($array)){
      return "";
    }

    if(self::array_is_assoc($array)){
      return self::arrayAssocToString($array, '=', $separator);
    }

    $result = "";
    foreach($array as $element){
      if(is_object($element)){
        $result .= self::objToStr($element) . $separator;
      }else{
        $result .= $element . $separator;
      }
    }
    if(substr($result, -1) == $separator){
      $result = substr($result, 0, -1);
    }

    return $result;
  }

  /**
   * convert to string
   *
   * @param $data
   *
   * @return string
   */
  public static function toString($data)
  {
    if(is_string($data)){
      return $data;
    }else{
      if(is_object($data) && method_exists($data, '__toString')){
        return $data->__toString();
      }else{
        if(is_object($data)){
          return Util::objToStr($data);
        }else if(is_array($data)){
          return Util::arrayToString($data, "\n");
        }else{
          return "";
        }
      }
    }
  }

  /**
   * join associative array elements with a string
   *
   * @param array $arrayAssoc
   * @param string $keyGlue
   * @param string $elementGlue
   *
   * @return string
   */
  public static function implodeAssoc($arrayAssoc, $keyGlue = "=", $elementGlue = "\n")
  {
    $str = "";
    foreach($arrayAssoc as $key => $value){
      if(is_array($value)){
        $isAssoc = Util::array_is_assoc($value);
        $arrayData = '';
        foreach($value as $k => $v){
          if($isAssoc){
            $arrayData .= $key . "[$k]" . $keyGlue . $v . $elementGlue;
          }else{
            $arrayData .= $key . "[]" . $keyGlue . $v . $elementGlue;
          }
        }
        $str .= $arrayData;
      }else{
        $str .= $key . $keyGlue . $value . $elementGlue;
      }
    }
    $str = substr($str, 0, -1);

    return $str;
  }

  /**
   * explode a string into an associative array
   *
   * @param array $str
   * @param string $keyGlue
   * @param string $elementGlue
   *
   * @return array
   */
  public static function explodeStr($str, $keyGlue = ":", $elementGlue = "|")
  {
    $elements = explode($elementGlue, $str);
    $arrayAssoc = array();
    foreach($elements as $element){
      $newElement = explode($keyGlue, $element, 2);
      $arrayAssoc [trim($newElement [0])] = trim($newElement [1]);
    }

    return $arrayAssoc;
  }

  /**
   * array representation for the options in a select input<br/>
   *
   * @param array $array
   * @param string $id
   * @param string $valueId
   * @param string $format
   * @param array $default
   *
   * @return array
   */
  public static function arrayAssocToSelect($array, $id = null, $valueId = null, $format = null, $default = null)
  {
    $data = array();
    if($default && is_array($default)){
      array_push($data, $default);
    }
    foreach($array as $k => $v){
      $newValue = array();
      $newValue ['id'] = $id ? $v [$id] : $k;

      if(is_array($valueId) && $format){
        $value = $format;
        foreach($valueId as $valueIdK){
          $valueIdV = $v[$valueIdK];
          $value = str_replace("{" . $valueIdK . "}", $valueIdV, $value);
        }
      }else{
        $value = $valueId ? $v [$valueId] : $v;
      }

      $newValue ['value'] = $value;

      array_push($data, $newValue);
    }

    return $data;
  }

  /**
   * escape string
   *
   * @param $s
   *
   * @return string
   */
  public static function escapeText($s)
  {
    if(!is_string($s)){
      $s .= '';
    }

    $characters = array();
    $characters ['&'] = '&amp;';
    $characters ['<'] = '&lt;';
    $characters ['>'] = '&gt;';
    $characters ['\''] = '&apos;';
    $characters ['"'] = '&quot;';
    $characters ['?'] = '&#63;';

    $characters ['�'] = '&#225;';
    $characters ['�'] = '&#193;';
    $characters ['�'] = '&#233;';
    $characters ['�'] = '&#201;';
    $characters ['�'] = '&#237;';
    $characters ['�'] = '&#205;';
    $characters ['�'] = '&#243;';
    $characters ['�'] = '&#211;';
    $characters ['�'] = '&#250;';
    $characters ['�'] = '&#218;';

    $characters ['�'] = '&#209;';
    $characters ['�'] = '&#241;';

    $characters ['�'] = '&#231;';

    $result = '';
    $len = strlen($s);
    for($i = 0; $i < $len; $i++){
      if($characters [$s{$i}]){
        $result .= $characters [$s{$i}];
      }else if(ord($s{$i}) > 127){
        // skipping UTF-8 escape sequences requires a bit of work
        if((ord($s{$i}) & 0xf0) == 0xf0){
          $result .= $s{$i++};
          $result .= $s{$i++};
          $result .= $s{$i++};
          $result .= $s{$i};
        }else if((ord($s{$i}) & 0xe0) == 0xe0){
          $result .= $s{$i++};
          $result .= $s{$i++};
          $result .= $s{$i};
        }else if((ord($s{$i}) & 0xc0) == 0xc0){
          $result .= $s{$i++};
          $result .= $s{$i};
        }
      }else{
        $result .= $s{$i};
      }
    }

    return $result;
  }

  /**
   * convert xml elements to attributes
   *
   * @param $xml
   * @param $callBack
   *
   * @return \XmlElement
   */
  function xmlElementsToAttr($xml, $callBack = 0)
  {
    $result = new XmlElement($xml->getName());
    if($xml){
      $elements = $xml->getElements();
      foreach($elements as $element){
        $elems = $element->getElements();
        $newElement = new XmlElement($element->getName());
        foreach($elems as $elem){
          $name = $elem->getName();
          $value = $elem->getValue();
          $newElement->addAttr($name, $value);
        }
        if($callBack){
          call_user_func($callBack, $newElement);
        }
        $result->addElement($newElement);
      }
    }

    return $result;
  }

  /**
   * put the xml headers
   *
   * @param string $format
   * @param string $encoding
   */
  public static function putResponseHeaders($format = 'xml', $encoding = '')
  {
    $encoding = (trim($encoding) == '') ? CoreConfig::SYS_ENCODING : $encoding;

    if(trim($format) == ''){
      $format = 'xml';
    }

    header('Expires: Wed, 23 Dec 1980 00:30:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-Type: application/' . $format);

    if($format == 'xml'){
      echo "<?xml version=\"1.0\" encoding=\"$encoding\"?>";
    }else if($format == 'json'){
      header("HTTP/1.1 200 OK");
      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
      header('Access-Control-Allow-Headers: accept, origin, content-type');
    }
  }

  /**
   * check if the file exists using the include_path
   *
   * @param string $file
   *
   * @return string
   */
  public static function file_exists($file)
  {
    $ps = explode(PATH_SEPARATOR, ini_get('include_path'));
    foreach($ps as $path){
      if(file_exists($path . '/' . $file)){
        return $path . '/' . $file;
      }
    }
    if(file_exists($file)){
      return $file;
    }

    return false;
  }

  /**
   * Writes the content to a file, it creates the file if does not exist, append text is optional.
   *
   * @param string $path
   * @param string $content
   *
   * @return boolean
   */
  public static function writeToFile($path, $content)
  {
    $f = @fopen($path, 'w');
    if($f){
      if(@flock($f, LOCK_EX)){
        $w = @fwrite($f, $content);
        @flock($f, LOCK_UN);
      }
      @fclose($f);
    }

    return $w > 0;
  }

  /**
   * Reads the content from a file.
   *
   * @param $path
   *
   * @return string+
   */
  public static function readFromFile($path)
  {
    $f = @fopen($path, 'r');
    if($f){
      if(@flock($f, LOCK_EX)){
        $content = @fread($f, filesize($path));
        @flock($f, LOCK_UN);
      }
      @fclose($f);
    }

    return $content;
  }

  /**
   * checks if an array is associative or not
   *
   * @param $array
   *
   * @return bool
   */
  public static function array_is_assoc($array)
  {
    return array_keys($array) !== range(0, count($array) - 1);
  }

  /**
   * insert '$element' in a specific position of '$array'
   *
   * @param array $array
   * @param int $position
   * @param mixed $element
   */
  public static function array_insert(&$array, $position, $element)
  {
    if(is_array($array)){
      $first_array = array_splice($array, 0, $position);
      $element = is_array($element) ? $element : array($element);
      $array = array_merge($first_array, $element, $array);
    }
  }

  /**
   * insert '$element' after the $key in '$array'
   *
   * @param array $array
   * @param string $key
   * @param mixed $element
   */
  public static function array_insert_after_key(&$array, $key, $element)
  {
    $position = is_null($key) ? 0 : count($array);
    if(is_array($array) && array_key_exists($key, $array)){
      $position = array_search($key, array_keys($array)) + 1;
    }

    self::array_insert($array, $position, $element);
  }

  /**
   * Obtain the customer's country through the web browser.
   *
   * @param $ipAddress
   *
   * @return String
   */
  public static function getCountryFrom($ipAddress)
  {
    //TODO not implemented yet
    $data = "IP NOT FOUND.";

    return $data;
  }

  /**
   * get the list of directories contained in a folder
   *
   * @param string $dir
   * @param array $directories
   *
   * @return array
   */
  public static function getDirectories($dir, $directories = array())
  {
    $items = scandir($dir);
    foreach($items as $item){
      $path = $dir . DIRECTORY_SEPARATOR . $item;
      if(!is_dir($path) || //not a directory
        $item == '.' || //current directory
        $item == '..' || //parent directory
        substr($item, 0, 1) == '.' //hidden directory
      ){
        continue;
      }
      array_push($directories, $path);
      $directories = self::getDirectories($path, $directories);
    }

    return $directories;
  }

  /**
   * get the list of months
   *
   * @return array
   */
  public static function generateMonthList()
  {
    $months = array();
    $months ['01'] = 'Jan';
    $months ['02'] = 'Feb';
    $months ['03'] = 'Mar';
    $months ['04'] = 'Apr';
    $months ['05'] = 'May';
    $months ['06'] = 'Jun';
    $months ['07'] = 'Jul';
    $months ['08'] = 'Aug';
    $months ['09'] = 'Sep';
    $months ['10'] = 'Oct';
    $months ['11'] = 'Nov';
    $months ['12'] = 'Dec';

    return $months;
  }

  /**
   * get the list of number months
   *
   * @return array
   */
  public static function generateMonthNumberList()
  {
    $months = array();
    $months ['01'] = '01';
    $months ['02'] = '02';
    $months ['03'] = '03';
    $months ['04'] = '04';
    $months ['05'] = '05';
    $months ['06'] = '06';
    $months ['07'] = '07';
    $months ['08'] = '08';
    $months ['09'] = '09';
    $months ['10'] = '10';
    $months ['11'] = '11';
    $months ['12'] = '12';

    return $months;
  }

  /**
   * get the list of days
   *
   * @param $month
   * @param $year
   * @param null $daysM
   *
   * @return array
   */
  public static function generateDayList($month, $year, $daysM = null)
  {
    $days = array();
    $d = 1;
    if(!$daysM){
      if($year == null){
        $year = date('Y');
      }
      if($month == null){
        $month = intval(date("m", time()));
      }
      $daysM = Util::getMonthDays($month, $year);
    }
    while($d <= $daysM){
      $dValue = ($d < 10) ? "0$d" : "$d";
      $days [$dValue] = $dValue;
      $d += 1;
    }

    return $days;
  }

  /**
   * Gets the number of days in a month
   *
   * @param $month
   * @param $year
   *
   * @return false|int|string
   */
  public static function getMonthDays($month, $year)
  {
    if($month != null and $year != null){
      if(is_callable("cal_days_in_month")){
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
      }else{
        return date("d", mktime(0, 0, 0, $month + 1, 0, $year));
      }
    }else{
      return 31;
    }
  }

  /**
   * get the list of countries
   *
   * @param int $min
   * @param int $max
   * @param bool $revert
   *
   * @return array
   */
  public static function generateYearList($min = 2000, $max = 2020, $revert = false)
  {
    $years = array();
    if($revert){
      for($i = $max; $i >= $min; $i--){
        $years [$i] = $i;
      }
    }else{
      for($i = $min; $i <= $max; $i++){
        $years [$i] = $i;
      }
    }

    return $years;
  }

  /**
   * truncate a decimal value
   *
   * @param $amount
   * @param int $decimals
   *
   * @return float|int
   */
  public static function truncate($amount, $decimals = 2)
  {
    $pow = pow(10, $decimals);
    if($amount > 0){
      return floor($amount * $pow) / $pow;
    }else{
      return ceil($amount * $pow) / $pow;
    }
  }

  /**
   * scratch a value, optional you could get the last X letters/digits
   *
   * @param $value
   * @param int $digits [-1 will scratch the whole value]
   *
   * @return string
   */
  public static function scratch($value, $digits = -1)
  {
    if($digits < 0){
      $digits = 0;
    }
    $scratchedLength = strlen($value) - $digits;
    if($scratchedLength < 0){
      $scratchedLength = 0;
    }
    $scratchedValue = str_repeat("*", $scratchedLength) . substr($value, $scratchedLength, $digits);

    return $scratchedValue;
  }

  /**
   * convert an object into a string
   *
   * @param object $obj
   *
   * @return string
   */
  public static function objToStr($obj)
  {
    if(is_object($obj) && method_exists($obj, '__toString')){
      return $obj->__toString();
    }

    ob_start();
    print_r($obj);
    $str = ob_get_contents();
    ob_end_clean();

    return $str;
  }

  /**
   * sorts an array by key
   *
   * @param array $array
   * @param string $key
   * @param int $mode
   */
  public static function sortArray(&$array, $key, $mode = SORT_ASC)
  {
    $sorter = array();
    $ret = array();
    reset($array);
    foreach($array as $ii => $va){
      $sorter [$ii] = $va [$key];
    }
    asort($sorter, $mode);
    foreach($sorter as $ii => $va){
      $ret [$ii] = $array [$ii];
    }
    $array = $ret;
  }

  /**
   * Manage an array of rows to exclude or rename keys
   *
   * @param array $rows
   * @param array $config
   *
   */
  public static function manageArrayList(&$rows, $config = null)
  {
    $result = array();
    foreach($rows as $key => $row){
      $result [$key] = Util::manageArray($row, $config);
    }
    $rows = $result;
  }

  /**
   * exclude or rename keys
   *
   * @param array $row
   * @param array $config
   *
   * @return array
   */
  public static function manageArray($row, $config = null)
  {
    $escape = $config ['escape'];

    $result = array();
    if(!is_array($row)){
      return null;
    }

    foreach($row as $field => $value){
      $fieldName = $field;
      if($config && is_array($config ['exclude']) && array_intersect(array($field), $config ['exclude'])){
        continue;
      }
      if($config && is_array($config ['rename']) && array_intersect_key(array($field => $value), $config ['rename'])){
        $fieldName = $config ['rename'] [$field];
      }
      if($escape){
        $value = Util::escapeText($value);
      }
      if($config && is_array($config ['prefix']) && array_intersect_key(array($field => $value), $config ['prefix'])){
        $value = $config ['prefix'] [$field] . $value;
      }
      if($config && is_array($config ['replace']) && array_intersect_key(array($field => $value), $config ['replace'])){
        $value = $config ['replace'][$field];
      }
      $result [$fieldName] = $value;
    }

    return $result;
  }

  /**
   * Verifies if the array contents the key else returns empty string of the default value
   *
   * @param string $key
   * @param array $search
   * @param mixed $default
   *
   * @return mixed
   */
  public static function verifyValue($key, $search, $default = null)
  {
    return (array_key_exists($key, $search)) ? $search [$key] : $default;
  }

  /**
   * Creates a random string according to parameter options
   *
   * @param int $length
   * @param boolean $uc
   * @param boolean $n
   * @param boolean $sc
   *
   * @return string
   */
  public static function RandomString($length = 4, $uc = TRUE, $n = FALSE, $sc = FALSE)
  {
    $randomString = "";
    $source = 'abcdefghijklmnopqrstuvwxyz';
    if($uc == 1){
      $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if($n == 1){
      $source .= '1234567890';
    }
    if($sc == 1){
      $source .= '|@#~$%()=^*+[]{}-_';
    }
    if($length > 0){
      $source = str_split($source, 1);
      for($i = 1; $i <= $length; $i++){
        mt_srand(( double )microtime() * 1000000);
        $num = mt_rand(1, count($source));
        $randomString .= $source [$num - 1];
      }

    }

    return $randomString;
  }

  /**
   * Quits all columns whose there're not in $columns
   *
   * @param array $array
   * @param array $columns
   */
  public static function quitColumns(&$array, $columns)
  {
    foreach($array as $key => $value){
      $element = array();
      foreach($columns as $column){
        $element [$column] = $value [$column];
      }
      $array [$key] = $element;
    }
  }

  /**
   * verifies the state of WS response
   *
   * @param WSRequest $wsResponse
   *
   * @return bool
   *
   * @throws GeneralException
   */
  public static function verifiesStateWSResponse($wsResponse)
  {
    if($wsResponse){
      $state = $wsResponse->getElementValue('state');
      if($state == 'ok'){
        return true;
      }
      $message = $wsResponse->getElementValue('userMessage');

      if($message == 'InvalidStateException'){
        throw new GeneralException('Error: Session has expired. You should start a new session.');
      }
      throw new GeneralException($message);
    }else{
      throw new GeneralException('Error Loading Information');
    }

  }

  /**
   * Set the parameters
   *
   * @param $paramsConfig
   * @param null $recordId
   *
   * @return array|string
   */
  public static function setParameters($paramsConfig, $recordId = null)
  {
    try{

      $parameters = array();

      foreach($paramsConfig as $key => $element){
        $type = $element ['type'];
        $otherType = $element ['otherType'];
        $value = Util::getParameter($type, $key, $element, $recordId);

        if($value != ''){
          $parameters [$key] = $value;
        }else if($otherType){
          $value = Util::getParameter($otherType, $key, $element, $recordId);
          $parameters [$key] = $value;
        }else{
          $parameters [$key] = $value;
        }

      }
    }catch(GeneralException $ex){
      return $ex->getMessage();
    }

    return $parameters;
  }

  /**
   * get the parameter value according to type
   *
   * @param string $type
   * @param string $key
   * @param array $element
   * @param mixed $recordId
   *
   * @return string
   */
  public static function getParameter($type, $key, $element, $recordId)
  {
    switch($type){
      case 'id' :
        return $recordId;
      case 'value' :
        return $element ['value'];
      case 'session' :
        return $_SESSION [$key];
      case 'request' :
        return $_REQUEST [$key];
      case 'post' :
        return $_POST [$key];
      case 'get' :
        return $_GET [$key];
      case 'server' :
        return $_SERVER [$key];
      case 'env' :
        return $_ENV [$key];
      case 'files' :
        return $_FILES [$key];
      case 'cookie' :
        return $_COOKIE [$key];
      case 'config' :
        return Util::getConstants($element ['value']);
    }

    return '';
  }

  /**
   * If possible retrieves the phone area code from the complete phone.
   *
   * @param $phone
   * @param string $countrySmallCode
   * @param string $default
   *
   * @return string
   */
  public static function getAreaCodeFrom($phone, $countrySmallCode = '', $default = '000')
  {
    // ##### Strip all Non-Numeric Characters
    $phone = preg_replace('/[^0-9]+/', '', $phone);
    $areaCode = '';
    switch($countrySmallCode){
      case "US":
      case "CA":
        if(strlen($phone) == 11 && $phone[0] == '1'){
          $areaCode = substr($phone, 1, 3);
        }elseif(strlen($phone) == 10){
          $areaCode = substr($phone, 0, 3);
        }
        break;
    }
    $areaCode = (empty($areaCode)) ? $default : $areaCode;

    return $areaCode;
  }

  /**
   * Gets all the constants or the constant value specified by $name
   *
   * @param string $name
   *
   * @return mixed
   */
  public static function getConstants($name = null)
  {
    if(!$name){
      return get_defined_constants();
    }else{
      $constants = get_defined_constants();

      return $constants [$name];
    }
  }

  /**
   * convert an array into a query string
   *
   * @param $data
   * @param bool $includeEmpty
   * @param bool $encodeFields
   *
   * @return string
   */
  public static function arrayToQueryString($data, $includeEmpty = false, $encodeFields = true)
  {
    $returnValue = '';
    if(is_array($data)){
      foreach($data as $k => $v){
        $skip = false;

        if(($k == "") || ($v == "")){
          $skip = !$includeEmpty;
        }

        if(!$skip){
          $returnValue .= (($encodeFields) ? urlencode($k) : $k) . "=" . (($encodeFields) ? urlencode($v) : $v) . "&";
        }
      }
      $returnValue = substr($returnValue, 0, -1);
    }

    return $returnValue;
  }

  /**
   * gets the time difference by interval
   *
   * @param string $interval
   * @param string $dateStart
   * @param string $dateEnd
   * @param bool $relative
   *
   * @return double
   */
  public static function getTimeDifferenceByInterval($interval, $dateStart, $dateEnd, $relative = false)
  {
    if(is_string($dateStart)){
      $dateTimeStart = date_create($dateStart);
    }

    if(is_string($dateEnd)){
      $dateTimeEnd = date_create($dateEnd);
    }

    $diff = date_diff($dateTimeStart, $dateTimeEnd, !$relative);

    switch($interval){
      case "y":
        $total = $diff->y + $diff->m / 12 + $diff->d / 365.25;
        break;
      case "m":
        $total = $diff->y * 12 + $diff->m + $diff->d / 30 + $diff->h / 24;
        break;
      case "d":
        $total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h / 24 + ($diff->i / 60) / 24;
        break;
      case "h":
        $total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i / 60;
        break;
      case "i":
        $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s / 60;
        break;
      case "s":
        $total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i) * 60 + $diff->s;
        break;
    }

    if($diff->invert){
      return -1 * $total;
    }else{
      return $total;
    }
  }

  /**
   * checks if the string starts with X string.
   *
   * @param string $haystack
   * @param string $needle
   *
   * @return boolean
   */
  public static function startsWith($haystack, $needle)
  {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
  }

  /**
   * get the string between $start and $end
   *
   * @param string $string
   * @param string $start
   * @param string $end
   *
   * @return string
   *
   */
  public static function getStringBetween($string, $start, $end)
  {
    if(!is_string($string)){
      return "";
    }

    $string = " " . $string;
    $ini = strpos($string, $start);
    if($ini == 0){
      return "";
    }

    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;

    return substr($string, $ini, $len);
  }

  /**
   * convert date into GMT date, offset can be specified
   *
   * @param string $date
   * @param int $offset
   * @param string $timezone
   *
   * @return string
   */
  public static function dateToGMT($date, $offset = 0, $timezone = null)
  {
    $time = strtotime(trim($date));
    if(!$time || $time <= 0){
      return '';
    }
    if(!$timezone){
      //if not timezone given, we use the timezone used by the system
      $timezone = CoreConfig::TIMEZONE_APP;
    }

    //create the date using the timezone
    $date = new DateTime($date, new DateTimeZone($timezone));

    //convert into GMT
    $date->setTimezone(new DateTimeZone('GMT'));
    $gmtTime = strtotime($date->format('Y-m-d H:i:s'));

    //generate new date using offset
    $newDate = date('Y-m-d H:i:s', strtotime($offset . " hours", $gmtTime));

    return $newDate;
  }

}

?>