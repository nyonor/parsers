<?php

/**
 * Класс создан для обновления НАШЕЙ номенклатуры
 * из файла по пути (или из потока по url)
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 21.10.15
 * Time: 15:49
 */
class ProductsUpdater implements IProductsUpdater
{
	const TOCHKI_PRODUCTS_TIRES_URL = "http://www.4tochki.ru/mods/system/export/report_all_tyres.php";
	const LOCAL_PRODUCTS_FILE_REL_PATH = "/files/report_all_tyres.php";
	const LOCAL_AVAILABILITY_FILE_REL_PATH = "/files/restnew.php";
	const AVAILABILITY_FILE_PATH = "http://www.4tochki.ru/mods/system/export/restnew.php";

	protected $_rawContent;
	protected $_xmlContent;
	protected $_dbController;

	/*public function __call() {

		if ($this->_dbController == null)
			throw new Exception("No IDbController provided!");

	}*/

	public function __construct(IDbController $dbController) {
		$this->_dbController = $dbController;
	}

	/**
	 * Обновляет номенклатуру
	 * @throws Exception
	 */
	public function UpdateProducts() {

		$this->_dbController->TruncateOldProductsData();

		$this->LoadSimpleXmlContent(self::TOCHKI_PRODUCTS_TIRES_URL);
		$results = [];

		/**
		 * @var $productXml SimpleXMLElement
		 */
		foreach ($this->_xmlContent as $productXml) {
			$productTireModel = new ProductTireModel();
			$productTireModel->brand = strtoupper((string)$productXml->brand);
			$productTireModel->cae = $productXml->code;
			$productTireModel->constructionType = strtolower((string)$productXml->constr);

			$seasonClass = SeasonModel::Factory((string)$productXml->season);
			if ($seasonClass != null)
				$productTireModel->season = strtolower($seasonClass->GetSeasonName());

			$productTireModel->diameter = strtolower((string)$productXml->diameter);
			$productTireModel->model = strtolower((string)$productXml->model);
			$productTireModel->width = strtolower((string)$productXml->width);
			$productTireModel->height = strtolower((string)$productXml->height);
			$productTireModel->loadIndex = strtolower((string)$productXml->load_index);
			$productTireModel->speedIndex = strtolower((string)$productXml->speed_index);
			$productTireModel->runFlat = (string)$productXml->puncture != null && (string)$productXml->puncture != ' ';
			$results[] = $productTireModel;
		}

		$this->_dbController->AddProducts($results);
	}

	/**
	 * Загружает объект SimpleXMLElement в свойство
	 * экземпляра данного класса
	 * @param $filePathToLoad
	 */
	protected function LoadSimpleXmlContent($filePathToLoad) {
		if ($filePathToLoad == self::TOCHKI_PRODUCTS_TIRES_URL)
		{
			$this->_xmlContent = simplexml_load_file($this->GetProductsFilePath());
		}

		if ($filePathToLoad == self::AVAILABILITY_FILE_PATH)
		{
			$this->_xmlContent = simplexml_load_file($this->GetAvailabilityFilePath());
		}

	}

	protected function GetProductsFilePath() {
		$this->DownloadFile(self::LOCAL_PRODUCTS_FILE_REL_PATH, self::TOCHKI_PRODUCTS_TIRES_URL);
		return getcwd() . self::LOCAL_PRODUCTS_FILE_REL_PATH;
	}

	protected function GetAvailabilityFilePath() {
		$this->DownloadFile(self::LOCAL_AVAILABILITY_FILE_REL_PATH, self::AVAILABILITY_FILE_PATH);
		return getcwd() . self::LOCAL_AVAILABILITY_FILE_REL_PATH;
	}

	protected function DownloadFile($localRelPath, $remoteFilePath) {
		$f = fopen(getcwd().$localRelPath,'w');
		$curl = curl_init($remoteFilePath);
		curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,0);
		curl_setopt($curl,CURLOPT_FILE,$f);
		$result = curl_exec($curl);
		curl_close($curl);
	}

	/**
	 * Обновление наличия товара
	 * @return mixed
	 */
	function UpdateProductsAvailability()
	{
		//загрузим данные о наличии из удаленного файла на 4точках
		$this->LoadSimpleXmlContent(self::AVAILABILITY_FILE_PATH);

		//пробегаем данные о наличии
		/**
		 * @var $availabilityXml SimpleXMLElement
		 */
		foreach($this->_xmlContent as $availabilityXml) {

			//обновляем данные о наличии
			for($i = 1; $i < 30; $i++){

				$restTag = "rest".$i;
				if (empty($availabilityXml->{$restTag}) == false && $availabilityXml->{$restTag} > 0){

					$this->_dbController->UpdateProductAvailability($availabilityXml->cae, true);
					break;

				}

			}

		}
	}
}