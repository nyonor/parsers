<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.10.15
 * Time: 20:59
 * Парсер сайта koleso-russia.ru
 */

//todo поменять все на USE/<NAMESPACE>
use Sunra\PhpSimple\HtmlDomParser;

class KolesoRussiaParser extends RivalParserBase implements IProductParametersParser {

	const SITE_URL = "koleso-russia.ru";
	protected $_curl;

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		$sprint = 1;
		$maxSprints = 0;

		$url = sprintf($this->_urlPattern, $sprint);

		echo "<br/><br/><br/><br/>" . $url . "<br/><br/><br/><br/>";

		$curl = $this->GetCurl($url);

		$results = [];

		$allBrandsWeHave = $this->GetAllBrands();
		//$allModelsWeHave = $dbController->GetAllModels();

		//var_dump($allBrandsWeHave);
		//print_r($allModelsWeHave);

		/*foreach($allModelsWeHave as $k => $val) {
			$first = strtolower(str_replace('/','\/',$val));
			$second = str_replace('(','\(', $first);
			$allModelsWeHave[$k] = str_replace(')','\)',$second);
		}*/

		//print_r($allModelsWeHave);//die;

		$implodedBrands = implode('|', $allBrandsWeHave);
		//$implodedModels = implode('|', $allModelsWeHave);

