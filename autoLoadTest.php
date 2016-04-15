<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.04.16
 * Time: 16:26
 */

require_once "sys/myAutoLoader.php";
require __DIR__ . '/vendor/autoload.php';

$hub = new RivalParseHub();
$hub->InjectDBController(new MysqlDbController())
	->UpdateProducts();