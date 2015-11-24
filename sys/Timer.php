<?php
/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 05.11.15
 * Time: 13:51
 */

namespace sys;


class Timer
{
	public static function Start() {
		return microtime(true);
	}

	public static function StopAndResult($timeStart) {
		$timeEnd = microtime(true);
		return $timeEnd - $timeStart;
	}
}