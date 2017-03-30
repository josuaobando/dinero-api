<?php

/** 
 * @author jobando
 */
abstract class ExportExcel extends Export
{
	/**
	 * file extention
	 */
	protected $extention = null;
	
	/**
	 * writer type
	 */
	protected $writerType = null;
	
	/**
	 * @var string
	 */
	protected $enclosure = '"';	
	
	/**
	 * @see Export::process()
	 */
	protected function process()
	{		
		$objPHPExcel = new PHPExcel();
		
		//sets the properties
		$objPHPExcel->getProperties()->setCreator("DineroSeguroHF.com");		
		$objPHPExcel->getProperties()->setLastModifiedBy("DineroSeguroHF.com");
		$objPHPExcel->getProperties()->setTitle($this->title);
		$objPHPExcel->getProperties()->setSubject($this->title);
		$objPHPExcel->getProperties()->setDescription($this->title);
		$objPHPExcel->getProperties()->setKeywords("Excel Office 2007 openxml php");
		$objPHPExcel->getProperties()->setCategory("");	
	
		$column = 0;
		foreach ($this->headers as $header)
		{
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column)->getFont()->setBold(true);
			
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column)->getFill()->getStartColor()->setARGB('4F81BD');
			
			$objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($column)->setAutoSize(true);
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, 1, $header);
			$column ++;
		}
		
		$column = 0;
		$line = (count($this->headers))?2:1;
		foreach ($this->data as $row)
		{
			foreach ($row as $key=>$field)
			{
				$t = $this->getFormatDataType($key);				
				if($t == PHPExcel_Cell_DataType::TYPE_NUMERIC)
				{
					$f = $this->getFormat($key);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow($column, $line)->getNumberFormat()->setFormatCode($f);
				}
				
				$v = $this->applyFormat($key, $field);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow($column++, $line, $v, $t);
			}
			$line++;
			$column = 0;
		}
				
		//set active sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		// Save Excel 2007 file
		$phpExcelWrite = new PHPExcel_Writer_Excel2007($objPHPExcel);

		return $phpExcelWrite;		
	}		
	
	/**
	 * set enclosure
	 *
	 * @param string $enclosure
	 *
	 */
	public function setEnclosured($enclosure)
	{
		$this->enclosure = $enclosure;
	}
	
	/**
	 * @return the $enclosure
	 */
	public function getEnclosure()
	{
		return $this->enclosure;
	}
	
}

?>