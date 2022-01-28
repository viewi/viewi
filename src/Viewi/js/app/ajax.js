function OnReady(func) {
    var $this = this;
    this.action = func;
    this.then = function (onOk, onError) {
        this.onOk = onOk;
        this.onError = onError;
        this.action(function (data) {
            $this.onOk(data);
        }, function (data) {
            $this.onError(data);
        }
        );
    };
    this.catch = function (onError) {
        this.onError = onError;
    };
}
function isFile(data) {
    if ('File' in window && data instanceof File)
        return true;
    else return false;
}

function isBlob(data) {
    if ('Blob' in window && data instanceof Blob)
        return true;
    else return false;
}
var ajax = {
    request: function (type, url, data, options) {
        return new OnReady(function (onOk, onError) {
            var req = new XMLHttpRequest();
            req.onreadystatechange = function () {
                if (req.readyState === 4) {
                    var status = req.status;
                    var contentType = req.getResponseHeader("Content-Type");
                    var itsJson = contentType && contentType.indexOf('application/json') === 0;
                    var content = req.responseText;
                    if (itsJson) {
                        content = JSON.parse(req.responseText);
                    }
                    if (status === 0 || (status >= 200 && status < 400)) {
                        onOk(content);
                    } else {
                        onError(content);
                    }
                }
            }
            req.open(type.toUpperCase(), url, true);
            if (options && options.headers) {
                for (var h in options.headers) {
                    req.setRequestHeader(h, options.headers[h]);
                }
            }
            data !== null ?
                req.send(isBlob(data) ? data : JSON.stringify(data))
                : req.send();
        });
    },
    get: function (url, options) {
        return ajax.request('GET', url, null, options);
    },
    post: function (url, data, options) {
        return ajax.request('POST', url, data, options);
    },
    put: function (url, data, options) {
        return ajax.request('PUT', url, data, options);
    },
    delete: function (url, data, options) {
        return ajax.request('DELETE', url, data, options);
    }
};