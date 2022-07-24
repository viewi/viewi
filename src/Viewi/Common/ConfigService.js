/**
 * @var Viewi app
 */
var app = bring('viewiApp');
var ConfigService = function () {
    this.getConfig = function () {
        return app.getConfig();
    }
};
exports.ConfigService = ConfigService;
