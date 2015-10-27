<?php

/**
 * RivalParserBase
 * Абстрактный класс-основа для написания парсеров
 * TODO: перенести функционал с формированием CURL из KolesoRussiaParser на этот уровень
 * Created by PhpStorm.
 * User: NyoNor
 * Date: 06.10.15
 * Time: 20:00
 */

abstract class RivalParserBase {
    /**
     * @var string
     */
    protected $_siteUrl;

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
     * @param IDbController $dbController
     * @return array RivalTireModel | RivalDiskModel
     */
    public abstract function Parse(IDbController $dbController = null);

    /**
     * Возвращает url сайта для парсинга
     * @return string
     */
    public abstract function GetSiteToParseUrl();
}