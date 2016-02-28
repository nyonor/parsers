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

	public static function WriteObjectWithVarDump($object, $logLevelConstant) {

		ob_start();                    // start buffer capture
		var_dump( $object );           // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		self::WriteToLog( $contents, $logLevelConstant );

	}
}