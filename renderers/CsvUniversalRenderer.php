<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 14.03.16
 * Time: 15:01
 */
class CsvUniversalRenderer implements IUniversalRenderer
{
	protected $_columnNames;
	protected $_values;
	protected $_filename;
	public function __construct($fileName) {
		$this->_filename = $fileName;
	}

	/**
	 * Вывод собственно
	 * @param $arg array
	 * @param $asOutput boolean
	 * @return mixed
	 */
	function Render($arg, $asOutput = false)
	{
		$filePath = "files/".$this->_filename.'.csv';
		if ($asOutput) {
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=' . $this->_filename . '.csv');
			$filePath = 'php://output';
		}
		$output = fopen($filePath, 'w');
		fputs($output,$bom=(chr(0xEF) . chr(0xBB) . chr(0xBF)));
		fputcsv($output, array_values($this->_columnNames),';',' ');

		/**
		 * @var $comparisonResult ComparisonResult
		 */
		foreach($this->_values as $row) {

			fputcsv($output, array_values($row), ';', ' ');

		}

		fclose($output);
	}

	/**
	 * Название колонок
	 * @param $rowNames array
	 * @return mixed
	 */
	function SetColumnNames($rowNames)
	{
		$this->_columnNames = $rowNames;
	}

	/**
	 * Значения одного ряда
	 * @param $rowValues array
	 * @return mixed
	 */
	function FeedValues($rowValues)
	{
		$this->_values[] = $rowValues;
	}
}