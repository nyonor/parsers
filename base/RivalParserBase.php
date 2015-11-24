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
     * @var $_dbController IDbController
     */
    protected $_dbController;

    protected $_allModels;

    /**
     * @param $urlPattern string
     * Url сайта для парсинга
     */
    public function __construct($urlPattern) {
        $this->_urlPattern = $urlPattern;
    }

    public function SetIDbController(IDbController $iDbController) {
        $this->_dbController = $iDbController;
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

    /**
     * Возвращает готовый объект curl для запросов
     * @param $url
     * @return resource
     */
    protected abstract function GetCurl($url);
}