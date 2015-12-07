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

    protected $_curlResponseCookies;

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

    public function GetResponseCookies() {
        if ($this->_curlResponseCookies == null) {
            $curl = curl_init($this->GetSiteToParseUrl());
            curl_setopt($curl, CURLOPT_URL, $this->GetSiteToParseUrl());
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curl, CURLOPT_USERAGENT,
                "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36");
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            $response = curl_exec($curl);
            preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
            if (count($matches) > 1)
                $this->_curlResponseCookies = $matches[1][1];
            curl_close($curl);
            sleep(3);
        }

        return $this->_curlResponseCookies;
    }
}