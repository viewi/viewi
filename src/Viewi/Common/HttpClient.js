var HttpClient = function () {
    this.get = function (url, options) {
        var resolver = ajax.get(url, options);
        return resolver;
    };

    this.request = function (type, url, data, options) {
        var resolver = ajax.request(type, url, data, options);
        return resolver;
    };

    this.post = function (url, data, options) {
        var resolver = ajax.post(url, data, options);
        return resolver;
    };

    this.put = function (url, data, options) {
        var resolver = ajax.put(url, data, options);
        return resolver;
    };

    this.delete = function (url, data, options) {
        var resolver = ajax.delete(url, data, options);
        return resolver;
    };
};
