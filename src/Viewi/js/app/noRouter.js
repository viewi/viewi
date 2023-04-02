(function (exports, bring) {
    /**
     * No Router 
     */
    var Router = function () {
        this.setRoutes = function (routeList) {
            routes = routeList;
        };

        this.register = function (method, url, action) {
            return null;
        };

        this.get = function (url, action) {
            return null;
        };

        this.resolve = function (url) {
            return null;
        };
    }
    exports.Router = Router;
})(viewiExports, viewiBring);
