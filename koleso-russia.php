<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 25.09.15
 * Time: 10:57
 */

//настроим выполнение
require_once "include.php";
require_once "base/RivalParserBase.php";
require_once 'base/RivalParseHub.php';
require_once 'KolesoRussiaParser.php';
require_once 'models/RivalTireModel.php';
//header('Content-Type: text/html; charset=windows-1251');
header('Content-Type: text/html; charset=utf-8');

$urlPattern = "http://www.koleso-russia.ru/catalog/search/tires/bysize/?PAGEN_1=%d&AJAX=Y";

$hub = new RivalParseHub();
//$hub->ExecuteNodeJsScript("js/nodeJsKolesoRussia.js");
//$hub->ProcessParsedDataFromFileToDB("kolesoRussiaSwapFile.txt");
$hub->InjectParser(new KolesoRussiaParser($urlPattern))->ProcessParsedDataFromInjectedParserToDB();