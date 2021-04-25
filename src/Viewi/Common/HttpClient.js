var HttpClient = function () {
    this.interceptors = [];
    this.options = {};
    var $this = this;

    this.request = function (type, url, data, options) {
        this.setOptions(options);
        var resolver = ajax.request(type, url, data, this.options);
        if (this.interceptors.length > 0) {
            for (var i = this.interceptors.length - 1; i >= 0; i--) {
                var httpMiddleWare = this.interceptors[i];
                var nextAction = resolver.action;
                resolver = (function (nextAction) {
                    return new OnReady(function (onOk, onError) {
                        httpMiddleWare[0][httpMiddleWare[1]]($this,
                            // next
                            function () {
                                nextAction(onOk, onError);
                            },
                            // onError
                            onError
                        );
                    })
                })(nextAction);
            }
        }
        return resolver;
    };

    this.get = function (url, options) {
        var resolver = $this.request('GET', url, null, options);
        return resolver;
    };

    this.post = function (url, data, options) {
        var resolver = $this.request('POST', url, data, options);
        return resolver;
    };

    this.put = function (url, data, options) {
        var resolver = $this.request('PUT', url, data, options);
        return resolver;
    };

    this.delete = function (url, data, options) {
        var resolver = $this.request('DELETE', url, data, options);
        return resolver;
    };

    this.with = function (interceptor) {
        var client = new HttpClient();
        client.interceptors = this.interceptors.slice();
        client.interceptors.push(interceptor);
        return client;
    }

    this.setOptions = function (options) {
        for (var k in options) {
            this.options[k] = options[k];
        }
    }
};
