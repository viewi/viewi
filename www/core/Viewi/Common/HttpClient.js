var HttpClient = function () {

    this.get = function (url) {
        var resolver = ajax.get(url);
        return resolver;
    };
};
