<?php

/**
 * @author jobando
 */
class ExportXSLX extends ExportExcel
{
  public function __construct($fileName, $data, $headers, $title, $format)
  {
    parent::__construct($fileName, $data, $headers, $title, $format);
    $this->extention = "xlsx";
    $this->writerType = 'Excel2007';
  }
}

?>