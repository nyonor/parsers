/**
 * Created by NyoNor on 15.11.15.
 */
/**
 * Created by NyoNor on 28.09.15.
 */

//инциализируем ресурсы
var request = require("request");
var cheerio = require("cheerio");
var iconv = require('iconv-lite');
var async = require('async');
var trim = require('trim');
var fs = require('fs');

//начальные условия
var results = [];
var currentLoop = 0;
var maxLoop = 0; //пока не все
var isEmpty = true;
var url = ""; //инициализируется ниже

(function() {

    function getFirstNotEmptyElement (array) {
        for (var i = 0 ; i < array.length; i++) {
            if (array[i] != undefined && array[i] != null && array[i].length > 0) {
                return array[i];
            }
        }
    }

    function saveToFile() {
        //console.log(__dirname);
        fs.writeFile("./files/kolesoRussiaSwapFile.txt", JSON.stringify(results), function(err){

            if (err) {
                return console.log(err);
            }

            console.log("<br/>FILE WAS CREATED!<br/>");
        });
    }

    function getBrand(title) {
        var splittedBySpaces = title.split(' ');

        var result = "";

        if (splittedBySpaces[0] == splittedBySpaces[0].toUpperCase()) {
            result = result + splittedBySpaces[0];
        }
        if(splittedBySpaces[1] == splittedBySpaces[1].toUpperCase()) {
            result = result + " " + splittedBySpaces[1];
        }

        return result;
    }

    function parseProperties($mainconteinercenter) {

        var result = {};
        var title = $mainconteinercenter.find("a.title").text();
        var price = parseInt($mainconteinercenter.find("span.discount").text().replace(' ', ''));
        result.title = title;
        result.price = price;
        console.log("TITLE IS ==>" + title + "<==TITLE IS");

        //удалим каки
        $mainconteinercenter.find(".contentblock").children("a, table").remove();

        //разбираем...
        result.brand = getBrand(title).toLowerCase();
        var text = $mainconteinercenter.find(".contentblock").text();
        //var somePropertiesMatches =
        //    text.match(new RegExp('.+:\\s+(.+\\S)\\s+.+:\\s+(.+\\S)\\s+.+:\\s(.+\\S)\\s+(.+\\S)\\s+.+:\\s+(.+\\S)'));
        result.model = text.match(new RegExp('Модель:\\s*(.+)\\s+'))[1];
        result.diameter = getFirstNotEmptyElement(title.match(new RegExp('(?=C(\\d+)|R(\\d+)|LT(\\d+))')));
        var widthAndHeight = title.match(new RegExp('(\\d+)(?:x|\\/)(\\d+\\,\\d+|\\d+\\.\\d+|\\d+)|\\s+?(\\d+)\\s+?(?:R\\d*|LT\\d*|lt\\d*|C\\d*)'));
        result.width = widthAndHeight[1];
        if(widthAndHeight != null && widthAndHeight[2] !== undefined)
            result.height = widthAndHeight[2];
        var speedIndexAndLoadIndex =
            title.match(new RegExp('\\s+?(\\d|\\d\\d|\\d\\d\\d|\\d+\\/\\d+)([a-zA-Z]|\\([a-zA-Z]\\))\\s*'));
        if (speedIndexAndLoadIndex != null && speedIndexAndLoadIndex.length >= 2+1)
            result.speedIndex = speedIndexAndLoadIndex[2];

        if (speedIndexAndLoadIndex && speedIndexAndLoadIndex.length >= 1+1)
            result.loadIndex = parseInt(speedIndexAndLoadIndex[1]);

        result.runflat = title.toLowerCase().indexOf('flat') != -1 ? 1 : 0;
        result.url = url;
        result.site = "koleso-russia.ru";
        //console.log("<br/> ->" + result.runflat + "<br/>");

        return result;
    }

    //здесь выполняется асинхронный луп, по типу do - while
    async.doWhilst(
        //выполняется на каждом шаге
        function (callback) {

            //это урл асинхронного вызова
            url = "http://www.koleso-russia.ru/catalog/search/tires/bysize/?PAGEN_1=" + currentLoop + "&AJAX=Y";

            //вызовем
            request.get(
                {
                    "uri": url,
                    "encoding": "binary"
                },
                //обработчик ответа
                function (error, response, body) {
                    if (!error) {

                        //если ответ не пустой
                        if (body.length > 0) {
                            isEmpty = false;
                            console.log("<br/>Body length is: " + body.length + "<br/>");
                        }

                        if (body.length == 27666) {
                            isEmpty = true;
                        }

                        //декодируем из binary в windows-1251 и в utf8
                        var result = iconv.encode(iconv.decode(new Buffer(body, 'binary'), 'windows-1251'), 'utf8');

                        //инциализируем jquery-подобное cheerio
                        var $ = cheerio.load(result);

                        //ищем 404
                        if ($.contains("Ошибка 404. Страница не найдена")) {
                            isEmpty = true;
                        }

                        //начнем парсинг, пробежимся по этому добру
                        $(".mainconteinercenter").each(function (index) {
                            var result = parseProperties($(this));
                            //console.log(sprintf("%s %s %s", result.title, result.brand, result.model));
                            console.log(result.title + " ===> " + result.price + "<br/>");
                            results.push(result);
                        });

                        currentLoop++;

                        //а это для того, чтобы имитировать нажатие пользователем (ну хоть немного)
                        var interval = setInterval(function () {
                            callback();
                            clearInterval(interval);
                        }, Math.random() * (1000 - 500) + 500);

                    } else {
                        console.log("We’ve encountered an error: " + error);
                    }

                    console.log("currentLoop <= maxLoop: " + currentLoop <= maxLoop);
                    if (isEmpty == true) {
                        //saveToDb();
                        saveToFile();
                    }
                }
            );
        },
        //условия продолжения цикла
        function () {
            console.log("<br/>Current loop is: " + currentLoop + "<br/>");
            return isEmpty == false && (maxLoop == 0 || currentLoop <= maxLoop)
        },
        //если ошибка
        function (error) {
            if (error) {
                console.log(error);
            }
        }
    );
})();