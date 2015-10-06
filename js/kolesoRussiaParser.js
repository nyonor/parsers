/**
 * Created by NyoNor on 22.09.15.
 */

"use strict"

//инициализация phantomjs
var sys = require('system');
var page = require('webpage').create();
var siteUrl = 'http://www.koleso-russia.ru/catalog/search/tires/bysize/';
var exampleSiteUrl = 'http://example.com';
page.settings.loadImages = false;

page.onConsoleMessage = function(msg) {
    console.log(msg + "\n");
};

page.onCallback = function(data){
    if (data.type === "exit") {
        phantom.exit();
    }
};

//открываем страницу и начинаем работу
page.open(siteUrl, function(status){
    page.settings.loadImages = false;
    if(status === "success")
    {
        //console.log("200 OK");
        page.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js",function(){
            //console.log("Jquery 2.1.4 Included!");
            var parsedProducts = page.evaluate(function(){

                var lastIndex = 0;

                function countProducts()
                {
                    return $("table.podbor_po_razmery_cont > tbody > tr > td").size() - 1;
                }

                function parseProducts(fromIndex, toIndex)
                {
                    for (var i = fromIndex; i < toIndex; i++)
                    {
                        console.log($($("table.podbor_po_razmery_cont > tbody > tr > td").get(i)).html());
                    }

                    lastIndex = toIndex;

                    return null;
                }

                var productsCount = countProducts();
                parseProducts(lastIndex, productsCount);
                $("a.scroll_button").click();


               /* var loops = 0;
                do {
                    var totalProductsIndex = countProducts();
                    parseProducts(lastIndex, totalProductsIndex);
                    console.log("Total products index:" + totalProductsIndex);
                    loops++;
                } while (totalProductsIndex > lastIndex && loops < 1)*/

                /*$.ajax({
                    async: false, // this
                    url: '?PAGEN_1=1&AJAX=Y',
                    data: null,
                    type: 'get',
                    success: function (output) {
                        console.log(output);
                        window.callPhantom({type: "exit"});
                    }
                });*/
                phantom.exit();
            });
            //console.log(parsedProducts);
            phantom.exit();
        });
    }
});
