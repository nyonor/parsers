<?php
//header('Content-Type: text/html; charset=utf8');
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 17.09.15
 * Time: 16:42
 */

$mongo = new MongoClient();
$collection = $mongo->selectCollection("RivalParsingDB", "RivalParsedResults");
$collection->insert(['field1' => 'value1']);

print_r($collection);die;


ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);
//todo увеличить время выполнения скрипта!!!

$parserScriptUrlsArray = [
    "koleso-russia.ru" => "kolesoRussiaParser.js", //первый вариант - слишком медленный
    "testPageParser" => "testPhantomJs.js", // просто тест
    "koleso-russia-node-js" => "nodeJsKolesoRussia.js" //оно самое - рабочий вариант!
];

//выберем нужный скрипт на основе переменной в GET
$parserName = $parserScriptUrlsArray[$_GET['siteUrl']];

//сюда будем выводить то, что вернет exec (просто для наглядности)
$result = [];

//в данном случае будем использоватье nodejs
if($parserScriptUrlsArray[$_GET['siteUrl']] == "nodeJsKolesoRussia.js") {
    printf("Using nodejs to parse! </br></br>");
    exec("node js/".$parserName, $result);
}
//юзал в начале - это медленно!
else {
    printf("Parsing %s with PhantomJS", $parserName);
    exec("/home/parallels/phantomjs/phantomjs js/".$parserName, $result);
}

//выводим результаты на экран
foreach($result as $k => $v)
{
    echo "$v";
}

//print_r($result);