<?php
/**
 * RivalParseHub
 * Может осуществлять обработку результатов парсеров RivalParserBase.
 * Может считывать в качестве результатов файлы содержащие json массив с
 * объектами результатов парсинга.
 * Может запускать nodeJs скрипты (предполагается, что скрипт будет
 * парсить сайт и создавать файл с json для
 * дальнейшей обработки).
 * Обрабатывает и сохраняет результаты парсинга,
 * позволяет делать сравнение результатов парсинга и нашей номенклатуры,
 * представляет результаты в виде файла csv
 *
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 01.10.15
 * Time: 10:43
 */

//некоторые константы TODO: перенести в статику класса
define ("NODE_VAR_NAME", "node");
define ("PATH_TO_PARSED_FILES", "/files");


class RivalParseHub
{

    /**
     * Если установлен в true, то хаб будет сначала обновлять номенклатуру и лишь затем выполнять парсинг
     * @var bool
     */
    public $shouldUpdateProductsBeforeParsingResults = true;

	/**
	 * @var string путь к файлу с данными результатов парсинга
	 */
    protected $_jsonSwapFilePath;

    /**
     * @var StdClass[]
     */
    protected $_swapFileContentArray;

    /**
     * @var RivalParserBase
     */
    protected $_currentParser;

    /**
     * @var IDbController
     */
    protected $_dbController;

    /**
     * @var IProductsUpdater
     */
    protected $_productsUpdater;

    /**
     * @var RivalTireModel[]
     */
    protected $_lastParseResults;

	/**
	 * @param int $scriptMaxExecutionTime
	 */
    public function __construct($scriptMaxExecutionTime = 0) {

        set_time_limit($scriptMaxExecutionTime); //парсинг обычно выполняется не быстро
    }


    //TODO: разбить на методы
    /**
     * @param $rivalSiteUrl
     * @return bool|ComparisonResult[]|CsvViewModel[]|RivalTireModel[]
     */
    public function GetComparingResult($rivalSiteUrl) {

        if (empty($rivalSiteUrl)) {
            throw new InvalidArgumentException("rivalSiteUrl argument is missing!");
        }

        //ищем в таблице сравнений
        $subjectResults = $this->_dbController->FindInComparedByUrl($rivalSiteUrl);
        if($subjectResults != false) {
            //возвращаем результат
            //var_dump($subjectResults);
            return $subjectResults;
        }

        //если не нашли, то получаем массив результатов парсинга
        $subjectResults = $this->_dbController->FindParsedResultsBySiteUrl($rivalSiteUrl);

        //var_dump($subjectResults);

        /*
         * пробегаемся массив результатов парсинга и сравниваем...
         * все будет добавлено в табилцу сравнений
         */
        /**
         * @var $results ComparisonResult[]
         */
        $results = [];
        $notMatchedArray = [];
        $matchedTrulyArray = [];
        $matchedNotTrulyArray = [];
        foreach($subjectResults as $rivalModel) {
            //echo"1";
            $comparisonRes = null;
            $comparisonRes = $this->_dbController->CompareWithProducts($rivalModel);
            $results[] = $comparisonRes;

            if(empty($comparisonRes->cae)) {
                $notMatchedArray[] = $comparisonRes;
                continue;
            }

            if($comparisonRes->shouldCheckByOperator == false) {
                $matchedTrulyArray[] = $comparisonRes;
                continue;
            } else {
                $matchedNotTrulyArray[] = $comparisonRes;
            }
        }

        echo "Всего сопоставлено: ". count($results) ."<br/>";
        echo "Из них сопоставлено на 100%: ". count($matchedTrulyArray) ."<br/>";
        echo "Из них нужно проверить оператором: ". count($matchedNotTrulyArray) ."<br/>";
        echo "Из них НЕ сопоставлено: ". count($notMatchedArray) ."<br/>";
        //var_dump($results);

        //возвращаем
        return array_merge($matchedTrulyArray, $matchedNotTrulyArray);
    }

    /**
     * Для получения экземпляра IProductsUpdater используй этот метод!
     * @return IProductsUpdater|ProductsUpdater
     */
    protected function GetProductsUpdater() {
        if ($this->_productsUpdater == null) {
            $this->_productsUpdater = new ProductsUpdater($this->_dbController);
        }
        return $this->_productsUpdater;
    }

