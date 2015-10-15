<?php

/**
 * RivalParserBase
 * Абстрактный класс-основа для написания парсеров
 *
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.10.15
 * Time: 20:00
 */

abstract class RivalParserBase {
    /**
     * @var $urlPattern string
     */
    protected $_urlPattern;

    /**
     * @param $urlPattern string
     * Url сайта для парсинга
     */
    public function __construct($urlPattern) {
        $this->_urlPattern = $urlPattern;
    }

    /**
     * Запуск парсинга сайта по переданному $urlPattern
     * @return array RivalTireModel | RivalDiskModel
     */
    public abstract function Parse();
}