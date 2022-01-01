// console.log('app.js included');
// load /public/app/build/components.json

var app = new Viewi();
var notify = app.notify;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { app.start(); });
} else {
    // setTimeout(function () {
    //     app.start();
    // }, 1000);
    app.start();
}