		do {

			$rawRes =  iconv('cp1251', 'utf8', curl_exec($curl));

			$stopRequestsMatchResult = "";
			preg_match('/(Ошибка 404. Страница не найдена)/', $rawRes, $stopRequestsMatchResult);
			if(count($stopRequestsMatchResult) > 1 && $stopRequestsMatchResult[1] != null) {
				break;
			}

			$htmlDom = new HtmlDomParser();
			$strHtmlDom = $htmlDom->str_get_html($rawRes);

			/*$pattern = '/<a[^>]+class="title"[^>]+>([^<]+)<\/a>/';
			$outputArray = [];
			preg_match_all($pattern, $rawRes, $outputArray);

			foreach($outputArray[1] as $title) {*/
			foreach($strHtmlDom->find('.mainconteinercenter') as $div) {;
				$title = $div->find('a.title')[0]->plaintext;
				//echo "<br/>" . $title;die;

				$rivalTireModel = new RivalTireModel();

				$brandMatchResult = "";

				$brandRegex = "/(" . $implodedBrands . "|[а-яА-Я]+)/i";
				//$modelsRegex = "/(" . $implodedModels . ")/is"; //todo остановился тут!!!

				//echo  $modelsRegex;//die;

				//preg_match('/([А-ЯA-Z]+\s[А-ЯA-Z]+)\s|([А-ЯA-Z]+)/', $title, $brandMatchResult);
				preg_match($brandRegex, $title, $brandMatchResult);
				$brand = $brandMatchResult[1] != null ? $brandMatchResult[1] : $brandMatchResult[2];
				//echo "<br/>" . $brand;
				$rivalTireModel->brand = $brand;

				//сделаем бренд в верхнем регистре, все остальное в нижнем регистре
				$title = str_replace(strtolower($brand),strtoupper($brand),strtolower($title));
				echo "<br/>" . $title;



				//$modelMatchResult = "";
				//preg_match('/(?:[А-ЯA-Z]+\s+[А-ЯA-Z]+)\s+([^<\/]+)\s(?:\d{3}\/|\d{2}\/)|(?:[А-ЯA-Z]+)\s+([^<\/]+)\s(?:\d{3}\/|\d{2}\/)/',
				//preg_match('/(?:'. $implodedBrands .'|[а-яА-Я]+)\s+(.*?)\s+(?:\d+\/\d+|\d+x\d+|\d+X\d+|\d+)/is',
				//preg_match($modelsRegex,
				//		$title, $modelMatchResult);
				//$model = $modelMatchResult[1] != null ? $modelMatchResult[1] : $modelMatchResult[2];
				//echo "<br/>" . $model;
				//$rivalTireModel->model = $model;
				$contentBlockText = $div->find('p.contentblock')[0]->plaintext;
				$rivalTireModel->model = $this->GetModelName($contentBlockText);
				//var_dump($rivalTireModel);die;

				$widthAndHeightMatchResult = "";
				//preg_match('/(?:[А-ЯA-Z]+\s+[А-ЯA-Z]+)\s+(?:[^<\/]+)\s(\d{3}|\d{2})(?:\/|x|X)(\d+[,\.]?\d+)?|(?:[А-ЯA-Z]+)\s+(?:[^<\/]+)\s(\d{3}|\d{2})(?:\/|x|X)(\d+[,\.]\d+)?/is',
				preg_match('/(\d+)(?:\/|x|X)(\d+[,.]?\d+|\d+)/is', $title, $widthAndHeightMatchResult);
				$width = $widthAndHeightMatchResult[1] != null ? $widthAndHeightMatchResult[1] : $widthAndHeightMatchResult[3];
				$height = $widthAndHeightMatchResult[2] != null ? $widthAndHeightMatchResult[2] : $widthAndHeightMatchResult[4];
				//echo "<br/>" . $width . " " . $height;
				$rivalTireModel->width = $width;
				$rivalTireModel->height = $height;

				$diameterCounstructionMatchResult = "";
				preg_match('/(?:\s|\/)([Rr]|[ZRzr]+)(\d+)(?:\s|)/', $title, $diameterMatchResult);
				$construction = $diameterMatchResult[1];
				$diameter = $diameterMatchResult[2];
				//echo "<br/>" . $diameter;
				$rivalTireModel->diameter = $diameter;
				$rivalTireModel->constructionType = $construction;

				$loadIndexAndSpeedIndexMatchResult = "";
				//preg_match('/(\d+)(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)/is', $title,
					//$loadIndexAndSpeedIndexMatchResult);
				preg_match('/(\d+\/\d+)(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)|(\d+)(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)/is', $title,
					$loadIndexAndSpeedIndexMatchResult);
				$loadIndex = $loadIndexAndSpeedIndexMatchResult[1] != null ? $loadIndexAndSpeedIndexMatchResult[1]
							:$loadIndexAndSpeedIndexMatchResult[3];
				$speedIndex = $loadIndexAndSpeedIndexMatchResult[2] != null ? $loadIndexAndSpeedIndexMatchResult[2]
							:$loadIndexAndSpeedIndexMatchResult[4];
				//echo "<br/>" . $loadIndex . " " . $speedIndex;
				$rivalTireModel->loadIndex = $loadIndex;
				$rivalTireModel->speedIndex = $speedIndex;

				$runFlatMatchResult = "";
				preg_match("/(flat)/", $title, $runFlatMatchResult);
				$runFlat = count($runFlatMatchResult) > 1 && $runFlatMatchResult[1] != null ? true : false;
				//echo "<br/>" . $runFlat;
				$rivalTireModel->runFlat = $runFlat;

				$rivalTireModel->season = $this->GetSeason($contentBlockText);
				$rivalTireModel->price = (float)$this->GetPrice($div->find('p.contentblock',0)->find('span.discount',0));

				//echo "<br/><br/>";
				
				$rivalTireModel->url = $url;
				$rivalTireModel->site = self::SITE_URL;

				//var_dump($rivalTireModel);

				$results[] = $rivalTireModel;
			}

			$sprint++;

			$url = sprintf($this->_urlPattern, $sprint);
			//echo "<br/><br/><br/><br/>" . $url . "<br/><br/><br/><br/>";

			//curl_close($curl);
			$curl = $this->GetCurl($url);
			//curl_setopt($curl, CURLOPT_URL, sprintf($this->_urlPattern, $url));

		} while($maxSprints == 0 || $maxSprints >= $sprint);

		curl_close($curl);

		return $results;
	}

	/**
	 * @param $subject
	 * @return string
	 */
	public function GetModelName($subject) {
		$modelMatchResult = null;
		preg_match('/Модель:.?(.+?).?Размер:/is', $subject, $modelMatchResult);
		return trim($modelMatchResult[1]);
	}

	/**
	 * Возвращает сезон шин
	 * @param $subject string
	 * @return string
	 */
	function GetSeason($subject)
	{
		$seasonModel = SeasonModel::Factory($subject);
		$seasonName = null;
		if ($seasonModel != null)
			$seasonName = $seasonModel->GetSeasonName();
		//var_dump($seasonModel);
		return $seasonName;
	}

	/**
	 * Возвращает цену
	 * @param $subject
	 * @return float
	 */
	function GetPrice($subject)
	{
		$match = null;
		preg_match('/([\d\s]+)/is',$subject->plaintext,$match);
		return trim(str_replace(' ','',$match[1]));
	}

	/**
	 * @deprecated
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

	/**
	 * Возвращает url сайта для парсинга
	 * @return string
	 */
	public function GetSiteToParseUrl()
	{
		return self::SITE_URL;
	}
}