    /**
     * Установка парсера - он содержит всю логику парсинга!
     * @param RivalParserBase $parser
     * @return $this
     */
    public function InjectParser(RivalParserBase $parser) {
        //todo проверить что объект реализуют необходимый интерфейс или наследуется от нужного класса
        $this->_currentParser = $parser;
        return $this;
    }

	/**
	 * Объект будет использоваться для работы с БД
	 * @param IDbController $controller
	 * @return $this
	 */
    public function InjectDBController(IDbController $controller) {
		//todo проверить что объект реализуют необходимый интерфейс
		$this->_dbController = $controller;
		return $this;
    }

    /**
     * Объект будет использоваться для обновления номенклатуры (нашей)
     * @param IProductsUpdater $productsUpdater
     * @return $this
     */
    public function InjectProductsUpdater(IProductsUpdater $productsUpdater) {
        //todo проверить что объект реализуют необходимый интерфейс
        $this->_productsUpdater = $productsUpdater;
        return $this;
    }

	/**
	 * Выполнение скрипта по пути с помощью nodeJs
	 * @param string $path
	 */
    public function ExecuteNodeJsScript($path) {
        passthru(NODE_VAR_NAME . " " . $path); //выполняем скрипт nodejs по пути $path
    }

	/**
	 * Обработка файла с json данными парсинга
	 * @param string $fileName
     * @deprecated
     * todo в принципе пока не используется, перед использованием проверить функциональность!
	 */
    public function ProcessParsedDataFromFileToDB($fileName) {

        $swapFileContentArr = $this->GetSwapFileContentArray($fileName);
        //print_r($swapFileContentArr); die;
        //$that = $this;
        foreach($swapFileContentArr as $productObj) {
            //echo $productObj->title;die;
            $this->StoreObjectToDB($productObj);
        }

    }

    public function UpdateProducts() {
        $this->_dbController->TruncateOldProductsData();
        $pu = $this->GetProductsUpdater();
        $pu->UpdateProducts();
        return $this;
    }

    /**
     * Выполняет парсинг и обработку результатов, это основной метод для работы
     * @param bool $shouldTruncateOldData
     * @return $this
     * @throws Exception
     */
    public function ProcessParsedDataFromInjectedParserToDB($shouldTruncateOldData = true) {

        //начинаем парсинг
        $this->_currentParser->SetIDbController($this->_dbController);
        $parsedModel = $this->_currentParser->Parse($this->_dbController);

        $this->_lastParseResults = $parsedModel;

        MyLogger::WriteToLog("Final result count is ..." . count($this->_lastParseResults), LOG_ERR);

		//echo "<br/><br/>Cпарсено товаров:" . count($parsedModel) ."<br/><br/>";

        //очищаем предыдущие результаты парсинга по полю site
		if (count($parsedModel) > 0 && $shouldTruncateOldData) {
			$this->_dbController->TruncateOldParseResult($this->_currentParser->GetSiteToParseUrl());
		}

        //записываем результаты парсинга в бд
        foreach($parsedModel as $key => $rivalTireModel) {
            $this->StoreObjectToDB($rivalTireModel);
        }

        return $this;
    }

	/**
	 * Считывание файла c json данными парсинга
	 * @param string $fileName
	 * @return mixed|StdClass[]
	 */
    protected function GetSwapFileContentArray($fileName) {

        if (strpos($fileName, '/') == false && strpos($fileName, '\\') == false) {
            $fileName = "/" . $fileName;
        }

        // преобразуем файл свопа в массив объектов с данными
        $this->_jsonSwapFilePath = getcwd() . PATH_TO_PARSED_FILES . $fileName;
        $fileContent = file_get_contents($this->_jsonSwapFilePath, true);
        $this->_swapFileContentArray = json_decode($fileContent);

        return $this->_swapFileContentArray;
    }

	/**
	 * Сохраняем результат парсинга
	 * @param $object RivalTireModel | StdClass
	 * @throws Exception
	 */
	protected function StoreObjectToDB($object) {
		if ($this->_dbController == null)
			throw new Exception("IDbController should be injected to RivalParserHub!");
		$this->_dbController->AddParsingResult($object);
    }
}