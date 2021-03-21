var HttpClient = function () {

    this.get = function (url) {
        var resolver = ajax.get(url);
        return resolver;
    };

    this.post = function (url, data) {
        var resolver = ajax.post(url, data);
        return resolver;
    };

    this.put = function (url, data) {
        var resolver = ajax.put(url, data);
        return resolver;
    };

    this.delete = function (url, data) {
        var resolver = ajax.delete(url, data);
        return resolver;
    };
};
