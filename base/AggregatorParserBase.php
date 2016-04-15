<?php

/**
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 01.03.16
 * Time: 9:12
 */
abstract class AggregatorParserBase extends RivalParserBase
{
	protected $_iInstantStore;

	public function __construct($urlPattern, IInstantStore $parseHub) {

		parent::__construct($urlPattern);
		$this->_iInstantStore = $parseHub;

	}

	/**
	 * Постпроцессинг данных собранных парсером-аггрегатора
	 * @return mixed
	 */
	public abstract function PostProcess();

}