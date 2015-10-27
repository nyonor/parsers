<?php
/**
 * RivalParseHub
 * Может осуществлять обработку результатов парсеров RivalParserBase
 * Может считывать в качестве результатов файлы содержащие json массив с объектами результатов парсинга
 * Может запускать nodeJs скрипты (предполагается, что скрипт будет парсить сайт и создавать файл с json для
 * дальнейшей обработки)
 *
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 01.10.15
 * Time: 10:43
 */

//некоторые константы
define ("NODE_VAR_NAME", "node");
define ("PATH_TO_PARSED_FILES", "/files");


class RivalParseHub {

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

    //todo делаю
    public function GetComparingResultAsCsv($rivalSiteUrl) {

        //echo "HI!";
        $subjectResults = $this->_lastParseResults;
        if ($subjectResults == null) {
            //throw new Exception("No parsed results!"); //todo достать из базы
            //echo "here";die;
            $subjectResults = $this->_dbController->FindParsedResultsBySiteUrl($rivalSiteUrl);
            //var_dump($subjectResults);
        }

        // echo "2";die;
        foreach($subjectResults as $rivalModel) {
            //var_dump($rivalModel);
            $this->_dbController->FindInProducts($rivalModel);
        }

    }

    /**
     * Для получения экземпляра IProductsUpdater используй этот метод!
     * @return IProductsUpdater|ProductsUpdater
     */
    public function GetProductsUpdater() {
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

    /**
     * Выполняет парсинг и обработку результатов, это основной метод для работы
     * @throws Exception
     */
    public function ProcessParsedDataFromInjectedParserToDB() {
        //обновим номенклатуру если свойство == true
        if ($this->shouldUpdateProductsBeforeParsingResults) {
            $pu = $this->GetProductsUpdater();
            $pu->UpdateProducts();
        }

        $parsedModel = $this->_currentParser->Parse($this->_dbController);

        $this->_lastParseResults = $parsedModel;

		echo count($parsedModel);

		if (count($parsedModel) > 0) {
			$this->_dbController->TruncateOldParseResult($this->_currentParser->GetSiteToParseUrl());
		}

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

    //todo нет проверки типа и остатков, пишется в таблицу с шинами...
	/*
    protected function StoreObjectToDB($object) {
        $insertSql =
			"INSERT INTO tyres SET
			company = '".$object->brand."',
			model = '".$object->model."',
			d = ".$object->diameter.",
			sh = ".$object->width.",
			prof = '".$object->height."',
			speed_index = '".$object->speedIndex."',
			loading_index = ".$object->loadIndex.",
			type = 1,
			price = ".$object->price.",
			site = '".$object->site."',
			url = '".$object->url."',
			date = NOW(),
			runflat = ".$object->runflat.",
			rest = 1";

        //echo $object->title . "<br/>";
        //echo $insertSql . "<br/><br/>";
        //return false;

        $selectSql =
            "SELECT id FROM tyres WHERE
            company = '".$object->brand."'
            AND model = '".$object->model."'
            AND d = ".$object->diameter."
            AND sh = ".$object->width."
            AND prof = '".$object->height."'
            AND speed_index = '".$object->speedIndex."'
            AND loading_index = ".$object->loadIndex."
            AND type = 1
            AND price = ".$object->price."
            AND site = '".$object->site."'
            AND url = '".$object->url."'
            AND runflat = ".$object->runflat."
            AND rest = 1";

        //echo $selectSql;
        //die;

        $selectResult = mysql_query($selectSql);
        //echo "checl!!!";
        echo "<fieldset><legend>".$object->title."</legend>";
        echo "<br/>Проверяю... ". $selectSql . "...нашел? ==> " .mysql_num_rows($selectResult)." <br/>";
        //echo mysql_num_rows($selectResult); die;
        if(mysql_num_rows($selectResult) == 0)
        {
            //echo "CHECK!";
            $insertResult = mysql_query($insertSql);
            echo sprintf("Пишу... %s <br/> ... Получилось? ==> %d <br/><br/>", $insertSql, $insertResult);
        }
        echo "</fieldset>";
//die;
        //print_r($result);
        //die;

        //if ($result)
    }*/
}