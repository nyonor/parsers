<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.10.15
 * Time: 20:59
 *
 * Парсер сайта koleso-russia.ru
 */

require_once "base/RivalParserBase.php";

class KolesoRussiaParser extends RivalParserBase {

	const SITE_URL = "koleso-russia.ru";
	protected $_curl;

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse()
	{
		$sprint = 1;
		$maxSprints = 0;

		$url = sprintf($this->_urlPattern, $sprint);

		echo "<br/><br/><br/><br/>" . $url . "<br/><br/><br/><br/>";

		$curl = $this->GetCurl($url);

		$results = [];

		$allBrandsWeHave = $this->GetAllBrands();
		var_dump($allBrandsWeHave);

		//print_r($allBrandsWeHave);die;

		// TODO: Implement Parse() method.
		do {

			$rawRes =  iconv('cp1251', 'utf8', curl_exec($curl));

			$stopRequestsMatchResult = "";
			preg_match('/(Ошибка 404. Страница не найдена)/', $rawRes, $stopRequestsMatchResult);
			if(count($stopRequestsMatchResult) > 1 && $stopRequestsMatchResult[1] != null) {
				break;
			}

			$pattern = '/<a[^>]+class="title"[^>]+>([^<]+)<\/a>/';
			$outputArray = [];
			preg_match_all($pattern, $rawRes, $outputArray);

			$rivalTireModel = new RivalTireModel();

			foreach($outputArray[1] as $title) {
				echo "<br/>" . $title;

				$brandMatchResult = "";
				$implodedBrands = implode('|', $allBrandsWeHave);
				$brandRegex = "/(" . $implodedBrands . "|[а-яА-Я]+)/i";

				//preg_match('/([А-ЯA-Z]+\s[А-ЯA-Z]+)\s|([А-ЯA-Z]+)/', $title, $brandMatchResult);
				preg_match($brandRegex, $title, $brandMatchResult);
				$brand = $brandMatchResult[1] != null ? $brandMatchResult[1] : $brandMatchResult[2];
				echo "<br/>" . $brand;
				$rivalTireModel->brand = $brand;

				//сделаем бренд в верхнем регистре, все остальное в нижнем регистре
				$title = str_replace(strtolower($brand),strtoupper($brand),strtolower($title));
				echo "<br/>" . $title;

				$modelMatchResult = "";
				//preg_match('/(?:[А-ЯA-Z]+\s+[А-ЯA-Z]+)\s+([^<\/]+)\s(?:\d{3}\/|\d{2}\/)|(?:[А-ЯA-Z]+)\s+([^<\/]+)\s(?:\d{3}\/|\d{2}\/)/',
				preg_match('/(?:'. $implodedBrands .')\s+(.*?)\s+(?:\d+\/\d+|\d+x\d+|\d+X\d+)/is',
					$title, $modelMatchResult);
				$model = $modelMatchResult[1] != null ? $modelMatchResult[1] : $modelMatchResult[2];
				echo "<br/>" . $model;
				$rivalTireModel->model = $model;

				$widthAndHeightMatchResult = "";
				//preg_match('/(?:[А-ЯA-Z]+\s+[А-ЯA-Z]+)\s+(?:[^<\/]+)\s(\d{3}|\d{2})(?:\/|x|X)(\d+[,\.]?\d+)?|(?:[А-ЯA-Z]+)\s+(?:[^<\/]+)\s(\d{3}|\d{2})(?:\/|x|X)(\d+[,\.]\d+)?/is',
				preg_match('/(\d+)(?:\/|x|X)(\d+[,.]?\d+|\d+)/is',
					$title, $widthAndHeightMatchResult);
				$width = $widthAndHeightMatchResult[1] != null ? $widthAndHeightMatchResult[1] : $widthAndHeightMatchResult[3];
				$height = $widthAndHeightMatchResult[2] != null ? $widthAndHeightMatchResult[2] : $widthAndHeightMatchResult[4];
				echo "<br/>" . $width . " " . $height;
				$rivalTireModel->width = $width;
				$rivalTireModel->height = $height;

				$diameterMatchResult = "";
				preg_match('/(?:\s|\/)(?:[Rr]|[ZRzr]+)(\d+)(?:\s|)/', $title, $diameterMatchResult);
				$diameter = $diameterMatchResult[1];
				echo "<br/>" . $diameter;
				$rivalTireModel->diameter = $diameter;

				$loadIndexAndSpeedIndexMatchResult = "";
				preg_match('/(\d+)(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)/is', $title,
					$loadIndexAndSpeedIndexMatchResult);
				$loadIndex = $loadIndexAndSpeedIndexMatchResult[1] != null ? $loadIndexAndSpeedIndexMatchResult[1]
							:$loadIndexAndSpeedIndexMatchResult[3];
				$speedIndex = $loadIndexAndSpeedIndexMatchResult[2] != null ? $loadIndexAndSpeedIndexMatchResult[2]
							:$loadIndexAndSpeedIndexMatchResult[4];
				echo "<br/>" . $loadIndex . " " . $speedIndex;
				$rivalTireModel->loadIndex = $loadIndex;
				$rivalTireModel->speedIndex = $speedIndex;

				$runFlatMatchResult = "";
				preg_match("/(flat)/", $title, $runFlatMatchResult);
				$runFlat = count($runFlatMatchResult) > 1 && $runFlatMatchResult[1] != null ? true : false;
				echo "<br/>" . $runFlat;
				$rivalTireModel->runFlat = $runFlat;

				echo "<br/><br/>";
				
				$rivalTireModel->url = $url;
				$rivalTireModel->site = self::SITE_URL;

				$results[] = $rivalTireModel;
			}

			$sprint++;

			$url = sprintf($this->_urlPattern, $sprint);
			echo "<br/><br/><br/><br/>" . $url . "<br/><br/><br/><br/>";

			//curl_close($curl);
			$curl = $this->GetCurl($url);
			//curl_setopt($curl, CURLOPT_URL, sprintf($this->_urlPattern, $url));

		} while($maxSprints == 0 || $maxSprints >= $sprint);

		curl_close($curl);
	}

	protected function GetTitle() {

	}

	//todo переместить в базовый класс?
	/**
	 * @return array
	 */
	protected function GetAllBrands() {
		$sql = "select * from (SELECT distinct(CONVERT(Company USING ASCII)) AS 'Brand' FROM 4tochki.tyres) as q1 where Brand not like '%?%' and Brand != ''";
		$sqlResult = mysql_query($sql);
		$brandsArr = [];
		while($row = mysql_fetch_assoc($sqlResult)) {
			//echo "<br/>". $row['Brand'] . "---->" . mb_detect_encoding($row['Brand']);
			//$brandsArr[] = iconv('UTF8', 'ASCII', $row['Brand']);
			$brandsArr[] = $row['Brand'];
		}
		return $brandsArr;
	}

	//todo переместить в базовый класс?
	/**
	 * @param $subjectString
	 * @return bool
	 */
	protected function IsItUsaMarkup($subjectString) {
		$markUpMatchResult = "";
		preg_match('/(\d+x\d+[,]?)/', $subjectString, $markUpMatchResult);
		return count($markUpMatchResult) > 1;
	}

	/**
	 * Возвращает готовый объект curl для запросов
	 * @param $url
	 * @return resource
	 */
	protected function GetCurl($url) {
		if ($this->_curl != null) {
			curl_close($this->_curl);
		}

		$this->_curl = curl_init($url);
		curl_setopt($this->_curl,CURLOPT_URL,$url);
		curl_setopt($this->_curl,CURLOPT_RETURNTRANSFER,1);

		return $this->_curl;
	}
}