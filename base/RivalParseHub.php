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

//настроим выполнение
require_once "../include.php";
require_once "../base/RivalParserBase.php";

//некоторые константы
define ("NODE_VAR_NAME", "node");
define ("PATH_TO_PARSED_FILES", "/files");


class RivalParseHub {

	/**
	 * @var string путь к файлу с данными результатов парсинга
	 */
    protected $jsonSwapFilePath;

    /**
     * @var StdClass[]
     */
    protected $swapFileContentArray;

    /**
     * @var RivalParserBase
     */
    protected $currentParser;

	/**
	 * @param int $scriptMaxExecutionTime
	 */
    public function __construct($scriptMaxExecutionTime = 0) {

        set_time_limit($scriptMaxExecutionTime); //парсинг обычно выполняется не быстро

    }

	/**
     * Установка парсера - он содержит всю логику парсинга!
     * @param RivalParserBase $parser
     */
    public function InjectParser(RivalParserBase $parser) {
        $this->currentParser = $parser;
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
	 * Считывание файла c json данными парсинга
	 * @param string $fileName
	 * @return mixed|StdClass[]
	 */
    protected function GetSwapFileContentArray($fileName) {

        if (strpos($fileName, '/') == false && strpos($fileName, '\\') == false) {
            $fileName = "/" . $fileName;
        }

        // преобразуем файл свопа в массив объектов с данными
        $this->jsonSwapFilePath = getcwd() . PATH_TO_PARSED_FILES . $fileName;
        $fileContent = file_get_contents($this->jsonSwapFilePath, true);
        $this->swapFileContentArray = json_decode($fileContent);

        return $this->swapFileContentArray;
    }

    //todo нет проверки типа и остатков, пишется в таблицу с шинами...
	/**
	 * Процедура проверок и сохранения результатов парсинга в БД
	 * @param StdClass $object
	 */
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
    }
}