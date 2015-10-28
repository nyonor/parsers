<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 21.10.15
 * Time: 15:49
 */
class ProductsUpdater implements IProductsUpdater
{
	const TOCHKI_PRODUCTS_TIRES_URL = "http://www.4tochki.ru/mods/system/export/report_all_tyres.php";
	const LOCAL_PRODUCTS_FILE_REL_PATH = "/files/report_all_tyres.php";

	protected $_rawContent;
	protected $_xmlContent;
	protected $_dbController;

	public function __construct(IDbController $dbController) {
		$this->_dbController = $dbController;
	}

	public function UpdateProducts() {
		if ($this->_dbController == null)
			throw new Exception("No IDbController provided!");

		$this->LoadSimpleXmlContent();
		$results = [];

		/**
		 * @var $productXml SimpleXMLElement
		 */
		foreach ($this->_xmlContent as $productXml) {
			$productTireModel = new ProductTireModel();
			$productTireModel->brand = strtoupper((string)$productXml->brand);
			$productTireModel->cae = strtolower((string)$productXml->code);
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
			//break;

			if((string)$productXml->code == "510043") {
				var_dump($productXml);
				var_dump($productTireModel);
			}
		}

		$this->_dbController->AddProductsData($results);
	}

	protected function LoadSimpleXmlContent() {
		$this->_xmlContent = simplexml_load_file($this->GetProductsFilePath());
	}

	protected function GetProductsFilePath() {
		return getcwd() . self::LOCAL_PRODUCTS_FILE_REL_PATH;
	}
}