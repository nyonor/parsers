<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 16.12.15
 * Time: 14:32
 */
use Sunra\PhpSimple\HtmlDomParser;

class KolesatytParser extends RivalParserBase implements IProductParametersParser
{

	use ProductParametersParserTrait;
	protected $_curl;
	const SITE_URL = "kolesatyt.ru";

	/**
	 * Запуск парсинга сайта по переданному $urlPattern
	 * @param IDbController $dbController
	 * @return array RivalTireModel | RivalDiskModel
	 */
	public function Parse(IDbController $dbController = null)
	{
		//var_dump($this->_dbController);die;
		$ourBrands = $dbController->GetAllBrands();
		$currentSprint = 1;
		$firstUrl = null;
		$rivalResult = [];

		do {
			$url = sprintf($this->_urlPattern, $currentSprint);
			$curl = $this->GetCurl($url);
			$rawRes = curl_exec($curl);
			print_r($rawRes);die;

			$htmlDom = new HtmlDomParser();
			$strHtmlDom = $htmlDom->str_get_html($rawRes);

			if (!method_exists($strHtmlDom,"find"))
			{
				break;
			}

			foreach($strHtmlDom->find(".drives-fit_item-podbor") as $div) {

				$rivalTireResult = new RivalTireModel();
				$titleRaw = $div->find(".title", 0)->plaintext;

				var_dump($titleRaw);

				//brand
				$explodedTitle = explode(' ',$titleRaw);
				$implodedTitle = implode(' ', array_slice($explodedTitle, 1));
				//var_dump(array_slice($explodedTitle, 1));
				$rivalTireResult->brand = $this->GetBrandWithList($implodedTitle,
					$ourBrands);

				//var_dump($explodedTitle);

				//model
				$hrefWithModel = $div->find("a.drives-fit_link", 0)->href;
				$hrefExploded = explode('/', $hrefWithModel);
				$rivalTireResult->model = str_replace('-', ' ',$hrefExploded[4]);

				//url, site
				$rivalTireResult->site = $this->GetSiteToParseUrl();
				$rivalTireResult->url = $url;

				//constr type
				$rivalTireResult->constructionType = $this->GetConstructionType($implodedTitle);

				//width
				$rivalTireResult->width = $this->GetWidth($implodedTitle);

				//height
				$rivalTireResult->height = $this->GetHeight($implodedTitle);

				//diameter
				$rivalTireResult->diameter = $this->GetDiameter($implodedTitle);

				//spindex
				$spindexMatchRes = null;
				preg_match('/\s\d+(J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)\s/', $implodedTitle, $spindexMatchRes);
				$rivalTireResult->speedIndex = isset($spindexMatchRes[1]) ? $spindexMatchRes[1] : null;

				//loadIndex
				$loadIndexMatchRes = null;
				preg_match('/\s(\d+)(?:J|K|L|M|N|P|Q|R|S|T|U|H|V|VR|W|Y|ZR)\s/', $implodedTitle, $loadIndexMatchRes);
				$rivalTireResult->loadIndex = isset($loadIndexMatchRes[1]) ? $loadIndexMatchRes[1] : null;

				//price
				$priceRawString = $div->find(".drives-fit_price", 0)->plaintext;
				$rivalTireResult->price = (int)str_replace(' ','',$priceRawString);

				//qty
				$qtyString = $div->find(".drives-fit_stock", 0)->plaintext;
				$qtyMatchRes = null;
				preg_match('/(\d+)/', $qtyString, $qtyMatchRes);
				$rivalTireResult->quantity = isset($qtyMatchRes[1]) ? (int)$qtyMatchRes[1] : null;

				//season
				$seasonRawString = $div->find(".drives-fit_item_icons i", 0)->title;
				if ($seasonRawString == "Вссезонная") {
					$rivalTireResult->season = SeasonModel::ALL_SEASONS;
				} else {
					$rivalTireResult->season = SeasonModel::Factory($seasonRawString)
						->GetSeasonName();
				}

				var_dump($rivalTireResult);

				$rivalResult[] = $rivalTireResult;
			}

			$currentSprint++;

			$currentUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
			//MyLogger::WriteToLog($currentUrl, LOG_WARNING);

			if ($currentUrl == $firstUrl) {
				$shouldContinue = false;
				break;
			} else {
				$shouldContinue = true;
			}

			if ($firstUrl == null) {
				$firstUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
			}

		} while ($shouldContinue == true);

		return $rivalResult;
	}

	/**
	 * Возвращает url сайта для парсинга
	 * @return string
	 */
	public function GetSiteToParseUrl()
	{
		return self::SITE_URL;
	}

	/**
	 * Возвращает готовый объект curl для запросов
	 * @param $url
	 * @return resource
	 */
	protected function GetCurl($url)
	{
		if ($this->_curl != null) {
			curl_close($this->_curl);
		}

		$this->_curl = curl_init($url);
		curl_setopt($this->_curl, CURLOPT_URL,$url);
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->_curl, CURLOPT_USERAGENT,
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36");
		curl_setopt($this->_curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($this->_curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
		//curl_setopt($this->_curl, CURLOPT_HEADER, true);
		//curl_setopt($this->_curl, CURLOPT_COOKIE, $this->GetResponseCookies());

		return $this->_curl;
	}
}