<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 26.02.16
 * Time: 9:33
 */

/**
 * Interface IParseHub @deprecated
 */
interface IParseHub
{
	/**
	 * @param $rivalSiteUrl
	 * @return bool|ComparisonResult[]|CsvViewModel[]|RivalTireModel[]
	 */
	public function GetComparingResult($rivalSiteUrl);

	/**
	 * Установка парсера - он содержит всю логику парсинга!
	 * @param RivalParserBase $parser
	 * @return $this
	 */
	public function InjectParser(RivalParserBase $parser);

	/**
	 * Объект будет использоваться для работы с БД
	 * @param IDbController $controller
	 * @return $this
	 */
	public function InjectDBController(IDbController $controller);

	/**
	 * Объект будет использоваться для обновления номенклатуры (нашей)
	 * @param IProductsUpdater $productsUpdater
	 * @return $this
	 */
	public function InjectProductsUpdater(IProductsUpdater $productsUpdater);

	/**
	 * Выполнение скрипта по пути с помощью nodeJs
	 * @param string $path
	 */
	public function ExecuteNodeJsScript($path);

	/**
	 * Обработка файла с json данными парсинга
	 * @param string $fileName
	 * @deprecated
	 * todo в принципе пока не используется, перед использованием проверить функциональность!
	 */
	public function ProcessParsedDataFromFileToDB($fileName);

	public function UpdateProducts();

	/**
	 * Выполняет парсинг и обработку результатов, это основной метод для работы
	 * @param bool $shouldTruncateOldData
	 * @return $this
	 * @throws Exception
	 */
	public function ProcessParsedDataFromInjectedParserToDB($shouldTruncateOldData = true);
}