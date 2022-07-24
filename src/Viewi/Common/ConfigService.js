var ConfigService = function () {
    this.getConfig = function () {
        /**
         * @var Viewi app
         */
        var app = bring('viewiApp');
        return app.getConfig();
    }
};
exports.ConfigService = ConfigService;
