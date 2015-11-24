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
use sys\Timer;

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
		//echo "<br/><br/><br/><br/>" . $url . "<br/><br/><br/><br/>";
		$curl = $this->GetCurl($url);

		$results = [];

		$allBrandsWeHave = $dbController->GetAllBrands();
		$implodedBrands = implode('|', $allBrandsWeHave);
		//$implodedModels = implode('|', $allModelsWeHave);

		do {

			echo "Запрос курлом...";
			$ts = Timer::Start();
			$rawRes =  iconv('cp1251', 'utf8', curl_exec($curl));
			echo "...".Timer::StopAndResult($ts)."sec <br/>";

			$stopRequestsMatchResult = "";
			preg_match('/(Ошибка 404. Страница не найдена)/', $rawRes, $stopRequestsMatchResult);
			if(count($stopRequestsMatchResult) > 1 && $stopRequestsMatchResult[1] != null) {
				break;
			}

			echo "HtmlDomParser->str_get_html...";
			$ts = Timer::Start();
			$htmlDom = new HtmlDomParser();
			$strHtmlDom = $htmlDom->str_get_html($rawRes);
			echo "...".Timer::StopAndResult($ts)."<br/>";

			if (!method_exists($strHtmlDom,"find"))
				continue;

			echo "HtmlDomParser->find('.mainconteinercenter')...";
			$ts = Timer::Start();
			foreach($strHtmlDom->find('.mainconteinercenter') as $div) {
				$title = $div->find('a.title')[0]->plaintext;
				//echo "<br/>" . $title;die;

				$rivalTireModel = new RivalTireModel();

				$brandMatchResult = "";

				$brandRegex = "/(" . $implodedBrands . "|[а-яА-Я]+)/is";
				//echo $brandRegex; die;
				//$modelsRegex = "/(" . $implodedModels . ")/is";

				//echo  $modelsRegex;//die;

				//preg_match('/([А-ЯA-Z]+\s[А-ЯA-Z]+)\s|([А-ЯA-Z]+)/', $title, $brandMatchResult);
				preg_match($brandRegex, $title, $brandMatchResult);
				if(count($brandMatchResult) == 1 || count($brandMatchResult) == 0) {
					continue;
				}

				$brand = $brandMatchResult[1] != null ? $brandMatchResult[1] : $brandMatchResult[2];
				//echo "<br/>" . $brand;
				$rivalTireModel->brand = $brand;

				//сделаем бренд в верхнем регистре, все остальное в нижнем регистре
				$title = str_replace(strtolower($brand),strtoupper($brand),strtolower($title));
				//echo "<br/>" . $title;

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
				//print_r($div->find('p.contentblock table.info_data ul.ostatki')); die;
				$rivalTireModel->quantity = $this->GetQuantity($div->find('p.contentblock table.info_data ul.ostatki'));
				//die;
				//echo "<br/><br/>";

				$rivalTireModel->url = $url;
				$rivalTireModel->site = self::SITE_URL;

				//var_dump($rivalTireModel);

				$results[] = $rivalTireModel;
			}
			echo "...".Timer::StopAndResult($ts)."<br/>";

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
	 * Возвращает количество
	 * @param $subject
	 * @return int
	 */
	function GetQuantity($subject)
	{
		$totalQty = 0;
		foreach($subject as $ostatkiUl) {
			preg_match_all('/\(>?(\d+)\)/is', $ostatkiUl->innertext, $qtyMatchResult);
			//echo ($qtyMatchResult);
			foreach($qtyMatchResult[1] as $qty) {
				$totalQty += $qty;
			}
		}
		return $totalQty;
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
	 * TODO: перенести в IDbController! и изменить таблицу источник?
	 * @deprecated
	 * @return array
	 */
	protected function GetAllBrands() {
		$sql = "select * from (SELECT distinct(CONVERT(company USING ASCII)) AS 'Brand' FROM 4tochki.tyres) as q1 where Brand not like '%?%' and Brand != ''";
		$sqlResult = mysql_query($sql);
		print_r($sqlResult);die;
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

	function GetBrand($subject)
	{
		// TODO: Implement GetBrand() method.
	}

	/**
	 * Получить ширину шины
	 * @param $subject
	 * @return string
	 */
	function GetWidth($subject)
	{
		// TODO: Implement GetWidth() method.
	}

	/**
	 * Получить профиль шины
	 * @param $subject
	 * @return string
	 */
	function GetHeight($subject)
	{
		// TODO: Implement GetHeight() method.
	}

	/**
	 * Получить тип конструкции
	 * @param $subject
	 * @return string
	 */
	function GetConstructionType($subject)
	{
		// TODO: Implement GetConstructionType() method.
	}

	/**
	 * Получить диаметр шины
	 * @param $subject
	 * @return string
	 */
	function GetDiameter($subject)
	{
		// TODO: Implement GetDiameter() method.
	}

	/**
	 * Получить индекс нагрузки
	 * @param $subject
	 * @return string
	 */
	function GetLoadIndex($subject)
	{
		// TODO: Implement GetLoadIndex() method.
	}

	/**
	 * Получить индекс скорости
	 * @param $subject
	 * @return string
	 */
	function GetSpeedIndex($subject)
	{
		// TODO: Implement GetSpeedIndex() method.
	}

	/**
	 * Получить runFlat
	 * @param $subject
	 * @return string
	 */
	function GetRunFlat($subject)
	{
		// TODO: Implement GetRunFlat() method.
	}

	/**
	 * Получить имя сайта
	 * @return string
	 */
	function GetSiteName()
	{
		// TODO: Implement GetSiteName() method.
	}

	/**
	 * Получить url спарсенного товара
	 * @return string
	 */
	function GetParseUrl()
	{
		// TODO: Implement GetParseUrl() method.
	}

	/**
	 * Получить имя модели
	 * @param $subject
	 * @return string
	 */
	function GetProductType($subject)
	{
		// TODO: Implement GetProductType() method.
	}
}