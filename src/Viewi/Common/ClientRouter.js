/**
 * @var Viewi app
 */
var app = bring('viewiApp');
var ClientRouter = function () {
    this.navigateBack = function () {
        history.back();
    };

    this.navigate = function (url) {
        /**
         * @var Viewi app
         */
        app.go(url, true);
    }

    this.getUrl = function () {
        return location.pathname;
    }
};
exports.ClientRouter = ClientRouter;
