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
	public function __construct($fileName) {
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$fileName.'.csv');
	}

	/**
	 * Вывод собственно
	 * @param $arg array
	 * @return mixed
	 */
	function Render($arg)
	{
		$output = fopen('php://output', 'w');
		fputcsv($output, ['САЕ','Количество','Цена','НужнаПроверка']);

		/**
		 * @var $csvViewModel CsvViewModel
		 */
		foreach($arg as $csvViewModel) {
			$row = [];
			$row[] = $csvViewModel->cae;
			$row[] = $csvViewModel->quantity;
			$row[] = $csvViewModel->price;
			$row[] = $csvViewModel->shouldCheckByOperator;
			fputcsv($output, $row);
		}
	}
}