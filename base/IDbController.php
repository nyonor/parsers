<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 21.10.15
 * Time: 8:19
 */
interface IDbController
{
	/**
	 * @param $objectToInsert RivalTireModel | StdClass
	 */
	function AddParsingResult($objectToInsert);

	/**
	 * @param $siteUrl
	 */
	function TruncateOldParseResult($siteUrl);

	/**
	 * @return mixed
	 */
	function TruncateOldProductsData();

	/**
	 * @param $objectToInsert ProductTireModel[] | StdClass[]
	 * @return mixed
	 */
	function AddProductsData($objectToInsert);

	/**
	 * Поиск и сопоставление товара в нашей номенклатуре
	 * @param $rivalTireModel RivalTireModel
	 * @return ProductTireModel[]|StdClass[]
	 */
	function FindInProducts($rivalTireModel);

	function FindParsedResultsBySiteUrl($siteUrl);

	function GetAllModels();

	function GetAllBrands();
}