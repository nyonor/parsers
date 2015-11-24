<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 13.11.15
 * Time: 15:17
 */

trait ProductParametersParserTrait
{
	function GetBrand($subject) {
		//todo не нужен тут
	}

	/**
	 * Получить марку (Бренд),
	 * используй этот метод
	 * @param $subject string
	 * @param $allBrands string[]
	 * @return string
	 * @throws Exception
	 */
	function GetBrandWithList($subject, $allBrands)
	{
		/**
		 * @var $dbController IDbController
		 */
		$dbController = $this->_dbController;
		if($dbController == null) {
			throw new Exception("Class which using ProductParametersParserTrait
			must have this->_dbController (IDbController). Короче используй SetIDbController
			для установки");
		}

		$implodedBrands = implode('|', $allBrands);

		$brandRegex = "/(" . $implodedBrands . "|[а-яА-Я]+)/is";
		preg_match($brandRegex, $subject, $brandMatchResult);
		if(count($brandMatchResult) == 1 || count($brandMatchResult) == 0) {
			return null;
		}

		$brand = $brandMatchResult[1] != null ? $brandMatchResult[1] : $brandMatchResult[2];
		return $brand;
	}

	/**
	 * Получить имя модели
	 * @param $subject
	 * @return string
	 */
	function GetModelName($subject)
	{
		// TODO: Implement GetModelName() method.
	}

	/**
	 * Получить ширину шины
	 * @param $subject
	 * @return string
	 */
	function GetWidth($subject)
	{
		$widthAndHeightMatchResult = "";
		preg_match('/(\d+)(?:\/|x|X)(\d+[,.]?\d+|\d+)?/is', $subject, $widthAndHeightMatchResult);
		$width = $widthAndHeightMatchResult[1] != null ? $widthAndHeightMatchResult[1] : $widthAndHeightMatchResult[3];
		$height = $widthAndHeightMatchResult[2] != null ? $widthAndHeightMatchResult[2] : $widthAndHeightMatchResult[4];
		return $width;
	}

	/**
	 * Получить профиль шины
	 * @param $subject
	 * @return string
	 */
	function GetHeight($subject)
	{
		$widthAndHeightMatchResult = "";
		preg_match('/(\d+)(?:\/|x|X)(\d+[,.]?\d+|\d+)?/is', $subject, $widthAndHeightMatchResult);
		$width = $widthAndHeightMatchResult[1] != null ? $widthAndHeightMatchResult[1] : $widthAndHeightMatchResult[3];
		$height = $widthAndHeightMatchResult[2] != null ? $widthAndHeightMatchResult[2] : $widthAndHeightMatchResult[4];
		return $height;
	}

	/**
	 * Получить тип конструкции
	 * @param $subject
	 * @return string
	 */
	function GetConstructionType($subject)
	{
		$diameterCounstructionMatchResult = "";
		preg_match('/(?:\s|\/)([Rr]|[ZRzr]+)(\d+)(?:\s|)/', $subject, $diameterMatchResult);
		$construction = $diameterMatchResult[1];
		$diameter = $diameterMatchResult[2];
		return $construction;
	}

	/**
	 * Получить диаметр шины
	 * @param $subject
	 * @return string
	 */
	function GetDiameter($subject)
	{
		$diameterCounstructionMatchResult = "";
		preg_match('/(?:\s|\/)([Rr]|[ZRzr]+)(\d+)(?:\s|)/', $subject, $diameterMatchResult);
		$construction = $diameterMatchResult[1];
		$diameter = $diameterMatchResult[2];
		return $diameter;
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
	 * Возвращает сезон шин
	 * @param $subject
	 * @return string
	 */
	function GetSeason($subject)
	{
		// TODO: Implement GetSeason() method.
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
	 * Возвращает цену
	 * @param $subject
	 * @return float
	 */
	function GetPrice($subject)
	{
		// TODO: Implement GetPrice() method.
	}

	/**
	 * Возвращает количество
	 * @param $subject
	 * @return int
	 */
	function GetQuantity($subject)
	{
		// TODO: Implement GetQuantity() method.
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