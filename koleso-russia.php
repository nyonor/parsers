<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 25.09.15
 * Time: 10:57
 */

require_once 'base/RivalParseHub.php';
require_once 'KolesoRussiaParser.php';

$urlPattern = "http://www.koleso-russia.ru/catalog/search/tires/bysize/?PAGEN_1=%d&AJAX=Y";

$hub = new RivalParseHub();
//$hub->ExecuteNodeJsScript("js/nodeJsKolesoRussia.js");
//$hub->ProcessParsedDataFromFileToDB("kolesoRussiaSwapFile.txt");
$hub
    ->InjectParser(
        new KolesoRussiaParser($urlPattern)
    );