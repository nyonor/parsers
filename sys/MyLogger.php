<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 31.12.15
 * Time: 15:05
 */

class MyLogger
{
	public static function WriteToLog($message, $logLevelConstant) {

		openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
		syslog($logLevelConstant, $message);
		closelog();

	}
}