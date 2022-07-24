(function (exports, bring) {
    // register compiled components
    viewiBundleEntry(exports, bring);
    // create an app
    var Viewi = bring('Viewi');
    var app = new Viewi();
    exports.viewiApp = app;
    var notify = app.notify;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { app.start(); });
    } else {
        // setTimeout(function () {
        //     app.start();
        // }, 1000);
        app.start();
    }
})(viewiExports, viewiBring);
