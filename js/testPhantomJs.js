/**
 * Created by NyoNor on 21.09.15.
 */

var page = require('webpage').create();
page.settings.loadImages = false;

page.onConsoleMessage = function(msg) {
    console.log(msg + "\n");
};

/* Global error handler for phantom */
phantom.onError = function(msg, trace) {
    var msgStack = ['PHANTOM ERROR: ' + msg];
    if (trace) {
        msgStack.push('TRACE:');
        trace.forEach(function(t) {
            msgStack.push(' -> ' + (t.file || t.sourceURL) + ': ' + t.line);
        });
    }
    util.log.error(msgStack.join('\n'));

    // exit phantom on error
    phantom.exit();
};


page.open("http://www.koleso-russia.ru/catalog/search/tires/bysize/?PAGEN_1=1&AJAX=Y", function(status) {
    console.log("Status: " + status);
    if(status === "success") {
        page.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js",function(){
            page.evaluate(function() {
                for (var i = 0; i < 29; i++)
                {
                    console.log($($(">td").get(i)).html());
                }
            });
            phantom.exit();
        });
    }
});
