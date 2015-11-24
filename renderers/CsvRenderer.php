<?php
/**
 * Создает CSV с результатами сравнения
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 30.10.15
 * Time: 14:53
 */
class CsvRenderer implements IRenderer
{
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
		fputcsv($output, array_values(['САЕ','Бренд','Модель','Ширина','Профиль','Диаметр','Индекс скорости',
			'Индекс нагрузки', 'Сезон', 'RunFlat', 'Кол-во','Цена','НужнаПроверка']),';',' ');

		/**
		 * @var $comparisonResult ComparisonResult
		 */
		foreach($arg as $comparisonResult) {
			$row = [];
			$row[] = $comparisonResult->cae;
			$row[] = $comparisonResult->conBrand;
			$row[] = $comparisonResult->conModel;
			$row[] = $comparisonResult->width;
			$row[] = $comparisonResult->height;
			$row[] = $comparisonResult->diameter;
			$row[] = $comparisonResult->speedIndex;
			$row[] = $comparisonResult->loadIndex;
			$row[] = $comparisonResult->season;
			$row[] = $comparisonResult->runFlat;
			$row[] = $comparisonResult->quantity;
			$row[] = $comparisonResult->price;
			$row[] = $comparisonResult->shouldCheckByOperator != null && $comparisonResult->shouldCheckByOperator == true ? 1 : 0;
			fputcsv($output, array_values($row), ';', ' ');
		}

		fclose($output);
	}
}