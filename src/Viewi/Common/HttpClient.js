var HttpClient = function () {

    this.get = function (url) {
        var resolver = ajax.get(url);
        return resolver;
    };

    this.post = function (url, data) {
        var resolver = ajax.post(url, data);
        return resolver;
    };
};
